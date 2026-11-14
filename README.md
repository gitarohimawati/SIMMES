# SIMMES

SIMMES (Sistem Informasi Monitoring & Maintenance Mesin) adalah aplikasi Laravel untuk pencatatan aset mesin, pelaporan kerusakan, penugasan dan pemantauan pekerjaan teknisi, serta pengelolaan jadwal pemeliharaan.

## Fitur

- **Manajemen:** dashboard, data mesin, tiket perbaikan, jadwal dan kalender maintenance, pengelolaan akun/teknisi, serta rekap laporan.
- **Teknisi:** dashboard dan daftar tugas, klaim tiket yang masih terbuka, pembaruan progres, unggah foto pekerjaan, pengelolaan jadwal maintenance, laporan, dan profil.
- **Pelaporan kerusakan:** laporan dapat dibuat dari portal manajemen atau akun teknisi, disertai informasi mesin, urgensi, keluhan, dan media pendukung.
- **Notifikasi WhatsApp:** penugasan, siaran tiket, pengingat jadwal, dan pesan uji dapat memakai gateway yang kompatibel dengan endpoint Fonnte. Tanpa token, pesan dicatat sebagai simulasi di log aplikasi.
- **Akses berbasis peran:** halaman manajemen dan teknisi dilindungi middleware role. Akun yang belum aktif tidak dapat mengakses halaman aplikasi.

Antarmuka manajemen ditujukan untuk desktop/tablet, sedangkan halaman teknisi dirancang agar nyaman digunakan di ponsel.

## Teknologi

- PHP `^8.3`
- Laravel `^13.0`
- MySQL atau MariaDB (atau database lain yang didukung Laravel dan dikonfigurasi di `.env`)
- Node.js dan npm untuk membangun aset frontend dengan Vite

## Persiapan lokal

Pastikan PHP dan ekstensi yang dibutuhkan Laravel, Composer, Node.js/npm, serta server database tersedia.

1. Pasang dependensi PHP dan salin konfigurasi lokal:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

   Di Windows PowerShell, salin file dengan:

   ```powershell
   Copy-Item .env.example .env
   ```

   Jika `.env` sudah tersedia, jangan menimpanya; cukup jalankan `php artisan key:generate` bila `APP_KEY` belum diisi.

2. Atur koneksi database di `.env`, lalu buat database yang sesuai. Contoh MySQL:

   ```env
   APP_NAME=SIMMES
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=simmes_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. Jalankan migration, buat tautan storage, dan pasang aset frontend:

   ```bash
   php artisan migrate --seed
   php artisan storage:link
   npm install
   npm run build
   ```

4. Jalankan aplikasi:

   ```bash
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000`.

### Akun awal

Seeder standar membuat dua akun berikut. Segera ganti kata sandi setelah login pertama.

| Peran | NIK untuk login | Kata sandi awal |
| --- | --- | --- |
| Manajemen | `MGR001` | `ganti-sandi-123` |
| Teknisi | `TEK001` | `ganti-sandi-123` |

Seeder standar hanya membuat akun awal. Data contoh mesin, tiket, dan jadwal tersedia melalui seeder terpisah:

```bash
php artisan db:seed --class=SimmesSeeder
```

Seeder contoh memakai beberapa akun dan data demo tambahan; gunakan hanya pada lingkungan pengembangan.

## Konfigurasi WhatsApp (opsional)

Tambahkan konfigurasi berikut ke `.env` untuk mengirim pesan melalui gateway:

```env
WA_GATEWAY_URL=https://api.fonnte.com/send
WA_API_TOKEN=token_gateway_anda
WA_TARGET_PHONE=628xxxxxxxxxx
```

`WA_TARGET_PHONE` adalah nomor tambahan pengelola/supervisor dan bersifat opsional. Pastikan akun teknisi yang menerima pesan memiliki nomor WhatsApp di profil. Jika token tidak tersedia, layanan mencatat pesan simulasi ke log; tidak ada pesan WhatsApp nyata yang dikirim.

## Pengingat maintenance

Perintah pengingat tersedia sebagai `php artisan simmes:reminders`. Aplikasi menjadwalkannya di `routes/console.php`; agar scheduler Laravel berjalan di server, aktifkan pemanggilan `php artisan schedule:run` setiap menit melalui cron atau Task Scheduler. Perintah memeriksa jadwal H-7, H-1, hari-H, dan terlambat. Untuk jadwal yang memiliki teknisi dan konfigurasi gateway, pengingat dikirim melalui WhatsApp; hasil juga dicatat di log.

## Struktur utama

```text
app/
├── Console/Commands/     # Perintah pengingat jadwal
├── Enums/                # Role dan status akun
├── Http/Controllers/     # Modul manajemen, teknisi, autentikasi, pelaporan
├── Models/               # User, Machine, Ticket, media, log, jadwal
└── Services/             # Integrasi WhatsApp
database/migrations/      # Skema tabel
database/seeders/         # Akun awal dan data contoh opsional
resources/views/          # Halaman Blade aplikasi
routes/                   # Route web dan scheduler
```

## Perintah pengembangan

```bash
npm run dev                 # Vite dalam mode pengembangan
php artisan route:list      # Lihat daftar route
php artisan simmes:reminders # Jalankan pemeriksaan pengingat secara manual
```
