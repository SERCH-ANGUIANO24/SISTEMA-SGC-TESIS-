<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcesoCustom;
use App\Helpers\HistorialVersionesHelper;
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

        $proceso = trim($request->proceso);
        $departamento = trim($request->departamento);

        $existe = ProcesoCustom::where('proceso', $proceso)
            ->where('departamento', $departamento)
            ->exists();

        if (!$existe) {
            ProcesoCustom::create([
                'proceso' => $proceso,
                'departamento' => $departamento,
            ]);
            
            // REGISTRAR EN HISTORIAL - CREACIÓN DE PROCESO
            $procesoData = (object)['nombre' => $proceso];
            HistorialVersionesHelper::crear('PROCESOS', $procesoData);
            
            // REGISTRAR EN HISTORIAL - CREACIÓN DE DEPARTAMENTO
            $deptoData = (object)[
                'proceso' => $proceso,
                'departamento' => $departamento
            ];
            HistorialVersionesHelper::crear('DEPARTAMENTOS', $deptoData);
        }

        return back()->with('success', "Proceso \"{$proceso}\" agregado correctamente.");
    }

    /**
     * Agrega un nuevo departamento a un proceso custom existente.
     */
    public function addDepartamento(Request $request)
    {
        $request->validate([
            'proceso'      => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
        ]);

        $proceso      = trim($request->proceso);
        $departamento = trim($request->departamento);

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

        // REGISTRAR EN HISTORIAL - CREACIÓN DE DEPARTAMENTO
        $deptoData = (object)[
            'proceso' => $proceso,
            'departamento' => $departamento
        ];
        HistorialVersionesHelper::crear('DEPARTAMENTOS', $deptoData);

        return back()->with('success', "Departamento \"{$departamento}\" agregado al proceso \"{$proceso}\".");
    }

    /**
     * Elimina UN registro proceso+departamento (elimina solo ese departamento).
     */
    public function destroyDepartamento(ProcesoCustom $proceso)
    {
        $depto  = $proceso->departamento;
        $nombre = $proceso->proceso;
        
        $deptoData = (object)[
            'proceso' => $nombre,
            'departamento' => $depto
        ];
        
        $proceso->delete();

        // REGISTRAR EN HISTORIAL - ELIMINACIÓN DE DEPARTAMENTO
        HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $deptoData);

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
        
        $departamentos = ProcesoCustom::where('proceso', $nombre)->get();
        
        // Registrar cada departamento eliminado
        foreach ($departamentos as $depto) {
            $deptoData = (object)[
                'proceso' => $nombre,
                'departamento' => $depto->departamento
            ];
            HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $deptoData);
        }
        
        $count = ProcesoCustom::where('proceso', $nombre)->delete();

        if ($count === 0) {
            return back()->with('error', "No se encontró el proceso \"{$nombre}\".");
        }

        // REGISTRAR EN HISTORIAL - ELIMINACIÓN DEL PROCESO
        $procesoData = (object)['nombre' => $nombre];
        HistorialVersionesHelper::eliminar('PROCESOS', $procesoData);

        return back()->with('success', "Proceso \"{$nombre}\" y todos sus departamentos fueron eliminados.");
    }

    /**
     * Elimina un proceso+departamento por ID (alias)
     */
    public function destroy(ProcesoCustom $proceso)
    {
        return $this->destroyDepartamento($proceso);
    }
}