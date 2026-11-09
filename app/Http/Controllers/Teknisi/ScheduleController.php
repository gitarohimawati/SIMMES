<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $r)
    {
        $userId = auth()->id();
        $tab = $r->get('tab', 'aktif');

        $base = MaintenanceSchedule::with('machine')->where('technician_id', $userId);

        $countActive = (clone $base)->where('status', '!=', 'selesai')->count();
        $countDone   = (clone $base)->where('status', 'selesai')->count();

        if ($tab === 'selesai') {
            $schedules = (clone $base)->where('status', 'selesai')
                ->orderByDesc('completed_at')
                ->orderByDesc('updated_at')
                ->paginate(12);
        } else {
            // Aktif / perlu dikerjakan: yang terlambat & hari ini ditaruh di paling atas
            $schedules = (clone $base)->where('status', '!=', 'selesai')
                ->orderBy('due_date')
                ->orderBy('due_time')
                ->paginate(12);
        }

        $manager = User::where('role', 'manajemen')->whereNotNull('whatsapp')->first();
        $managerPhone = $manager?->whatsapp;

        $openSchedule = null;
        if ($r->filled('open')) {
            $openSchedule = MaintenanceSchedule::with('machine')->where('technician_id', $userId)->find($r->open);
        }

        return view('teknisi.jadwal.index', compact('schedules', 'countActive', 'countDone', 'tab', 'managerPhone', 'openSchedule'));
    }

    public function show(MaintenanceSchedule $jadwal)
    {
        abort_unless($jadwal->technician_id === auth()->id(), 403);
        $tab = $jadwal->status === 'selesai' ? 'selesai' : 'aktif';
        return redirect()->route('teknisi.jadwal', ['tab' => $tab, 'open' => $jadwal->id]);
    }

    /** Unggah foto bukti sebelum / sesudah pengerjaan maintenance. */
    public function upload(Request $r, MaintenanceSchedule $jadwal)
    {
        abort_unless($jadwal->technician_id === auth()->id(), 403);

        $r->validate([
            'phase' => 'required|in:before,after',
            'photo' => 'required|image|max:20480',
        ], [
            'photo.required' => 'Pilih atau potret foto terlebih dahulu.',
            'photo.image'    => 'File yang dipilih harus berupa foto/gambar.',
            'photo.max'      => 'Ukuran foto maksimal 20MB.',
        ]);

        $phase = $r->input('phase');
        $path = $r->file('photo')->store("maintenance/{$jadwal->id}", 'public');

        if ($phase === 'before') {
            $jadwal->before_path = $path;
            if ($jadwal->status === 'terjadwal') {
                $jadwal->status = 'dikerjakan';
            }
            $jadwal->progress = max($jadwal->progress, 25);
            $jadwal->save();
        } else {
            $jadwal->after_path = $path;
            if ($jadwal->status === 'terjadwal') {
                $jadwal->status = 'dikerjakan';
            }
            $jadwal->progress = max($jadwal->progress, 75);
            $jadwal->save();
        }

        $label = $phase === 'before' ? 'sebelum perawatan' : 'sesudah perawatan';
        $tab = $jadwal->status === 'selesai' ? 'selesai' : 'aktif';
        return redirect()->route('teknisi.jadwal', ['tab' => $tab, 'open' => $jadwal->id])
            ->with('ok', "Foto {$label} berhasil diunggah.");
    }

    /** Update progres 25/50/75/100 untuk pekerjaan rutin. */
    public function progress(Request $r, MaintenanceSchedule $jadwal)
    {
        abort_unless($jadwal->technician_id === auth()->id(), 403);
        $d = $r->validate([
            'progress'   => 'required|in:25,50,75,100',
            'spareparts' => 'nullable|max:255',
            'notes'      => 'nullable|max:1000',
        ]);

        $jadwal->update([
            'progress'     => $d['progress'],
            'spareparts'   => $d['spareparts'] ?? $jadwal->spareparts,
            'notes'        => $d['notes'] ?? $jadwal->notes,
            'status'       => $d['progress'] == 100 ? 'selesai' : 'dikerjakan',
            'completed_at' => $d['progress'] == 100 ? now() : null,
        ]);

        if ($d['progress'] == 100) {
            $jadwal->machine->update(['last_service_at' => now(), 'status' => 'aktif']);
            if ($next = $jadwal->nextDue()) {
                MaintenanceSchedule::create([
                    'machine_id'      => $jadwal->machine_id,
                    'technician_id'   => $jadwal->technician_id,
                    'type'            => 'rutin',
                    'task'            => $jadwal->task,
                    'interval_label'  => $jadwal->interval_label,
                    'interval_months' => $jadwal->interval_months,
                    'due_date'        => $next,
                    'due_time'        => $jadwal->due_time ?: '09:00',
                    'status'          => 'terjadwal',
                    'progress'        => 0,
                ]);
            }
        }

        $tab = $jadwal->status === 'selesai' ? 'selesai' : 'aktif';
        return redirect()->route('teknisi.jadwal', ['tab' => $tab, 'open' => $jadwal->id])
            ->with('ok', 'Progress jadwal diperbarui.');
    }

    /** Selesaikan maintenance lengkap dengan catatan dan foto hasil. */
    public function complete(Request $r, MaintenanceSchedule $jadwal)
    {
        abort_unless($jadwal->technician_id === auth()->id(), 403);

        $d = $r->validate([
            'spareparts'   => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:1000',
            'before_photo' => 'nullable|image|max:20480',
            'after_photo'  => 'nullable|image|max:20480',
        ], [
            'before_photo.image' => 'File foto sebelum harus berupa gambar/foto.',
            'after_photo.image'  => 'File foto sesudah harus berupa gambar/foto.',
            'before_photo.max'   => 'Ukuran foto maksimal 20MB.',
            'after_photo.max'    => 'Ukuran foto maksimal 20MB.',
        ]);

        if ($r->hasFile('before_photo')) {
            $path = $r->file('before_photo')->store("maintenance/{$jadwal->id}", 'public');
            $jadwal->before_path = $path;
        }

        if ($r->hasFile('after_photo')) {
            $path = $r->file('after_photo')->store("maintenance/{$jadwal->id}", 'public');
            $jadwal->after_path = $path;
        }

        $jadwal->spareparts   = !empty($d['spareparts']) ? trim($d['spareparts']) : $jadwal->spareparts;
        $jadwal->notes        = !empty($d['notes']) ? trim($d['notes']) : $jadwal->notes;
        $jadwal->progress     = 100;
        $jadwal->status       = 'selesai';
        $jadwal->completed_at = now();
        $jadwal->save();

        // Update mesin status dan last_service_at
        $jadwal->machine->update([
            'last_service_at' => now(),
            'status'          => 'aktif',
        ]);

        // Otomatis jadwalkan siklus berikutnya bila merupakan jadwal rutin
        if ($next = $jadwal->nextDue()) {
            MaintenanceSchedule::create([
                'machine_id'      => $jadwal->machine_id,
                'technician_id'   => $jadwal->technician_id,
                'type'            => 'rutin',
                'task'            => $jadwal->task,
                'interval_label'  => $jadwal->interval_label,
                'interval_months' => $jadwal->interval_months,
                'due_date'        => $next,
                'due_time'        => $jadwal->due_time ?: '09:00',
                'status'          => 'terjadwal',
                'progress'        => 0,
            ]);
        }

        return redirect()->route('teknisi.jadwal', ['tab' => 'selesai', 'open' => $jadwal->id])
            ->with('ok', 'Pekerjaan maintenance berhasil diselesaikan dan dicatat di sistem!')
            ->with('just_completed', true);
    }
}
