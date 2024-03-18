<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaDeFormacion extends Model
{
    protected $table = 'area_de_formaciones';

    protected $fillable = [
        'id',
        'nombre',
    ];

    public function personas()
    {
        return $this->hasMany(Persona::class);
    }

}
