<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'region'; // Pastikan nama tabel sesuai di database

    protected $primaryKey = 'region_id'; // Kunci utamanya adalah region_id, bukan id

    public $incrementing = false; // Set false jika region_id bukan auto-increment integer (sesuaikan jika bertipe angka auto-increment)
    protected $keyType = 'string'; // Ubah ke 'int' jika region_id berupa angka

    protected $fillable = [
        'region_id',
        'region_name',
        'region_type',
        'country_code',
        'latitude',
        'longitude',
    ];
}