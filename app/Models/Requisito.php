<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    protected $table = 'requisitos';

    protected $fillable = [
        'id',
        'formacion_requerida',
        'experiencia_profesional_segun_cargo',
        'experiencia_relacionado_al_area',
        'experiencia_en_funciones_de_mando',
        'puesto_id'
    ];

    public function puesto()
    {
        return $this->belongsTo(Puesto::class);
    }

}
