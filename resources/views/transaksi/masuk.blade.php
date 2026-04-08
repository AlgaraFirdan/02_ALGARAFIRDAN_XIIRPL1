<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Input Kendaraan Masuk</title>
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
            <a class="menu-item active" href="{{ route('transaksi.masuk') }}">Input Kendaraan Masuk</a>
            <a class="menu-item" href="{{ route('transaksi.keluar') }}">Transaksi Keluar</a>
            <a class="menu-item" href="{{ route('transaksi.keluar') }}">Cetak Struk</a>
        </nav>
    </aside>

    <main class="main-content">
        <section class="heading-row">
            <div>
                <h1 class="page-title">Input Kendaraan Masuk</h1>
                <p class="page-subtitle">Masukkan data kendaraan yang masuk ke area parkir</p>
            </div>
            <a class="link-back" href="{{ route('dashboard') }}">&larr; Kembali ke Daftar</a>
        </section>

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <section class="inbound-card">
            <div class="inbound-header">
                <div class="square-icon">&#9998;</div>
                <div>
                    <h2>Form Kendaraan</h2>
                    <p>Lengkapi field dibawah ini secara akurat</p>
                </div>
            </div>

            <form method="POST" action="{{ route('transaksi.masuk.store') }}" class="inbound-form">
                @csrf
                <div>
                    <label for="plat_nomor">Plat Nomor</label>
                    <input id="plat_nomor" name="plat_nomor" type="text" value="{{ old('plat_nomor') }}" placeholder="B 1234 XYZ" required>
                    @error('plat_nomor')<small>{{ $message }}</small>@enderror
                </div>

                <div>
                    <label for="waktu_masuk">Waktu Masuk (Auto)</label>
                    <input id="waktu_masuk" type="text" value="{{ $autoTime }}" readonly>
                </div>

                <div>
                    <label for="jenis_kendaraan">Jenis Kendaraan</label>
                    <select id="jenis_kendaraan" name="jenis_kendaraan" required>
                        <option value="">Pilih jenis</option>
                        <option value="Mobil" @selected(old('jenis_kendaraan') === 'Mobil')>Mobil</option>
                        <option value="Motor" @selected(old('jenis_kendaraan') === 'Motor')>Motor</option>
                        <option value="Bus" @selected(old('jenis_kendaraan') === 'Bus')>Bus</option>
                        <option value="Truk" @selected(old('jenis_kendaraan') === 'Truk')>Truk</option>
                    </select>
                    @error('jenis_kendaraan')<small>{{ $message }}</small>@enderror
                </div>

                <div>
                    <label for="id_area">Area Parkir</label>
                    <select id="id_area" name="id_area" required>
                        <option value="">Pilih area</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id_area }}" @selected((string) old('id_area') === (string) $area->id_area)>
                                {{ $area->nama_area }} ({{ $area->kapasitas }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_area')<small>{{ $message }}</small>@enderror
                </div>

                <div class="form-divider"></div>

                <div class="inbound-actions">
                    <button type="reset" class="btn-ghost">Reset</button>
                    <button type="submit" class="btn-primary btn-pill">Simpan Data</button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>
