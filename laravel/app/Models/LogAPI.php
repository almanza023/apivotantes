<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAPI extends Model
{
    use HasFactory;

    protected $table="log_api";
    protected $fillable = [
        'respuesta',
        'operacion',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


}
