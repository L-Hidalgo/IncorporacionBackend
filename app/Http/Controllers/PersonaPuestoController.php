<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Puesto;
use Illuminate\Support\Facades\DB;

class PersonaPuestoController extends Controller
{
    public function listarPuesto(Request $request)
    {
        $limit = 9;
        $page = $request->input('page', 1);

        // Filtros
        $item = $request->input('item');
        $gerenciasIds = $request->input('gerenciasIds');
        $departamentosIds = $request->input('departamentosIds');
        $estado = $request->input('estado');
        $tipoMovimiento = $request->input('tipoMovimiento');

        $query = DB::table('puestos')
            ->join('departamentos', 'puestos.departamento_id', '=', 'departamentos.id')
            ->join('gerencias', 'departamentos.gerencia_id', '=', 'gerencias.id')
            ->leftJoin('requisitos', 'puestos.id', '=', 'requisitos.puesto_id')
            ->leftJoin('funcionarios', 'puestos.id', '=', 'funcionarios.puesto_id')
            ->leftJoin('personas', 'personas.id', '=', 'puestos.persona_actual_id');

        if (isset($item)) {
            $query = $query->where('puestos.item', $item);
        }
        if (isset($departamentosIds) && count($departamentosIds) > 0) {
            $query = $query->whereIn('departamentos.id', $departamentosIds);
        }
        if (isset($gerenciasIds) && count($gerenciasIds) > 0) {
            $query = $query->whereIn('departamentos.gerencia_id', $gerenciasIds);
        }
        if (isset($estado)) {
            $query = $query->where('puestos.estado', $estado);
        }

        $query = $query->select([
            'personas.ci',
            'personas.exp',
            'personas.nombre_completo',
            'personas.formacion',
            'personas.fecha_nacimiento',
            'personas.fecha_inicion_sin',
            'funcionarios.fecha_inicio_puesto as fecha_inicio_puesto',
            'personas.imagen',
            'puestos.id',
            'puestos.item',
            'puestos.denominacion',
            'puestos.estado',
            'puestos.salario',
            'gerencias.nombre as gerencia',
            'departamentos.nombre as departamento',
            'puestos.objetivo',
            'requisitos.formacion_requerida as formacion_requerida',
            'requisitos.experiencia_profesional_segun_cargo as experiencia_profesional_segun_cargo',
            'requisitos.experiencia_relacionado_al_area as experiencia_relacionado_al_area',
            'requisitos.experiencia_en_funciones_de_mando as experiencia_en_funciones_de_mando',
            'puestos.persona_actual_id'
        ]);

        $query = $query->orderBy('puestos.item');

        // paginacion
        $personaPuestos = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json($personaPuestos);
    }


    public function obtenerInfoDePersonapuesto($puestoId)
    {
        $personaPuesto = Puesto::with(['persona_actual', 'departamento.gerencia', 'requisitos', 'personaPuesto'])->find($puestoId);

        return response()->json($personaPuesto);
    }

    public function filtrarAutoComplete(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $result = DB::table('puestos')
            ->leftJoin('personas', 'personas.id', '=', 'puestos.persona_actual_id')
            ->orWhere(DB::raw('CAST(puestos.item AS CHAR)'), 'LIKE', $keyword . "%")
            ->orWhere('personas.nombre_completo', 'LIKE', $keyword . "%")
            ->select(['puestos.item as item', 'personas.nombre_completo as nombre_completo'])
            ->limit(6)->get();
        $results = [];
        if (ctype_digit($keyword)) {
            $results = $result->map(function ($obj) {
                return (object) ['text' => "" . $obj->item . ": " . ($obj->nombre_completo ? $obj->nombre_completo : "ACEFALIA"), 'item' => $obj->item];
            });
        } else {
            $results = $result->map(function ($obj) {
                return (object) ['text' => $obj->nombre_completo . " [" . $obj->item . "]", 'item' => $obj->item];
            });
        }
        return response()->json(['elementos' => $results], 200);
    }
}
