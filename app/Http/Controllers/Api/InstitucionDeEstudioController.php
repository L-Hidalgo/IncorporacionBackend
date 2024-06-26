<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitucionDeEstudioController extends Controller
{
    public function listar()
    {
        $dde_institucionesDeEstudio = Institucion::select(['id', 'nombre'])->get();
        return $this->sendSuccess($dde_institucionesDeEstudio);
    }

    public function crear(Request $request)
    {
        try {
            $institucionDeEstudio = new Institucion();
            $institucionDeEstudio->nombre = $request->input('nombre');
            $institucionDeEstudio->save();

            return $this->sendSuccess($institucionDeEstudio);
        } catch (\Exception $e) {
            return $this->sendSuccess($e->getMessage());
        }
    }
}
