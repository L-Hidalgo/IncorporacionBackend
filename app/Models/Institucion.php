<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = "dde_instituciones";

    protected $fillable = [
        'id',
        'nombre',
    ];

    public function dde_personas()
    {
        return $this->hasMany(Persona::class);
    }

}
