<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Puesto;

class PuestoController extends Controller
{
    public function getList() {
        $puestos = Puesto::select(['denominacion', 'item', 'id'])->get();
        return $this->sendSuccess($puestos);
    }

    public function getById($puestoId) {
        $puesto = Puesto::with(['persona_actual'])->select(['denominacion', 'item', 'id','persona_actual_id'])->find($puestoId);
        return $this->sendSuccess($puesto);
    }

    public function getByItem($item) {
        $puesto = Puesto::with(['persona_actual:id,nombre_completo,nombres,primer_apellido,segundo_apellido,ci,exp,sexo'])->select(['denominacion', 'item', 'id','persona_actual_id'])->where('item', $item)->first();
        return $this->sendSuccess($puesto);
    }
}
