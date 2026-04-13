<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProcesosDepartamento;
use App\Helpers\HistorialVersionesHelper;

class ProcesosDepartamentosController extends Controller
{
    /**
     * INDEX
     * LISTA TODOS LOS PROCESOS AGRUPADOS CON SUS DEPARTAMENTOS.
     * CONSULTA LA TABLA, CONSTRUYE UN MAPA PROCESO → [DEPARTAMENTOS]
     * Y RETORNA EL RESULTADO EN FORMATO JSON.
     */
    public function index()
    {
        // OBTIENE TODOS LOS REGISTROS ORDENADOS POR PROCESO Y DEPARTAMENTO
        $rows = ProcesosDepartamento::orderBy('proceso')->orderBy('departamento')->get();

        // CONSTRUYE UN MAPA ASOCIATIVO: PROCESO => [DEPARTAMENTO_1, DEPARTAMENTO_2, ...]
        $mapa = [];
        foreach ($rows as $row) {
            $mapa[$row->proceso][] = $row->departamento;
        }

        // TRANSFORMA EL MAPA EN UN ARREGLO DE OBJETOS CON proceso Y departamentos
        $resultado = [];
        foreach ($mapa as $proceso => $deptos) {
            $resultado[] = [
                'proceso'       => $proceso,
                'departamentos' => $deptos,
            ];
        }

        // RETORNA LA RESPUESTA JSON CON TODOS LOS PROCESOS Y SUS DEPARTAMENTOS
        return response()->json($resultado);
    }

    /**
     * STORE
     * CREA UN NUEVO PROCESO CON UNO O MÁS DEPARTAMENTOS ASOCIADOS.
     * SOLO PERMITE ACCESO A USUARIOS CON ROL superadmin O admin.
     * EVITA DUPLICADOS VERIFICANDO LA EXISTENCIA PREVIA DE CADA PAR PROCESO-DEPARTAMENTO.
     * REGISTRA CADA CREACIÓN EN EL HISTORIAL DE VERSIONES.
     */
    public function store(Request $request)
    {
        // OBTIENE EL USUARIO AUTENTICADO PARA VERIFICAR SU ROL
        $user = Auth::user();

        // VERIFICA QUE EL USUARIO TENGA PERMISO DE CREACIÓN (SOLO superadmin O admin)
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear procesos.',
            ], 403);
        }

        // VALIDA QUE proceso SEA STRING REQUERIDO Y departamentos SEA UN ARREGLO CON AL MENOS UN ELEMENTO
        $request->validate([
            'proceso'           => 'required|string|max:200',
            'departamentos'     => 'required|array|min:1',
            'departamentos.*'   => 'required|string|max:200',
        ]);

        // NORMALIZA: CONVIERTE proceso A MAYÚSCULAS Y ELIMINA ESPACIOS AL INICIO Y FINAL
        $proceso       = strtoupper(trim($request->proceso));

        // NORMALIZA CADA DEPARTAMENTO Y FILTRA LOS ELEMENTOS VACÍOS
        $departamentos = array_filter(
            array_map(fn($d) => strtoupper(trim($d)), $request->departamentos)
        );

        // SI EL ARREGLO DE DEPARTAMENTOS QUEDÓ VACÍO TRAS FILTRAR, RETORNA ERROR 422
        if (empty($departamentos)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes agregar al menos un departamento.',
            ], 422);
        }

        // ITERA CADA DEPARTAMENTO: SI NO EXISTE EL PAR, LO CREA EN BASE DE DATOS
        $guardados = [];
        foreach ($departamentos as $departamento) {
            // VERIFICA SI YA EXISTE EL PAR proceso-departamento PARA EVITAR DUPLICADOS
            $existe = ProcesosDepartamento::where('proceso', $proceso)
                ->where('departamento', $departamento)
                ->exists();

            // SOLO INSERTA SI EL REGISTRO NO EXISTE PREVIAMENTE
            if (!$existe) {
                ProcesosDepartamento::create([
                    'proceso'      => $proceso,
                    'departamento' => $departamento,
                ]);
            }
            $guardados[] = $departamento;
        }

        // REGISTRAR EN HISTORIAL - Creación de proceso
        $procesoData = (object)['nombre' => $proceso];
        HistorialVersionesHelper::crear('PROCESOS', $procesoData);
        
        // REGISTRAR CADA DEPARTAMENTO CREADO EN HISTORIAL
        foreach ($guardados as $departamento) {
            $deptoData = (object)[
                'proceso' => $proceso,
                'departamento' => $departamento
            ];
            HistorialVersionesHelper::crear('DEPARTAMENTOS', $deptoData);
        }

        // RETORNA RESPUESTA EXITOSA CON EL PROCESO Y LOS DEPARTAMENTOS GUARDADOS
        return response()->json([
            'success'       => true,
            'message'       => 'Proceso y departamentos guardados correctamente.',
            'proceso'       => $proceso,
            'departamentos' => array_values($guardados),
        ]);
    }

    /**
     * DESTROY
     * ELIMINA UN PROCESO COMPLETO Y TODOS SUS DEPARTAMENTOS ASOCIADOS.
     * SOLO PERMITE ACCESO A USUARIOS CON ROL superadmin O admin.
     * REGISTRA EN HISTORIAL CADA DEPARTAMENTO ELIMINADO ANTES DEL DELETE MASIVO,
     * Y LUEGO REGISTRA LA ELIMINACIÓN DEL PROCESO EN SÍ.
     */
    public function destroy(Request $request)
    {
        // OBTIENE EL USUARIO AUTENTICADO PARA VERIFICAR SU ROL
        $user = Auth::user();

        // VERIFICA QUE EL USUARIO TENGA PERMISO DE ELIMINACIÓN (SOLO superadmin O admin)
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar procesos.',
            ], 403);
        }

        // VALIDA QUE proceso SEA UN STRING REQUERIDO
        $request->validate([
            'proceso' => 'required|string|max:200',
        ]);

        // NORMALIZA: CONVIERTE proceso A MAYÚSCULAS Y ELIMINA ESPACIOS AL INICIO Y FINAL
        $proceso = strtoupper(trim($request->proceso));

        // PREPARA EL OBJETO DEL PROCESO PARA EL HISTORIAL
        $procesoData = (object)['nombre' => $proceso];
        
        // OBTIENE TODOS LOS DEPARTAMENTOS DEL PROCESO ANTES DE ELIMINARLOS
        // (NECESARIO PARA REGISTRAR CADA UNO EN EL HISTORIAL ANTES DEL DELETE MASIVO)
        $departamentos = ProcesosDepartamento::where('proceso', $proceso)->get();

        // REGISTRA EN HISTORIAL LA ELIMINACIÓN DE CADA DEPARTAMENTO ANTES DE BORRARLOS
        foreach ($departamentos as $depto) {
            $deptoData = (object)[
                'proceso' => $proceso,
                'departamento' => $depto->departamento
            ];
            HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $deptoData);
        }

        // ELIMINA TODOS LOS REGISTROS DEL PROCESO (DELETE MASIVO)
        $eliminados = ProcesosDepartamento::where('proceso', $proceso)->delete();

        // SI NO SE ENCONTRÓ NINGÚN REGISTRO, RETORNA ERROR 404
        if ($eliminados === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el proceso.',
            ], 404);
        }

        // REGISTRAR EN HISTORIAL - Eliminación del proceso
        HistorialVersionesHelper::eliminar('PROCESOS', $procesoData);

        // RETORNA RESPUESTA EXITOSA CON EL NOMBRE DEL PROCESO ELIMINADO
        return response()->json([
            'success' => true,
            'message' => "Proceso \"{$proceso}\" eliminado correctamente.",
        ]);
    }

    /**
     * DESTROY DEPARTAMENTO
     * ELIMINA UN DEPARTAMENTO ESPECÍFICO DENTRO DE UN PROCESO
     * SIN AFECTAR LOS DEMÁS DEPARTAMENTOS DEL MISMO PROCESO.
     * SOLO PERMITE ACCESO A USUARIOS CON ROL superadmin O admin.
     * REGISTRA LA ELIMINACIÓN EN EL HISTORIAL DE VERSIONES.
     */
    public function destroyDepartamento(Request $request)
    {
        // OBTIENE EL USUARIO AUTENTICADO PARA VERIFICAR SU ROL
        $user = Auth::user();

        // VERIFICA QUE EL USUARIO TENGA PERMISO DE ELIMINACIÓN (SOLO superadmin O admin)
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar departamentos.',
            ], 403);
        }

        // VALIDA QUE TANTO proceso COMO departamento SEAN STRINGS REQUERIDOS
        $request->validate([
            'proceso'      => 'required|string|max:200',
            'departamento' => 'required|string|max:200',
        ]);

        // NORMALIZA: CONVIERTE AMBOS VALORES A MAYÚSCULAS Y ELIMINA ESPACIOS
        $proceso      = strtoupper(trim($request->proceso));
        $departamento = strtoupper(trim($request->departamento));

        // PREPARA EL OBJETO DEL DEPARTAMENTO PARA EL HISTORIAL
        $departamentoData = (object)[
            'proceso'      => $proceso,
            'departamento' => $departamento,
        ];

        // BUSCA Y ELIMINA EL REGISTRO QUE COINCIDA CON EL PAR proceso-departamento
        $eliminado = ProcesosDepartamento::where('proceso', $proceso)
            ->where('departamento', $departamento)
            ->delete();

        // SI NO SE ENCONTRÓ EL REGISTRO, RETORNA ERROR 404
        if ($eliminado === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el departamento.',
            ], 404);
        }

        // REGISTRAR EN HISTORIAL - Eliminación de departamento
        HistorialVersionesHelper::eliminar('DEPARTAMENTOS', $departamentoData);

        // RETORNA RESPUESTA EXITOSA CON EL NOMBRE DEL DEPARTAMENTO ELIMINADO
        return response()->json([
            'success' => true,
            'message' => "Departamento \"{$departamento}\" eliminado correctamente.",
        ]);
    }
}