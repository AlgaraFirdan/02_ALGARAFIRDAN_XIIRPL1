<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekap Transaksi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .header {
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .subtitle {
            margin: 4px 0;
            color: #444;
        }

        .summary {
            margin: 12px 0;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .summary-row {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f0f4f8;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 12px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Laporan Rekap Transaksi Parkir</h1>
        <p class="subtitle">Periode: {{ $filters['start'] }} s/d {{ $filters['end'] }}</p>
        <p class="subtitle">Dicetak: {{ $printedAt->format('d M Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row"><strong>Total Transaksi:</strong> {{ number_format($summary['total_transaksi']) }}</div>
        <div class="summary-row"><strong>Total Pendapatan:</strong> Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</div>
        <div class="summary-row"><strong>Rata Durasi:</strong> {{ number_format($summary['rata_durasi_jam'], 2, ',', '.') }} jam</div>
    </div>

    <table>
        <thead>
        <tr>
            <th class="center">No</th>
            <th>Plat</th>
            <th>Jenis</th>
            <th>Area</th>
            <th>Masuk</th>
            <th>Keluar</th>
            <th class="center">Durasi</th>
            <th class="right">Total</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($rekap as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $item->kendaraan?->plat_nomor ?? '-' }}</td>
                <td>{{ $item->kendaraan?->jenis_kendaraan ?? '-' }}</td>
                <td>{{ $item->areaParkir?->nama_area ?? '-' }}</td>
                <td>{{ $item->waktu_masuk?->format('d M Y H:i') ?? '-' }}</td>
                <td>{{ $item->waktu_keluar?->format('d M Y H:i') ?? '-' }}</td>
                <td class="center">{{ $item->durasi_jam }} jam</td>
                <td class="right">Rp {{ number_format((float) $item->biaya_total, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="center">Tidak ada transaksi pada rentang ini.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">
        Precision Flow - Sistem Parkir
    </div>
</body>
</html>
