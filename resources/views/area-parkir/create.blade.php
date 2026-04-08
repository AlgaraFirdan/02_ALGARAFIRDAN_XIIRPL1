<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Area Parkir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="parkir-body">
<div class="form-page">
    <div class="form-card">
        <h1>Tambah Area Parkir</h1>
        <p>Isi data area parkir baru.</p>

        <form method="POST" action="{{ route('area-parkir.store') }}" class="form-grid">
            @csrf
            <label>
                Nama Area
                <input type="text" name="nama_area" value="{{ old('nama_area') }}" required>
                @error('nama_area')<small>{{ $message }}</small>@enderror
            </label>

            <label>
                Kapasitas
                <input type="number" name="kapasitas" value="{{ old('kapasitas') }}" min="1" required>
                @error('kapasitas')<small>{{ $message }}</small>@enderror
            </label>

            <div class="form-actions">
                <a class="btn-ghost" href="{{ route('area-parkir.index') }}">Kembali</a>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
