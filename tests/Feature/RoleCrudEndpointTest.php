<?php

namespace Tests\Feature;

use App\Models\AreaParkir;
use App\Models\Kendaraan;
use App\Models\TarifParkir;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleCrudEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_use_master_crud_endpoints(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'nama' => 'New User',
                'username' => 'new_user',
                'password' => 'secret123',
                'role' => 'owner',
                'status_aktif' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['username' => 'new_user', 'role' => 'owner']);

        $this->actingAs($admin)
            ->post(route('tarif-parkir.store'), [
                'jenis_kendaraan' => 'Sepeda',
                'tarif_per_jam' => 2000,
                'status_aktif' => '1',
            ])
            ->assertRedirect(route('tarif-parkir.index'));

        $this->assertDatabaseHas('tarif_parkir', ['jenis_kendaraan' => 'Sepeda']);

        $this->actingAs($admin)
            ->post(route('area-parkir.store'), [
                'nama_area' => 'Main Plaza',
                'kapasitas' => 120,
            ])
            ->assertRedirect(route('area-parkir.index'));

        $area = AreaParkir::query()->where('nama_area', 'Main Plaza')->first();
        $this->assertNotNull($area);

        $this->actingAs($admin)
            ->post(route('kendaraan.store'), [
                'plat_nomor' => 'B 4444 XYZ',
                'jenis_kendaraan' => 'Mobil',
                'warna' => 'Hitam',
                'pemilik' => 'John Doe',
                'id_user' => $admin->id_user,
            ])
            ->assertRedirect(route('kendaraan.index'));

        $this->assertDatabaseHas('kendaraan', ['plat_nomor' => 'B 4444 XYZ']);
    }

    public function test_non_admin_cannot_use_admin_crud_endpoints(): void
    {
        $petugas = $this->createUser('petugas');
        $owner = $this->createUser('owner');

        $this->actingAs($petugas)
            ->post(route('users.store'), [
                'nama' => 'Blocked',
                'username' => 'blocked',
                'password' => 'secret123',
                'role' => 'owner',
                'status_aktif' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('area-parkir.store'), [
                'nama_area' => 'Owner Zone',
                'kapasitas' => 10,
            ])
            ->assertForbidden();

        $this->actingAs($petugas)
            ->post(route('tarif-parkir.store'), [
                'jenis_kendaraan' => 'Blocked',
                'tarif_per_jam' => 1234,
                'status_aktif' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('kendaraan.store'), [
                'plat_nomor' => 'B 1111 OWN',
                'jenis_kendaraan' => 'Mobil',
                'warna' => 'Biru',
                'pemilik' => 'Owner',
                'id_user' => $owner->id_user,
            ])
            ->assertForbidden();
    }

    public function test_petugas_can_process_transaksi_and_other_roles_cannot(): void
    {
        $petugas = $this->createUser('petugas');
        $admin = $this->createUser('admin');
        $owner = $this->createUser('owner');

        $area = AreaParkir::query()->create([
            'nama_area' => 'Terminal A',
            'kapasitas' => 50,
        ]);

        TarifParkir::query()->create([
            'jenis_kendaraan' => 'Mobil',
            'tarif_per_jam' => 5000,
            'status_aktif' => 1,
        ]);

        $this->actingAs($petugas)
            ->post(route('transaksi.masuk.store'), [
                'plat_nomor' => 'B 8888 PTG',
                'jenis_kendaraan' => 'Mobil',
                'id_area' => $area->id_area,
            ])
            ->assertRedirect(route('transaksi.masuk'));

        $kendaraan = Kendaraan::query()->where('plat_nomor', 'B 8888 PTG')->first();
        $this->assertNotNull($kendaraan);

        $trx = Transaksi::query()
            ->where('id_kendaraan', $kendaraan->id_kendaraan)
            ->where('status', 'masuk')
            ->first();

        $this->assertNotNull($trx);

        $this->actingAs($petugas)
            ->post(route('transaksi.keluar.proses', $trx->id_parkir))
            ->assertRedirect(route('transaksi.keluar', ['q' => 'B 8888 PTG']));

        $this->assertDatabaseHas('transaksi', [
            'id_parkir' => $trx->id_parkir,
            'status' => 'keluar',
        ]);

        $this->actingAs($admin)
            ->post(route('transaksi.masuk.store'), [
                'plat_nomor' => 'B 1234 ADM',
                'jenis_kendaraan' => 'Mobil',
                'id_area' => $area->id_area,
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('transaksi.masuk.store'), [
                'plat_nomor' => 'B 1234 OWN',
                'jenis_kendaraan' => 'Mobil',
                'id_area' => $area->id_area,
            ])
            ->assertForbidden();
    }

    public function test_admin_validation_returns_422_for_invalid_payloads(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->postJson(route('users.store'), [
                'nama' => '',
                'username' => '',
                'password' => '123',
                'role' => 'invalid',
                'status_aktif' => '9',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama', 'username', 'password', 'role', 'status_aktif']);

        $this->actingAs($admin)
            ->postJson(route('tarif-parkir.store'), [
                'jenis_kendaraan' => '',
                'tarif_per_jam' => -1,
                'status_aktif' => '9',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['jenis_kendaraan', 'tarif_per_jam', 'status_aktif']);
    }

    public function test_owner_can_filter_rekap_by_date_range(): void
    {
        $owner = $this->createUser('owner');

        $area = AreaParkir::query()->create([
            'nama_area' => 'Owner Zone',
            'kapasitas' => 20,
        ]);

        $tarif = TarifParkir::query()->create([
            'jenis_kendaraan' => 'Mobil',
            'tarif_per_jam' => 5000,
            'status_aktif' => 1,
        ]);

        $kendaraanLama = Kendaraan::query()->create([
            'plat_nomor' => 'B 1111 OLD',
            'jenis_kendaraan' => 'Mobil',
            'warna' => 'Hitam',
            'pemilik' => 'Owner Lama',
            'id_user' => $owner->id_user,
        ]);

        $kendaraanBaru = Kendaraan::query()->create([
            'plat_nomor' => 'B 2222 NEW',
            'jenis_kendaraan' => 'Mobil',
            'warna' => 'Putih',
            'pemilik' => 'Owner Baru',
            'id_user' => $owner->id_user,
        ]);

        Transaksi::query()->create([
            'id_kendaraan' => $kendaraanLama->id_kendaraan,
            'waktu_masuk' => Carbon::parse('2026-03-01 08:00:00'),
            'waktu_keluar' => Carbon::parse('2026-03-01 10:00:00'),
            'id_tarif' => $tarif->id_tarif,
            'durasi_jam' => 2,
            'biaya_total' => 10000,
            'status' => 'keluar',
            'id_user' => $owner->id_user,
            'id_area' => $area->id_area,
        ]);

        Transaksi::query()->create([
            'id_kendaraan' => $kendaraanBaru->id_kendaraan,
            'waktu_masuk' => Carbon::parse('2026-04-08 08:00:00'),
            'waktu_keluar' => Carbon::parse('2026-04-08 09:00:00'),
            'id_tarif' => $tarif->id_tarif,
            'durasi_jam' => 1,
            'biaya_total' => 5000,
            'status' => 'keluar',
            'id_user' => $owner->id_user,
            'id_area' => $area->id_area,
        ]);

        $this->actingAs($owner)
            ->get(route('rekap.index', [
                'start' => '2026-04-01',
                'end' => '2026-04-30',
            ]))
            ->assertOk()
            ->assertSee('B 2222 NEW')
            ->assertDontSee('B 1111 OLD');
    }

    public function test_petugas_cannot_input_transaksi_when_tariff_is_inactive(): void
    {
        $petugas = $this->createUser('petugas');

        $area = AreaParkir::query()->create([
            'nama_area' => 'Inactive Tarif Zone',
            'kapasitas' => 15,
        ]);

        TarifParkir::query()->create([
            'jenis_kendaraan' => 'Mobil',
            'tarif_per_jam' => 5000,
            'status_aktif' => 0,
        ]);

        $response = $this->actingAs($petugas)
            ->from(route('transaksi.masuk'))
            ->post(route('transaksi.masuk.store'), [
                'plat_nomor' => 'B 9090 OFF',
                'jenis_kendaraan' => 'Mobil',
                'id_area' => $area->id_area,
            ]);

        $response
            ->assertRedirect(route('transaksi.masuk'))
            ->assertSessionHasErrors(['jenis_kendaraan']);

        $this->assertDatabaseMissing('transaksi', [
            'status' => 'masuk',
            'id_area' => $area->id_area,
        ]);
    }

    public function test_keluar_rounds_duration_up_and_calculates_total_by_tariff(): void
    {
        $petugas = $this->createUser('petugas');

        $area = AreaParkir::query()->create([
            'nama_area' => 'Round Zone',
            'kapasitas' => 30,
        ]);

        $tarif = TarifParkir::query()->create([
            'jenis_kendaraan' => 'Mobil',
            'tarif_per_jam' => 5000,
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::query()->create([
            'plat_nomor' => 'B 7777 RND',
            'jenis_kendaraan' => 'Mobil',
            'warna' => 'Abu',
            'pemilik' => 'Tester',
            'id_user' => $petugas->id_user,
        ]);

        $trx = Transaksi::query()->create([
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'waktu_masuk' => Carbon::parse('2026-04-08 08:00:00'),
            'waktu_keluar' => null,
            'id_tarif' => $tarif->id_tarif,
            'durasi_jam' => 0,
            'biaya_total' => 0,
            'status' => 'masuk',
            'id_user' => $petugas->id_user,
            'id_area' => $area->id_area,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-04-08 09:01:00'));

        $this->actingAs($petugas)
            ->post(route('transaksi.keluar.proses', $trx->id_parkir))
            ->assertRedirect(route('transaksi.keluar', ['q' => 'B 7777 RND']));

        Carbon::setTestNow();

        $trx->refresh();

        $this->assertSame('keluar', $trx->status);
        $this->assertSame(2, (int) $trx->durasi_jam);
        $this->assertSame(10000.0, (float) $trx->biaya_total);
    }

    public function test_keluar_minimum_charge_is_one_hour(): void
    {
        $petugas = $this->createUser('petugas');

        $area = AreaParkir::query()->create([
            'nama_area' => 'Min Zone',
            'kapasitas' => 10,
        ]);

        $tarif = TarifParkir::query()->create([
            'jenis_kendaraan' => 'Motor',
            'tarif_per_jam' => 3000,
            'status_aktif' => 1,
        ]);

        $kendaraan = Kendaraan::query()->create([
            'plat_nomor' => 'B 1001 MIN',
            'jenis_kendaraan' => 'Motor',
            'warna' => 'Merah',
            'pemilik' => 'Tester Min',
            'id_user' => $petugas->id_user,
        ]);

        $trx = Transaksi::query()->create([
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'waktu_masuk' => Carbon::parse('2026-04-08 10:00:00'),
            'waktu_keluar' => null,
            'id_tarif' => $tarif->id_tarif,
            'durasi_jam' => 0,
            'biaya_total' => 0,
            'status' => 'masuk',
            'id_user' => $petugas->id_user,
            'id_area' => $area->id_area,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-04-08 10:00:00'));

        $this->actingAs($petugas)
            ->post(route('transaksi.keluar.proses', $trx->id_parkir))
            ->assertRedirect(route('transaksi.keluar', ['q' => 'B 1001 MIN']));

        Carbon::setTestNow();

        $trx->refresh();

        $this->assertSame(1, (int) $trx->durasi_jam);
        $this->assertSame(3000.0, (float) $trx->biaya_total);
    }

    private function createUser(string $role): User
    {
        return User::query()->create([
            'nama' => ucfirst($role) . ' Endpoint',
            'username' => $role . '_endpoint_' . uniqid(),
            'password' => Hash::make('secret123'),
            'role' => $role,
            'status_aktif' => 1,
        ]);
    }
}
