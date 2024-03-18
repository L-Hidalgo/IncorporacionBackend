<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = "instituciones";

    protected $fillable = [
        'id',
        'nombre',
    ];

    public function personas()
    {
        return $this->hasMany(Persona::class);
    }

}
