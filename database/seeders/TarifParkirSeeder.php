<?php

namespace Database\Seeders;

use App\Models\TarifParkir;
use Illuminate\Database\Seeder;

class TarifParkirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['jenis_kendaraan' => 'Motor', 'tarif_per_jam' => 3000, 'status_aktif' => 1],
            ['jenis_kendaraan' => 'Mobil', 'tarif_per_jam' => 5000, 'status_aktif' => 1],
            ['jenis_kendaraan' => 'Bus', 'tarif_per_jam' => 8000, 'status_aktif' => 1],
            ['jenis_kendaraan' => 'Truk', 'tarif_per_jam' => 10000, 'status_aktif' => 1],
        ];

        foreach ($defaults as $item) {
            TarifParkir::query()->updateOrCreate(
                ['jenis_kendaraan' => $item['jenis_kendaraan']],
                [
                    'tarif_per_jam' => $item['tarif_per_jam'],
                    'status_aktif' => $item['status_aktif'],
                ]
            );
        }
    }
}
