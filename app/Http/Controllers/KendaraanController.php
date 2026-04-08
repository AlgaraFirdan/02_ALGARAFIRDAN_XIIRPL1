<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KendaraanController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $kendaraan = Kendaraan::query()
            ->with('user')
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where('plat_nomor', 'like', '%' . $q . '%')
                    ->orWhere('jenis_kendaraan', 'like', '%' . $q . '%')
                    ->orWhere('pemilik', 'like', '%' . $q . '%');
            })
            ->orderBy('id_kendaraan')
            ->paginate(10)
            ->withQueryString();

        return view('kendaraan.index', [
            'kendaraan' => $kendaraan,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('kendaraan.create', [
            'users' => User::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plat_nomor' => ['required', 'string', 'max:20', 'unique:kendaraan,plat_nomor'],
            'jenis_kendaraan' => ['required', 'string', 'max:100'],
            'warna' => ['required', 'string', 'max:100'],
            'pemilik' => ['required', 'string', 'max:255'],
            'id_user' => ['required', 'integer', 'exists:users,id_user'],
        ]);

        $item = Kendaraan::query()->create([
            'plat_nomor' => strtoupper(trim($validated['plat_nomor'])),
            'jenis_kendaraan' => $validated['jenis_kendaraan'],
            'warna' => $validated['warna'],
            'pemilik' => $validated['pemilik'],
            'id_user' => (int) $validated['id_user'],
        ]);

        $this->log((int) $request->user()->id_user, 'Menambah kendaraan #' . $item->id_kendaraan . ' (' . $item->plat_nomor . ')');

        return redirect()->route('kendaraan.index')->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function edit(Kendaraan $kendaraan): View
    {
        return view('kendaraan.edit', [
            'item' => $kendaraan,
            'users' => User::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $validated = $request->validate([
            'plat_nomor' => ['required', 'string', 'max:20', 'unique:kendaraan,plat_nomor,' . $kendaraan->id_kendaraan . ',id_kendaraan'],
            'jenis_kendaraan' => ['required', 'string', 'max:100'],
            'warna' => ['required', 'string', 'max:100'],
            'pemilik' => ['required', 'string', 'max:255'],
            'id_user' => ['required', 'integer', 'exists:users,id_user'],
        ]);

        $kendaraan->update([
            'plat_nomor' => strtoupper(trim($validated['plat_nomor'])),
            'jenis_kendaraan' => $validated['jenis_kendaraan'],
            'warna' => $validated['warna'],
            'pemilik' => $validated['pemilik'],
            'id_user' => (int) $validated['id_user'],
        ]);

        $this->log((int) $request->user()->id_user, 'Memperbarui kendaraan #' . $kendaraan->id_kendaraan . ' (' . $kendaraan->plat_nomor . ')');

        return redirect()->route('kendaraan.index')->with('success', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $id = $kendaraan->id_kendaraan;
        $plat = $kendaraan->plat_nomor;
        $kendaraan->delete();

        $this->log((int) $request->user()->id_user, 'Menghapus kendaraan #' . $id . ' (' . $plat . ')');

        return redirect()->route('kendaraan.index')->with('success', 'Kendaraan berhasil dihapus.');
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
