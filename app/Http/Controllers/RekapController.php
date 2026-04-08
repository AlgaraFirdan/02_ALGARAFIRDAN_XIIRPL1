<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateFilters($request);
        $query = $this->baseQuery($startDate, $endDate);

        $rekap = $query
            ->clone()
            ->orderByDesc('waktu_keluar')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total_transaksi' => $query->clone()->count(),
            'total_pendapatan' => (float) $query->clone()->sum('biaya_total'),
            'rata_durasi_jam' => (float) $query->clone()->avg('durasi_jam'),
        ];

        return view('rekap.index', [
            'rekap' => $rekap,
            'summary' => $summary,
            'filters' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        [$startDate, $endDate] = $this->resolveDateFilters($request);
        $query = $this->baseQuery($startDate, $endDate);

        $data = $query
            ->clone()
            ->orderByDesc('waktu_keluar')
            ->get();

        $summary = [
            'total_transaksi' => $query->clone()->count(),
            'total_pendapatan' => (float) $query->clone()->sum('biaya_total'),
            'rata_durasi_jam' => (float) $query->clone()->avg('durasi_jam'),
        ];

        $pdf = Pdf::loadView('rekap.pdf', [
            'rekap' => $data,
            'summary' => $summary,
            'filters' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'printedAt' => now(),
        ])->setPaper('a4', 'landscape');

        $filename = 'rekap-transaksi-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    private function resolveDateFilters(Request $request): array
    {
        $start = (string) $request->query('start', now()->startOfMonth()->toDateString());
        $end = (string) $request->query('end', now()->toDateString());

        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        return [$startDate, $endDate];
    }

    private function baseQuery(Carbon $startDate, Carbon $endDate): Builder
    {
        return Transaksi::query()
            ->with(['kendaraan', 'areaParkir'])
            ->where('status', 'keluar')
            ->whereBetween('waktu_keluar', [$startDate, $endDate]);
    }
}
