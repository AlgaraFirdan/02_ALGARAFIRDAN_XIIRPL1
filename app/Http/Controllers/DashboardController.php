<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $stats = [
            'total_kendaraan' => Kendaraan::count(),
            'transaksi_harian' => $this->formatRupiah(
                (float) Transaksi::query()
                    ->whereDate('waktu_keluar', $today)
                    ->where('status', 'keluar')
                    ->sum('biaya_total')
            ),
            'kendaraan_masuk' => Transaksi::query()
                ->whereDate('waktu_masuk', $today)
                ->count(),
            'kendaraan_keluar' => Transaksi::query()
                ->whereDate('waktu_keluar', $today)
                ->where('status', 'keluar')
                ->count(),
        ];

        $transaksiQuery = Transaksi::query()->with('kendaraan');

        if ($q !== '') {
            $transaksiQuery->whereHas('kendaraan', function ($builder) use ($q) {
                $builder->where('plat_nomor', 'like', '%' . $q . '%');
            });
        }

        if (in_array($status, ['masuk', 'keluar'], true)) {
            $transaksiQuery->where('status', $status);
        }

        $transaksiTerbaru = $transaksiQuery
            ->orderByDesc('id_parkir')
            ->paginate(8)
            ->withQueryString();

        $offset = ($transaksiTerbaru->currentPage() - 1) * $transaksiTerbaru->perPage();

        $transaksiTerbaru->setCollection(
            $transaksiTerbaru->getCollection()->values()->map(function ($trx, $index) use ($offset) {
                $isKeluar = $trx->status === 'keluar';

                return [
                    'no' => str_pad((string) ($offset + $index + 1), 2, '0', STR_PAD_LEFT),
                    'plat_nomor' => $trx->kendaraan?->plat_nomor ?? '-',
                    'jenis' => $trx->kendaraan?->jenis_kendaraan ?? '-',
                    'jam_masuk' => optional($trx->waktu_masuk)->format('H:i:s') ?? '-',
                    'jam_keluar' => optional($trx->waktu_keluar)->format('H:i:s') ?? '-',
                    'total_bayar' => $isKeluar ? $this->formatRupiah((float) $trx->biaya_total) : '-',
                    'status' => $isKeluar ? 'selesai' : 'parkir',
                ];
            })
        );

        return view('dashboard', [
            'stats' => $stats,
            'transaksiTerbaru' => $transaksiTerbaru,
            'filters' => [
                'q' => $q,
                'status' => $status,
            ],
            'todayLabel' => now()->format('M d, Y'),
        ]);
    }

    private function formatRupiah(float $nominal): string
    {
        return 'Rp ' . number_format($nominal, 0, ',', '.');
    }
}
