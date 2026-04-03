<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProcesosDepartamento;
use App\Helpers\HistorialVersionesHelper;

class ProcesosDepartamentosController extends Controller
{
    public function index()
    {
        $rows = ProcesosDepartamento::orderBy('proceso')->orderBy('departamento')->get();

        $mapa = [];
        foreach ($rows as $row) {
            $mapa[$row->proceso][] = $row->departamento;
        }

        $resultado = [];
        foreach ($mapa as $proceso => $deptos) {
            $resultado[] = [
                'proceso'       => $proceso,
                'departamentos' => $deptos,
            ];
        }

        return response()->json($resultado);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear procesos.',
            ], 403);
        }

        $request->validate([
            'proceso'           => 'required|string|max:200',
            'departamentos'     => 'required|array|min:1',
            'departamentos.*'   => 'required|string|max:200',
        ]);

        $proceso       = strtoupper(trim($request->proceso));
        $departamentos = array_filter(
            array_map(fn($d) => strtoupper(trim($d)), $request->departamentos)
        );

        if (empty($departamentos)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes agregar al menos un departamento.',
            ], 422);
        }

        $guardados = [];
        foreach ($departamentos as $departamento) {
            $existe = ProcesosDepartamento::where('proceso', $proceso)
                ->where('departamento', $departamento)
                ->exists();

            if (!$existe) {
                ProcesosDepartamento::create([
                    'proceso'      => $proceso,
                    'departamento' => $departamento,
                ]);
            }
            $guardados[] = $departamento;
        }

        // Registrar en historial - Creación de proceso
        $procesoData = (object)['nombre' => $proceso];
        HistorialVersionesHelper::crear('PROCESOS', $procesoData);
        
        // Registrar cada departamento creado en historial
        foreach ($guardados as $departamento) {
            $deptoData = (object)[
                'proceso' => $proceso,
                'departamento' => $departamento
            ];
            HistorialVersionesHelper::crear('DEPARTAMENTOS', $deptoData);
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Proceso y departamentos guardados correctamente.',
            'proceso'       => $proceso,
            'departamentos' => array_values($guardados),
        ]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar procesos.',
            ], 403);
        }

        $request->validate([
            'proceso' => 'required|string|max:200',
        ]);

        $proceso = strtoupper(trim($request->proceso));

        $procesoData = (object)['nombre' => $proceso];
        
        // Obtener todos los departamentos del proceso antes de eliminarlos
        $departamentos = ProcesosDepartamento::where('proceso', $proceso)->get();

        // Registrar cada departamento eliminado en historial
        foreach ($departamentos as $depto) {
            $deptoData = (object)[
                'proceso' => $proceso,
                'departamento' => $depto->departamento
            ];
            HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $deptoData);
        }

        $eliminados = ProcesosDepartamento::where('proceso', $proceso)->delete();

        if ($eliminados === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el proceso.',
            ], 404);
        }

        // Registrar en historial - Eliminación del proceso
        HistorialVersionesHelper::eliminar('PROCESOS', $procesoData);

        return response()->json([
            'success' => true,
            'message' => "Proceso \"{$proceso}\" eliminado correctamente.",
        ]);
    }

    public function destroyDepartamento(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar departamentos.',
            ], 403);
        }

        $request->validate([
            'proceso'      => 'required|string|max:200',
            'departamento' => 'required|string|max:200',
        ]);

        $proceso      = strtoupper(trim($request->proceso));
        $departamento = strtoupper(trim($request->departamento));

        $departamentoData = (object)[
            'proceso'      => $proceso,
            'departamento' => $departamento,
        ];

        $eliminado = ProcesosDepartamento::where('proceso', $proceso)
            ->where('departamento', $departamento)
            ->delete();

        if ($eliminado === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el departamento.',
            ], 404);
        }

        // Registrar en historial - Eliminación de departamento
        HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $departamentoData);

        return response()->json([
            'success' => true,
            'message' => "Departamento \"{$departamento}\" eliminado correctamente.",
        ]);
    }
}