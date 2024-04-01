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
        $dde_gerenciasIds = $request->input('dde_gerenciasIds');
        $dde_departamentosIds = $request->input('dde_departamentosIds');
        $estado = $request->input('estado');
        $tipoMovimiento = $request->input('tipoMovimiento');

        $query = DB::table('dde_puestos')
            ->join('dde_departamentos', 'dde_puestos.departamento_id', '=', 'dde_departamentos.id')
            ->join('dde_gerencias', 'dde_departamentos.gerencia_id', '=', 'dde_gerencias.id')
            ->leftJoin('dde_requisitos', 'dde_puestos.id', '=', 'dde_requisitos.puesto_id')
            ->leftJoin('dde_funcionarios', 'dde_puestos.id', '=', 'dde_funcionarios.puesto_id')
            ->leftJoin('dde_personas', 'dde_personas.id', '=', 'dde_puestos.persona_actual_id');

        if (isset($item)) {
            $query = $query->where('dde_puestos.item', $item);
        }
        if (isset($dde_departamentosIds) && count($dde_departamentosIds) > 0) {
            $query = $query->whereIn('dde_departamentos.id', $dde_departamentosIds);
        }
        if (isset($dde_gerenciasIds) && count($dde_gerenciasIds) > 0) {
            $query = $query->whereIn('dde_departamentos.gerencia_id', $dde_gerenciasIds);
        }
        if (isset($estado)) {
            $query = $query->where('dde_puestos.estado', $estado);
        }

        $query = $query->select([
            'dde_personas.ci',
            'dde_personas.exp',
            'dde_personas.nombre_completo',
            'dde_personas.formacion',
            'dde_personas.fecha_nacimiento',
            'dde_personas.fecha_inicion_sin',
            'dde_funcionarios.fecha_inicio_puesto as fecha_inicio_puesto',
            'dde_personas.imagen',
            'dde_puestos.id',
            'dde_puestos.item',
            'dde_puestos.denominacion',
            'dde_puestos.estado',
            'dde_puestos.salario',
            'dde_gerencias.nombre as gerencia',
            'dde_departamentos.nombre as departamento',
            'dde_puestos.objetivo',
            'dde_requisitos.formacion_requerida as formacion_requerida',
            'dde_requisitos.experiencia_profesional_segun_cargo as experiencia_profesional_segun_cargo',
            'dde_requisitos.experiencia_relacionado_al_area as experiencia_relacionado_al_area',
            'dde_requisitos.experiencia_en_funciones_de_mando as experiencia_en_funciones_de_mando',
            'dde_puestos.persona_actual_id'
        ]);

        $query = $query->orderBy('dde_puestos.item');

        // paginacion
        $personadde_puestos = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json($personadde_puestos);
    }


    public function obtenerInfoDePersonapuesto($puestoId)
    {
        $personaPuesto = Puesto::with(['persona_actual', 'departamento.gerencia', 'dde_requisitos', 'personaPuesto'])->find($puestoId);

        return response()->json($personaPuesto);
    }

    public function filtrarAutoComplete(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $result = DB::table('dde_puestos')
            ->leftJoin('dde_personas', 'dde_personas.id', '=', 'dde_puestos.persona_actual_id')
            ->orWhere(DB::raw('CAST(dde_puestos.item AS CHAR)'), 'LIKE', $keyword . "%")
            ->orWhere('dde_personas.nombre_completo', 'LIKE', $keyword . "%")
            ->select(['dde_puestos.item as item', 'dde_personas.nombre_completo as nombre_completo'])
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
