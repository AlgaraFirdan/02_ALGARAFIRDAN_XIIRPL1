<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area Parkir</title>
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
            <a class="menu-item" href="{{ route('tarif-parkir.index') }}">Tarif Parkir</a>
            <a class="menu-item active" href="{{ route('area-parkir.index') }}">Area Parkir</a>
            <a class="menu-item" href="{{ route('kendaraan.index') }}">Kendaraan</a>
            <a class="menu-item" href="{{ route('log-aktivitas.index') }}">Log Aktivitas</a>
        </nav>
    </aside>

    <main class="main-content">
        <section class="heading-block">
            <h1>Area Parkir</h1>
            <p>Kelola data area parkir dan kapasitas kendaraan.</p>
        </section>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <section class="table-card">
            <div class="table-head">
                <h2>Daftar Area</h2>
                <form class="table-tools" method="GET" action="{{ route('area-parkir.index') }}">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama area...">
                    <button type="submit">Cari</button>
                    <a class="btn-primary" href="{{ route('area-parkir.create') }}">+ Tambah Area</a>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>NAMA AREA</th>
                        <th>KAPASITAS</th>
                        <th>AKSI</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($areaParkir as $item)
                        <tr>
                            <td>{{ $item->id_area }}</td>
                            <td>{{ $item->nama_area }}</td>
                            <td>{{ $item->kapasitas }}</td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn-ghost" href="{{ route('area-parkir.edit', $item->id_area) }}">Edit</a>
                                    <form method="POST" action="{{ route('area-parkir.destroy', $item->id_area) }}" onsubmit="return confirm('Hapus area ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-row">Belum ada data area parkir.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-foot">
                <p>Total data: {{ $areaParkir->total() }}</p>
                <div class="pagination">
                    @if ($areaParkir->onFirstPage())
                        <span class="page-disabled">&lsaquo;</span>
                    @else
                        <a href="{{ $areaParkir->previousPageUrl() }}">&lsaquo;</a>
                    @endif

                    @for ($page = 1; $page <= $areaParkir->lastPage(); $page++)
                        <a href="{{ $areaParkir->url($page) }}" class="{{ $areaParkir->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
                    @endfor

                    @if ($areaParkir->hasMorePages())
                        <a href="{{ $areaParkir->nextPageUrl() }}">&rsaquo;</a>
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
