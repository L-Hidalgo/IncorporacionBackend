<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incorporacion extends Model
{
    protected $table = 'incorporaciones';

    protected $fillable = [
        'id',
        // Section: Evaluacion
        'paso',
        'persona_id',
        'puesto_actual_id',
        'puesto_nevo_id',
        'evaluacion_estado', // 1:inicio, 2: con_formulario, 3: cumple, 4: no_cumple, finalizado
        // !Section
        // Section: Incoporacion
        'incorporacion_estado',
        'seguimiento_estado',
        'gerente_acta_posicion',
        'respaldo_formacion',
        'cumple_exp_profesional',
        'cumple_exp_especifica',
        'cumple_exp_mando',
        'cumple_con_formacion',
        'fecha_de_incorporacion',
        'hp',
        'cite_nota_minuta',
        'codigo_nota_minuta',
        'fecha_nota_minuta',
        'fecha_recepcion',
        'cite_informe',
        'fecha_informe',
        'cite_memorandum',
        'codigo_memorandum',
        'fecha_memorandum',
        'cite_rap',
        'codigo_rap',
        'fecha_rap',
        'responsable',
        'observacion'
    ];

    public function puesto_actual()
    {
        return $this->belongsTo(Puesto::class, 'puesto_actual_id', 'id');
    }

    public function puesto_nuevo()
    {
        return $this->belongsTo(Puesto::class, 'puesto_nuevo_id', 'id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

}
