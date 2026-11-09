<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Ticket;

/** Dashboard teknisi: ringkasan kerja pribadi. */
class HomeController extends Controller
{
    public function index()
    {
        $u = auth()->user();
        $mine = Ticket::where('technician_id', $u->id);

        $stats = [
            'baru'      => (clone $mine)->whereIn('status', ['ditugaskan', 'dilaporkan'])->count(),
            'proses'    => (clone $mine)->where('status', 'diproses')->count(),
            'selesai'   => (clone $mine)->where('status', 'selesai')->whereMonth('finished_at', now()->month)->count(),
            'terlambat' => (clone $mine)->where('status', '!=', 'selesai')->whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];

        // Tugas perbaikan saya: tugas yang baru di-acc/diambil berada di paling atas, yang selesai turun ke bawah
        $priority = Ticket::with('machine')->where('technician_id', $u->id)
            ->orderByRaw("CASE WHEN status = 'selesai' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CASE WHEN status = 'selesai' THEN finished_at ELSE COALESCE(started_at, updated_at, created_at) END DESC")
            ->take(6)->get();

        // Jadwal maintenance saya: yang aktif/baru di-acc berada di atas, yang selesai turun ke bawah
        $schedules = MaintenanceSchedule::with('machine')->where('technician_id', $u->id)
            ->orderByRaw("CASE WHEN status = 'selesai' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CASE WHEN status = 'selesai' THEN COALESCE(completed_at, updated_at) ELSE updated_at END DESC")
            ->take(6)->get();

        $recent = Ticket::with('machine')->where('technician_id', $u->id)->where('status', 'selesai')
            ->latest('finished_at')->take(3)->get();

        // pengingat sesuai aturan notifikasi: H-7, H-1, hari-H, terlambat (hanya jadwal yang BELUM selesai dan memiliki tanggal)
        $reminders = $schedules->filter(fn ($s) => $s->status !== 'selesai' && !empty($s->due_date))->map(function ($s) {
            $d = (int) now()->startOfDay()->diffInDays($s->due_date, false);
            $tag = match (true) {
                $d < 0 => ['Terlambat '.abs($d).' hari', 'late'],
                $d === 0 => ['Hari ini', 'today'],
                $d <= 1 => ['H-1', 'soon'],
                $d <= 7 => ['H-'.$d, 'soon'],
                default => null,
            };
            return $tag ? ['s' => $s, 'label' => $tag[0], 'tone' => $tag[1]] : null;
        })->filter()->values();

        // jumlah selesai 7 hari terakhir untuk mini-chart
        $week = collect(range(6, 0))->map(fn ($i) => [
            'd' => now()->subDays($i)->translatedFormat('D'),
            'n' => Ticket::where('technician_id', $u->id)->whereDate('finished_at', now()->subDays($i))->count(),
        ]);

        $availableTickets = Ticket::with('machine')->whereNull('technician_id')->where('status', '!=', 'selesai')->latest('id')->get();
        $availableCount = $availableTickets->count();
        $latestAvailable = $availableTickets->first();

        return view('teknisi.home', compact('stats', 'priority', 'schedules', 'recent', 'reminders', 'week', 'availableCount', 'latestAvailable'));
    }

    /**
     * Endpoint polling notifikasi real-time untuk teknisi.
     */
    public function liveAlerts()
    {
        $openTickets = Ticket::with('machine')
            ->whereNull('technician_id')
            ->where('status', '!=', 'selesai')
            ->latest('id')
            ->get();

        $latest = $openTickets->first();
        $openIds = $openTickets->pluck('id')->values()->all();

        return response()->json([
            'count'    => $openTickets->count(),
            'open_ids' => $openIds,
            'max_id'   => $openTickets->max('id') ?? 0,
            'latest'   => $latest ? [
                'id'           => $latest->id,
                'code'         => $latest->code,
                'machine'      => $latest->machine?->name ?? 'Mesin',
                'machine_code' => $latest->machine?->code ?? '',
                'location'     => $latest->machine?->location ?? '-',
                'urgency'      => $latest->urgency,
                'complaint'    => \Illuminate\Support\Str::limit($latest->complaint, 60),
                'claim_url'    => route('teknisi.tugas.show', $latest),
            ] : null,
        ]);
    }
}
