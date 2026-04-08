<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifParkir extends Model
{
    protected $table = 'tarif_parkir';

    protected $primaryKey = 'id_tarif';

    public $timestamps = false;

    protected $fillable = [
        'jenis_kendaraan',
        'tarif_per_jam',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tarif_per_jam' => 'decimal:2',
            'status_aktif' => 'integer',
        ];
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_tarif', 'id_tarif');
    }
}
