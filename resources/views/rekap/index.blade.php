<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Rekap Transaksi</title>
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
			<a class="menu-item active" href="{{ route('rekap.index') }}">Laporan Rekap</a>
		</nav>
	</aside>

	<main class="main-content">
		<section class="heading-block">
			<h1>Rekap Transaksi</h1>
			<p>Rekap transaksi sesuai rentang waktu yang diminta.</p>
		</section>

		<section class="table-card">
			<div class="table-head">
				<h2>Filter Rekap</h2>
				<form class="table-tools" method="GET" action="{{ route('rekap.index') }}">
					<input type="date" name="start" value="{{ $filters['start'] }}">
					<input type="date" name="end" value="{{ $filters['end'] }}">
					<button type="submit">Terapkan</button>
					<a
						class="btn-primary"
						href="{{ route('rekap.pdf', ['start' => $filters['start'], 'end' => $filters['end']]) }}"
						target="_blank"
					>
						Cetak PDF
					</a>
				</form>
			</div>

			<div class="stats-grid" style="padding: 16px;">
				<article class="stat-card">
					<p>TOTAL TRANSAKSI</p>
					<h3>{{ number_format($summary['total_transaksi']) }}</h3>
				</article>
				<article class="stat-card">
					<p>TOTAL PENDAPATAN</p>
					<h3>Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</h3>
				</article>
				<article class="stat-card">
					<p>RATA DURASI (JAM)</p>
					<h3>{{ number_format($summary['rata_durasi_jam'], 2, ',', '.') }}</h3>
				</article>
			</div>

			<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>PLAT</th>
						<th>JENIS</th>
						<th>AREA</th>
						<th>MASUK</th>
						<th>KELUAR</th>
						<th>DURASI</th>
						<th>TOTAL</th>
					</tr>
					</thead>
					<tbody>
					@forelse($rekap as $item)
						<tr>
							<td>{{ $item->kendaraan?->plat_nomor ?? '-' }}</td>
							<td>{{ $item->kendaraan?->jenis_kendaraan ?? '-' }}</td>
							<td>{{ $item->areaParkir?->nama_area ?? '-' }}</td>
							<td>{{ $item->waktu_masuk?->format('d M Y H:i') ?? '-' }}</td>
							<td>{{ $item->waktu_keluar?->format('d M Y H:i') ?? '-' }}</td>
							<td>{{ $item->durasi_jam }} jam</td>
							<td>Rp {{ number_format((float) $item->biaya_total, 0, ',', '.') }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="empty-row">Tidak ada transaksi pada rentang ini.</td>
						</tr>
					@endforelse
					</tbody>
				</table>
			</div>
		</section>
	</main>
</div>
</body>
</html>
