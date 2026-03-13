<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProcesosDepartamento;

class ProcesosDepartamentosController extends Controller
{
    /**
     * Devuelve todos los procesos dinámicos con sus departamentos. — TODOS los roles
     */
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

    /**
     * Guarda un nuevo proceso con sus departamentos. — SOLO SUPERADMIN/ADMIN
     */
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

        return response()->json([
            'success'       => true,
            'message'       => 'Proceso y departamentos guardados correctamente.',
            'proceso'       => $proceso,
            'departamentos' => array_values($guardados),
        ]);
    }

    /**
     * Elimina un proceso completo con todos sus departamentos. — SOLO SUPERADMIN/ADMIN
     */
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

        $eliminados = ProcesosDepartamento::where('proceso', $proceso)->delete();

        if ($eliminados === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el proceso.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Proceso \"{$proceso}\" eliminado correctamente.",
        ]);
    }

    /**
     * Elimina un departamento específico de un proceso. — SOLO SUPERADMIN/ADMIN
     */
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

        $eliminado = ProcesosDepartamento::where('proceso', $proceso)
            ->where('departamento', $departamento)
            ->delete();

        if ($eliminado === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el departamento.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Departamento \"{$departamento}\" eliminado correctamente.",
        ]);
    }
}