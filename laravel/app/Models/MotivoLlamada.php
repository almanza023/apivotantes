<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivoLlamada extends Model
{
    use HasFactory;

    protected $table="motivollamadas";
    protected $fillable = [
        'descripcion',
        'estado',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function setDescripcionAttribute($value)
    {
        $this->attributes['descripcion'] = strtoupper(($value));
    }

}
