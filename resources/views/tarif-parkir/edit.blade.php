<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Tarif Parkir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="parkir-body">
<div class="form-page">
    <div class="form-card">
        <h1>Edit Tarif Parkir</h1>
        <p>Perbarui tarif kendaraan per jam.</p>

        <form method="POST" action="{{ route('tarif-parkir.update', $item->id_tarif) }}" class="form-grid">
            @csrf
            @method('PUT')
            <label>
                Jenis Kendaraan
                <input type="text" name="jenis_kendaraan" value="{{ old('jenis_kendaraan', $item->jenis_kendaraan) }}" required>
                @error('jenis_kendaraan')<small>{{ $message }}</small>@enderror
            </label>

            <label>
                Tarif Per Jam
                <input type="number" name="tarif_per_jam" value="{{ old('tarif_per_jam', (int) $item->tarif_per_jam) }}" min="0" step="100" required>
                @error('tarif_per_jam')<small>{{ $message }}</small>@enderror
            </label>

            <label>
                Status
                <select name="status_aktif" required>
                    <option value="1" @selected((string) old('status_aktif', (int) $item->status_aktif) === '1')>Aktif</option>
                    <option value="0" @selected((string) old('status_aktif', (int) $item->status_aktif) === '0')>Nonaktif</option>
                </select>
                @error('status_aktif')<small>{{ $message }}</small>@enderror
            </label>

            <div class="form-actions">
                <a class="btn-ghost" href="{{ route('tarif-parkir.index') }}">Kembali</a>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
