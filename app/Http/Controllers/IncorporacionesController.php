<?php

namespace App\Http\Controllers;

use App\Models\Incorporacion;
use App\Models\AreaDeFormacion;
use App\Models\GradoAcademico;
use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Puesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncorporacionesController extends Controller
{

    public function listarIncorporaciones(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        //Mostrar datos o listar datos
        $paso = $request->input('paso');
        $personaId = $request->input('personaId');
        $gerenciasIds = $request->input('gerenciasIds');
        $departamentosIds = $request->input('departamentosIds');
        $estado = $request->input('estado');

        $query = Incorporacion::with([
            'persona',
            'persona.incorporacionFormulario',
            'puesto_actual.departamento.gerencia',
            'puesto_nuevo.departamento.gerencia',
            'puesto_nuevo.persona_actual',
            'puesto_nuevo.personaPuesto.persona',
            'puesto_nuevo.requisitos'
        ]);

        if (isset($personaId)) {
            $query = $query->where('persona_id', $personaId);
        }
        if (isset($paso)) {
            $query = $query->where('paso', $paso);
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

        // $query = $query->whereNotNull('puesto_actual_id')->whereNotNull('puesto_nuevo_id');

        $query->orderBy('created_at', 'desc');

        // Paginacion de incorporaciones
        $incorporaciones = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json($incorporaciones);
    }

    public function filtrarAutoComplete(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $result = DB::table('incorporacion_formularios')
            ->leftJoin('personas', 'incorporacion_formularios.persona_id', '=', 'personas.id')
            ->orWhere('personas.nombreCompleto', 'LIKE', $keyword . "%")
            ->orWhere('personas.ci', 'LIKE', $keyword . "%")
            ->select(['personas.id as idPersona', 'personas.nombreCompleto as nombreCompleto', 'personas.ci as ci'])
            ->limit(6)->get();
        $results = [];
        if (ctype_digit($keyword)) {
            $results = $result->map(function ($obj) {
                return (object) ['text' => "" . $obj->ci . ": " . $obj->nombreCompleto, 'idPersona' => $obj->idPersona];
            });
        } else {
            $results = $result->map(function ($obj) {
                return (object) ['text' => $obj->nombreCompleto . " [" . $obj->ci . "]", 'idPersona' => $obj->idPersona];
            });
        }
        return response()->json(['elementos' => $results], 200);
    }

    //Crear evaluacion para cambio de item
    public function crearEvaluacion(Request $request)
    {
        $validatedData = $request->validate([
            'persona_id' => 'integer',
            'persona.nombres' => 'string|nullable',  // Hacer el campo opcional y nulo
            'persona.primer_apellido' => 'string',
            'persona.segundo_apellido' => 'string',
            'persona.ci' => 'string',
            'persona.exp' => 'string',
            'persona.sexo' => 'string',
            'persona.fecha_nacimiento' => 'date',
            'puesto_actual_id' => 'integer',
            'puesto_nuevo_id' => 'required|integer',
            'observacion' => 'required|string',
        ]);

        // if (empty($request->gradoAcademico_id) || empty($request->areaFormacion_id) || empty($request->institucion_id) || empty($request->anioConclusion) || empty($request->observacion)) {
        //     return response()->json(['error' => 'Campos requeridos vacíos'], 400);
        // }
        if (!empty($request->persona_id)) {
            $persona = Persona::find($validatedData['persona_id']);
        } else {
            $persData = $validatedData['persona'];
            $fechaNacimiento = isset($persData['fecha_nacimiento']) ? Carbon::parse($persData['fecha_nacimiento'])->toDateString() : null;

            $persona = Persona::create([
                'nombres' => $persData['nombres'],
                'primer_apellido' => $persData['primer_apellido'],
                'segundo_apellido' => $persData['segundo_apellido'],
                'nombre_completo' => $persData['nombres'] . " " .
                    $persData['primer_apellido'] . " " .
                    $persData['segundo_apellido'],
                'ci' => $persData['ci'],
                'exp' => $persData['exp'],
                'sexo' => $persData['sexo'] ?? null,
                'fecha_nacimiento' => $fechaNacimiento,
            ]);
        }

        // puesto dar de baja y alta
        $puestoNuevo = Puesto::find($validatedData['puesto_nuevo_id']);
        if (isset($validatedData['puesto_actual_id'])) {
            $puestoActual = Puesto::find($validatedData['puesto_actual_id']);
            if (isset($puestoActual) && $puestoActual->persona_actual_id > 0) {
                $puestoActual->persona_actual_id = null;
                $puestoActual->estado = 'ACEFALIA';
                $puestoActual->save();
            }
        }
        if (isset($puestoNuevo)) {
            $puestoNuevo->persona_actual_id = $persona->id;
            $puestoNuevo->estado = 'OCUPADO';
            $puestoNuevo->save();
        }

        if ($persona) {
            $dataPersona = $request->input('persona');
            if ($dataPersona['anio_conclusion']) {
                $anioConclusion = Carbon::parse($dataPersona['anio_conclusion'])->setTimezone('UTC')->format('Y-m-d');
                $persona->anio_conclusion = $anioConclusion;
            }
            if ($dataPersona['fecha_nacimiento']) {
                $fechaNacFormated = Carbon::parse($dataPersona['fecha_nacimiento'])->setTimezone('UTC')->format('Y-m-d');
                $persona->fecha_nacimiento = $fechaNacFormated;
            }
            $persona->grado_academico_id = $dataPersona['grado_academico_id'] ?? null;
            $persona->area_formacion_id = $dataPersona['area_formacion_id'] ?? null;
            $persona->institucion_id = $dataPersona['institucion_id'] ?? null;
            $persona->con_respaldo = $dataPersona['con_respaldo'] ?? null;
            $persona->nombres = $dataPersona['nombres'] ?? null;
            $persona->primer_apellido = $dataPersona['primer_apellido'] ?? null;
            $persona->segundo_apellido = $dataPersona['segundo_apellido'];
            $persona->nombre_completo = $dataPersona['nombres'] . " " .
                $dataPersona['primer_apellido'] . " " .
                $dataPersona['segundo_apellido'];
            $persona->ci = $dataPersona['ci'] ?? null;
            $persona->sexo = $dataPersona['sexo'] ?? null;
            $persona->save();
        }

        $incForm = new Incorporacion();
        $incForm->persona_id = $persona->id;
        $incForm->puesto_actual_id = $request->input('puesto_actual_id');
        $incForm->puesto_nuevo_id = $validatedData['puesto_nuevo_id'];
        $incForm->observacion = $validatedData['observacion'];
        $incForm->evaluacion_estado = 1;
        $incForm->paso = 1;
        $incForm->cumple_exp_profesional = $request->input('cumple_exp_profesional');
        $incForm->cumple_exp_especifica = $request->input('cumple_exp_especifica');
        $incForm->cumple_exp_mando = $request->input('cumple_exp_mando');
        $incForm->cumple_con_formacion = $request->input('cumple_con_formacion');

        if ($request->input('fecha_de_incorporacion')) {
            $fechaIncFormated = Carbon::parse($request->input('fecha_de_incorporacion'))->setTimezone('UTC')->format('Y-m-d');
            $incForm->fecha_de_incorporacion = $fechaIncFormated;
        }
        $incForm->hp = $request->input('hp');
        $incForm->cite_nota_minuta = $request->input('cite_nota_minuta');
        $incForm->codigo_nota_minuta = $request->input('codigo_nota_minuta');
        if ($request->input('fecha_nota_minuta')) {
            $fecha_nota_minuta = Carbon::parse($request->input('fecha_nota_minuta'))->setTimezone('UTC')->format('Y-m-d');
            $incForm->fecha_nota_minuta = $fecha_nota_minuta;
        }
        if ($request->input('fecha_recepcion')) {
            $fecha_recepcion = Carbon::parse($request->input('fecha_recepcion'))->setTimezone('UTC')->format('Y-m-d');
            $incForm->fecha_recepcion = $fecha_recepcion;
        }
        $incForm->cite_informe = $request->input('cite_informe');
        if ($request->input('fecha_informe')) {
            $fecha_informe = Carbon::parse($request->input('fecha_informe'))->setTimezone('UTC')->format('Y-m-d');
            $incForm->fecha_informe = $fecha_informe;
        }
        $incForm->cite_memorandum = $request->input('cite_memorandum');
        $incForm->codigo_memorandum = $request->input('codigo_memorandum');
        if ($request->input('fecha_memorandum')) {
            $fecha_memorandum = Carbon::parse($request->input('fecha_memorandum'))->setTimezone('UTC')->format('Y-m-d');
            $incForm->fecha_memorandum = $fecha_memorandum;
        }
        $incForm->cite_rap = $request->input('cite_rap');
        $incForm->codigo_rap = $request->input('codigo_rap');
        if ($request->input('fecha_rap')) {
            $fecha_rap = Carbon::parse($request->input('fecha_rap'))->setTimezone('UTC')->format('Y-m-d');
            $incForm->fecha_rap = $fecha_rap;
        }
        $incForm->responsable = $request->input('responsable');

        if (
            $request->input('cite_informe') &&
            $request->input('fecha_informe') &&
            $request->input('cite_memorandum') &&
            $request->input('codigo_memorandum') &&
            $request->input('fecha_memorandum') &&
            $request->input('cite_rap') &&
            $request->input('codigo_rap') &&
            $request->input('fecha_rap')
        ) {
            $incForm->incorporacion_estado = 2;
        }
        $incForm->save();
        $incForm->persona;
        $incForm->puesto_actual;
        $incForm->puesto_nuevo;
        $incForm->save();
        return $this->sendSuccess($incForm);
    }

    //Buscar por Persona
    public function buscarPersona(Request $request)
    {
        $puestoActual = $request->input('puesto_actual', '');

        $persona = Persona::with('puestos_actuales.departamento.gerencia')
            ->whereHas('puestos_actuales', function ($query) use ($puestoActual) {
                $query->where('item', $puestoActual);
            })
            ->orWhere('ci', $puestoActual)
            ->first();

        if ($persona) {
            return response()->json($persona);
        }
        return response()->json(['message' => 'Persona sin item!'], 404);
    }

    //Buscar por Item Nuevo
    public function buscarItemApi($item)
    {
        $puesto = Puesto::with(['persona_actual', 'requisitos_puesto.requisito', 'departamento.gerencia'])->where('item', $item)->first();
        if (isset($puesto)) {
            $puesto->persona_actual;
            return response()->json($puesto);
        } else {
            return response()->json(['message' => 'Item no existe!'], 404);
        }
    }

    //genera el word R-1023 cambio de item
    public function generarFormularioEvalucaion($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        if ($incorporacion->evaluacion_estado == 1) {
            $incorporacion->evaluacion_estado = 2;
            $incorporacion->save();
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1023-01-CambioItem.docx'); // ruta de plantilla
        $templateProcessor = new TemplateProcessor($pathTemplate);
        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.grado', isset($incorporacion->persona->gradoAcademico) ? $incorporacion->persona->gradoAcademico->nombre : '');
        $templateProcessor->setValue('persona.formacion', isset($incorporacion->persona->areaFormacion) ? $incorporacion->persona->areaFormacion->nombre : '');

        if (!$incorporacion->puesto_actual->personaPuesto->isEmpty()) {
            $fechaDesignacion = $incorporacion->puesto_actual->personaPuesto->first()->fecha_inicio;
            $carbonFecha = Carbon::parse($fechaDesignacion);
            setlocale(LC_TIME, 'es_UY');
            $carbonFecha->locale('es_UY');
            $fechaFormateada = $carbonFecha->isoFormat('LL');
            $templateProcessor->setValue('puesto_actual.fechaDeUltimaDesignacion', $fechaFormateada);
        }

        $templateProcessor->setValue('puesto_actual.item', $incorporacion->puesto_actual->item);
        $templateProcessor->setValue('puesto_actual.gerencia', $incorporacion->puesto_actual->departamento->gerencia->nombre);
        $templateProcessor->setValue('puesto_actual.departamento', $incorporacion->puesto_actual->departamento->nombre);
        $templateProcessor->setValue('puesto_actual.denominacion', $incorporacion->puesto_actual->denominacion);
        $templateProcessor->setValue('puesto_actual.salario', $incorporacion->puesto_actual->salario);

        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);
        $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);

        foreach ($incorporacion->puesto_nuevo->requisitos as $requisito) {
            if ($requisito) {
                $templateProcessor->setValue('puesto_nuevo.formacionRequerida', $requisito->formacion_requerida);
                $templateProcessor->setValue('puesto_nuevo.experienciaProfesionalSegunCargo', $requisito->experiencia_profesional_segun_cargo);
                $templateProcessor->setValue('puesto_nuevo.experienciaRelacionadoAlArea', $requisito->experiencia_relacionado_al_area);
                $templateProcessor->setValue('puesto_nuevo.experienciaEnFuncionesDeMando', $requisito->experiencia_en_funciones_de_mando);
                break;
            }
        }

        $templateProcessor->setValue('incorporacion.observacion', strtoupper($incorporacion->observacion));
        $fileName = 'R-1023-01-CambioItem_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);
        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //genera el word R-1129 Cmabio de Item
    public function generarFormularioCambioItem($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1129-01-CambioItem.docx'); // ruta de plantilla
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);

        $fileName = 'R-1129-01-CambioItem_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);
        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //genera el words R-0980 Cambio de Item y Nueva Incorporacion
    public function generarFormularioDocumentosCambioItem($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-0980-01.docx');
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);

        $carbonFechaInfo = Carbon::parse($incorporacion->fecha_informe);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInfo->locale('es_UY');
        $fechaInfoFormateada = $carbonFechaInfo->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaInfo', $fechaInfoFormateada);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);

        $gerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        $gerenciasDepartamentos = array(
            "Gerencia Distrital La Paz I" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia Distrital La Paz II" => "la Administrativo y Recursos Humanos",
            "Gerencia GRACO La Paz" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital El Alto" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Cochabamba" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia GRACO Cochabamba" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia Distrital Santa Cruz I" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia Distrital Santa Cruz II" => "la Administrativo y Recursos Humanos",
            "Gerencia GRACO Santa Cruz" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Montero" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Chuquisaca" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Tarija" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Yacuiba" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Oruro" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Potosí" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Beni" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Pando" => "la Administrativo y Recursos Humanos",
        );

        if (isset($gerenciasDepartamentos[$gerencia])) {
            $departamento = $gerenciasDepartamentos[$gerencia];
        } else {
            $departamento = "el Departamento de Dotación y Evaluación";
        }
        $templateProcessor->setValue('puesto_nuevo.departamento', $departamento);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);

        $fileName = 'R-0980-01_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);
        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //genera el words R-0078 Nueva Incorporacion
    public function generarFormularioEvalR0078($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        if ($incorporacion->evaluacion_estado == 1) {
            $incorporacion->evaluacion_estado = 2;
            $incorporacion->save();
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-0078-01.docx'); // ruta de plantilla
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.gradoAcademico', $incorporacion->persona->grado_academico->nombre);
        $templateProcessor->setValue('persona.formacion', $incorporacion->persona->area_formacion->nombre);

        $fechaNacimiento = Carbon::parse($incorporacion->persona->fecha_nacimiento);
        $edad = $fechaNacimiento->age;
        $templateProcessor->setValue('persona.edad', $edad);

        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);
        $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);

        foreach ($incorporacion->puesto_nuevo->requisitos as $requisito) {
            if ($requisito) {
                $templateProcessor->setValue('puesto_nuevo.formacionRequerida', $requisito->formacion_requerida);
                $templateProcessor->setValue('puesto_nuevo.experienciaProfesionalSegunCargo', $requisito->experiencia_profesional_segun_cargo);
                $templateProcessor->setValue('puesto_nuevo.experienciaRelacionadoAlArea', $requisito->experiencia_relacionado_al_area);
                $templateProcessor->setValue('puesto_nuevo.experienciaEnFuncionesDeMando', $requisito->experiencia_en_funciones_de_mando);
                break;
            }
        }

        $templateProcessor->setValue('incorporacion.observacion', strtoupper($incorporacion->observacion));
        $fileName = 'R-0078_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);
        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }
    public function genFormEvalR1401($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1401-01.docx');
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $templateProcessor->setValue('fechaIncorporacion', $fechaIncorporacionFormateada);
        $fileName = 'R-1401_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);
        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }
    //Para el R-1469, Remision de documentos
    public function genFormRemisionDeDocumentos($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1469-01-CambioItem.docx');
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('puesto_nuevo.gerencia', strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre));
        $templateProcessor->setValue('incoporacion.hp', strtoupper($incorporacion->hp));

        mb_internal_encoding("UTF-8");
        $templateProcessor->setValue('puesto_nuevo.departamento', mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre, "UTF-8"));

        $templateProcessor->setValue('persona.nombreCompleto', strtoupper($incorporacion->persona->nombre_completo));

        $templateProcessor->setValue('fechaMemo', $incorporacion->fecha_memorandum);
        $templateProcessor->setValue('incorporacion.fechaRAP', $incorporacion->fecha_rap);
        $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $incorporacion->fecha_de_incorporacion);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'R-1469-01-CambioItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'R-1469-01_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //Informe RAP
    public function genFormRAP($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('RAPCambioItem.docx');
        } else {
            $pathTemplate = $disk->path('RAP.docm');
        }
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('incorporacion.citeRAP', $incorporacion->cite_rap);
        $templateProcessor->setValue('incorporacion.codigoRAP', $incorporacion->codigo_rap);
        $templateProcessor->setValue('codigo', $incorporacion->codigo_rap);

        $carbonFechaRap = Carbon::parse($incorporacion->fecha_rap);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaRap->locale('es_UY');
        $fechaRapFormateada = $carbonFechaRap->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaRAP', $fechaRapFormateada);

        $templateProcessor->setValue('incorporacion.citeInforme', $incorporacion->cite_informe);

        $carbonFechaInforme = Carbon::parse($incorporacion->fecha_informe);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInforme->locale('es_UY');
        $fechaInformeFormateada = $carbonFechaInforme->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaInforme', $fechaInformeFormateada);

        if (isset($incorporacion->puesto_actual)) {
            $descripcion = 'recomienda el cambio del Ítem N°' . $incorporacion->puesto_actual->item . ', al Ítem N°' . $incorporacion->puesto_nuevo->item;

        } else {
            $descripcion = 'recomienda la designación al Ítem N°' . $incorporacion->puesto_nuevo->item;
        }
        $templateProcessor->setValue('descripcion', $descripcion);

        $nombreCompleto = $incorporacion->persona->nombre_completo;
        $sexo = $incorporacion->persona->sexo;

        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.deLa', 'de la servidora publica ' . $nombreCompleto);
            $templateProcessor->setValue('persona.reasignada', 'a la servidora publica interina ' . $nombreCompleto);
        } else {
            $templateProcessor->setValue('persona.deLa', 'del servidor publico ' . $nombreCompleto);
            $templateProcessor->setValue('persona.reasignada', 'al servidor publico interino ' . $nombreCompleto);
        }

        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = strtoupper(substr($nombreDepartamento, 0, 1));
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento . ' ');

        $valorGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        $templateProcessor->setValue('puesto_nuevo.gerencia', $valorGerencia . ' ');

        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
        $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $fechaIncorporacionFormateada);

        $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'RAPCambioItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'RAP_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //PARA MEMORANDUM
    public function genFormMemo($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('MemoCambioItem.docx');
        } else {
            $pathTemplate = $disk->path('memorandum.docx');
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('incorporacion.codigoMemorandum', $incorporacion->codigo_memorandum);
        $templateProcessor->setValue('incorporacion.citeMemorandum', $incorporacion->cite_memorandum);

        $carbonFechaMemo = Carbon::parse($incorporacion->fecha_memorandum);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaMemo->locale('es_UY');
        $fechaMemoFormateada = $carbonFechaMemo->isoFormat('LL');
        $templateProcessor->setValue('fechaMemo', $fechaMemoFormateada);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);

        if (isset($incorporacion->puesto_actual)) {
            $denominacion = $incorporacion->puesto_actual->denominacion;
        } else {
            $denominacion = $incorporacion->puesto_nuevo->denominacion;
        }
        $denominacionEnMayusculas = mb_strtoupper($denominacion, 'UTF-8');
        $templateProcessor->setValue('denominacionPuesto', $denominacionEnMayusculas);

        $primerApellido = $incorporacion->persona->primer_apellido;
        $sexo = $incorporacion->persona->sexo;

        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.para', 'Señora ' . $primerApellido);
            $templateProcessor->setValue('persona.reasignada', 'reasignada' . ' ');
        } else {
            $templateProcessor->setValue('persona.para', 'Señor ' . $primerApellido);
            $templateProcessor->setValue('persona.reasignada', 'reasignado' . ' ');

        }

        $templateProcessor->setValue('incoporacion.codigoRap', $incorporacion->codigo_rap);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = strtoupper(substr($nombreDepartamento, 0, 1));
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
        $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $fechaIncorporacionFormateada);

        $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'MemoCambioItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'Memorandum_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //para acta de posesion de cambio de item
    public function genFormActaDePosesion($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('ActaDePosesionCambioDeItem.docx');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('ActaDePosesionCambioDeItem.docx');
        } else {
            $pathTemplate = $disk->path('R-0242-01.docx');
        }
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $nombreDiaIncorporacion = $carbonFechaIncorporacion->isoFormat('dddd');
        $templateProcessor->setValue('incorporacion.nombreDiaDeIncorporacion', $nombreDiaIncorporacion);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $fechaIncorporacionFormateada);

        $sexo = $incorporacion->persona->sexo;

        if ($sexo === 'F') {
            $templateProcessor->setValue('ciudadano', 'la ciudadana');
        } else {
            $templateProcessor->setValue('ciudadano', 'el ciudadano');

        }

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
        $templateProcessor->setValue('incorporacion.codigoRAP', $incorporacion->codigo_rap);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = strtoupper(substr($nombreDepartamento, 0, 1));
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'ActaDePosesionCambioDeItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'ActaDePosesion_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //para acta de entrega
    public function genFormActaDeEntrega($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('ActaEntregaCambioDeItem.docx');
        } else {
            $pathTemplate = $disk->path('R-0243-01.docx');
        }
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $templateProcessor->setValue('fechaIncorporacion', $fechaIncorporacionFormateada);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);
        $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'ActaEntregaCambioDeItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'ActaEntrega_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }
    //para informe con nota
    public function genFormInformeNota($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('InfNotaCambioItem.docx'); // ruta de plantilla
        } else {
            $pathTemplate = $disk->path('informenota.docx');
        }
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('incorporacion.citeInforme', $incorporacion->cite_informe);

        $nombreCompleto = $incorporacion->persona->nombre_completo;
        $sexo = $incorporacion->persona->sexo;
        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.referenciaMayuscula', 'DE LA SERVIDORA PÚBLICA INTERINA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDORA PÚBLICA INTERINA DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referencia', 'de la servidora publica interina ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia1', ' servidora publica interina de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'La servidora publica interina ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'La señora ' . $nombreCompleto);
        } else {
            $templateProcessor->setValue('persona.referenciaMayuscula', 'DEL SERVIDOR PÚBLICO INTERINO ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDOR PÚBLICO INTERINO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referencia', 'del servidor publico interino ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia1', 'servidor publico interino del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'El servidor publico interino ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'El señor ' . $nombreCompleto);
        }

        if ($incorporacion->puesto_actual) {
            $templateProcessor->setValue('puesto_actual.item', $incorporacion->puesto_actual->item);

            $denominacion = isset($incorporacion->puesto_actual->denominacion) ? $incorporacion->puesto_actual->denominacion : 'Valor predeterminado o mensaje de error';

            $templateProcessor->setValue('puesto_actual.denominacionMayuscula', mb_strtoupper($denominacion, 'UTF-8'));

            $nombreDepartamento = mb_strtoupper($incorporacion->puesto_actual->departamento->nombre, 'UTF-8');
            $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');

            if (in_array($inicialDepartamento, ['D'])) {
                $valorDepartamento = 'DEL ' . $nombreDepartamento;
            } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
                $valorDepartamento = 'DE LA ' . $nombreDepartamento;
            } else {
                $valorDepartamento = 'DE ' . $nombreDepartamento;
            }

            $templateProcessor->setValue('puesto_actual.departamentoMayuscula', $valorDepartamento);

            $templateProcessor->setValue('puesto_actual.gerenciaMayuscula', mb_strtoupper($incorporacion->puesto_actual->departamento->gerencia->nombre, 'UTF-8'));
        } else {
            $templateProcessor->setValue('puesto_actual.item', 'Valor predeterminado o mensaje de error');
            $templateProcessor->setValue('puesto_actual.denominacionMayuscula', 'Valor predeterminado o mensaje de error');
            $templateProcessor->setValue('puesto_actual.departamentoMayuscula', 'Valor predeterminado o mensaje de error');
            $templateProcessor->setValue('puesto_actual.gerenciaMayuscula', 'Valor predeterminado o mensaje de error');
        }

        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.denominacionMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->denominacion, 'UTF-8'));

        $nombreDepartamento = mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre, 'UTF-8');
        $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'DEL ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'DE LA ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'DE ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_nuevo.departamentoMayuscula', $valorDepartamento);

        $templateProcessor->setValue('puesto_nuevo.gerenciaMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre, 'UTF-8'));

        $carbonFechaInfo = Carbon::parse($incorporacion->fecha_informe);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInfo->locale('es_UY');
        $fechaInfoFormateada = $carbonFechaInfo->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaInfo', $fechaInfoFormateada);

        $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);
        $templateProcessor->setValue('incorporacion.citeInfNotaMinuta', $incorporacion->cite_nota_minuta);

        $carbonFechaNotaMinuta = Carbon::parse($incorporacion->fecha_nota_minuta);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaNotaMinuta->locale('es_UY');
        $fechaNotaMinutaFormateada = $carbonFechaNotaMinuta->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaNotaMinuta', $fechaNotaMinutaFormateada);

        $carbonFechaRecepcion = Carbon::parse($incorporacion->fecha_recepcion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaRecepcion->locale('es_UY');
        $fechaRecepcionFormateada = $carbonFechaRecepcion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaRecepcion', $fechaRecepcionFormateada);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        if ($incorporacion->puesto_actual) {
            $denominacion = isset($incorporacion->puesto_actual->denominacion) ? $incorporacion->puesto_actual->denominacion : 'Valor predeterminado o mensaje de error';
            $templateProcessor->setValue('puesto_actual.denominacion', $denominacion);

            if ($incorporacion->puesto_actual->departamento && $incorporacion->puesto_actual->departamento->gerencia) {
                $templateProcessor->setValue('puesto_actual.gerencia', $incorporacion->puesto_actual->departamento->gerencia->nombre);
                $templateProcessor->setValue('puesto_actual.departamento', $incorporacion->puesto_actual->departamento->nombre);
            } else {
                $templateProcessor->setValue('puesto_actual.gerencia', 'Valor predeterminado o mensaje de error');
                $templateProcessor->setValue('puesto_actual.departamento', 'Valor predeterminado o mensaje de error');
            }

            $salario = isset($incorporacion->puesto_actual->salario) ? $incorporacion->puesto_actual->salario : 'Valor predeterminado o mensaje de error';
            $templateProcessor->setValue('puesto_actual.salario', $salario);
        } else {
            $templateProcessor->setValue('puesto_actual.denominacion', 'Valor predeterminado o mensaje de error');
            $templateProcessor->setValue('puesto_actual.gerencia', 'Valor predeterminado o mensaje de error');
            $templateProcessor->setValue('puesto_actual.departamento', 'Valor predeterminado o mensaje de error');
            $templateProcessor->setValue('puesto_actual.salario', 'Valor predeterminado o mensaje de error');
        }

        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
        $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
        $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);
        $templateProcessor->setValue('puesto_nuevo.estado', $incorporacion->puesto_nuevo->estado);
        $templateProcessor->setValue('persona.formacion', $incorporacion->persona->formacion);
        $templateProcessor->setValue('persona.grado', $incorporacion->persona->grado_academico->nombre ?? 'Valor predeterminado');
        $templateProcessor->setValue('persona.areaformacion', $incorporacion->persona->area_formacion->nombre ?? 'Valor predeterminado');
       $templateProcessor->setValue('persona.institucion', $incorporacion->persona->institucion->nombre ?? 'Valor predeterminado');

        $carbonFechaConclusion = Carbon::parse($incorporacion->persona->anio_conclusion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaConclusion->locale('es_UY');
        $fechaConclusionFormateada = $carbonFechaConclusion->isoFormat('LL');
        $templateProcessor->setValue('persona.conclusion', $fechaConclusionFormateada);

        $templateProcessor->setValue('incorporacion.respaldoFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->respaldo_formacion));

        if ($incorporacion) {
            $puestoNuevo = $incorporacion->puesto_nuevo;
            if ($puestoNuevo) {
                $requisitosPuestoNuevo = $puestoNuevo->requisitos;
                if ($requisitosPuestoNuevo->isNotEmpty()) {
                    $primerRequisitoPuestoNuevo = $requisitosPuestoNuevo->first();
                    if ($primerRequisitoPuestoNuevo) {
                        $formacionRequerida = $primerRequisitoPuestoNuevo->formacion_requerida;
                        $expProfesionalSegunCargo = $primerRequisitoPuestoNuevo->experiencia_profesional_segun_cargo;
                        $expRelacionadoAlArea = $primerRequisitoPuestoNuevo->experiencia_relacionado_al_area;
                        $expEnFuncionesDeMando = $primerRequisitoPuestoNuevo->experiencia_en_funciones_de_mando;

                        $templateProcessor->setValue('puesto_nuevo.formacion', $formacionRequerida);
                        $templateProcessor->setValue('puesto_nuevo.expSegunCargo', $expProfesionalSegunCargo);
                        $templateProcessor->setValue('puesto_nuevo.expSegunArea', $expRelacionadoAlArea);
                        $templateProcessor->setValue('puesto_nuevo.expEnMando', $expEnFuncionesDeMando);
                    }
                }
            }
        }

        $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunCargo', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_profesional));
        $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunArea', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_especifica));
        $templateProcessor->setValue('puesto_nuevo.cumpleExpEnMando', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_mando));
        $templateProcessor->setValue('puesto_nuevo.cumpleFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->cumple_con_formacion));
        $templateProcessor->setValue('puesto_nuevo.salario_literal', $incorporacion->puesto_nuevo->salario_literal);

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_nuevo.departamentoRef', $valorDepartamento);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'InfNotaCambioItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'InfNota_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //para informe con minuta
    public function genFormInformeMinuta($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('InfMinutaCambioItem.docx'); // ruta de plantilla
        } else {
            $pathTemplate = $disk->path('informeminuta.docx');
        }
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('incorporacion.citeInforme', $incorporacion->cite_informe);
        $templateProcessor->setValue('incorporacion.codigoNotaMinuta', $incorporacion->codigo_nota_minuta);

        //falta el responsable y su profesion

        $nombreCompleto = $incorporacion->persona->nombre_completo;
        $sexo = $incorporacion->persona->sexo;
        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.referenciaMayuscula', 'DE LA SERVIDORA PÚBLICA INTERINA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDORA PÚBLICA INTERINA DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referencia', 'de la servidora publica interina ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia1', ' servidora publica interina de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'La servidora publica interina ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'La señora ' . $nombreCompleto);
        } else {
            $templateProcessor->setValue('persona.referenciaMayuscula', 'DEL SERVIDOR PÚBLICO INTERINO ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDOR PÚBLICO INTERINO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referencia', 'del servidor publico interino ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia1', 'servidor publico interino del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'El servidor publico interino ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'El señor ' . $nombreCompleto);
        }

        if ($incorporacion && $incorporacion->puesto_actual) {
            $templateProcessor->setValue('puesto_actual.item', optional($incorporacion->puesto_actual)->item);
            $templateProcessor->setValue('puesto_actual.denominacionMayuscula', mb_strtoupper(optional($incorporacion->puesto_actual)->denominacion, 'UTF-8'));
        }


        if ($incorporacion && $incorporacion->puesto_actual && $incorporacion->puesto_actual->departamento) {
            $nombreDepartamento = mb_strtoupper(optional($incorporacion->puesto_actual->departamento)->nombre, 'UTF-8');
        } else {
            $nombreDepartamento = null;
        }

        $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'DEL ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'DE LA ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'DE ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_actual.departamentoMayuscula', $valorDepartamento);

        if ($incorporacion && $incorporacion->puesto_actual && $incorporacion->puesto_actual->departamento && $incorporacion->puesto_actual->departamento->gerencia) {
            $nombreGerencia = mb_strtoupper(optional($incorporacion->puesto_actual->departamento->gerencia)->nombre, 'UTF-8');
        } else {
            $nombreGerencia = null;
        }

        $templateProcessor->setValue('puesto_actual.gerenciaMayuscula', $nombreGerencia);

        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.denominacionMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->denominacion, 'UTF-8'));

        $nombreDepartamento = mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre, 'UTF-8');
        $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'DEL ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'DE LA ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'DE ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_nuevo.departamentoMayuscula', $valorDepartamento);

        $templateProcessor->setValue('puesto_nuevo.gerenciaMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre, 'UTF-8'));

        $carbonFechaInfo = Carbon::parse($incorporacion->fecha_informe);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInfo->locale('es_UY');
        $fechaInfoFormateada = $carbonFechaInfo->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaInfo', $fechaInfoFormateada);

        $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);
        $templateProcessor->setValue('incorporacion.codigoNotaMinuta', $incorporacion->codigo_nota_minuta);
        $templateProcessor->setValue('incorporacion.citeInfNotaMinuta', $incorporacion->cite_nota_minuta);

        $carbonFechaNotaMinuta = Carbon::parse($incorporacion->fecha_nota_minuta);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaNotaMinuta->locale('es_UY');
        $fechaNotaMinutaFormateada = $carbonFechaNotaMinuta->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaNotaMinuta', $fechaNotaMinutaFormateada);

        $carbonFechaRecepcion = Carbon::parse($incorporacion->fecha_recepcion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaRecepcion->locale('es_UY');
        $fechaRecepcionFormateada = $carbonFechaRecepcion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaRecepcion', $fechaRecepcionFormateada);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);

        $templateProcessor->setValue('puesto_actual.denominacion', optional($incorporacion->puesto_actual)->denominacion);
        if ($incorporacion && $incorporacion->puesto_actual) {
            $puestoActual = $incorporacion->puesto_actual;

            if ($puestoActual->departamento) {
                $departamento = $puestoActual->departamento;

                if ($departamento->gerencia) {
                    $gerenciaNombre = $departamento->gerencia->nombre;
                    $templateProcessor->setValue('puesto_actual.gerencia', $gerenciaNombre);
                }
            }
            $templateProcessor->setValue('puesto_actual.departamento', optional($incorporacion->puesto_nuevo->departamento)->nombre);
            $templateProcessor->setValue('puesto_actual.salario', optional($puestoActual)->salario);
        }

        $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
        $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);
        $templateProcessor->setValue('puesto_nuevo.estado', $incorporacion->puesto_nuevo->estado);
        $templateProcessor->setValue('persona.formacion', $incorporacion->persona->formacion);
        $templateProcessor->setValue('persona.grado', $incorporacion->persona->grado_academico->nombre ?? 'Valor predeterminado');
        $templateProcessor->setValue('persona.areaformacion', $incorporacion->persona->area_formacion->nombre ?? 'Valor predeterminado');
        $templateProcessor->setValue('persona.institucion', $incorporacion->persona->institucion->nombre ?? 'Valor predeterminado');

        $carbonFechaConclusion = Carbon::parse($incorporacion->persona->anio_conclusion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaConclusion->locale('es_UY');
        $fechaConclusionFormateada = $carbonFechaConclusion->isoFormat('LL');
        $templateProcessor->setValue('persona.conclusion', $fechaConclusionFormateada);

        $templateProcessor->setValue('incorporacion.respaldoFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->respaldo_formacion));

        if ($incorporacion) {
            $puestoNuevo = $incorporacion->puesto_nuevo;
            if ($puestoNuevo) {
                $requisitosPuestoNuevo = $puestoNuevo->requisitos;
                if ($requisitosPuestoNuevo->isNotEmpty()) {
                    $primerRequisitoPuestoNuevo = $requisitosPuestoNuevo->first();
                    if ($primerRequisitoPuestoNuevo) {
                        $formacionRequerida = $primerRequisitoPuestoNuevo->formacion_requerida;
                        $expProfesionalSegunCargo = $primerRequisitoPuestoNuevo->experiencia_profesional_segun_cargo;
                        $expRelacionadoAlArea = $primerRequisitoPuestoNuevo->experiencia_relacionado_al_area;
                        $expEnFuncionesDeMando = $primerRequisitoPuestoNuevo->experiencia_en_funciones_de_mando;

                        $templateProcessor->setValue('puesto_nuevo.formacion', $formacionRequerida);
                        $templateProcessor->setValue('puesto_nuevo.expSegunCargo', $expProfesionalSegunCargo);
                        $templateProcessor->setValue('puesto_nuevo.expSegunArea', $expRelacionadoAlArea);
                        $templateProcessor->setValue('puesto_nuevo.expEnMando', $expEnFuncionesDeMando);
                    }
                }
            }
        }

        $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunCargo', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_profesional));
        $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunArea', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_especifica));
        $templateProcessor->setValue('puesto_nuevo.cumpleExpEnMando', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_mando));
        $templateProcessor->setValue('puesto_nuevo.cumpleFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->cumple_con_formacion));
        $templateProcessor->setValue('persona.profesion', $incorporacion->persona->profesion);
        $templateProcessor->setValue('puesto_nuevo.salario_literal', $incorporacion->puesto_nuevo->salario_literal);

        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puesto_nuevo.departamentoRef', $valorDepartamento);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'InfMinutaCambioItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'informeminuta.docx_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //para informe R-0976 compromiso
    public function genFormCompromiso($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-0976-01.docx'); // ruta de plantilla
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        Carbon::setLocale('es');
        $fechaHoy = Carbon::now();
        $fechaFormateada = $fechaHoy->isoFormat('LL');
        $templateProcessor->setValue('fecha', $fechaFormateada);

        $fileName = 'R-0976-01_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);
        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //para informe R-0921 incompatibilidad
    public function genFormDeclaracionIncompatibilidad($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-0921-01.docx'); // ruta de plantilla
        $templateProcessor = new TemplateProcessor($pathTemplate);
        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);
        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);

        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }

        $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        Carbon::setLocale('es');
        $fechaHoy = Carbon::now();
        $fechaFormateada = $fechaHoy->isoFormat('LL');
        $templateProcessor->setValue('fecha', $fechaFormateada);

        $fileName = 'R-0921-01_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //para informe R-0716 etica
    public function genFormEtica($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-0716-01.docx'); // ruta de plantilla

        $templateProcessor = new TemplateProcessor($pathTemplate);
        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
        $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);

        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }

        $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        Carbon::setLocale('es');
        $fechaHoy = Carbon::now();
        $fechaFormateada = $fechaHoy->isoFormat('LL');
        $templateProcessor->setValue('fecha', $fechaFormateada);

        $fileName = 'R-0716-01_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    //para informe R-SGC-0033 confidencialidad
    public function genFormConfidencialidad($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-SGC-0033-01.docx'); // ruta de plantilla

        $templateProcessor = new TemplateProcessor($pathTemplate);
        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
        $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);

        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }

        $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

        $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        Carbon::setLocale('es');
        $fechaHoy = Carbon::now();
        $fechaFormateada = $fechaHoy->isoFormat('LL');
        $templateProcessor->setValue('fecha', $fechaFormateada);

        $fileName = 'R-SGC-0033-01_' . $incorporacion->persona->nombre_completo;
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }

    public function downloadEvalForm($fileName)
    {
        $disk = Storage::disk('form_templates');
        return response()->download($disk->path('generados/') . $fileName)->deleteFileAfterSend(true);
    }

    public function observacion($incorporacionId, $calificacion)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        $incorporacion->evaluacion_estado = $calificacion == 1 ? 3 : 4;
        $incorporacion->save();
        return response()->json($incorporacion);
    }

    public function evaluacionFinalizar($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        $incorporacion->paso = $incorporacion->evaluacion_estado != 4 ? 2 : 1;
        $incorporacion->evaluacion_estado = 5;
        $incorporacion->save();
        return response()->json($incorporacion);
    }

    // Actualizar cambio de item
    public function incActualizar(Request $request, $incorporacionId)
    {
        $incorporacionForm = Incorporacion::find($incorporacionId);

        if (!$incorporacionForm) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $dataPersona = $request->input('persona');
        $persona = Persona::find($dataPersona['id']);
        if ($persona) {
            if ($dataPersona['anio_conclusion']) {
                $anioConclusion = Carbon::parse($dataPersona['anio_conclusion'])->setTimezone('UTC')->format('Y-m-d');
                $persona->anio_conclusion = $anioConclusion;
            }
            if ($dataPersona['fecha_nacimiento']) {
                $fechaNacFormated = Carbon::parse($dataPersona['fecha_nacimiento'])->setTimezone('UTC')->format('Y-m-d');
                $persona->fecha_nacimiento = $fechaNacFormated;
            }
            $persona->grado_academico_id = $dataPersona['grado_academico_id'] ?? null;
            $persona->area_formacion_id = $dataPersona['area_formacion_id'] ?? null;
            $persona->institucion_id = $dataPersona['institucion_id'] ?? null;
            $persona->con_respaldo = $dataPersona['con_respaldo'] ?? null;
            $persona->nombres = $dataPersona['nombres'] ?? null;
            $persona->primer_apellido = $dataPersona['primer_apellido'] ?? null;
            $persona->segundo_apellido = $dataPersona['segundo_apellido'];
            $persona->nombre_completo = $dataPersona['nombres'] . " " .
                $dataPersona['primer_apellido'] . " " .
                $dataPersona['segundo_apellido'];
            $persona->ci = $dataPersona['ci'] ?? null;
            $persona->sexo = $dataPersona['sexo'] ?? null;
            $persona->save();
        }

        $incorporacionForm->cumple_exp_profesional = $request->input('cumple_exp_profesional');
        $incorporacionForm->cumple_exp_especifica = $request->input('cumple_exp_especifica');
        $incorporacionForm->cumple_exp_mando = $request->input('cumple_exp_mando');
        $incorporacionForm->cumple_con_formacion = $request->input('cumple_con_formacion');

        if ($request->input('fecha_de_incorporacion')) {
            $fechaIncFormated = Carbon::parse($request->input('fecha_de_incorporacion'))->setTimezone('UTC')->format('Y-m-d');
            $incorporacionForm->fecha_de_incorporacion = $fechaIncFormated;
        }
        $incorporacionForm->hp = $request->input('hp');
        $incorporacionForm->cite_nota_minuta = $request->input('cite_nota_minuta');
        $incorporacionForm->codigo_nota_minuta = $request->input('codigo_nota_minuta');
        if ($request->input('fecha_nota_minuta')) {
            $fecha_nota_minuta = Carbon::parse($request->input('fecha_nota_minuta'))->setTimezone('UTC')->format('Y-m-d');
            $incorporacionForm->fecha_nota_minuta = $fecha_nota_minuta;
        }
        if ($request->input('fecha_recepcion')) {
            $fecha_recepcion = Carbon::parse($request->input('fecha_recepcion'))->setTimezone('UTC')->format('Y-m-d');
            $incorporacionForm->fecha_recepcion = $fecha_recepcion;
        }
        $incorporacionForm->cite_informe = $request->input('cite_informe');
        if ($request->input('fecha_informe')) {
            $fecha_informe = Carbon::parse($request->input('fecha_informe'))->setTimezone('UTC')->format('Y-m-d');
            $incorporacionForm->fecha_informe = $fecha_informe;
        }
        $incorporacionForm->cite_memorandum = $request->input('cite_memorandum');
        $incorporacionForm->codigo_memorandum = $request->input('codigo_memorandum');
        if ($request->input('fecha_memorandum')) {
            $fecha_memorandum = Carbon::parse($request->input('fecha_memorandum'))->setTimezone('UTC')->format('Y-m-d');
            $incorporacionForm->fecha_memorandum = $fecha_memorandum;
        }
        $incorporacionForm->cite_rap = $request->input('cite_rap');
        $incorporacionForm->codigo_rap = $request->input('codigo_rap');
        if ($request->input('fecha_rap')) {
            $fecha_rap = Carbon::parse($request->input('fecha_rap'))->setTimezone('UTC')->format('Y-m-d');
            $incorporacionForm->fecha_rap = $fecha_rap;
        }
        $incorporacionForm->responsable = $request->input('responsable');

        if (
            $request->input('cite_informe') &&
            $request->input('fecha_informe') &&
            $request->input('cite_memorandum') &&
            $request->input('codigo_memorandum') &&
            $request->input('fecha_memorandum') &&
            $request->input('cite_rap') &&
            $request->input('codigo_rap') &&
            $request->input('fecha_rap')
        ) {
            $incorporacionForm->incorporacion_estado = 2;
        }
        $incorporacionForm->save();
        $incorporacionForm->persona;
        $incorporacionForm->puesto_actual;
        $incorporacionForm->puesto_nuevo;
        return $this->sendSuccess($incorporacionForm);
    }

    //funciones de ayuda para ver si cumple o no cumple los requisit
    public function obtenerTextoSegunValor($valor)
    {
        switch ($valor) {
            case 0:
                return 'No';
            case 1:
                return 'Si';
            case 2:
                return 'No corresponde';
            default:
                return 'Valor no reconocido';
        }
    }

    //funciones de ayuda para ver si cumple o no cumple la formacion
    public function obtenerTextoSegunValorDeFormacion($valor)
    {
        switch ($valor) {
            case 0:
                return 'No Cumple';
            case 1:
                return 'Cumple';
            default:
                return 'Valor no reconocido';
        }
    }
}
