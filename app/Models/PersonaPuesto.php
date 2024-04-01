<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonaPuesto extends Model
{
    protected $table = 'dde_funcionarios';

    protected $fillable = [
        'id',
        'estado_formacion',  //ya sea carrera regular o irregular jalado de la planilla
        'file_ac', //jalado de la planila
        'fecha_inicio_puesto',
        'personal_antiguo_en_el_cargo',
        'motivo_baja',
        'fecha_fin_puesto',
        'puesto_id',
        'persona_id',
        'estado',        
        'creador_user_id',
        'actualizador_user_id'
    ];

    public function puesto()
    {
        return $this->belongsTo(Puesto::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

}
