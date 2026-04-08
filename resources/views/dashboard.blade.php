<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Precision Flow Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="parkir-body">
<div class="parkir-shell">
    @php
        $currentUser = auth()->user();
        $roleLabel = match ($currentUser->role) {
            'admin' => 'SYSTEM MANAGER',
            'petugas' => 'PARKING OPERATOR',
            'owner' => 'BUSINESS OWNER',
            default => strtoupper((string) $currentUser->role),
        };
        $avatarInitials = collect(explode(' ', trim((string) $currentUser->nama)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');
        if ($avatarInitials === '') {
            $avatarInitials = strtoupper(substr((string) $currentUser->username, 0, 2));
        }
    @endphp

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-dot">P</div>
            <div class="brand-title">Precision Flow</div>
        </div>

        <nav class="menu">
            <a class="menu-item active" href="{{ route('dashboard') }}">Dashboard</a>
            @if (auth()->user()->role === 'admin')
                <a class="menu-item" href="{{ route('users.index') }}">User Management</a>
                <a class="menu-item" href="{{ route('tarif-parkir.index') }}">Tarif Parkir</a>
                <a class="menu-item" href="{{ route('area-parkir.index') }}">Area Parkir</a>
                <a class="menu-item" href="{{ route('kendaraan.index') }}">Kendaraan</a>
                <a class="menu-item" href="{{ route('log-aktivitas.index') }}">Log Aktivitas</a>
            @endif

            @if (auth()->user()->role === 'petugas')
                <a class="menu-item" href="{{ route('transaksi.masuk') }}">Input Kendaraan Masuk</a>
                <a class="menu-item" href="{{ route('transaksi.keluar') }}">Transaksi Keluar</a>
                <a class="menu-item" href="{{ route('transaksi.keluar') }}">Cetak Struk</a>
            @endif

            @if (auth()->user()->role === 'owner')
                <a class="menu-item" href="{{ route('rekap.index') }}">Rekap Transaksi</a>
            @endif
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left"></div>
            <div class="topbar-right">
                <div class="status-chip">
                    <span class="dot"></span>
                    System Online
                </div>
                <div class="date-chip">{{ $todayLabel }}</div>
                <div class="user-box">
                    <div>
                        <div class="user-name">{{ $currentUser->nama }}</div>
                        <div class="user-role">{{ $roleLabel }}</div>
                    </div>
                    <div class="avatar">{{ $avatarInitials }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout" type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <section class="heading-block">
            <h1>Overview Dashboard</h1>
            <p>Real-time parking statistics for Avenue Core Infrastructure.</p>
        </section>

        <section class="stats-grid">
            <article class="stat-card">
                <span class="icon blue">&#128663;</span>
                <p>TOTAL KENDARAAN</p>
                <h3>{{ number_format($stats['total_kendaraan']) }}</h3>
            </article>
            <article class="stat-card">
                <span class="icon violet">&#128179;</span>
                <p>TRANSAKSI HARI INI</p>
                <h3>{{ $stats['transaksi_harian'] }}</h3>
            </article>
            <article class="stat-card">
                <span class="icon green">&#10145;</span>
                <p>KENDARAAN MASUK</p>
                <h3>{{ $stats['kendaraan_masuk'] }}</h3>
            </article>
            <article class="stat-card">
                <span class="icon red">&#11013;</span>
                <p>KENDARAAN KELUAR</p>
                <h3>{{ $stats['kendaraan_keluar'] }}</h3>
            </article>
        </section>

        <section class="table-card">
            <div class="table-head">
                <h2>Transaksi Terbaru</h2>
                <form class="table-tools" method="GET" action="{{ route('dashboard') }}">
                    <input
                        type="text"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="Cari plat nomor..."
                    >
                    <select name="status" aria-label="Filter status">
                        <option value="">Semua Status</option>
                        <option value="masuk" @selected($filters['status'] === 'masuk')>Masuk / Parkir</option>
                        <option value="keluar" @selected($filters['status'] === 'keluar')>Keluar / Selesai</option>
                    </select>
                    <button type="submit">Filter</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>NO</th>
                        <th>PLAT NOMOR</th>
                        <th>JENIS</th>
                        <th>JAM MASUK</th>
                        <th>JAM KELUAR</th>
                        <th>TOTAL BAYAR</th>
                        <th>STATUS</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($transaksiTerbaru as $item)
                        <tr>
                            <td>{{ $item['no'] }}</td>
                            <td><span class="plate">{{ $item['plat_nomor'] }}</span></td>
                            <td>{{ $item['jenis'] }}</td>
                            <td>{{ $item['jam_masuk'] }}</td>
                            <td>{{ $item['jam_keluar'] }}</td>
                            <td class="payment">{{ $item['total_bayar'] }}</td>
                            <td>
                                <span class="badge {{ $item['status'] === 'selesai' ? 'done' : 'parked' }}">
                                    {{ strtoupper($item['status']) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-row">Belum ada data transaksi.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-foot">
                <p>Menampilkan {{ $transaksiTerbaru->count() }} dari {{ $transaksiTerbaru->total() }} transaksi</p>
                <div class="pagination">
                    @if ($transaksiTerbaru->onFirstPage())
                        <span class="page-disabled">&lsaquo;</span>
                    @else
                        <a href="{{ $transaksiTerbaru->previousPageUrl() }}">&lsaquo;</a>
                    @endif

                    @for ($page = 1; $page <= $transaksiTerbaru->lastPage(); $page++)
                        <a
                            href="{{ $transaksiTerbaru->url($page) }}"
                            class="{{ $transaksiTerbaru->currentPage() === $page ? 'active' : '' }}"
                        >
                            {{ $page }}
                        </a>
                    @endfor

                    @if ($transaksiTerbaru->hasMorePages())
                        <a href="{{ $transaksiTerbaru->nextPageUrl() }}">&rsaquo;</a>
                    @else
                        <span class="page-disabled">&rsaquo;</span>
                    @endif
                </div>
            </div>
        </section>

        <footer class="footer">&copy; 2023 PRECISION FLOW MANAGEMENT - INTELLIGENCE IN MOTION</footer>
    </main>
</div>
</body>
</html>
