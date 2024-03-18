<?php
namespace App\Imports;

use App\Models\Departamento;
use App\Models\Gerencia;
use App\Models\Persona;
use App\Models\PersonaPuesto;
use App\Models\Puesto;
use App\Models\Requisito;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

class ImportExcelData implements ToModel, WithStartRow
{

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $gerencia = $this->migrateGerencia($row[0], $row[2]);

        $departamento = $this->migrarDepartamento($row[3], $gerencia->id);

        $puesto = $this->migrarPuesto($row[1], $row[4], $row[5], $row[6], $row[43], $departamento->id);

        if (isset($row[7]) && isset($row[12])) {
            $persona = $this->migrarPersona(
                $row[7],
                $row[9],
                $row[10],
                $row[11],
                $row[12],
                $row[12] . ' ' . $row[10] . ' ' . $row[11],
                $row[15],
                $row[16],
                $row[17],
                $row[19],
                $row[20],
            );
            $puesto->persona_actual_id = $persona->id;
            $puesto->estado = 'OCUPADO';
            $puesto->save();
            $personaPuesto = $this->migrarPersonaPuesto(
                $row[14],
                $row[21],
                $row[39],
                $row[40],
                $row[41],
                $puesto->id,
                $persona->id);
        }
        // $procesoDeIncorporacion = $this->migrarProcesoDeIncorporacion($row[21], $row[22], $row[23], $row[24], $row[25], $row[26], $row[27], $row[28], $row[29], $row[30], $row[31], $row[32], $row[33], $row[34], $row[35], $row[36], $row[37], $row[38], $puesto->id);

        $requisitos = $this->migrarRequisito($puesto->id, $row[43], $row[44], $row[45], $row[46]);
    }

    public function migrateGerencia($abreviatura,$nombre): Gerencia
    {
        $gerencia = Gerencia::where('nombre', $nombre)->first();
        if (!isset($gerencia)) {
            $gerencia = Gerencia::create([
                'nombre' => $nombre,
                'abreviatura' =>  $abreviatura
            ]);
        }
        return $gerencia;
    }

    public function migrarDepartamento($nombre, $gerenciaId): Departamento
    {
        $departamento = Departamento::where('nombre', $nombre)->where('gerencia_id', $gerenciaId)->first();
        if (!isset($departamento)) {
            $departamento = Departamento::create([
                'nombre' => $nombre,
                'gerencia_id' => $gerenciaId
            ]);
        }
        return $departamento;
    }

    public function migrarPuesto(
        $item,
        $denominacion,
        $salario,
        $salario_literal,
        $objetivo,$departamentoId): Puesto
    {
        $puesto = Puesto::where('item', $item)->first();
        if (!isset($puesto)) {
            $puesto = Puesto::create([
                'item' => $item,
                'denominacion' => $denominacion,
                'salario' => $salario,
                'salario_literal' => $salario_literal,
                'objetivo' => $objetivo,
                'departamento_id' => $departamentoId,
                'estado' => 'ACEFALIA',
            ]);
        } else {
            $puesto->denominacion = $denominacion;
            $puesto->salario = $salario;
            $puesto->salario_literal = $salario_literal;
            $puesto->objetivo = $objetivo;
            $puesto->departamento_id = $departamentoId;
            $puesto->estado = 'ACEFALIA';
            $puesto->persona_actual_id = null;
            $puesto->save();
        }
        return $puesto;
    }

    public function migrarPersona(
        $ci,  // 7
        $exp, // 9
        $primerApellido, // 10
        $segundoApellido, // 11
        $nombres,         // 12
        $nombreCompleto,  // 12 + 10 + 11
        $formacion,       // 15
        $sexo,            // 16
        $fechaNacimiento, // 17
        $telefono,        // 19
        $fechaInicioEnSin,// 20
    ): Persona
    {
        $persona = Persona::where('ci', $ci)->first();
        if (!isset($persona)) {
            // formato fecha Nac
            $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($fechaNacimiento);
            $fechaNacimiento = Carbon::createFromTimestamp($timestamp)->format('Y-m-d');
            // formato fecha inicio en SIN
            $timestampfsin = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($fechaInicioEnSin);
            $fechaInicioEnSin = Carbon::createFromTimestamp($timestampfsin)->format('Y-m-d');

            $persona = Persona::create([
                'ci' => $ci,
                'exp' => $exp,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'nombres' => $nombres,
                'nombre_completo' => $nombreCompleto,
                'formacion' => $formacion,
                'sexo' => $sexo,
                'fecha_nacimiento' => $fechaNacimiento,
                'telefono' => $telefono,
                'fecha_inicion_sin' => $fechaInicioEnSin
            ]);
        }
        return $persona;
    }

    public function migrarPersonaPuesto(
        $estadoFormacion,
        $fechaInicio,
        $personalAntiguoEnElCargo,
        $motivoBaja,
        $fechaFin,
        $puestoId,
        $personaId,
    ): PersonaPuesto
    {
        $persona = Persona::find($personaId);
        $puesto = Puesto::find($puestoId);

        if (!$persona || !$puesto) {
            return null;
        }

        $fileAc = $puesto->item . '-' . $persona->ci;

        $personaPuesto = PersonaPuesto::where('estado_formacion', $estadoFormacion)
            ->where('puesto_id', $puestoId)
            ->where('persona_id', $personaId)
            ->first();

        if (!isset($personaPuesto)) {
            $timestampFechaInicio = $this->convertirFechaATimestamp($fechaInicio);
            $fechaInicio = Carbon::createFromTimestamp($timestampFechaInicio)->format('Y-m-d');

            $timestampFechaFin = $this->convertirFechaATimestamp($fechaFin);
            $fechaFin = Carbon::createFromTimestamp($timestampFechaFin)->format('Y-m-d');

            $personaPuesto = PersonaPuesto::create([
                'estado_formacion' => $estadoFormacion,
                'file_ac' => $fileAc,
                'fecha_inicio' => $fechaInicio,
                'personal_antiguo_en_el_cargo' => $personalAntiguoEnElCargo,
                'motivo_baja' => $motivoBaja,
                'estado' => 1,
                'fecha_fin' => $fechaFin,
                'puesto_id' => $puesto->id,
                'persona_id' => $persona->id,
            ]);
        }
        return $personaPuesto;
    }

    // public function migrarProcesoDeIncorporacion($propuestos, $estado, $remitente, $fechaAccion, $responsable, $informeCuadro, $fechaInformeCuadro, $hpHr, $sippase, $idioma, $fechaMovimiento, $tipoMovimiento, $itemOrigen, $cargoOrigen, $memorandum, $ra, $fechaMermorialRap, $sayri, $puestoId): ProcesoDeIncorporacion
    // {
    //     $procesoDeIncorporacion = ProcesoDeIncorporacion::where('propuestos', $propuestos)->where('puesto_id', $puestoId)->first();

    //     if (!isset($procesoDeIncorporacion)) {
    //         if (!empty($fechaAccion)) {
    //             $timestampFechaAccion = $this->convertirFechaATimestamp($fechaAccion);
    //             $fechaAccion = Carbon::createFromTimestamp($timestampFechaAccion)->format('Y-m-d');
    //         }

    //         if (!empty($fechaInformeCuadro)) {
    //             $timestampFechaInformeCuadro = $this->convertirFechaATimestamp($fechaInformeCuadro);
    //             $fechaInformeCuadro = Carbon::createFromTimestamp($timestampFechaInformeCuadro)->format('Y-m-d');
    //         }

    //         if (!empty($fechaMovimiento)) {
    //             $timestampFechaMovimiento = $this->convertirFechaATimestamp($fechaMovimiento);
    //             $fechaMovimiento = Carbon::createFromTimestamp($timestampFechaMovimiento)->format('Y-m-d');
    //         }

    //         if (!empty($fechaMermorialRap)) {
    //             $timestampFechaMermorialRap = $this->convertirFechaATimestamp($fechaMermorialRap);
    //             $fechaMermorialRap = Carbon::createFromTimestamp($timestampFechaMermorialRap)->format('Y-m-d');
    //         }

    //         $procesoDeIncorporacion = ProcesoDeIncorporacion::create([
    //             'propuestos' => $propuestos,
    //             'estado' => $estado,
    //             'remitente' => $remitente,
    //             'fechaAccion' => $fechaAccion,
    //             'responsable' => $responsable,
    //             'informeCuadro' => $informeCuadro,
    //             'fechaInformeCuadro' => $fechaInformeCuadro,
    //             'hpHr' => $hpHr,
    //             'sippase' => $sippase,
    //             'idioma' => $idioma,
    //             'fechaMovimiento' => $fechaMovimiento,
    //             'tipoMovimiento' => $tipoMovimiento,
    //             'itemOrigen' => $itemOrigen,
    //             'cargoOrigen' => $cargoOrigen,
    //             'memorandum' => $memorandum,
    //             'ra' => $ra,
    //             'fechaMermorialRap' => $fechaMermorialRap,
    //             'sayri' => $sayri,
    //             'puesto_id' => $puestoId,
    //         ]);
    //     }

    //     return $procesoDeIncorporacion;
    // }

    public function migrarRequisito($puesto_id, $formacionRequerida, $experienciaProfesionalSegunCargo, $experienciaRelacionadoAlArea, $experienciaEnFuncionesDeMando): Requisito
    {
        $requisitos = Requisito::where('puesto_id', $puesto_id)->first();
        if (!isset($requisitos)) {
            $requisitos = Requisito::create([
                'puesto_id' => $puesto_id,
                'formacion_requerida' => $formacionRequerida,
                'experiencia_profesional_segun_cargo' => $experienciaProfesionalSegunCargo,
                'experiencia_relacionado_al_area' => $experienciaRelacionadoAlArea,
                'experiencia_en_funciones_de_mando' => $experienciaEnFuncionesDeMando
            ]);
        }
        return $requisitos;
    }

    private function convertirFechaATimestamp($fecha)
    {
        try {
            $carbonDate = Carbon::createFromFormat('d/m/Y', $fecha);

            if ($carbonDate instanceof Carbon) {
                return $carbonDate->getTimestamp();
            }
        } catch (\Exception $e) {
            error_log("Error al convertir fecha: " . $e->getMessage());
        }

        try {
            $excelDate = intval($fecha);
            $carbonDate = Carbon::createFromTimestamp(($excelDate - 25569) * 86400);

            if ($carbonDate instanceof Carbon) {
                return $carbonDate->getTimestamp();
            }
        } catch (\Exception $e) {
            error_log("Error al convertir número de serie de Excel: " . $e->getMessage());
        }

        error_log("No se pudo convertir la fecha: $fecha");
        return 0;
    }
}
