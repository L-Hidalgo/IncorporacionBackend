<?php

use App\Http\Controllers\Controller;
use App\Models\Persona;

class PersonaController extends Controller
{
    public function mostrarDatosEnTabla()
    {
        $dde_personas = Persona::with('puestoPersona.puesto')->get();
        return view('tu_vista', compact('dde_personas'));
    }
}
