<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as AuthenticatableModel;
use Illuminate\Notifications\Notifiable;

class Usuario extends AuthenticatableModel implements Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre', 'correo', 'contrasena', 'rol_id'];

    public function roles()
    {
    return $this->belongsToMany(Rol::class, 'rol_id');
    }


    public function getAuthPassword()
    {
    return $this->contrasena;
    }
    
}
