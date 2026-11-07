<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = MaintenanceSchedule::with('machine')->where('technician_id', auth()->id())
            ->orderByRaw("CASE WHEN status = 'selesai' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CASE WHEN status = 'selesai' THEN COALESCE(completed_at, updated_at) ELSE updated_at END DESC")
            ->get();
        return view('teknisi.jadwal.index', compact('schedules'));
    }

    /** Update progres 25/50/75/100 untuk pekerjaan rutin. */
    public function progress(Request $r, MaintenanceSchedule $jadwal)
    {
        abort_unless($jadwal->technician_id === auth()->id(), 403);
        $d = $r->validate(['progress' => 'required|in:25,50,75,100', 'spareparts' => 'nullable|max:120']);
        $jadwal->update([
            'progress' => $d['progress'], 'spareparts' => $d['spareparts'] ?? $jadwal->spareparts,
            'status' => $d['progress'] == 100 ? 'selesai' : 'dikerjakan',
            'completed_at' => $d['progress'] == 100 ? now() : null,
        ]);
        if ($d['progress'] == 100) {
            $jadwal->machine->update(['last_service_at' => now()]);
            if ($next = $jadwal->nextDue()) {
                MaintenanceSchedule::create([
                    'machine_id' => $jadwal->machine_id, 'technician_id' => $jadwal->technician_id, 'type' => 'rutin',
                    'task' => $jadwal->task, 'interval_label' => $jadwal->interval_label,
                    'interval_months' => $jadwal->interval_months, 'due_date' => $next,
                    'due_time' => $jadwal->due_time ?: '09:00',
                ]);
            }
        }
        return back()->with('ok', 'Progress jadwal diperbarui.');
    }
}
