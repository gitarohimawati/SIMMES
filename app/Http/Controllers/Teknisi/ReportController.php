<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Ticket;
use App\Models\TicketMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Tampilkan daftar laporan perbaikan teknisi (Read / Index).
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $filter = $request->get('f', 'semua');
        $search = trim($request->get('q', ''));

        $query = Ticket::with(['machine', 'media', 'logs'])
            ->where(function ($q) use ($userId) {
                $q->where('reporter_id', $userId)
                  ->orWhere('technician_id', $userId);
            })
            ->latest();

        match ($filter) {
            'dilaporkan' => $query->whereIn('status', ['dilaporkan', 'ditugaskan']),
            'diproses'   => $query->where('status', 'diproses'),
            'selesai'    => $query->where('status', 'selesai'),
            default      => null,
        };

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('complaint', 'like', "%{$search}%")
                  ->orWhereHas('machine', function ($m) use ($search) {
                      $m->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(10)->withQueryString();

        $statsQuery = Ticket::where(function ($q) use ($userId) {
            $q->where('reporter_id', $userId)
              ->orWhere('technician_id', $userId);
        });

        $stats = [
            'total'   => (clone $statsQuery)->count(),
            'aktif'   => (clone $statsQuery)->where('status', '!=', 'selesai')->count(),
            'selesai' => (clone $statsQuery)->where('status', 'selesai')->count(),
        ];

        return view('teknisi.laporan.index', compact('tickets', 'filter', 'search', 'stats'));
    }

    /**
     * Tampilkan form buat laporan perbaikan baru (Create).
     */
    public function create()
    {
        $machines = Machine::orderBy('category')->orderBy('name')->get(['id', 'code', 'name', 'category', 'model', 'location']);
        return view('teknisi.laporan.create', compact('machines'));
    }

    /**
     * Cari mesin yang cocok atau buat mesin baru secara otomatis ke database jika belum ada.
     */
    protected function resolveMachine(string $name, ?string $code = null, ?string $location = null): Machine
    {
        $name = trim($name);
        $location = $location ? trim($location) : 'Area Operasional';

        // 1. Jika kode mesin diisi manual, cari berdasarkan kode
        if (!empty($code)) {
            $code = strtoupper(trim($code));
            $machine = Machine::where('code', $code)->first();
            if ($machine) {
                $machine->update([
                    'name'     => $name ?: $machine->name,
                    'location' => $location ?: $machine->location,
                ]);
                return $machine;
            }
        }

        // 2. Cari berdasarkan nama mesin yang mirip
        $machine = Machine::where('name', $name)->first();
        if ($machine) {
            if (!empty($code) && $machine->code !== $code) {
                $machine->update(['code' => $code]);
            }
            if (!empty($location)) {
                $machine->update(['location' => $location]);
            }
            return $machine;
        }

        // 3. Jika belum ada kode, buat kode otomatis yang unik (contoh: MC-PRS-001)
        if (empty($code)) {
            $letters = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $name));
            $prefix = 'MC-' . (substr($letters, 0, 3) ?: 'MSN');
            $count = Machine::where('code', 'like', "{$prefix}%")->count() + 1;
            $code = "{$prefix}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
            while (Machine::where('code', $code)->exists()) {
                $count++;
                $code = "{$prefix}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        }

        // 4. Daftarkan mesin baru otomatis ke tabel machines
        return Machine::create([
            'code'     => $code,
            'name'     => $name,
            'location' => $location,
            'status'   => 'dilaporkan',
        ]);
    }

    /**
     * Simpan laporan perbaikan baru ke database (Store).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'machine_name' => 'required|string|max:120',
            'machine_code' => 'nullable|string|max:50',
            'location'     => 'nullable|string|max:120',
            'complaint'    => 'required|string|max:1000',
            'urgency'      => 'required|in:rendah,sedang,tinggi',
            'status'       => 'nullable|in:dilaporkan,ditugaskan,diproses,selesai',
            'files.*'      => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov|max:30720',
        ], [
            'machine_name.required' => 'Nama mesin wajib diisi.',
            'complaint.required'    => 'Deskripsi keluhan / kerusakan wajib diisi.',
            'urgency.required'      => 'Pilih tingkat urgensi perbaikan.',
        ]);

        $machine = $this->resolveMachine(
            $data['machine_name'],
            $data['machine_code'] ?? null,
            $data['location'] ?? null
        );

        $user = $request->user();
        $status = $data['status'] ?? 'ditugaskan';

        $dueDays = match ($data['urgency']) {
            'tinggi' => 1,
            'sedang' => 3,
            default  => 7,
        };

        $isClaimedImmediately = in_array($status, ['diproses', 'selesai']);
        $ticket = Ticket::create([
            'code'          => Ticket::nextCode(),
            'machine_id'    => $machine->id,
            'reporter_id'   => $user->id,
            'technician_id' => $isClaimedImmediately ? $user->id : null,
            'complaint'     => $data['complaint'],
            'urgency'       => $data['urgency'],
            'status'        => $status,
            'progress'      => $status === 'selesai' ? 100 : ($status === 'diproses' ? 50 : 0),
            'due_at'        => now()->addDays($dueDays),
            'started_at'    => $isClaimedImmediately ? now() : null,
            'finished_at'   => $status === 'selesai' ? now() : null,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                TicketMedia::create([
                    'ticket_id' => $ticket->id,
                    'phase'     => 'report',
                    'kind'      => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'photo',
                    'path'      => $file->store("tickets/{$ticket->code}", 'public'),
                ]);
            }
        }

        $ticket->log('Dilaporkan', "Laporan dibuat oleh teknisi {$user->name} ({$machine->label}) - Siapa cepat dia dapat");
        $machine->update(['status' => $status === 'selesai' ? 'aktif' : 'dilaporkan']);

        // Siarkan notifikasi WhatsApp ke supervisor & teknisi
        \App\Services\WhatsAppService::broadcastToAllTechnicians($ticket);

        if ($ticket->technician_id === null) {
            return redirect()->route('teknisi.lapor.sukses', $ticket);
        }

        return redirect()->route('teknisi.laporan.show', $ticket)
            ->with('ok', "Laporan {$ticket->code} berhasil dibuat dan disinkronkan ke Manajemen.");
    }

    /**
     * Tampilkan detail laporan perbaikan (Read / Show).
     */
    public function show(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->load(['machine', 'media', 'logs.user', 'reporter', 'technician']);

        return view('teknisi.laporan.show', compact('ticket'));
    }

    /**
     * Tampilkan form edit laporan perbaikan (Edit).
     */
    public function edit(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $machines = Machine::orderBy('category')->orderBy('name')->get(['id', 'code', 'name', 'category', 'model', 'location']);

        return view('teknisi.laporan.edit', compact('ticket', 'machines'));
    }

    /**
     * Perbarui data laporan perbaikan (Update).
     */
    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $data = $request->validate([
            'machine_name' => 'required|string|max:120',
            'machine_code' => 'nullable|string|max:50',
            'location'     => 'nullable|string|max:120',
            'complaint'    => 'required|string|max:1000',
            'urgency'      => 'required|in:rendah,sedang,tinggi',
            'status'       => 'nullable|in:dilaporkan,ditugaskan,diproses,selesai',
            'action'       => 'nullable|string|max:255',
            'spareparts'   => 'nullable|string|max:255',
            'files.*'      => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov|max:30720',
        ], [
            'machine_name.required' => 'Nama mesin wajib diisi.',
            'complaint.required'    => 'Deskripsi keluhan wajib diisi.',
        ]);

        $machine = $this->resolveMachine(
            $data['machine_name'],
            $data['machine_code'] ?? null,
            $data['location'] ?? null
        );

        $newStatus = $data['status'] ?? $ticket->status;
        $statusChanged = $newStatus !== $ticket->status;

        $ticket->update([
            'machine_id'  => $machine->id,
            'complaint'   => $data['complaint'],
            'urgency'     => $data['urgency'],
            'status'      => $newStatus,
            'action'      => $data['action'] ?? $ticket->action,
            'spareparts'  => $data['spareparts'] ?? $ticket->spareparts,
            'started_at'  => in_array($newStatus, ['diproses', 'selesai']) ? ($ticket->started_at ?? now()) : $ticket->started_at,
            'finished_at' => $newStatus === 'selesai' ? ($ticket->finished_at ?? now()) : ($statusChanged ? null : $ticket->finished_at),
            'progress'    => $newStatus === 'selesai' ? 100 : ($newStatus === 'diproses' ? 50 : $ticket->progress),
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                TicketMedia::create([
                    'ticket_id' => $ticket->id,
                    'phase'     => 'report',
                    'kind'      => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'photo',
                    'path'      => $file->store("tickets/{$ticket->code}", 'public'),
                ]);
            }
        }

        // Update status mesin di manajemen
        if ($newStatus === 'selesai') {
            if (!Ticket::where('machine_id', $machine->id)->where('id', '!=', $ticket->id)->where('status', '!=', 'selesai')->exists()) {
                $machine->update([
                    'status'          => 'aktif',
                    'last_service_at' => $ticket->finished_at ?? now(),
                ]);
            }
        } else {
            $machine->update(['status' => $newStatus === 'diproses' ? 'diproses' : 'dilaporkan']);
        }

        $logMsg = 'Laporan diperbarui oleh ' . auth()->user()->name;
        if ($statusChanged) {
            $logMsg .= " (Status: " . ucfirst($newStatus) . ")";
        }
        $ticket->log('Diperbarui', $logMsg);

        return redirect()->route('teknisi.laporan.show', $ticket)
            ->with('ok', "Laporan {$ticket->code} berhasil diperbarui dan tersinkron ke Manajemen.");
    }

    /**
     * Hapus laporan perbaikan (Delete / Destroy).
     */
    public function destroy(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $code = $ticket->code;
        $machine = $ticket->machine;

        // Hapus file media di storage jika ada
        foreach ($ticket->media as $media) {
            Storage::disk('public')->delete($media->path);
        }

        $ticket->delete();

        // Kembalikan status mesin ke aktif jika tidak ada tiket lain yang aktif
        if ($machine && !Ticket::where('machine_id', $machine->id)->where('status', '!=', 'selesai')->exists()) {
            $machine->update(['status' => 'aktif']);
        }

        return redirect()->route('teknisi.laporan.index')
            ->with('ok', "Laporan {$code} berhasil dihapus.");
    }

    /**
     * Hapus satu file media lampiran.
     */
    public function destroyMedia(TicketMedia $media)
    {
        $ticket = $media->ticket;
        $this->authorizeTicket($ticket);

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('ok', 'Lampiran berhasil dihapus.');
    }

    /**
     * Halaman sukses pendaftaran tiket.
     */
    public function success(Ticket $ticket)
    {
        $broadcastLink = \App\Services\WhatsAppService::makeBroadcastShareLink($ticket);
        $technicians = \App\Models\User::where('role', 'teknisi')->where('status', 'aktif')->whereNotNull('whatsapp')->get();
        return view('tiket.sukses', [
            'ticket'        => $ticket->load('machine'),
            'broadcastLink' => $broadcastLink,
            'technicians'   => $technicians,
        ]);
    }

    /**
     * Pastikan teknisi berhak melihat/mengubah tiket ini.
     */
    protected function authorizeTicket(Ticket $ticket): void
    {
        $userId = auth()->id();
        // Boleh diakses jika pelapor, atau teknisi yang ditugaskan, atau jika tiket terbuka (belum diambil)
        if ($ticket->reporter_id !== $userId && $ticket->technician_id !== $userId && $ticket->technician_id !== null) {
            abort(403, 'Anda tidak berhak mengakses laporan ini.');
        }
    }
}
