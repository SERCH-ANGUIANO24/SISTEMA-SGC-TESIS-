<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcesoCustom;
use App\Helpers\HistorialVersionesHelper;
use Illuminate\Http\Request;

// CONTROLADOR QUE GESTIONA LOS PROCESOS Y DEPARTAMENTOS PERSONALIZADOS DEL SISTEMA
// PERMITE CREAR, AGREGAR Y ELIMINAR PROCESOS CON SUS DEPARTAMENTOS ASOCIADOS
class ProcesoController extends Controller
{
    /**
     * Guarda un nuevo proceso+departamento personalizado.
     */
    public function store(Request $request)
    {
        // VALIDA QUE EL PROCESO Y EL DEPARTAMENTO SEAN OBLIGATORIOS Y NO EXCEDAN 255 CARACTERES
        $request->validate([
            'proceso'      => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
        ]);

        // LIMPIA ESPACIOS EN BLANCO AL INICIO Y AL FINAL DE LOS VALORES RECIBIDOS
        $proceso = trim($request->proceso);
        $departamento = trim($request->departamento);

        // VERIFICA SI YA EXISTE ESA COMBINACIÓN DE PROCESO + DEPARTAMENTO EN LA BASE DE DATOS
        // ESTO EVITA DUPLICADOS
        $existe = ProcesoCustom::where('proceso', $proceso)
            ->where('departamento', $departamento)
            ->exists();

        if (!$existe) {
            // SI NO EXISTE, CREA EL NUEVO REGISTRO EN LA BASE DE DATOS
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

        // REDIRIGE DE VUELTA CON UN MENSAJE DE ÉXITO INDICANDO EL PROCESO CREADO
        return back()->with('success', "Proceso \"{$proceso}\" agregado correctamente.");
    }

    /**
     * Agrega un nuevo departamento a un proceso custom existente.
     */
    public function addDepartamento(Request $request)
    {
        // VALIDA QUE EL PROCESO Y EL DEPARTAMENTO SEAN OBLIGATORIOS Y NO EXCEDAN 255 CARACTERES
        $request->validate([
            'proceso'      => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
        ]);

        // LIMPIA ESPACIOS EN BLANCO AL INICIO Y AL FINAL DE LOS VALORES RECIBIDOS
        $proceso      = trim($request->proceso);
        $departamento = trim($request->departamento);

        // VERIFICA SI EL DEPARTAMENTO YA EXISTE DENTRO DEL PROCESO PARA EVITAR DUPLICADOS
        $existe = ProcesoCustom::where('proceso', $proceso)
                               ->where('departamento', $departamento)
                               ->exists();

        // SI YA EXISTE ESA COMBINACIÓN, REGRESA CON UN MENSAJE DE ERROR SIN CREAR NADA
        if ($existe) {
            return back()->with('error', "El departamento \"{$departamento}\" ya existe en el proceso \"{$proceso}\".");
        }

        // SI NO EXISTE, CREA EL NUEVO DEPARTAMENTO ASOCIADO AL PROCESO EN LA BASE DE DATOS
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

        // REDIRIGE DE VUELTA CON MENSAJE DE ÉXITO INDICANDO EL DEPARTAMENTO Y PROCESO AFECTADO
        return back()->with('success', "Departamento \"{$departamento}\" agregado al proceso \"{$proceso}\".");
    }

    /**
     * Elimina UN registro proceso+departamento (elimina solo ese departamento).
     */
    public function destroyDepartamento(ProcesoCustom $proceso)
    {
        // GUARDA EL NOMBRE DEL DEPARTAMENTO Y DEL PROCESO ANTES DE ELIMINAR (PARA EL HISTORIAL)
        $depto  = $proceso->departamento;
        $nombre = $proceso->proceso;
        
        $deptoData = (object)[
            'proceso' => $nombre,
            'departamento' => $depto
        ];
        
        // ELIMINA SOLO ESTE REGISTRO (UN SOLO DEPARTAMENTO DEL PROCESO)
        $proceso->delete();

        // REGISTRAR EN HISTORIAL - ELIMINACIÓN DE DEPARTAMENTO
        HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $deptoData);

        // REDIRIGE DE VUELTA CON MENSAJE DE ÉXITO INDICANDO EL DEPARTAMENTO Y PROCESO AFECTADO
        return back()->with('success', "Departamento \"{$depto}\" eliminado del proceso \"{$nombre}\".");
    }

    /**
     * Elimina TODOS los registros de un proceso (proceso completo con todos sus deptos).
     */
    public function destroyProceso(Request $request)
    {
        // VALIDA QUE EL NOMBRE DEL PROCESO SEA OBLIGATORIO Y NO EXCEDA 255 CARACTERES
        $request->validate([
            'proceso' => ['required', 'string', 'max:255'],
        ]);

        // LIMPIA ESPACIOS EN BLANCO AL INICIO Y AL FINAL DEL NOMBRE DEL PROCESO
        $nombre = trim($request->proceso);
        
        // OBTIENE TODOS LOS DEPARTAMENTOS ASOCIADOS A ESTE PROCESO ANTES DE ELIMINARLOS
        $departamentos = ProcesoCustom::where('proceso', $nombre)->get();
        
        // REGISTRA EN EL HISTORIAL CADA DEPARTAMENTO QUE SERÁ ELIMINADO UNO POR UNO
        foreach ($departamentos as $depto) {
            $deptoData = (object)[
                'proceso' => $nombre,
                'departamento' => $depto->departamento
            ];
            HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $deptoData);
        }
        
        // ELIMINA TODOS LOS REGISTROS DEL PROCESO (TODOS SUS DEPARTAMENTOS) DE LA BASE DE DATOS
        $count = ProcesoCustom::where('proceso', $nombre)->delete();

        // SI NO SE ENCONTRÓ NINGÚN REGISTRO CON ESE NOMBRE, REGRESA CON UN MENSAJE DE ERROR
        if ($count === 0) {
            return back()->with('error', "No se encontró el proceso \"{$nombre}\".");
        }

        // REGISTRAR EN HISTORIAL - ELIMINACIÓN DEL PROCESO
        $procesoData = (object)['nombre' => $nombre];
        HistorialVersionesHelper::eliminar('PROCESOS', $procesoData);

        // REDIRIGE DE VUELTA CON MENSAJE DE ÉXITO INDICANDO EL PROCESO COMPLETAMENTE ELIMINADO
        return back()->with('success', "Proceso \"{$nombre}\" y todos sus departamentos fueron eliminados.");
    }

    /**
     * Elimina un proceso+departamento por ID (alias)
     */
    public function destroy(ProcesoCustom $proceso)
    {
        // ESTE MÉTODO ES UN ALIAS QUE DELEGA DIRECTAMENTE AL MÉTODO destroyDepartamento
        // SE USA PARA MANTENER COMPATIBILIDAD CON LAS RUTAS RESOURCEFUL DE LARAVEL
        return $this->destroyDepartamento($proceso);
    }
}