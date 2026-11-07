<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\MaintenanceSchedule;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $open = Ticket::where('status', '!=', 'selesai');

        $stats = [
            'aktif'      => Machine::where('status', 'aktif')->count(),
            'diperbaiki' => Ticket::where('status', 'diproses')->count(),
            'menunggu'   => Ticket::whereNull('technician_id')->where('status', '!=', 'selesai')->count(),
            'terlambat'  => (clone $open)->whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];

        $tickets = Ticket::with(['machine', 'technician', 'media', 'logs'])->latest()->take(6)->get();

        // Data distribusi tiket untuk grafik donut enam bulan terakhir.
        $trend = collect(range(5, 0))->map(function ($i) {
            $m = now()->subMonths($i);
            return [
                'label' => $m->translatedFormat('M'),
                'count' => Ticket::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
            ];
        });

        // Pekerjaan yang masih perlu dilakukan; siklus baru otomatis masuk ke sini setelah periode sebelumnya selesai.
        $upcoming = MaintenanceSchedule::with(['machine.tickets.media', 'machine.tickets.logs', 'technician', 'ticket.media', 'ticket.logs'])
            ->where('status', '!=', 'selesai')
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->take(6)
            ->get();

        // Riwayat pekerjaan yang sudah dituntaskan, dipisahkan dari antrian aktif.
        $completedSchedules = MaintenanceSchedule::with(['machine', 'technician'])
            ->where('status', 'selesai')
            ->latest('completed_at')
            ->take(6)
            ->get();

        return view('manajemen.dashboard', compact('stats', 'tickets', 'trend', 'upcoming', 'completedSchedules'));
    }

    /**
     * Uji coba pengiriman pesan WhatsApp Gateway secara langsung.
     */
    public function testWaGateway()
    {
        $user = auth()->user();
        $target = $user->whatsapp ?: (config('services.whatsapp.target') ?: env('WA_TARGET_PHONE'));

        if (empty($target)) {
            return back()->with('err', 'Nomor WhatsApp belum diatur di profil Anda atau di file .env (WA_TARGET_PHONE).');
        }

        $nowStr = now()->translatedFormat('d M Y, H:i');
        $msg = "✅ *SIMMES - UJI COBA KONEKSI WHATSAPP GATEWAY*\n\n"
            . "Halo *{$user->name}*,\n"
            . "Pesan ini membuktikan bahwa Gateway WhatsApp SIMMES telah berhasil terhubung dengan perangkat pengirim dan siap mengirim notifikasi perbaikan serta pemeliharaan mesin.\n\n"
            . "🕒 Waktu Uji Coba: {$nowStr} WIB\n"
            . "📱 Nomor Tujuan: {$target}\n\n"
            . "_SIMMES · Sistem Informasi Maintenance Mesin_";

        $res = \App\Services\WhatsAppService::send($target, $msg);

        if ($res['success']) {
            $text = ($res['simulated'] ?? false)
                ? "Simulasi kirim WA dicatat di log sistem (Token belum diatur di .env)."
                : "Pesan uji coba WhatsApp BERHASIL dikirim ke {$target}!";
            return back()->with('ok', $text);
        }

        return back()->with('err', "Uji coba WhatsApp gagal: {$res['message']}");
    }
}
