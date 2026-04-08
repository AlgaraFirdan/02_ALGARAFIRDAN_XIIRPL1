<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\TarifParkir;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TarifParkirController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $tarifParkir = TarifParkir::query()
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where('jenis_kendaraan', 'like', '%' . $q . '%');
            })
            ->orderBy('id_tarif')
            ->paginate(10)
            ->withQueryString();

        return view('tarif-parkir.index', [
            'tarifParkir' => $tarifParkir,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('tarif-parkir.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_kendaraan' => ['required', 'string', 'max:100', 'unique:tarif_parkir,jenis_kendaraan'],
            'tarif_per_jam' => ['required', 'numeric', 'min:0'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        $item = TarifParkir::query()->create($validated);

        $this->log((int) $request->user()->id_user, 'Menambah tarif #' . $item->id_tarif . ' (' . $item->jenis_kendaraan . ')');

        return redirect()->route('tarif-parkir.index')->with('success', 'Tarif parkir berhasil ditambahkan.');
    }

    public function edit(TarifParkir $tarifParkir): View
    {
        return view('tarif-parkir.edit', [
            'item' => $tarifParkir,
        ]);
    }

    public function update(Request $request, TarifParkir $tarifParkir): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_kendaraan' => ['required', 'string', 'max:100', 'unique:tarif_parkir,jenis_kendaraan,' . $tarifParkir->id_tarif . ',id_tarif'],
            'tarif_per_jam' => ['required', 'numeric', 'min:0'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        $tarifParkir->update($validated);

        $this->log((int) $request->user()->id_user, 'Memperbarui tarif #' . $tarifParkir->id_tarif . ' (' . $tarifParkir->jenis_kendaraan . ')');

        return redirect()->route('tarif-parkir.index')->with('success', 'Tarif parkir berhasil diperbarui.');
    }

    public function destroy(TarifParkir $tarifParkir): RedirectResponse
    {
        $id = $tarifParkir->id_tarif;
        $jenis = $tarifParkir->jenis_kendaraan;
        $tarifParkir->delete();

        $this->log((int) request()->user()->id_user, 'Menghapus tarif #' . $id . ' (' . $jenis . ')');

        return redirect()->route('tarif-parkir.index')->with('success', 'Tarif parkir berhasil dihapus.');
    }

    private function log(int $idUser, string $aktivitas): void
    {
        LogAktivitas::query()->create([
            'id_user' => $idUser,
            'aktivitas' => $aktivitas,
            'waktu' => now(),
        ]);
    }
}
