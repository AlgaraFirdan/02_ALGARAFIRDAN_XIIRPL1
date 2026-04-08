<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="parkir-body">
<div class="parkir-shell">
    <aside class="sidebar">
        <div class="brand"><div class="brand-dot">P</div><div class="brand-title">Precision Flow</div></div>
        <nav class="menu">
            <a class="menu-item" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="menu-item active" href="{{ route('users.index') }}">User Management</a>
            <a class="menu-item" href="{{ route('tarif-parkir.index') }}">Tarif Parkir</a>
            <a class="menu-item" href="{{ route('area-parkir.index') }}">Area Parkir</a>
            <a class="menu-item" href="{{ route('kendaraan.index') }}">Kendaraan</a>
            <a class="menu-item" href="{{ route('log-aktivitas.index') }}">Log Aktivitas</a>
        </nav>
    </aside>

    <main class="main-content">
        <section class="heading-block"><h1>User Management</h1><p>Kelola akun admin, petugas, dan owner.</p></section>
        @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif

        <section class="table-card">
            <div class="table-head">
                <h2>Daftar User</h2>
                <form class="table-tools" method="GET" action="{{ route('users.index') }}">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama/username...">
                    <button type="submit">Cari</button>
                    <a class="btn-primary" href="{{ route('users.create') }}">+ Tambah User</a>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>NAMA</th><th>USERNAME</th><th>ROLE</th><th>STATUS</th><th>AKSI</th></tr></thead>
                    <tbody>
                    @forelse ($users as $item)
                        <tr>
                            <td>{{ $item->id_user }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->username }}</td>
                            <td>{{ strtoupper($item->role) }}</td>
                            <td><span class="badge {{ (int) $item->status_aktif === 1 ? 'done' : 'parked' }}">{{ (int) $item->status_aktif === 1 ? 'AKTIF' : 'NONAKTIF' }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn-ghost" href="{{ route('users.edit', $item->id_user) }}">Edit</a>
                                    <form method="POST" action="{{ route('users.destroy', $item->id_user) }}" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-row">Belum ada data user.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
