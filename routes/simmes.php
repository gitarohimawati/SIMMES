<?php

use App\Http\Controllers\HomeRedirectController;
use App\Http\Controllers\Manajemen as M;
use App\Http\Controllers\Teknisi as T;
use App\Http\Controllers\TicketReportController;
use Illuminate\Support\Facades\Route;

/*
 | Di routes/web.php tambahkan di baris paling bawah:
 |     require __DIR__.'/simmes.php';
 | Route login/register milikmu tidak disentuh.
 */
Route::middleware('auth')->group(function () {

    Route::get('/simmes', HomeRedirectController::class)->name('simmes.home');
    Route::get('/mesin-lookup/{code}', [TicketReportController::class, 'lookup'])->name('mesin.lookup');

    // ================= MANAJEMEN =================
    Route::middleware('role:manajemen')->prefix('manajemen')->name('manajemen.')->group(function () {
        Route::get('/', [M\DashboardController::class, 'index'])->name('dashboard');
        Route::post('wa/tes-gateway', [M\DashboardController::class, 'testWaGateway'])->name('wa.testGateway');

        Route::get('perbaikan', [M\TicketController::class, 'index'])->name('perbaikan.index');
        Route::get('perbaikan/baru', [M\TicketController::class, 'create'])->name('perbaikan.create');
        Route::post('perbaikan', [M\TicketController::class, 'store'])->name('perbaikan.store');
        Route::get('perbaikan/{ticket}', [M\TicketController::class, 'show'])->name('perbaikan.show')->whereNumber('ticket');
        Route::get('perbaikan/{ticket}/edit', [M\TicketController::class, 'edit'])->name('perbaikan.edit')->whereNumber('ticket');
        Route::put('perbaikan/{ticket}', [M\TicketController::class, 'update'])->name('perbaikan.update')->whereNumber('ticket');
        Route::delete('perbaikan/{ticket}', [M\TicketController::class, 'destroy'])->name('perbaikan.destroy')->whereNumber('ticket');
        Route::post('perbaikan/{ticket}/tugaskan', [M\TicketController::class, 'assign'])->name('perbaikan.assign');
        Route::post('perbaikan/{ticket}/kirim-wa', [M\TicketController::class, 'notifyWa'])->name('perbaikan.notifyWa');
        Route::post('perbaikan/{ticket}/siarkan-wa', [M\TicketController::class, 'broadcastWa'])->name('perbaikan.broadcastWa');

        Route::get('lapor', [TicketReportController::class, 'create'])->name('lapor');
        Route::post('lapor', [TicketReportController::class, 'store'])->name('lapor.store');
        Route::get('lapor/{ticket}/sukses', [TicketReportController::class, 'success'])->name('lapor.sukses');

        Route::post('mesin/import', [M\MachineController::class, 'import'])->name('mesin.import');
        Route::resource('mesin', M\MachineController::class)->parameters(['mesin' => 'mesin']);

        Route::get('teknisi', [M\TechnicianController::class, 'index'])->name('teknisi.index');
        Route::get('teknisi/baru', [M\TechnicianController::class, 'create'])->name('teknisi.create');
        Route::post('teknisi', [M\TechnicianController::class, 'store'])->name('teknisi.store');
        Route::get('teknisi/{teknisi}', [M\TechnicianController::class, 'show'])->name('teknisi.show')->whereNumber('teknisi');

        Route::get('jadwal', [M\ScheduleController::class, 'index'])->name('jadwal.index');
        Route::get('jadwal/kalender', [M\ScheduleController::class, 'calendar'])->name('jadwal.kalender');
        Route::get('jadwal/baru', [M\ScheduleController::class, 'create'])->name('jadwal.create');
        Route::post('jadwal', [M\ScheduleController::class, 'store'])->name('jadwal.store');
        Route::get('jadwal/{jadwal}', [M\ScheduleController::class, 'show'])->name('jadwal.show')->whereNumber('jadwal');
        Route::get('jadwal/{jadwal}/edit', [M\ScheduleController::class, 'edit'])->name('jadwal.edit')->whereNumber('jadwal');
        Route::put('jadwal/{jadwal}', [M\ScheduleController::class, 'update'])->name('jadwal.update')->whereNumber('jadwal');
        Route::delete('jadwal/{jadwal}', [M\ScheduleController::class, 'destroy'])->name('jadwal.destroy')->whereNumber('jadwal');
        Route::post('jadwal/{jadwal}/tugaskan', [M\ScheduleController::class, 'assign'])->name('jadwal.assign');
        Route::post('jadwal/{jadwal}/kirim-wa', [M\ScheduleController::class, 'notifyWa'])->name('jadwal.notifyWa');
        Route::post('jadwal/{jadwal}/tiket', [M\ScheduleController::class, 'makeTicket'])->name('jadwal.tiket');
        Route::post('jadwal/{jadwal}/selesai', [M\ScheduleController::class, 'complete'])->name('jadwal.selesai');
        Route::post('jadwal/{jadwal}/tes-wa', [M\ScheduleController::class, 'testNotif'])->name('jadwal.testNotif');
        Route::post('jadwal/run-reminders', [M\ScheduleController::class, 'runReminders'])->name('jadwal.runReminders');

        Route::get('laporan', [M\ReportController::class, 'index'])->name('laporan.index');

        Route::get('pengguna', [M\UserController::class, 'index'])->name('pengguna.index');
        Route::get('pengguna/baru', [M\UserController::class, 'create'])->name('pengguna.create');
        Route::post('pengguna', [M\UserController::class, 'store'])->name('pengguna.store');
        Route::get('pengguna/{user}/edit', [M\UserController::class, 'edit'])->name('pengguna.edit');
        Route::put('pengguna/{user}', [M\UserController::class, 'update'])->name('pengguna.update');
        Route::delete('pengguna/{user}', [M\UserController::class, 'destroy'])->name('pengguna.destroy');
        Route::post('pengguna/{user}/setujui', [M\UserController::class, 'approve'])->name('pengguna.approve');
        Route::post('pengguna/{user}/tolak', [M\UserController::class, 'reject'])->name('pengguna.reject');
    });

    // ================= TEKNISI =================
    Route::middleware('role:teknisi')->prefix('teknisi')->name('teknisi.')->group(function () {
        Route::get('/', [T\HomeController::class, 'index'])->name('home');
        Route::get('live-alerts', [T\HomeController::class, 'liveAlerts'])->name('liveAlerts');

        Route::get('tugas', [T\TaskController::class, 'index'])->name('tugas.index');
        Route::get('tugas/{ticket}', [T\TaskController::class, 'show'])->name('tugas.show');
        Route::post('tugas/{ticket}/ambil', [T\TaskController::class, 'claim'])->name('tugas.claim');
        Route::post('tugas/{ticket}/mulai', [T\TaskController::class, 'start'])->name('tugas.start');
        Route::post('tugas/{ticket}/unggah', [T\TaskController::class, 'upload'])->name('tugas.upload');
        Route::post('tugas/{ticket}/progress', [T\TaskController::class, 'progress'])->name('tugas.progress');
        Route::post('tugas/{ticket}/selesai', [T\TaskController::class, 'finish'])->name('tugas.finish');

        Route::get('jadwal', [T\ScheduleController::class, 'index'])->name('jadwal');
        Route::get('jadwal/{jadwal}', [T\ScheduleController::class, 'show'])->name('jadwal.show')->whereNumber('jadwal');
        Route::post('jadwal/{jadwal}/upload', [T\ScheduleController::class, 'upload'])->name('jadwal.upload')->whereNumber('jadwal');
        Route::post('jadwal/{jadwal}/progress', [T\ScheduleController::class, 'progress'])->name('jadwal.progress')->whereNumber('jadwal');
        Route::post('jadwal/{jadwal}/selesai', [T\ScheduleController::class, 'complete'])->name('jadwal.selesai')->whereNumber('jadwal');

        // CRUD Laporan Teknisi
        Route::get('laporan', [T\ReportController::class, 'index'])->name('laporan.index');
        Route::get('laporan/buat', [T\ReportController::class, 'create'])->name('laporan.create');
        Route::post('laporan', [T\ReportController::class, 'store'])->name('laporan.store');
        Route::get('laporan/{ticket}', [T\ReportController::class, 'show'])->name('laporan.show');
        Route::get('laporan/{ticket}/edit', [T\ReportController::class, 'edit'])->name('laporan.edit');
        Route::put('laporan/{ticket}', [T\ReportController::class, 'update'])->name('laporan.update');
        Route::delete('laporan/{ticket}', [T\ReportController::class, 'destroy'])->name('laporan.destroy');
        Route::delete('laporan/media/{media}', [T\ReportController::class, 'destroyMedia'])->name('laporan.media.destroy');

        // Alias lapor agar FAB / link yang sudah ada tetap berjalan lancar
        Route::get('lapor', [T\ReportController::class, 'create'])->name('lapor');
        Route::post('lapor', [T\ReportController::class, 'store'])->name('lapor.store');
        Route::get('lapor/{ticket}/sukses', [T\ReportController::class, 'success'])->name('lapor.sukses');

        Route::get('profil', [T\ProfileController::class, 'show'])->name('profil');
        Route::put('profil', [T\ProfileController::class, 'update'])->name('profil.update');
        Route::post('profil/tes-wa', [T\ProfileController::class, 'testWhatsApp'])->name('profil.testWa');
    });
});
