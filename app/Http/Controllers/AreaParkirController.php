<?php

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaParkirController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $areaParkir = AreaParkir::query()
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where('nama_area', 'like', '%' . $q . '%');
            })
            ->orderBy('id_area')
            ->paginate(10)
            ->withQueryString();

        return view('area-parkir.index', [
            'areaParkir' => $areaParkir,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('area-parkir.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_area' => ['required', 'string', 'max:255'],
            'kapasitas' => ['required', 'integer', 'min:1'],
        ]);

        $item = AreaParkir::create($validated);

        $this->log((int) $request->user()->id_user, 'Menambah area parkir #' . $item->id_area . ' (' . $item->nama_area . ')');

        return redirect()
            ->route('area-parkir.index')
            ->with('success', 'Area parkir berhasil ditambahkan.');
    }

    public function edit(AreaParkir $areaParkir): View
    {
        return view('area-parkir.edit', [
            'item' => $areaParkir,
        ]);
    }

    public function update(Request $request, AreaParkir $areaParkir): RedirectResponse
    {
        $validated = $request->validate([
            'nama_area' => ['required', 'string', 'max:255'],
            'kapasitas' => ['required', 'integer', 'min:1'],
        ]);

        $areaParkir->update($validated);

        $this->log((int) $request->user()->id_user, 'Memperbarui area parkir #' . $areaParkir->id_area . ' (' . $areaParkir->nama_area . ')');

        return redirect()
            ->route('area-parkir.index')
            ->with('success', 'Area parkir berhasil diperbarui.');
    }

    public function destroy(Request $request, AreaParkir $areaParkir): RedirectResponse
    {
        $id = $areaParkir->id_area;
        $nama = $areaParkir->nama_area;
        $areaParkir->delete();

        $this->log((int) $request->user()->id_user, 'Menghapus area parkir #' . $id . ' (' . $nama . ')');

        return redirect()
            ->route('area-parkir.index')
            ->with('success', 'Area parkir berhasil dihapus.');
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
