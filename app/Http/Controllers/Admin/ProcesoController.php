<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcesoCustom;
use Illuminate\Http\Request;

class ProcesoController extends Controller
{
    /**
     * Guarda un nuevo proceso+departamento personalizado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'proceso'      => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
        ]);

        ProcesoCustom::create([
            'proceso'      => trim($request->proceso),
            'departamento' => trim($request->departamento),
        ]);

        return back()->with('success', "Proceso \"{$request->proceso}\" agregado correctamente.");
    }

    /**
     * Elimina un proceso+departamento personalizado.
     */
    public function destroy(ProcesoCustom $proceso)
    {
        $nombre = $proceso->proceso;
        $proceso->delete();

        return back()->with('success', "Proceso \"{$nombre}\" eliminado correctamente.");
    }
}