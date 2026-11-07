<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = User::where('role', 'teknisi')->where('status', 'aktif')
            ->withCount([
                'assignedTickets as active_count' => fn ($q) => $q->where('status', '!=', 'selesai'),
                'assignedTickets as done_count'   => fn ($q) => $q->where('status', 'selesai'),
            ])->orderBy('name')->get();

        return view('manajemen.teknisi.index', compact('technicians'));
    }

    public function create()
    {
        return view('manajemen.teknisi.create');
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name'       => 'required|string|max:120',
            'nik'        => 'required|string|max:30|unique:users,nik',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'required|string|min:4|max:64',
            'department' => 'nullable|string|max:80',
            'bagian'     => 'nullable|string|max:80',
            'whatsapp'   => 'nullable|string|max:25',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'nik.required'      => 'Nomor Induk Karyawan (NIK) wajib diisi manual sesuai standar perusahaan.',
            'nik.unique'        => 'NIK ini sudah terdaftar pada pengguna lain.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.unique'      => 'Email ini sudah digunakan oleh akun lain.',
            'password.required' => 'Kata sandi wajib diisi oleh manajemen.',
            'password.min'      => 'Kata sandi minimal 4 karakter.',
        ]);

        $nik = strtoupper(trim($d['nik']));
        $email = strtolower(trim($d['email']));
        $plainPassword = trim($d['password']);

        $user = User::create([
            'name'        => trim($d['name']),
            'nik'         => $nik,
            'email'       => $email,
            'password'    => Hash::make($plainPassword),
            'role'        => 'teknisi',
            'status'      => 'aktif',
            'department'  => !empty($d['department']) ? trim($d['department']) : 'Maintenance',
            'bagian'      => !empty($d['bagian']) ? trim($d['bagian']) : null,
            'whatsapp'    => !empty($d['whatsapp']) ? trim($d['whatsapp']) : null,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('manajemen.teknisi.index')
            ->with('ok', "Teknisi {$user->name} ({$user->nik}) berhasil didaftarkan dan dapat langsung login!")
            ->with('new_tech', [
                'name'     => $user->name,
                'nik'      => $user->nik,
                'email'    => $user->email,
                'password' => $plainPassword,
                'wa'       => $user->whatsapp,
            ]);
    }

    public function show(User $teknisi)
    {
        abort_unless($teknisi->role === 'teknisi' || $teknisi->role?->value === 'teknisi', 404);
        $tickets = Ticket::with('machine')->where('technician_id', $teknisi->id)->latest()->take(15)->get();
        return view('manajemen.teknisi.show', ['tech' => $teknisi, 'tickets' => $tickets]);
    }
}
