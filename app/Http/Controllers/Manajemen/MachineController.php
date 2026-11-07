<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function index(Request $r)
    {
        $q = Machine::query()->orderBy('code');
        if ($s = $r->q) $q->where(fn ($w) => $w->where('code', 'like', "%$s%")->orWhere('name', 'like', "%$s%"));
        if ($r->location) $q->where('location', $r->location);
        if ($r->status) $q->where('status', $r->status);

        return view('manajemen.mesin.index', [
            'machines'  => $q->paginate(12)->withQueryString(),
            'locations' => Machine::distinct()->orderBy('location')->pluck('location'),
        ]);
    }

    public function create()
    {
        return view('manajemen.mesin.form', ['machine' => new Machine(['maintenance_interval_months' => 3])]);
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);
        $data['media'] = $this->storeMedia($r);
        Machine::create($data);
        return redirect()->route('manajemen.mesin.index')->with('ok', 'Mesin berhasil ditambahkan.');
    }

    public function show(Machine $mesin)
    {
        $mesin->load(['nextSchedule', 'tickets' => fn ($t) => $t->with(['media', 'logs', 'technician'])->latest()]);
        return view('manajemen.mesin.show', ['machine' => $mesin]);
    }

    public function edit(Machine $mesin)
    {
        return view('manajemen.mesin.form', ['machine' => $mesin]);
    }

    public function update(Request $r, Machine $mesin)
    {
        $data = $this->validated($r, $mesin);
        if ($r->hasFile('media')) $data['media'] = array_merge($mesin->media ?? [], $this->storeMedia($r));
        $mesin->update($data);
        return redirect()->route('manajemen.mesin.index')->with('ok', 'Data mesin diperbarui.');
    }

    public function destroy(Machine $mesin)
    {
        $mesin->delete();
        return back()->with('ok', 'Mesin dihapus.');
    }

    /** Import CSV: kode,nama,kategori,tipe,serial,tgl_beli,lokasi,interval_bulan */
    public function import(Request $r)
    {
        $r->validate(['file' => 'required|file|mimes:csv,txt']);
        $h = fopen($r->file('file')->getRealPath(), 'r');
        fgetcsv($h); // lewati header
        $n = 0;
        while (($row = fgetcsv($h)) !== false) {
            if (count($row) < 7 || !trim($row[0])) continue;
            Machine::updateOrCreate(['code' => trim($row[0])], [
                'name' => $row[1], 'category' => $row[2], 'model' => $row[3], 'serial_number' => $row[4],
                'purchase_date' => $row[5] ?: null, 'location' => $row[6],
                'maintenance_interval_months' => (int) ($row[7] ?? 3) ?: 3,
            ]);
            $n++;
        }
        fclose($h);
        return back()->with('ok', "$n mesin berhasil diimpor.");
    }

    private function validated(Request $r, ?Machine $m = null): array
    {
        return $r->validate([
            'code'  => ['required', 'max:30', Rule::unique('machines', 'code')->ignore($m?->id)],
            'name'  => 'required|max:120',
            'category' => 'nullable|max:80',
            'model' => 'nullable|max:80',
            'serial_number' => 'nullable|max:80',
            'purchase_date' => 'nullable|date',
            'last_service_at' => 'nullable|date',
            'location' => 'required|max:80',
            'condition' => 'nullable|max:120',
            'maintenance_interval_months' => 'required|integer|min:1|max:60',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov|max:20480',
        ]);
    }

    private function storeMedia(Request $r): array
    {
        $out = [];
        foreach ($r->file('media', []) as $f) {
            $out[] = $f->store('machines', 'public');
        }
        return $out;
    }
}
