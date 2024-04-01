<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'dde_personas';

    protected $fillable = [
        'id',
        'ci',
        'exp',
        'primer_apellido',
        'segundo_apellido',
        'nombres',
        'nombre_completo',
        'ocupacion',
        'grado_academico_id',
        'area_formacion_id',
        'institucion_id',
        'anio_conclusion_estudios',
        //'con_documentos',
        'genero',
        'fecha_nacimiento',
        'telefono',
        'fecha_inicion_sin',
        'imagen',
    ];

    public function personaPuesto()
    {
        return $this->hasMany(PersonaPuesto::class);
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'persona_id');
    }

    public function incorporacionFormulario()
    {
        return $this->hasMany(Incorporacion::class);
    }

    public function dde_puestos_actuales()
    {
        return $this->hasMany(Puesto::class, 'persona_actual_id', 'id');
    }

    public function grado_academico()
    {
        return $this->belongsTo(GradoAcademico::class, 'grado_academico_id', 'id');
    }

    public function area_formacion()
    {
        return $this->belongsTo(AreaDeFormacion::class, 'area_formacion_id', 'id');
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id', 'id');
    }
}
