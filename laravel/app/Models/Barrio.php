<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    use HasFactory;

    protected $table="barrios";
    protected $fillable = [
        'descripcion',
        'municipio_id',
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

    public function municipio()
    {
      return $this->belongsTo(Municipio::class);
    }
}
