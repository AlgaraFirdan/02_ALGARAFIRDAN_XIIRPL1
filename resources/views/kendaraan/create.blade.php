<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tambah Kendaraan</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="parkir-body"><div class="form-page"><div class="form-card"><h1>Tambah Kendaraan</h1><p>Isi detail kendaraan baru.</p>
<form method="POST" action="{{ route('kendaraan.store') }}" class="form-grid">@csrf
<label>Plat Nomor<input type="text" name="plat_nomor" value="{{ old('plat_nomor') }}" required>@error('plat_nomor')<small>{{ $message }}</small>@enderror</label>
<label>Jenis Kendaraan<input type="text" name="jenis_kendaraan" value="{{ old('jenis_kendaraan') }}" required>@error('jenis_kendaraan')<small>{{ $message }}</small>@enderror</label>
<label>Warna<input type="text" name="warna" value="{{ old('warna') }}" required>@error('warna')<small>{{ $message }}</small>@enderror</label>
<label>Pemilik<input type="text" name="pemilik" value="{{ old('pemilik') }}" required>@error('pemilik')<small>{{ $message }}</small>@enderror</label>
<label>Penanggung Jawab User<select name="id_user" required><option value="">Pilih user</option>@foreach($users as $user)<option value="{{ $user->id_user }}" @selected((string) old('id_user')===(string)$user->id_user)>{{ $user->nama }} ({{ $user->username }})</option>@endforeach</select>@error('id_user')<small>{{ $message }}</small>@enderror</label>
<div class="form-actions"><a class="btn-ghost" href="{{ route('kendaraan.index') }}">Kembali</a><button class="btn-primary" type="submit">Simpan</button></div>
</form></div></div></body></html>
