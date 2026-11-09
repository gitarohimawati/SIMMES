<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    protected $guarded = [];
    protected $casts = ['due_date' => 'date', 'completed_at' => 'date'];

    public function machine()    { return $this->belongsTo(Machine::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function ticket()     { return $this->belongsTo(Ticket::class); }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'selesai') return 'selesai';
        if ($this->due_date && $this->due_date->isPast() && !$this->due_date->isToday()) return 'terlambat';
        return $this->status ?? 'terjadwal';
    }

    public function getFormattedScheduleAttribute(): string
    {
        $date = $this->due_date ? $this->due_date->translatedFormat('d M Y') : '-';
        if ($this->due_time) {
            return "{$date} · {$this->due_time} WIB";
        }
        return $date;
    }

    public function getBeforeUrlAttribute(): ?string
    {
        return $this->before_path ? \Illuminate\Support\Facades\Storage::url($this->before_path) : null;
    }

    public function getAfterUrlAttribute(): ?string
    {
        return $this->after_path ? \Illuminate\Support\Facades\Storage::url($this->after_path) : null;
    }

    /** Tanggal jatuh tempo berikutnya setelah selesai (berdasar interval). */
    public function nextDue(): ?\Illuminate\Support\Carbon
    {
        $m = $this->interval_months ?: $this->machine?->maintenance_interval_months;
        return $m ? ($this->completed_at ?? now())->copy()->addMonths($m) : null;
    }
}
