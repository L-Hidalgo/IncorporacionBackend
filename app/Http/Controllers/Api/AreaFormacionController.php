<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaDeFormacion;
use Illuminate\Http\Request;

class AreaFormacionController extends Controller
{
    public function listar()
    {
        $areasFormacion = AreaDeFormacion::select(['id', 'nombre'])->get();
        return $this->sendSuccess($areasFormacion);
    }

    public function crearAreaFormacion(Request $request)
    {
        try {
            $areaDeFormacion = new AreaDeFormacion();
            $areaDeFormacion->nombre = $request->input('nombre');
            $areaDeFormacion->save();

            return $this->sendSuccess($areaDeFormacion);
        } catch (\Exception $e) {
            return $this->sendSuccess($e->getMessage());
        }
    }
}
