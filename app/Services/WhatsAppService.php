<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Normalisasi nomor telepon ke format internasional WhatsApp (misal: 6281234567890).
     * Mendukung nomor tunggal atau multi-nomor dipisahkan koma.
     */
    public static function normalizeNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Jika berisi multi-nomor (dipisahkan koma), normalisasi masing-masing nomor
        if (str_contains($phone, ',')) {
            $parts = explode(',', $phone);
            $normalizedParts = [];
            foreach ($parts as $p) {
                $norm = self::normalizeSingleNumber($p);
                if ($norm) {
                    $normalizedParts[] = $norm;
                }
            }
            return !empty($normalizedParts) ? implode(',', array_unique($normalizedParts)) : null;
        }

        return self::normalizeSingleNumber($phone);
    }

    /**
     * Normalisasi nomor tunggal ke format 628...
     */
    public static function normalizeSingleNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Hapus karakter non-angka
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (empty($cleaned)) {
            return null;
        }

        // Ganti awalan 08 menjadi 628
        if (str_starts_with($cleaned, '08')) {
            $cleaned = '628' . substr($cleaned, 2);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }

        // Validasi panjang nomor Indonesia (minimal 10 digit, maksimal 15 digit)
        if (strlen($cleaned) < 10) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Buat URL langsung ke WhatsApp (wa.me) dengan pesan yang sudah terisi.
     */
    public static function makeDirectLink(?string $phone, string $message): ?string
    {
        $normalized = self::normalizeNumber($phone);
        if (!$normalized) {
            return null;
        }

        return 'https://wa.me/' . $normalized . '?text=' . rawurlencode($message);
    }

    /**
     * Kirim notifikasi WhatsApp ke teknisi / nomor tujuan.
     * Mendukung Gateway API (seperti Fonnte/Wablas) atau fallback ke log jika token belum diisi.
     */
    public static function send(string|User $target, string $message): array
    {
        $phone = $target instanceof User ? $target->whatsapp : $target;
        $name  = $target instanceof User ? $target->name : 'Teknisi';

        $normalized = self::normalizeNumber($phone);
        if (!$normalized) {
            Log::warning("[WhatsApp Service] Gagal kirim ke {$name}: Nomor WhatsApp tidak valid atau kosong ({$phone}).");
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp belum diisi atau tidak valid.',
            ];
        }

        $gatewayUrl = config('services.whatsapp.url', env('WA_GATEWAY_URL', 'https://api.fonnte.com/send'));
        $token      = config('services.whatsapp.token', env('WA_API_TOKEN'));

        // Jika token gateway belum dikonfigurasi, simpan di log sistem agar tetap bisa ditinjau
        if (empty($token)) {
            Log::info("[WhatsApp NOTIFIKASI (SIMULASI)] Penerima: {$name} ({$normalized})\n" . $message);
            return [
                'success'   => true,
                'simulated' => true,
                'phone'     => $normalized,
                'message'   => "Simulasi terkirim (Token WA belum diisi di .env). Pesan telah dicatat ke log.",
            ];
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(12)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->asForm()
                ->post($gatewayUrl, [
                    'target'      => $normalized,
                    'message'     => $message,
                    'countryCode' => '62',
                ]);

            $json = $response->json();
            // Fonnte mengembalikan status HTTP 200 namun dengan body {"status": false, "reason": "device disconnected"} jika perangkat WA offline/terputus
            $fonnteSuccess = $response->successful() && is_array($json) && ($json['status'] ?? false) === true;

            if ($fonnteSuccess) {
                Log::info("[WhatsApp Gateway Berhasil] Terkirim ke {$name} ({$normalized}).");
                return [
                    'success'   => true,
                    'simulated' => false,
                    'phone'     => $normalized,
                    'message'   => "Notifikasi WhatsApp berhasil dikirim ke {$name}.",
                ];
            }

            $reason = is_array($json) && !empty($json['reason']) 
                ? $json['reason'] 
                : ($response->body() ?: 'HTTP ' . $response->status());

            Log::error("[WhatsApp Gateway Gagal] Response dari {$gatewayUrl} untuk {$name} ({$normalized}): {$reason}");
            return [
                'success'   => false,
                'simulated' => false,
                'phone'     => $normalized,
                'message'   => "Gateway WhatsApp gagal mengirim ({$reason})",
            ];
        } catch (\Throwable $e) {
            Log::error("[WhatsApp Gateway Exception] " . $e->getMessage());
            return [
                'success'   => false,
                'simulated' => false,
                'phone'     => $normalized,
                'message'   => "Gagal menghubungi gateway WhatsApp: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Buat teks pesan notifikasi penugasan tiket perbaikan (Corrective) atau tindak lanjut rutin.
     */
    public static function buildTicketMessage(Ticket $ticket, User $tech): string
    {
        $ticket->loadMissing(['machine', 'reporter']);
        $due = $ticket->due_at ? $ticket->due_at->translatedFormat('d F Y, H:i') : '-';
        $appUrl = config('app.url', url('/'));
        $taskUrl = "{$appUrl}/teknisi/tugas/{$ticket->id}";
        $reporterName = $ticket->reporter?->name ?? 'Operator / Pengawas';

        // 1. Jika tiket dibuat dari agenda maintenance rutin
        if ($ticket->type === 'rutin') {
            return "🗓️ *SIMMES - PENUGASAN TIKET MAINTENANCE RUTIN*\n"
                . "*(Tindak Lanjut Agenda Pemeliharaan Mesin)*\n\n"
                . "Halo *{$tech->name}*,\n"
                . "Anda telah ditugaskan untuk mengeksekusi tiket pemeliharaan preventif berikut:\n\n"
                . "━━━━━━━━━━━━━━━━━━━━━\n"
                . "🎫 *Kode Tiket   :* {$ticket->code}\n"
                . "⚙️ *Mesin Target :* {$ticket->machine->label}\n"
                . "📍 *Area / Lokasi:* {$ticket->machine->location}\n"
                . "🛠️ *Item Servis  :* {$ticket->complaint}\n"
                . "📅 *Batas Target :* {$due} WIB\n"
                . "━━━━━━━━━━━━━━━━━━━━━\n\n"
                . "📌 *Panduan Pelaksanaan:*\n"
                . "1. Unggah foto kondisi awal mesin (*Sebelum*) di SIMMES.\n"
                . "2. Lakukan servis sesuai SOP & catat penggantian suku cadang.\n"
                . "3. Unggah foto hasil akhir (*Sesudah*) & tandai 100% selesai.\n\n"
                . "👉 *Buka Lembar Kerja Tiket:*\n"
                . "{$taskUrl}\n\n"
                . "_SIMMES · Sistem Informasi Maintenance Mesin_";
        }

        // 2. Tiket Perbaikan Kerusakan / Insiden (Corrective Breakdown)
        $urgencyBadges = [
            'tinggi' => '🔴 TINGGI (Prioritas Utama / Mesin Terhenti)',
            'sedang' => '🟠 SEDANG (Segera Tangani Sebelum Kendala Meluas)',
            'rendah' => '🟡 RENDAH (Penanganan Normal / Observasi)',
        ];
        $urgencyLabel = $urgencyBadges[$ticket->urgency] ?? strtoupper($ticket->urgency);

        return "🚨 *SIMMES - PENUGASAN TIKET PERBAIKAN KERUSAKAN*\n"
            . "*(Corrective Action / Penanganan Insiden Mesin)*\n\n"
            . "Halo *{$tech->name}*,\n"
            . "Terdapat laporan kerusakan mesin yang ditugaskan kepada Anda:\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━\n"
            . "🎫 *No. Tiket    :* {$ticket->code}\n"
            . "⚙️ *Mesin Rusak  :* {$ticket->machine->label}\n"
            . "📍 *Area / Lokasi:* {$ticket->machine->location}\n"
            . "⚠️ *Tingkat Urgensi :* {$urgencyLabel}\n"
            . "👤 *Pelapor      :* {$reporterName}\n"
            . "⏰ *Batas Waktu  :* {$due} WIB\n"
            . "━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "💥 *Gejala / Kendala Kerusakan:*\n"
            . "\"{$ticket->complaint}\"\n\n"
            . "📋 *SOP Tindakan Cepat Teknisi:*\n"
            . "1. Segera ke lokasi & utamakan prosedur K3 / LOTO.\n"
            . "2. Ambil & unggah foto *Sebelum Perbaikan* di SIMMES.\n"
            . "3. Lakukan analisa, perbaikan, serta catat komponen pengganti.\n\n"
            . "👉 *Buka Lembar Kerja Perbaikan:*\n"
            . "{$taskUrl}\n\n"
            . "_Mohon segera ditindaklanjuti demi kelancaran operasional._";
    }

    /**
     * Kirim notifikasi WhatsApp saat tiket ditugaskan ke teknisi.
     */
    public static function sendTicketAssignment(Ticket $ticket, User $tech): array
    {
        $message = self::buildTicketMessage($ticket, $tech);
        return self::send($tech, $message);
    }

    /**
     * Buat teks pesan pengingat jadwal pemeliharaan (Preventive Maintenance).
     */
    public static function buildScheduleMessage(MaintenanceSchedule $schedule, string $kind): string
    {
        $schedule->loadMissing(['machine', 'technician']);
        $techName = $schedule->technician?->name ?? 'Teknisi';
        $dueDate  = $schedule->due_date ? $schedule->due_date->translatedFormat('l, d F Y') : '-';
        if ($schedule->due_time) {
            $dueDate .= " pukul {$schedule->due_time} WIB";
        }
        $appUrl      = config('app.url', url('/'));
        $scheduleUrl = "{$appUrl}/teknisi/jadwal";

        // Variasi Header, Sub-header, dan Instruksi berdasarkan tahap pengingat ($kind)
        $kindLower = strtolower($kind);
        $header = "🗓️ *SIMMES - JADWAL PEMELIHARAAN PREVENTIF*";
        $subHeader = "*(Preventive Maintenance Terjadwal)*";
        $instruction = "Silakan tinjau jadwal dan siapkan perlengkapan pendukung.";

        if (str_contains($kindLower, 'h-7') || $kind === 'H-7') {
            $header = "🗓️ *SIMMES - PENGINGAT H-7 PEMELIHARAAN MESIN*";
            $subHeader = "*(Tahap: Persiapan Alat & Suku Cadang)*";
            $instruction = "📦 *Kesiapan Awal (H-7):* Mohon pastikan ketersediaan stok oli/pelumas, filter, belt, dan alat ukur yang dibutuhkan sebelum waktu eksekusi.";
        } elseif (str_contains($kindLower, 'h-1') || $kind === 'H-1') {
            $header = "🗓️ *SIMMES - PENGINGAT H-1 (BESOK) PEMELIHARAAN MESIN*";
            $subHeader = "*(Tahap: Pelaksanaan Besok)*";
            $instruction = "⏳ *Kesiapan H-1 (Besok):* Jadwal pemeliharaan akan dilaksanakan BESOK. Harap berkoordinasi dengan operator area mesin agar line produksi siap diservis.";
        } elseif (str_contains($kindLower, 'hari-h') || $kind === 'Hari-H') {
            $header = "🚀 *SIMMES - AGENDA PEMELIHARAAN MESIN HARI INI*";
            $subHeader = "*(Waktunya Pelaksanaan Servis Berkala)*";
            $instruction = "🔧 *Pelaksanaan Hari Ini:* Jadwal jatuh pada HARI INI. Silakan ke lokasi mesin, lakukan inspeksi sesuai checklist, dan perbarui progres di SIMMES.";
        } elseif (str_contains($kindLower, 'terlambat') || $kind === 'Terlambat') {
            $header = "⚠️ *SIMMES - PERINGATAN: PEMELIHARAAN MELEWATI JADWAL*";
            $subHeader = "*(Eskalasi: Melewati Tanggal Jatuh Tempo)*";
            $instruction = "⚠️ *Peringatan Keterlambatan:* Jadwal servis ini telah melampaui tanggal jatuh tempo! Harap segera prioritaskan pengerjaan untuk mencegah downtime.";
        } elseif (str_contains($kindLower, 'jadwal baru') || str_contains($kindLower, 'penugasan')) {
            $header = "📋 *SIMMES - PENUGASAN JADWAL PEMELIHARAAN RUTIN*";
            $subHeader = "*(Agenda Servis Preventif Baru)*";
            $instruction = "✅ *Penugasan Baru:* Anda telah dijadwalkan untuk tugas pemeliharaan berkala ini. Silakan tinjau tanggal dan rincian pekerjaan di portal.";
        }

        $siklus = $schedule->interval_label 
            ? "Tiap {$schedule->interval_label}" 
            : ($schedule->interval_months ? "Tiap {$schedule->interval_months} bulan" : 'Rutin Berkala');

        return "{$header}\n"
            . "{$subHeader}\n\n"
            . "Halo *{$techName}*,\n"
            . "Berikut rincian agenda pemeliharaan preventif yang tercatat di sistem:\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━\n"
            . "🛠️ *Item Perawatan :* {$schedule->task}\n"
            . "⚙️ *Mesin Target   :* {$schedule->machine->label}\n"
            . "📍 *Area / Lokasi  :* {$schedule->machine->location}\n"
            . "🔄 *Siklus Servis  :* {$siklus}\n"
            . "📅 *Waktu Rencana  :* {$dueDate}\n"
            . "🔖 *Status Pengingat:* {$kind}\n"
            . "━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "💡 *Petunjuk Kerja:*\n"
            . "{$instruction}\n\n"
            . "📊 *Tahapan Progres:* 25% Foto Awal → 50% Pengerjaan → 75% Foto Hasil → 100% Selesai\n\n"
            . "👉 *Buka Agenda Pemeliharaan:*\n"
            . "{$scheduleUrl}\n\n"
            . "_Perawatan berkala rutin menjaga efisiensi dan masa pakai mesin._";
    }

    /**
     * Buat URL langsung ke WhatsApp teknisi untuk jadwal maintenance.
     */
    public static function makeScheduleShareLink(MaintenanceSchedule $schedule, string $kind = 'Pengingat'): ?string
    {
        $phone = $schedule->technician?->whatsapp;
        $msg = self::buildScheduleMessage($schedule, $kind);
        return self::makeDirectLink($phone, $msg);
    }

    /**
     * Kirim notifikasi pengingat jadwal pemeliharaan.
     */
    public static function sendScheduleReminder(MaintenanceSchedule $schedule, string $kind): array
    {
        if (!$schedule->technician) {
            return ['success' => false, 'message' => 'Jadwal belum memiliki teknisi yang ditugaskan.'];
        }

        $message = self::buildScheduleMessage($schedule, $kind);
        return self::send($schedule->technician, $message);
    }

    /**
     * Buat teks pesan uji coba / verifikasi koneksi WhatsApp teknisi.
     */
    public static function buildTestMessage(User $tech): string
    {
        $appUrl = config('app.url', url('/'));
        $phone  = $tech->whatsapp ?? '-';

        return "✅ *SIMMES - KONEKSI NOTIFIKASI WHATSAPP AKTIF*\n\n"
            . "Halo *{$tech->name}*,\n"
            . "Nomor WhatsApp Anda ({$phone}) telah berhasil terhubung dengan sistem SIMMES!\n\n"
            . "Anda akan menerima 2 jenis notifikasi otomatis yang berbeda:\n\n"
            . "1️⃣ *🚨 NOTIFIKASI TIKET PERBAIKAN (Insiden)*\n"
            . "• Notifikasi instan saat ada kerusakan mesin tak terduga\n"
            . "• Berisi kode tiket, pelapor, tingkat urgensi, dan keluhan kendala\n\n"
            . "2️⃣ *🗓️ NOTIFIKASI JADWAL MAINTENANCE (Preventif)*\n"
            . "• Notifikasi terjadwal bertahap (H-7, H-1, Hari-H, & Keterlambatan)\n"
            . "• Berisi item checklist perawatan, siklus berkala, dan panduan persiapan\n\n"
            . "👉 Portal SIMMES: {$appUrl}\n\n"
            . "_SIMMES · Sistem Informasi Maintenance Mesin_";
    }

    /**
     * Kirim pesan uji coba / verifikasi ke WhatsApp teknisi.
     */
    public static function sendTestMessage(User $tech): array
    {
        $message = self::buildTestMessage($tech);
        return self::send($tech, $message);
    }

    /**
     * Buat teks pesan penyiaran laporan perbaikan baru (siapa cepat dia dapat).
     */
    public static function buildBroadcastMessage(Ticket $ticket): string
    {
        $ticket->loadMissing(['machine', 'reporter']);
        $due = $ticket->due_at ? $ticket->due_at->translatedFormat('d F Y, H:i') : '-';
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        $claimUrl = "{$baseUrl}/teknisi/tugas/{$ticket->id}";
        $reporterName = $ticket->reporter?->name ?? 'Operator Lapangan';

        $urgencyBadges = [
            'tinggi' => '🔴 TINGGI (Segera Tangani)',
            'sedang' => '🟠 SEDANG',
            'rendah' => '🟡 RENDAH',
        ];
        $urgencyLabel = $urgencyBadges[$ticket->urgency] ?? strtoupper($ticket->urgency);

        return "🚨 *SIMMES - PEMBERITAHUAN TIKET PERBAIKAN BARU*\n"
            . "*(SISTEM: SIAPA CEPAT DIA DAPAT)*\n\n"
            . "Halo Rekan Teknisi,\n"
            . "Terdapat insiden kendala mesin baru yang membutuhkan penanganan:\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━\n"
            . "🎫 *No. Tiket    :* {$ticket->code}\n"
            . "⚙️ *Mesin Rusak  :* {$ticket->machine->label}\n"
            . "📍 *Area / Lokasi:* {$ticket->machine->location}\n"
            . "⚠️ *Tingkat Urgensi :* {$urgencyLabel}\n"
            . "👤 *Pelapor      :* {$reporterName}\n"
            . "⏰ *Batas Waktu  :* {$due} WIB\n"
            . "━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "💥 *Keluhan Kerusakan:*\n"
            . "\"{$ticket->complaint}\"\n\n"
            . "👉 *Silakan buka website untuk mengambil tugas:*\n"
            . "{$claimUrl}\n\n"
            . "*(Sistem Siapa Cepat Dia Dapat: Tugas terbuka untuk semua teknisi. Silakan buka website di atas untuk mengambil tugas. Setelah tugas diambil oleh salah satu teknisi, tiket otomatis terkunci).*";
    }

    /**
     * Buat link WhatsApp share umum untuk disiarkan ke Grup WhatsApp Teknisi.
     */
    public static function makeBroadcastShareLink(Ticket $ticket): string
    {
        $msg = self::buildBroadcastMessage($ticket);
        return 'https://api.whatsapp.com/send?text=' . rawurlencode($msg);
    }

    /**
     * Siarkan notifikasi laporan tiket baru ke semua teknisi yang terdaftar dan ke WA Saya (Pengelola/Teknisi)
     * secara serentak (simultan) dalam satu pemanggilan multi-target Fonnte.
     */
    public static function broadcastToAllTechnicians(Ticket $ticket): int
    {
        $ticket->loadMissing('machine');
        $msg = self::buildBroadcastMessage($ticket);
        $targets = collect();

        // 1. Ambil nomor dari seluruh teknisi aktif
        $techs = User::where(function ($q) {
                $q->where('role', 'teknisi')
                  ->orWhere('role', \App\Enums\Role::Teknisi);
            })
            ->where(function ($q) {
                $q->where('status', 'aktif')
                  ->orWhere('status', \App\Enums\AccountStatus::Aktif);
            })
            ->whereNotNull('whatsapp')
            ->where('whatsapp', '!=', '')
            ->get();

        foreach ($techs as $t) {
            $num = self::normalizeNumber($t->whatsapp);
            if ($num) {
                $targets->put($num, $t->name);
            }
        }

        // 2. Salinan ke nomor WhatsApp Pengelola / Supervisor Utama dari .env (jika dikonfigurasi)
        $defaultTarget = config('services.whatsapp.target') ?: env('WA_TARGET_PHONE');
        if (!empty($defaultTarget)) {
            $num = self::normalizeNumber($defaultTarget);
            if ($num && !$targets->has($num)) {
                $targets->put($num, 'Pengelola / Supervisor');
            }
        }

        // 3. User yang sedang login jika memiliki nomor WhatsApp
        if (auth()->check() && !empty(auth()->user()->whatsapp)) {
            $num = self::normalizeNumber(auth()->user()->whatsapp);
            if ($num && !$targets->has($num)) {
                $targets->put($num, auth()->user()->name);
            }
        }

        if ($targets->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning("[WhatsApp Broadcast] Tidak ada nomor tujuan teknisi aktif untuk tiket {$ticket->code}.");
            session()->flash('broadcast_status', [
                'success' => false,
                'count'   => 0,
                'message' => 'Tidak ada nomor WhatsApp teknisi aktif yang terdaftar di sistem.',
            ]);
            return 0;
        }

        $allNumbers = array_keys($targets->toArray());
        $multiTargetString = implode(',', $allNumbers);

        // Kirim sekaligus secara bareng ke Fonnte menggunakan koma
        $res = self::send($multiTargetString, $msg);
        $count = !empty($res['success']) ? count($allNumbers) : 0;

        session()->flash('broadcast_status', [
            'success' => !empty($res['success']),
            'message' => $res['message'] ?? '',
            'count'   => count($allNumbers),
            'targets' => implode(', ', $allNumbers),
        ]);

        \Illuminate\Support\Facades\Log::info("[WhatsApp Broadcast Serentak] Tiket {$ticket->code} disiarkan ke " . count($allNumbers) . " nomor ({$multiTargetString}). Hasil: " . json_encode($res));

        return $count;
    }
}