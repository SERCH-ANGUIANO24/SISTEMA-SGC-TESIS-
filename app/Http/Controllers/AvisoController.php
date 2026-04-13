<?php
// app/Http/Controllers/AvisoController.php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Helpers\HistorialVersionesHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| CONTROLADOR: AVISOS
|--------------------------------------------------------------------------
| SE ENCARGA DE GESTIONAR LOS AVISOS DEL SISTEMA:
| CREARLOS, EDITARLOS, ELIMINARLOS, RESTAURARLOS Y VER SUS ARCHIVOS.
*/

class AvisoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: index
    |--------------------------------------------------------------------------
    | MUESTRA LA PANTALLA PRINCIPAL DEL MÓDULO DE AVISOS.
    | REGISTRA LA VISITA EN EL HISTORIAL DE VERSIONES.
    */
    public function index()
    {
        HistorialVersionesHelper::ver('AVISOS', null, 'index');
        return view('avisos.index');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: store
    |--------------------------------------------------------------------------
    | VALIDA LOS DATOS Y GUARDA UN NUEVO AVISO EN LA BASE DE DATOS.
    | SI SE SUBE UN ARCHIVO, LO GUARDA EN EL SERVIDOR Y REGISTRA SUS DATOS.
    | DEVUELVE JSON DE ÉXITO O ERRORES DE VALIDACIÓN.
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'archivo' => 'required|file|max:20480',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ], [
            'titulo.required' => 'El título es obligatorio',
            'archivo.required' => 'El archivo es requerido',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'archivo.max' => 'El archivo no debe exceder los 20MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('archivo');
        $data['created_by'] = auth()->id();
        $data['activo'] = true;

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $originalName);
            $path = $file->storeAs('avisos', $fileName, 'public');
            
            $data['archivo_path'] = $path;
            $data['archivo_nombre'] = $originalName;
            $data['tipo_archivo'] = $file->getMimeType();
            $data['tamano_archivo'] = $file->getSize();
        }

        $aviso = Aviso::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Aviso creado exitosamente',
            'aviso' => $aviso
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: show
    |--------------------------------------------------------------------------
    | BUSCA UN AVISO POR SU ID, SUMA UNA VISITA Y LO DEVUELVE EN JSON.
    | TAMBIÉN REGISTRA LA VISITA EN EL HISTORIAL DE VERSIONES.
    */
    public function show($id)
    {
        $aviso = Aviso::findOrFail($id);
        $aviso->increment('visitas');
        
        HistorialVersionesHelper::ver('AVISOS', $aviso, 'detalle');
        
        return response()->json($aviso);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: update
    |--------------------------------------------------------------------------
    | VALIDA Y ACTUALIZA LOS DATOS DE UN AVISO EXISTENTE.
    | SI SE SUBE UN ARCHIVO NUEVO, ELIMINA EL ANTERIOR Y GUARDA EL NUEVO.
    | DEVUELVE JSON DE ÉXITO O ERRORES DE VALIDACIÓN.
    */
    public function update(Request $request, $id)
    {
        $aviso = Aviso::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'archivo' => 'nullable|file|max:20480',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('archivo');

        if ($request->hasFile('archivo')) {
            // Si se sube un nuevo archivo, ELIMINAR EL ANTERIOR SOLO SI EXISTE
            if ($aviso->archivo_path && Storage::disk('public')->exists($aviso->archivo_path)) {
                Storage::disk('public')->delete($aviso->archivo_path);
            }
            
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $originalName);
            $path = $file->storeAs('avisos', $fileName, 'public');
            
            $data['archivo_path'] = $path;
            $data['archivo_nombre'] = $originalName;
            $data['tipo_archivo'] = $file->getMimeType();
            $data['tamano_archivo'] = $file->getSize();
        }

        $aviso->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Aviso actualizado exitosamente',
            'aviso' => $aviso
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: destroy
    |--------------------------------------------------------------------------
    | ELIMINA EL AVISO DE LA BASE DE DATOS (SOFT DELETE).
    | EL ARCHIVO FÍSICO NO SE BORRA DEL SERVIDOR PARA PODER RESTAURARLO DESPUÉS.
    | DEVUELVE JSON DE ÉXITO.
    */
    public function destroy($id)
    {
        $aviso = Aviso::findOrFail($id);
        
        // 🔥 IMPORTANTE: NO se elimina el archivo físico para poder restaurarlo después
        // El archivo permanece en el servidor para cuando se restaure el aviso
        
        $aviso->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Aviso eliminado exitosamente'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: restaurar
    |--------------------------------------------------------------------------
    | RESTAURA UN AVISO QUE FUE ELIMINADO (SOFT DELETE).
    | VERIFICA QUE NO EXISTA OTRO AVISO ACTIVO CON EL MISMO TÍTULO.
    | AVISA SI EL ARCHIVO FÍSICO YA NO EXISTE EN EL SERVIDOR.
    */
    public function restaurar($id)
    {
        $aviso = Aviso::withTrashed()->findOrFail($id);
        
        if (!$aviso->trashed()) {
            return redirect()->back()->with('error', 'El aviso no está eliminado');
        }
        
        // Verificar si ya existe un aviso activo con el mismo título
        $existing = Aviso::where('titulo', $aviso->titulo)
            ->whereNull('deleted_at')
            ->first();
            
        if ($existing) {
            return redirect()->back()->with('error', 'Ya existe un aviso activo con el mismo título.');
        }
        
        // El archivo físico debería existir porque NO se eliminó
        $archivoExiste = true;
        if ($aviso->archivo_path && !Storage::disk('public')->exists($aviso->archivo_path)) {
            $archivoExiste = false;
        }
        
        $aviso->restore();
        
        if ($archivoExiste) {
            return redirect('/avisos')->with('success', 'Aviso restaurado correctamente.');
        } else {
            return redirect('/avisos')->with('warning', 'Aviso restaurado correctamente, pero el archivo físico no existe en el servidor. Deberás subir el archivo nuevamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: verArchivo
    |--------------------------------------------------------------------------
    | ABRE EL ARCHIVO DEL AVISO DIRECTAMENTE EN EL NAVEGADOR (INLINE).
    | SI EL ARCHIVO NO EXISTE EN EL SERVIDOR → DEVUELVE ERROR 404.
    | REGISTRA LA ACCIÓN EN EL HISTORIAL DE VERSIONES.
    */
    public function verArchivo($id)
    {
        $aviso = Aviso::withTrashed()->findOrFail($id);
        
        if (!$aviso->archivo_path || !Storage::disk('public')->exists($aviso->archivo_path)) {
            abort(404, 'El archivo no existe en el servidor');
        }
        
        HistorialVersionesHelper::ver('AVISOS', $aviso, 'ver_archivo');
        
        $file = Storage::disk('public')->get($aviso->archivo_path);
        $mimeType = Storage::disk('public')->mimeType($aviso->archivo_path);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $aviso->archivo_nombre . '"');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: download
    |--------------------------------------------------------------------------
    | DESCARGA EL ARCHIVO DEL AVISO AL DISPOSITIVO DEL USUARIO.
    | SI EL ARCHIVO NO EXISTE EN EL SERVIDOR → DEVUELVE ERROR 404.
    | REGISTRA LA DESCARGA EN EL HISTORIAL DE VERSIONES.
    */
    public function download($id)
    {
        $aviso = Aviso::withTrashed()->findOrFail($id);
        
        if (!$aviso->archivo_path || !Storage::disk('public')->exists($aviso->archivo_path)) {
            abort(404, 'El archivo no existe');
        }
        
        HistorialVersionesHelper::descargar('AVISOS', $aviso);
        
        return Storage::disk('public')->download($aviso->archivo_path, $aviso->archivo_nombre);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: getActiveAvisos
    |--------------------------------------------------------------------------
    | OBTIENE TODOS LOS AVISOS DEL SISTEMA ORDENADOS DEL MÁS RECIENTE AL MÁS ANTIGUO.
    | INCLUYE LA INFORMACIÓN DEL USUARIO QUE CREÓ CADA AVISO.
    | DEVUELVE LOS RESULTADOS EN FORMATO JSON.
    */
    public function getActiveAvisos()
    {
        $avisos = Aviso::with('creador')
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return response()->json($avisos);
    }
}