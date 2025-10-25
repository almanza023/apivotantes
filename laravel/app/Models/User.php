<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'numerodocumento',
        'email',
        'rol',
        'password',
        'campanna_id',
        'municipio_id',
        'estado'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

      public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper(($value));
    }

    public function campanna()
    {
      return $this->belongsTo(Campanna::class, 'campanna_id', 'id');
    }

    public function municipio()
    {
      return $this->belongsTo(Municipio::class, 'municipio_id', 'id');
    }
}
