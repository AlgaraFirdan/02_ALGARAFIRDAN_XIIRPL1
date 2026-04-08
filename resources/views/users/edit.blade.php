<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit User</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="parkir-body"><div class="form-page"><div class="form-card"><h1>Edit User</h1><p>Perbarui data akun.</p>
<form method="POST" action="{{ route('users.update', $item->id_user) }}" class="form-grid">@csrf @method('PUT')
<label>Nama<input type="text" name="nama" value="{{ old('nama', $item->nama) }}" required>@error('nama')<small>{{ $message }}</small>@enderror</label>
<label>Username<input type="text" name="username" value="{{ old('username', $item->username) }}" required>@error('username')<small>{{ $message }}</small>@enderror</label>
<label>Password (opsional)<input type="password" name="password">@error('password')<small>{{ $message }}</small>@enderror</label>
<label>Role<select name="role" required><option value="admin" @selected(old('role', $item->role)==='admin')>Admin</option><option value="petugas" @selected(old('role', $item->role)==='petugas')>Petugas</option><option value="owner" @selected(old('role', $item->role)==='owner')>Owner</option></select>@error('role')<small>{{ $message }}</small>@enderror</label>
<label>Status<select name="status_aktif" required><option value="1" @selected((string) old('status_aktif', (int) $item->status_aktif)==='1')>Aktif</option><option value="0" @selected((string) old('status_aktif', (int) $item->status_aktif)==='0')>Nonaktif</option></select>@error('status_aktif')<small>{{ $message }}</small>@enderror</label>
<div class="form-actions"><a class="btn-ghost" href="{{ route('users.index') }}">Kembali</a><button class="btn-primary" type="submit">Update</button></div>
</form></div></div></body></html>
