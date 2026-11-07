<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $r)
    {
        $q = Ticket::with(['machine', 'technician', 'media', 'logs'])->latest();

        if ($s = $r->q) {
            $q->where(fn ($w) => $w->where('code', 'like', "%$s%")
                ->orWhere('complaint', 'like', "%$s%")
                ->orWhereHas('machine', fn ($m) => $m->where('code', 'like', "%$s%")
                    ->orWhere('name', 'like', "%$s%")
                    ->orWhere('category', 'like', "%$s%")
                    ->orWhere('model', 'like', "%$s%")
                    ->orWhere('location', 'like', "%$s%")));
        }
        if ($r->status === 'terlambat') {
            $q->where('status', '!=', 'selesai')->where('due_at', '<', now());
        } elseif ($r->status) {
            $q->where('status', $r->status);
        }
        if ($r->urgency) $q->where('urgency', $r->urgency);
        if ($r->location) $q->whereHas('machine', fn ($m) => $m->where('location', $r->location));

        return view('manajemen.perbaikan.index', [
            'tickets'   => $q->paginate(12)->withQueryString(),
            'locations' => Machine::distinct()->orderBy('location')->pluck('location'),
            'reportMachines' => Machine::orderBy('name')->get(['code', 'name', 'location']),
        ]);
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['machine', 'technician', 'reporter', 'media', 'logs.user']);
        $technicians = User::where('role', 'teknisi')->where('status', 'aktif')->orderBy('name')->get();
        return view('manajemen.perbaikan.show', compact('ticket', 'technicians'));
    }

    public function assign(Request $r, Ticket $ticket)
    {
        $data = $r->validate([
            'technician_id' => 'required|exists:users,id',
            'due_at'        => 'nullable|date',
        ]);
        $tech = User::findOrFail($data['technician_id']);

        $ticket->update([
            'technician_id' => $tech->id,
            'due_at'        => $data['due_at'] ?? now()->addDays($ticket->urgency === 'tinggi' ? 1 : 3),
            'status'        => $ticket->status === 'dilaporkan' ? 'ditugaskan' : $ticket->status,
        ]);
        $ticket->log('Ditugaskan', $tech->name);

        $waLink = null;
        if (!empty($tech->whatsapp)) {
            $msg = \App\Services\WhatsAppService::buildTicketMessage($ticket, $tech);
            $waLink = \App\Services\WhatsAppService::makeDirectLink($tech->whatsapp, $msg);
            \App\Services\WhatsAppService::sendTicketAssignment($ticket, $tech);
        }

        return back()
            ->with('ok', "Tiket {$ticket->code} berhasil ditugaskan ke {$tech->name}.")
            ->with('wa_link', $waLink)
            ->with('tech_name', $tech->name)
            ->with('tech_phone', $tech->whatsapp);
    }

    public function notifyWa(Ticket $ticket)
    {
        $ticket->load(['machine', 'technician']);
        if (!$ticket->technician || empty($ticket->technician->whatsapp)) {
            return back()->with('err', 'Teknisi belum ditugaskan atau belum memiliki nomor WhatsApp di profilnya.');
        }

        $msg = \App\Services\WhatsAppService::buildTicketMessage($ticket, $ticket->technician);
        $waLink = \App\Services\WhatsAppService::makeDirectLink($ticket->technician->whatsapp, $msg);

        // Coba kirim via gateway di background jika ada token
        \App\Services\WhatsAppService::sendTicketAssignment($ticket, $ticket->technician);

        // Buka langsung ke WhatsApp
        return redirect()->away($waLink);
    }

    /**
     * Siarkan notifikasi perbaikan mesin ke semua teknisi (Siapa cepat dia dapat).
     */
    public function broadcastWa(Ticket $ticket)
    {
        $ticket->load('machine');
        $count = \App\Services\WhatsAppService::broadcastToAllTechnicians($ticket);
        $shareLink = \App\Services\WhatsAppService::makeBroadcastShareLink($ticket);

        return back()
            ->with('ok', "Notifikasi 'Siapa Cepat Dia Dapat' berhasil disiarkan ke {$count} teknisi terdaftar.")
            ->with('broadcast_link', $shareLink);
    }

    public function create()
    {
        return redirect()->route('manajemen.lapor');
    }

    public function edit(Ticket $ticket)
    {
        $ticket->load(['machine', 'technician', 'reporter', 'media', 'logs']);
        $technicians = User::where('role', 'teknisi')->where('status', 'aktif')->orderBy('name')->get();
        $machines = Machine::orderBy('name')->get(['id', 'code', 'name', 'location']);

        return view('manajemen.perbaikan.edit', compact('ticket', 'technicians', 'machines'));
    }

    public function update(Request $r, Ticket $ticket)
    {
        $data = $r->validate([
            'machine_id'    => 'required|exists:machines,id',
            'complaint'     => 'required|string|max:1000',
            'urgency'       => 'required|in:rendah,sedang,tinggi',
            'status'        => 'required|in:dilaporkan,ditugaskan,diproses,selesai',
            'technician_id' => 'nullable|exists:users,id',
            'due_at'        => 'nullable|date',
            'started_at'    => 'nullable|date',
            'finished_at'   => 'nullable|date',
            'progress'      => 'nullable|integer|min:0|max:100',
            'action'        => 'nullable|string|max:255',
            'spareparts'    => 'nullable|string|max:255',
        ]);

        $oldTechId = $ticket->technician_id;
        $oldStatus = $ticket->status;

        if ($data['status'] === 'selesai') {
            $data['finished_at'] = $data['finished_at'] ?? $ticket->finished_at ?? now();
            $data['progress'] = 100;
            // Sinkronkan status mesin menjadi aktif dan catat tanggal servis terakhir
            $ticket->machine->update([
                'status'          => 'aktif',
                'last_service_at' => $data['finished_at'],
            ]);
        } elseif ($data['status'] !== 'selesai' && $ticket->status === 'selesai') {
            $data['finished_at'] = null;
        }

        if (in_array($data['status'], ['diproses', 'selesai']) && empty($data['started_at']) && empty($ticket->started_at)) {
            $data['started_at'] = now();
        }

        if ($data['status'] === 'diproses') {
            $ticket->machine->update(['status' => 'diproses']);
        } elseif ($data['status'] === 'dilaporkan') {
            $ticket->machine->update(['status' => 'dilaporkan']);
        }

        $ticket->update($data);

        $waMsg = '';
        if (!empty($data['technician_id']) && $data['technician_id'] != $oldTechId) {
            $tech = User::find($data['technician_id']);
            $ticket->log('Ditugaskan', "Teknisi ditugaskan ke {$tech?->name}");
            if ($tech && !empty($tech->whatsapp)) {
                $waRes = \App\Services\WhatsAppService::sendTicketAssignment($ticket, $tech);
                $waMsg = $waRes['success'] ? ' & notifikasi WA terkirim' : " (WA: {$waRes['message']})";
            }
        }
        if ($data['status'] !== $oldStatus) {
            $ticket->log(ucfirst($data['status']), 'Status tiket diperbarui manajemen');
        }

        return redirect()->route('manajemen.perbaikan.show', $ticket)
            ->with('ok', "Tiket {$ticket->code} berhasil diperbarui{$waMsg}.");
    }

    public function destroy(Ticket $ticket)
    {
        $code = $ticket->code;
        foreach ($ticket->media as $m) {
            if ($m->path && Storage::disk('public')->exists($m->path)) {
                Storage::disk('public')->delete($m->path);
            }
        }
        $ticket->delete();

        return back()->with('ok', "Tiket {$code} berhasil dihapus.");
    }
}
