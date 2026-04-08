<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tarif Parkir</title>
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
            <a class="menu-item" href="{{ route('users.index') }}">User Management</a>
            <a class="menu-item active" href="{{ route('tarif-parkir.index') }}">Tarif Parkir</a>
            <a class="menu-item" href="{{ route('area-parkir.index') }}">Area Parkir</a>
            <a class="menu-item" href="{{ route('kendaraan.index') }}">Kendaraan</a>
            <a class="menu-item" href="{{ route('log-aktivitas.index') }}">Log Aktivitas</a>
        </nav>
    </aside>

    <main class="main-content">
        <section class="heading-block">
            <h1>Tarif Parkir</h1>
            <p>Kelola tarif kendaraan per jam secara dinamis.</p>
        </section>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <section class="table-card">
            <div class="table-head">
                <h2>Daftar Tarif</h2>
                <form class="table-tools" method="GET" action="{{ route('tarif-parkir.index') }}">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari jenis kendaraan...">
                    <button type="submit">Cari</button>
                    <a class="btn-primary" href="{{ route('tarif-parkir.create') }}">+ Tambah Tarif</a>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>JENIS KENDARAAN</th>
                        <th>TARIF / JAM</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($tarifParkir as $item)
                        <tr>
                            <td>{{ $item->id_tarif }}</td>
                            <td>{{ $item->jenis_kendaraan }}</td>
                            <td>Rp {{ number_format((float) $item->tarif_per_jam, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ (int) $item->status_aktif === 1 ? 'done' : 'parked' }}">
                                    {{ (int) $item->status_aktif === 1 ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn-ghost" href="{{ route('tarif-parkir.edit', $item->id_tarif) }}">Edit</a>
                                    <form method="POST" action="{{ route('tarif-parkir.destroy', $item->id_tarif) }}" onsubmit="return confirm('Hapus tarif ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-row">Belum ada data tarif parkir.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-foot">
                <p>Total data: {{ $tarifParkir->total() }}</p>
                <div class="pagination">
                    @if ($tarifParkir->onFirstPage())
                        <span class="page-disabled">&lsaquo;</span>
                    @else
                        <a href="{{ $tarifParkir->previousPageUrl() }}">&lsaquo;</a>
                    @endif

                    @for ($page = 1; $page <= $tarifParkir->lastPage(); $page++)
                        <a href="{{ $tarifParkir->url($page) }}" class="{{ $tarifParkir->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
                    @endfor

                    @if ($tarifParkir->hasMorePages())
                        <a href="{{ $tarifParkir->nextPageUrl() }}">&rsaquo;</a>
                    @else
                        <span class="page-disabled">&rsaquo;</span>
                    @endif
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
