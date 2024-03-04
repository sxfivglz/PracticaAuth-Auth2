<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre', 'codigo_2fa','created_at','updated_at'];

    public function usuarios()
    {
    return $this->hasMany(Usuario::class, 'rol_id');
    }

    
}
