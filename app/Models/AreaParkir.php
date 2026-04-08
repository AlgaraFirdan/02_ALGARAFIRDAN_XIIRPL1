<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaParkir extends Model
{
    protected $table = 'area_parkir';

    protected $primaryKey = 'id_area';

    public $timestamps = false;

    protected $fillable = [
        'nama_area',
        'kapasitas',
    ];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_area', 'id_area');
    }
}
