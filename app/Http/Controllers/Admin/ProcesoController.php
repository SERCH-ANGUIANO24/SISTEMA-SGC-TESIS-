<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcesoCustom;
use Illuminate\Http\Request;

class ProcesoController extends Controller
{
    /**
     * Guarda un nuevo proceso+departamento personalizado.
     * Usado desde el modal de registro cuando se elige "Otro".
     */
    public function store(Request $request)
    {
        $request->validate([
            'proceso'      => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
        ]);

        ProcesoCustom::firstOrCreate([
            'proceso'      => trim($request->proceso),
            'departamento' => trim($request->departamento),
        ]);

        return back()->with('success', "Proceso \"{$request->proceso}\" agregado correctamente.");
    }

    /**
     * Agrega un nuevo departamento a un proceso custom existente.
     * Recibe: proceso (nombre), departamento (nuevo nombre)
     */
    public function addDepartamento(Request $request)
    {
        $request->validate([
            'proceso'      => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
        ]);

        $proceso      = trim($request->proceso);
        $departamento = trim($request->departamento);

        // Evitar duplicados
        $existe = ProcesoCustom::where('proceso', $proceso)
                               ->where('departamento', $departamento)
                               ->exists();

        if ($existe) {
            return back()->with('error', "El departamento \"{$departamento}\" ya existe en el proceso \"{$proceso}\".");
        }

        ProcesoCustom::create([
            'proceso'      => $proceso,
            'departamento' => $departamento,
        ]);

        return back()->with('success', "Departamento \"{$departamento}\" agregado al proceso \"{$proceso}\".");
    }

    /**
     * Elimina UN registro proceso+departamento (elimina solo ese departamento).
     */
    public function destroyDepartamento(ProcesoCustom $proceso)
    {
        $depto  = $proceso->departamento;
        $nombre = $proceso->proceso;
        $proceso->delete();

        return back()->with('success', "Departamento \"{$depto}\" eliminado del proceso \"{$nombre}\".");
    }

    /**
     * Elimina TODOS los registros de un proceso (proceso completo con todos sus deptos).
     */
    public function destroyProceso(Request $request)
    {
        $request->validate([
            'proceso' => ['required', 'string', 'max:255'],
        ]);

        $nombre = trim($request->proceso);
        $count  = ProcesoCustom::where('proceso', $nombre)->delete();

        if ($count === 0) {
            return back()->with('error', "No se encontró el proceso \"{$nombre}\".");
        }

        return back()->with('success', "Proceso \"{$nombre}\" y todos sus departamentos fueron eliminados.");
    }

    /**
     * Elimina un proceso+departamento por ID (alias, mantiene compatibilidad).
     */
    public function destroy(ProcesoCustom $proceso)
    {
        return $this->destroyDepartamento($proceso);
    }
}