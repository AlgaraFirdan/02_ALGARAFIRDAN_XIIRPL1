<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Kendaraan Keluar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="parkir-body">
<div class="parkir-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-dot">P</div>
            <div class="brand-title">Precision Flow</div>
        </div>

        <nav class="menu">
            <a class="menu-item" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="menu-item" href="{{ route('transaksi.masuk') }}">Input Kendaraan Masuk</a>
            <a class="menu-item active" href="{{ route('transaksi.keluar') }}">Transaksi Keluar</a>
        </nav>
    </aside>

    <main class="main-content">
        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <section class="heading-row">
            <div>
                <h1 class="page-title">Transaksi Kendaraan Keluar</h1>
                <p class="page-subtitle">Cari kendaraan, hitung biaya, dan cetak struk</p>
                <div class="tab-links">
                    <a href="{{ route('transaksi.masuk') }}">Input Kendaraan Masuk</a>
                    <a class="active" href="{{ route('transaksi.keluar') }}">Transaksi Kendaraan Keluar</a>
                </div>
            </div>
            <div class="current-date-box">
                <small>CURRENT DATE</small>
                <strong>{{ $nowLabel }}</strong>
            </div>
        </section>

        <section class="outbound-grid">
            @php
                $receipt = $lastStruk ?? $transaksi;
                $receiptTotal = $lastStruk
                    ? (int) round((float) $lastStruk->biaya_total)
                    : ($kalkulasi['total'] ?? 0);
                $receiptMasuk = $receipt?->waktu_masuk?->format('H:i') ?? '-';
                $receiptKeluar = $lastStruk
                    ? ($lastStruk->waktu_keluar?->format('H:i') ?? '-')
                    : ($kalkulasi ? $kalkulasi['waktu_keluar']->format('H:i') : '-');
                $receiptNo = $receipt
                    ? 'PF-TRX-' . str_pad((string) $receipt->id_parkir, 6, '0', STR_PAD_LEFT)
                    : 'PF-TRX-102433';
            @endphp

            <div class="outbound-main">
                <div class="step-box">
                    <h3><span>1</span> Cari Kendaraan</h3>
                    <form method="GET" action="{{ route('transaksi.keluar') }}" class="search-row">
                        <input name="q" value="{{ $q }}" type="text" placeholder="Masukkan Plat Nomor (Contoh: B 1234 ABC)">
                        <button class="btn-primary" type="submit">Cari</button>
                    </form>
                    @error('q')<small>{{ $message }}</small>@enderror
                    @if ($q !== '' && ! $transaksi)
                        <small class="text-muted">Kendaraan dengan plat tersebut tidak ditemukan di parkir aktif.</small>
                    @endif
                </div>

                <div class="step-box">
                    <h3><span>2</span> Detail Kendaraan</h3>
                    <div class="detail-grid">
                        <div>
                            <label>Plat Nomor</label>
                            <p>{{ $transaksi?->kendaraan?->plat_nomor ?? '-' }}</p>
                        </div>
                        <div>
                            <label>Jenis Kendaraan</label>
                            <p>{{ $transaksi?->kendaraan?->jenis_kendaraan ?? '-' }}</p>
                        </div>
                        <div>
                            <label>Area Parkir</label>
                            <p>{{ $transaksi?->areaParkir?->nama_area ?? '-' }}</p>
                        </div>
                        <div>
                            <label>Waktu Masuk</label>
                            <p>{{ $transaksi?->waktu_masuk?->format('d M Y, H:i:s') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="step-box">
                    <h3><span>3</span> Perhitungan Otomatis</h3>
                    <div class="detail-grid compact">
                        <div>
                            <label>Waktu Keluar</label>
                            <p>{{ $kalkulasi ? $kalkulasi['waktu_keluar']->format('H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <label>Durasi</label>
                            <p>{{ $kalkulasi['durasi_label'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label>Tarif / Jam</label>
                            <p>Rp {{ number_format($tarifPerJam, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="total-panel">
                        <div>
                            <small>TOTAL BAYAR</small>
                            <h2>{{ $kalkulasi ? 'Rp ' . number_format($kalkulasi['total'], 0, ',', '.') : 'Rp 0' }}</h2>
                        </div>
                        <div class="total-actions">
                            @if ($transaksi)
                                <form method="POST" action="{{ route('transaksi.keluar.proses', $transaksi->id_parkir) }}">
                                    @csrf
                                    <button class="btn-white-pill" type="submit">Proses &amp; Cetak Struk</button>
                                </form>
                            @else
                                <button class="btn-white-pill" type="button" disabled>Proses &amp; Cetak Struk</button>
                            @endif
                            <a class="btn-ghost dark-pill" href="{{ route('transaksi.keluar') }}">Reset</a>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="receipt-card">
                <div class="receipt-head">
                    <h3>PRECISION FLOW</h3>
                    <p>SMART PARKING SOLUTIONS</p>
                </div>
                <div class="receipt-line"></div>
                <div class="receipt-body">
                    <div><span>No. Struk:</span><b>{{ $receiptNo }}</b></div>
                    <div><span>Petugas:</span><b>{{ strtoupper((string) auth()->user()->username) }}</b></div>
                    <div><span>Kendaraan</span><b>{{ $receipt?->kendaraan?->plat_nomor ?? '-' }}</b></div>
                    <div><span>Tipe</span><b>{{ $receipt?->kendaraan?->jenis_kendaraan ?? '-' }}</b></div>
                    <div><span>Masuk</span><b>{{ $receiptMasuk }}</b></div>
                    <div><span>Keluar</span><b>{{ $receiptKeluar }}</b></div>
                </div>
                <div class="receipt-line"></div>
                <div class="receipt-total">
                    <span>TOTAL</span>
                    <strong>{{ 'Rp ' . number_format($receiptTotal, 0, ',', '.') }}</strong>
                </div>
                @if ($lastStruk)
                    <div class="total-actions" style="margin-top: 16px; justify-content: flex-end;">
                        <button class="btn-primary" type="button" onclick="window.print()">Cetak Ulang Struk</button>
                    </div>
                @endif
            </aside>

        </section>
    </main>
</div>
@if ($autoPrint && $lastStruk)
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
@endif
</body>
</html>
