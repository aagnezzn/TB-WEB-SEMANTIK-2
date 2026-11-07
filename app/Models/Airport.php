<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $table = 'airports';

    protected $primaryKey = 'airport_id';

    public $incrementing = false;

    protected $keyType = 'string';
}
