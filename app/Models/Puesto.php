<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    protected $fillable = [
        'id',
        'item',
        'denominacion',
        'salario',
        'salario_literal',
        'objetivo',
        'departamento_id',
        'estado',
        'persona_actual_id'
    ];

    public function personaPuesto()
    {
        return $this->hasMany(PersonaPuesto::class, 'puesto_id', 'id');
    }

    public function requisitos()
    {
        return $this->hasMany(Requisito::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function persona_actual()
    {
        return $this->belongsTo(Persona::class, 'persona_actual_id', 'id');
    }

    public function incorporacion()
    {
        return $this->hasMany(Incorporacion::class);
    }
}
