<?php

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\Kendaraan;
use App\Models\LogAktivitas;
use App\Models\TarifParkir;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    /**
     * Menampilkan form input kendaraan masuk beserta daftar area parkir aktif.
     */
    public function masukForm(): View
    {
        return view('transaksi.masuk', [
            'areas' => AreaParkir::query()->orderBy('nama_area')->get(),
            'autoTime' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Menyimpan transaksi kendaraan masuk setelah validasi plat, jenis, area, dan tarif aktif.
     */
    public function storeMasuk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plat_nomor' => ['required', 'string', 'max:20'],
            'jenis_kendaraan' => ['required', 'string', 'max:100'],
            'id_area' => ['required', 'integer', 'exists:area_parkir,id_area'],
        ]);

        $operatorId = $this->operatorUserId();

        $platNomor = strtoupper(trim($validated['plat_nomor']));

        // Plat dipakai sebagai identitas unik kendaraan untuk mencegah duplikasi data master.
        $kendaraan = Kendaraan::query()->firstOrCreate(
            ['plat_nomor' => $platNomor],
            [
                'jenis_kendaraan' => $validated['jenis_kendaraan'],
                'warna' => '-',
                'pemilik' => '-',
                'id_user' => $operatorId,
            ]
        );

        if ($kendaraan->jenis_kendaraan !== $validated['jenis_kendaraan']) {
            $kendaraan->update(['jenis_kendaraan' => $validated['jenis_kendaraan']]);
        }

        $tarif = TarifParkir::query()
            ->whereRaw('LOWER(jenis_kendaraan) = ?', [strtolower($validated['jenis_kendaraan'])])
            ->where('status_aktif', 1)
            ->first();

        if (! $tarif) {
            return back()
                ->withInput()
                ->withErrors(['jenis_kendaraan' => 'Tarif untuk jenis kendaraan ini belum tersedia.']);
        }

        // Cegah input masuk ganda ketika kendaraan masih tercatat parkir aktif.
        $aktif = Transaksi::query()
            ->where('id_kendaraan', $kendaraan->id_kendaraan)
            ->where('status', 'masuk')
            ->exists();

        if ($aktif) {
            return back()
                ->withInput()
                ->withErrors(['plat_nomor' => 'Kendaraan ini masih tercatat sebagai parkir aktif.']);
        }

        Transaksi::query()->create([
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'waktu_masuk' => now(),
            'waktu_keluar' => null,
            'id_tarif' => $tarif->id_tarif,
            'durasi_jam' => 0,
            'biaya_total' => 0,
            'status' => 'masuk',
            'id_user' => $operatorId,
            'id_area' => (int) $validated['id_area'],
        ]);

        $this->log((int) $request->user()->id_user, 'Input kendaraan masuk (' . $platNomor . ')');

        return redirect()
            ->route('transaksi.masuk')
            ->with('success', 'Data kendaraan masuk berhasil disimpan.');
    }

    /**
     * Menampilkan halaman transaksi keluar, termasuk kalkulasi biaya realtime.
     */
    public function keluarForm(Request $request): View
    {
        $q = strtoupper(trim((string) $request->query('q', '')));
        $tarifPerJam = 0;
        $transaksi = null;
        $kalkulasi = null;
        $lastStruk = null;

        // last_keluar_id dipakai untuk menampilkan data struk terakhir setelah redirect proses keluar.
        $lastKeluarId = (int) $request->session()->get('last_keluar_id', 0);
        if ($lastKeluarId > 0) {
            $lastStruk = Transaksi::query()
                ->with(['kendaraan', 'areaParkir'])
                ->where('id_parkir', $lastKeluarId)
                ->where('status', 'keluar')
                ->first();
        }

        if ($q !== '') {
            $transaksi = Transaksi::query()
                ->with(['kendaraan', 'areaParkir', 'tarifParkir'])
                ->where('status', 'masuk')
                ->whereHas('kendaraan', function ($builder) use ($q) {
                    $builder->where('plat_nomor', 'like', '%' . $q . '%');
                })
                ->orderByDesc('id_parkir')
                ->first();

            if ($transaksi) {
                $now = now();
                // Minimal durasi dibaca 1 menit agar biaya minimum 1 jam tetap terhitung.
                $menit = max(1, $transaksi->waktu_masuk->diffInMinutes($now));
                $durasiJam = (int) ceil($menit / 60);
                $tarifPerJam = (int) round((float) ($transaksi->tarifParkir?->tarif_per_jam ?? 0));

                $kalkulasi = [
                    'waktu_keluar' => $now,
                    'durasi_jam' => $durasiJam,
                    'durasi_label' => $this->durationLabel($menit),
                    'tarif_per_jam' => $tarifPerJam,
                    'total' => $durasiJam * $tarifPerJam,
                ];
            }
        }

        return view('transaksi.keluar', [
            'q' => $q,
            'transaksi' => $transaksi,
            'kalkulasi' => $kalkulasi,
            'tarifPerJam' => $tarifPerJam,
            'lastStruk' => $lastStruk,
            'autoPrint' => (bool) $request->session()->get('auto_print', false),
            'nowLabel' => now()->format('d M Y, H:i'),
        ]);
    }

    /**
     * Memproses kendaraan keluar, menghitung durasi dan total biaya, lalu kembali ke halaman keluar.
     */
    public function prosesKeluar(Transaksi $transaksi): RedirectResponse
    {
        if ($transaksi->status !== 'masuk') {
            return redirect()
                ->route('transaksi.keluar')
                ->withErrors(['q' => 'Transaksi sudah diproses keluar.']);
        }

        $transaksi->loadMissing(['kendaraan', 'tarifParkir']);

        $now = now();
        // Formula biaya: ceil(durasi menit / 60) * tarif per jam.
        $menit = max(1, $transaksi->waktu_masuk->diffInMinutes($now));
        $durasiJam = (int) ceil($menit / 60);
        $tarifPerJam = (int) round((float) ($transaksi->tarifParkir?->tarif_per_jam ?? 0));

        $transaksi->update([
            'waktu_keluar' => $now,
            'durasi_jam' => $durasiJam,
            'biaya_total' => $durasiJam * $tarifPerJam,
            'status' => 'keluar',
            'id_user' => $this->operatorUserId(),
        ]);

        $this->log((int) (Auth::id() ?? 0), 'Proses kendaraan keluar (' . ($transaksi->kendaraan?->plat_nomor ?? '-') . ')');

        return redirect()
            ->route('transaksi.keluar', ['q' => $transaksi->kendaraan?->plat_nomor])
            ->with('success', 'Kendaraan keluar berhasil diproses.')
            ->with('last_keluar_id', $transaksi->id_parkir)
            ->with('auto_print', true);
    }

    /**
     * Mengambil id user operator default saat id user eksplisit belum tersedia.
     */
    private function operatorUserId(): int
    {
        $user = User::query()->first();

        if (! $user) {
            $user = User::query()->create([
                'nama' => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status_aktif' => 1,
            ]);
        }

        return (int) $user->id_user;
    }

    /**
     * Mengubah total menit menjadi label jam-menit untuk kebutuhan UI.
     */
    private function durationLabel(int $totalMenit): string
    {
        $jam = intdiv($totalMenit, 60);
        $menit = $totalMenit % 60;

        return $jam . ' Jam ' . $menit . ' Menit';
    }

    /**
     * Menyimpan aktivitas pengguna ke tabel log_aktivitas.
     */
    private function log(int $idUser, string $aktivitas): void
    {
        if ($idUser <= 0) {
            return;
        }

        LogAktivitas::query()->create([
            'id_user' => $idUser,
            'aktivitas' => $aktivitas,
            'waktu' => now(),
        ]);
    }
}
