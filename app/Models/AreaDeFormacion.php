<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaDeFormacion extends Model
{
    protected $table = 'dde_area_formaciones';

    protected $fillable = [
        'id',
        'nombre',
    ];

    public function dde_personas()
    {
        return $this->hasMany(Persona::class);
    }

}
