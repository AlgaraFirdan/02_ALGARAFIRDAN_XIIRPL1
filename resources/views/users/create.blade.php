<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tambah User</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="parkir-body"><div class="form-page"><div class="form-card"><h1>Tambah User</h1><p>Isi data akun baru.</p>
<form method="POST" action="{{ route('users.store') }}" class="form-grid">@csrf
<label>Nama<input type="text" name="nama" value="{{ old('nama') }}" required>@error('nama')<small>{{ $message }}</small>@enderror</label>
<label>Username<input type="text" name="username" value="{{ old('username') }}" required>@error('username')<small>{{ $message }}</small>@enderror</label>
<label>Password<input type="password" name="password" required>@error('password')<small>{{ $message }}</small>@enderror</label>
<label>Role<select name="role" required><option value="admin">Admin</option><option value="petugas">Petugas</option><option value="owner">Owner</option></select>@error('role')<small>{{ $message }}</small>@enderror</label>
<label>Status<select name="status_aktif" required><option value="1">Aktif</option><option value="0">Nonaktif</option></select>@error('status_aktif')<small>{{ $message }}</small>@enderror</label>
<div class="form-actions"><a class="btn-ghost" href="{{ route('users.index') }}">Kembali</a><button class="btn-primary" type="submit">Simpan</button></div>
</form></div></div></body></html>
