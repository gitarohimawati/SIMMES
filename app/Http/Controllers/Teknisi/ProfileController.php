<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $u = auth()->user();
        $total = Ticket::where('technician_id', $u->id)->where('status', 'selesai')->count();
        return view('teknisi.profil.index', compact('u', 'total'));
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'name'     => 'required|string|max:120',
            'whatsapp' => 'nullable|string|max:30',
            'bagian'   => 'nullable|string|max:80',
        ], [
            'name.required' => 'Nama pengguna tidak boleh kosong.',
        ]);

        if (!empty($data['whatsapp'])) {
            $normalized = \App\Services\WhatsAppService::normalizeNumber($data['whatsapp']);
            if ($normalized) {
                $data['whatsapp'] = $normalized;
            }
        }

        auth()->user()->update($data);
        return back()->with('ok', 'Nama pengguna dan profil berhasil diperbarui.');
    }

    public function testWhatsApp()
    {
        $u = auth()->user();
        $targetNumber = $u->whatsapp;
        if (empty($targetNumber)) {
            return back()->with('err', 'Harap isi dan simpan nomor WhatsApp Anda di profil terlebih dahulu.');
        }

        $testMsg = \App\Services\WhatsAppService::buildTestMessage($u);
        $directWa = \App\Services\WhatsAppService::makeDirectLink($targetNumber, $testMsg);

        // Kirim juga via gateway Fonnte / log
        \App\Services\WhatsAppService::send($targetNumber, $testMsg);

        // Buka langsung aplikasi WhatsApp
        return redirect()->away($directWa);
    }
}
