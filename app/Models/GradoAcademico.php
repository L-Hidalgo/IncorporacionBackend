<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradoAcademico extends Model
{
    protected $table = 'dde_grado_academicos';

    protected $fillable = [
        'id',
        'nombre',
    ];

    public function dde_personas()
    {
        return $this->hasMany(Persona::class);
    }
}
