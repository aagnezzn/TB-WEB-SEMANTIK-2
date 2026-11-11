<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions'; 

    protected $primaryKey = 'region_id'; 

    public $incrementing = false; 
    protected $keyType = 'string';
    public $timestamps = false; 

    protected $fillable = [
        'region_id',
        'region_name',
        'region_type',
        'country_code',
        'latitude',
        'longitude',
    ];
}