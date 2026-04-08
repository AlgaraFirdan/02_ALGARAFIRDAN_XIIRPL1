<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_access_matrix(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('tarif-parkir.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('area-parkir.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('kendaraan.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('log-aktivitas.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('transaksi.masuk'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('rekap.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('rekap.pdf'))
            ->assertForbidden();
    }

    public function test_petugas_access_matrix(): void
    {
        $petugas = $this->createUser('petugas');

        $this->actingAs($petugas)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($petugas)
            ->get(route('transaksi.masuk'))
            ->assertOk();

        $this->actingAs($petugas)
            ->get(route('transaksi.keluar'))
            ->assertOk();

        $this->actingAs($petugas)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->get(route('tarif-parkir.index'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->get(route('rekap.index'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->get(route('rekap.pdf'))
            ->assertForbidden();
    }

    public function test_owner_access_matrix(): void
    {
        $owner = $this->createUser('owner');

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('rekap.index'))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('rekap.pdf'))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('tarif-parkir.index'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('transaksi.keluar'))
            ->assertForbidden();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::query()->create([
            'nama' => 'Inactive User',
            'username' => 'inactive_user',
            'password' => Hash::make('secret123'),
            'role' => 'petugas',
            'status_aktif' => 0,
        ]);

        $response = $this->post(route('login.process'), [
            'credential' => $inactive->username,
            'password' => 'secret123',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['credential']);

        $this->assertGuest();
    }

    private function createUser(string $role): User
    {
        return User::query()->create([
            'nama' => ucfirst($role) . ' Test',
            'username' => $role . '_test',
            'password' => Hash::make('secret123'),
            'role' => $role,
            'status_aktif' => 1,
        ]);
    }
}
