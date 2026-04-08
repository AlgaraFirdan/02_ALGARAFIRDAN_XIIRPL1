<?php

use App\Http\Controllers\AreaParkirController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\TarifParkirController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	return Auth::check()
		? redirect()->route('dashboard')
		: redirect()->route('login');
});

Route::middleware('guest')->group(function () {
	// Halaman login hanya untuk pengguna yang belum terautentikasi.
	Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::middleware('auth')->group(function () {
	// Seluruh route di bawah ini wajib login.
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

	Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

	Route::middleware('role:admin')->group(function () {
		// Modul master data khusus admin.
		Route::resource('users', UserController::class)
			->except(['show']);

		Route::resource('area-parkir', AreaParkirController::class)
			->except(['show'])
			->parameters(['area-parkir' => 'areaParkir']);

		Route::resource('tarif-parkir', TarifParkirController::class)
			->except(['show'])
			->parameters(['tarif-parkir' => 'tarifParkir']);

		Route::resource('kendaraan', KendaraanController::class)
			->except(['show']);

		Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');
	});

	Route::middleware('role:petugas')->group(function () {
		// Alur operasional parkir khusus petugas.
		Route::get('/transaksi/masuk', [TransaksiController::class, 'masukForm'])->name('transaksi.masuk');
		Route::post('/transaksi/masuk', [TransaksiController::class, 'storeMasuk'])->name('transaksi.masuk.store');
		Route::get('/transaksi/keluar', [TransaksiController::class, 'keluarForm'])->name('transaksi.keluar');
		Route::post('/transaksi/keluar/{transaksi}', [TransaksiController::class, 'prosesKeluar'])->name('transaksi.keluar.proses');
	});

	Route::middleware('role:owner')->group(function () {
		// Pemantauan bisnis dan laporan khusus owner.
		Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
		Route::get('/rekap/pdf', [RekapController::class, 'exportPdf'])->name('rekap.pdf');
	});
});
