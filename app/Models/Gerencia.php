<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gerencia extends Model
{
    protected $table = 'dde_gerencias';
    protected $fillable = [
        'id',
        'nombre',
        'abreviatura',
    ];

    public function departamento()
    {
        return $this->hasMany(Departamento::class);
    }

}
