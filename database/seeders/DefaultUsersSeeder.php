<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['nama' => 'Admin User', 'username' => 'admin', 'role' => 'admin', 'password' => 'admin123'],
            ['nama' => 'Petugas User', 'username' => 'petugas', 'role' => 'petugas', 'password' => 'petugas123'],
            ['nama' => 'Owner User', 'username' => 'owner', 'role' => 'owner', 'password' => 'owner123'],
        ];

        foreach ($defaults as $item) {
            User::query()->updateOrCreate(
                ['username' => $item['username']],
                [
                    'nama' => $item['nama'],
                    'password' => Hash::make($item['password']),
                    'role' => $item['role'],
                    'status_aktif' => 1,
                ]
            );
        }
    }
}
