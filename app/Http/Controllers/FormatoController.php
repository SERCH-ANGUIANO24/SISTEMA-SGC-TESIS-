<?php

namespace App\Http\Controllers;

use App\Models\Formato;
use App\Models\ProcesosDepartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FormatoController extends Controller
{
    /**
     * Muestra el dashboard principal del módulo de formatos.
     */
    public function index(Request $request)
    {
        $query = Formato::query();

        // Filtro por nombre de archivo
        if ($request->filled('nombre')) {
            $query->where('nombre_archivo', 'like', '%' . $request->nombre . '%');
        }

        // Filtro por versión (coincidencia exacta desde select)
        if ($request->filled('version')) {
            $query->where('version_procedimiento', $request->version);
        }

        // Filtro por código de procedimiento (coincidencia exacta desde select)
        if ($request->filled('codigo')) {
            $query->where('codigo_procedimiento', $request->codigo);
        }

        // Filtro por clave de formato (coincidencia exacta desde select)
        if ($request->filled('clave')) {
            $query->where('clave_formato', $request->clave);
        }

        // Filtro por proceso (coincidencia exacta desde select)
        if ($request->filled('proceso')) {
            $query->where('proceso', $request->proceso);
        }
        
        // Filtro por departamento (coincidencia exacta desde select)
        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        $formatos = $query->orderBy('created_at', 'desc')->get();

        // Combina estáticos del modelo + dinámicos de la BD
        $procesosYDepartamentos = ProcesosDepartamento::mapa();

        // Lista de procesos solo de la BD (para marcarlos con ✎ en el select)
        $procesosDinamicos = ProcesosDepartamento::select('proceso')
            ->distinct()
            ->pluck('proceso')
            ->toArray();

        // Listas únicas para los selects de filtros (siempre del total, no filtrado)
        $versionesUnicas = Formato::orderBy('version_procedimiento')
            ->distinct()
            ->pluck('version_procedimiento')
            ->filter()
            ->values();

        $codigosUnicos = Formato::orderBy('codigo_procedimiento')
            ->distinct()
            ->pluck('codigo_procedimiento')
            ->filter()
            ->values();

        $clavesUnicas = Formato::orderBy('clave_formato')
            ->distinct()
            ->pluck('clave_formato')
            ->filter()
            ->values();

        $procesosUnicos = Formato::orderBy('proceso')
            ->distinct()
            ->pluck('proceso')
            ->filter()
            ->values();
            
        $departamentosUnicos = Formato::orderBy('departamento')
            ->distinct()
            ->pluck('departamento')
            ->filter()
            ->values();

        return view('formatos.index', compact(
            'formatos',
            'procesosYDepartamentos',
            'procesosDinamicos',
            'versionesUnicas',
            'codigosUnicos',
            'clavesUnicas',
            'procesosUnicos',
            'departamentosUnicos'
        ));
    }
    
    /**
     * Almacena un nuevo formato. — SOLO SUPERADMIN/ADMIN
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para subir archivos.');
        }

        $request->validate([
            'proceso'               => 'required|string|max:255',
            'departamento'          => 'required|string|max:255',
            'clave_formato'         => 'required|string|max:100',
            'codigo_procedimiento'  => 'required|string|max:100',
            'version_procedimiento' => 'required|string|max:50',
            'archivo'               => 'required|file|max:20480',
        ]);

        $claveRepetida = Formato::claveExiste($request->clave_formato);

        $archivo        = $request->file('archivo');
        $nombreOriginal = $archivo->getClientOriginalName();
        $extension      = $archivo->getClientOriginalExtension();
        $nombreUnico    = Str::uuid() . '.' . $extension;
        $ruta           = $archivo->storeAs('formatos', $nombreUnico, 'public');

        $formato = Formato::create([
            'proceso'               => $request->proceso,
            'departamento'          => $request->departamento,
            'clave_formato'         => $request->clave_formato,
            'codigo_procedimiento'  => $request->codigo_procedimiento,
            'version_procedimiento' => $request->version_procedimiento,
            'nombre_archivo'        => $nombreOriginal,
            'ruta_archivo'          => $ruta,
            'extension_archivo'     => strtoupper($extension),
            'tamanio_archivo'       => $archivo->getSize(),
        ]);

        if ($claveRepetida) {
            return redirect()->route('formatos.index')
                ->with('warning', 'Archivo subido correctamente, pero LA CLAVE DE FORMATO ESTÁ REPETIDA, MODIFÍCALA.')
                ->with('formato_id_editar', $formato->id);
        }

        return redirect()->route('formatos.index')
            ->with('success', 'Formato subido correctamente.');
    }

    /**
     * Actualiza la información de un formato existente. — SOLO SUPERADMIN/ADMIN
     */
    public function update(Request $request, Formato $formato)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para editar archivos.');
        }

        $request->validate([
            'proceso'               => 'required|string|max:255',
            'departamento'          => 'required|string|max:255',
            'clave_formato'         => 'required|string|max:100',
            'codigo_procedimiento'  => 'required|string|max:100',
            'version_procedimiento' => 'required|string|max:50',
            'archivo'               => 'nullable|file|max:20480',
            'nombre_archivo'        => 'nullable|string|max:255',
        ]);

        $claveRepetida = Formato::claveExiste($request->clave_formato, $formato->id);

        $datos = [
            'proceso'               => $request->proceso,
            'departamento'          => $request->departamento,
            'clave_formato'         => $request->clave_formato,
            'codigo_procedimiento'  => $request->codigo_procedimiento,
            'version_procedimiento' => $request->version_procedimiento,
        ];

        if ($request->filled('nombre_archivo')) {
            $nuevoNombre    = $request->nombre_archivo;
            $extension      = $formato->extension_archivo;
            $nombreCompleto = $nuevoNombre . '.' . strtolower($extension);
            $datos['nombre_archivo'] = $nombreCompleto;
        }

        if ($request->hasFile('archivo')) {
            Storage::disk('public')->delete($formato->ruta_archivo);

            $archivo        = $request->file('archivo');
            $nombreOriginal = $archivo->getClientOriginalName();
            $extension      = $archivo->getClientOriginalExtension();
            $nombreUnico    = Str::uuid() . '.' . $extension;
            $ruta           = $archivo->storeAs('formatos', $nombreUnico, 'public');

            $datos['nombre_archivo']    = $nombreOriginal;
            $datos['ruta_archivo']      = $ruta;
            $datos['extension_archivo'] = strtoupper($extension);
            $datos['tamanio_archivo']   = $archivo->getSize();
        }

        $formato->update($datos);

        if ($claveRepetida) {
            return redirect()->route('formatos.index')
                ->with('warning', 'Formato actualizado, pero LA CLAVE DE FORMATO ESTÁ REPETIDA, MODIFÍCALA.')
                ->with('formato_id_editar', $formato->id);
        }

        return redirect()->route('formatos.index')
            ->with('success', 'Formato actualizado correctamente. Nombre: ' . ($datos['nombre_archivo'] ?? $formato->nombre_archivo));
    }

    /**
     * Elimina un formato y su archivo asociado. — SOLO SUPERADMIN/ADMIN
     */
    public function destroy(Formato $formato)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar archivos.');
        }

        Storage::disk('public')->delete($formato->ruta_archivo);
        $formato->delete();

        return redirect()->route('formatos.index')
            ->with('success', 'Formato eliminado correctamente.');
    }

    /**
     * Descarga el archivo de un formato. — TODOS los roles
     */
    public function download(Formato $formato)
    {
        $rutaCompleta = storage_path('app/public/' . $formato->ruta_archivo);

        if (!file_exists($rutaCompleta)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }

        return response()->download($rutaCompleta, $formato->nombre_archivo);
    }

    /**
     * Muestra/previsualiza el archivo de un formato. — TODOS los roles
     */
    public function show(Formato $formato)
    {
        $rutaCompleta = storage_path('app/public/' . $formato->ruta_archivo);

        if (!file_exists($rutaCompleta)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }

        $tipo = self::tipoArchivo($formato->extension_archivo);

        if ($tipo === 'imagen' || $tipo === 'pdf' || $tipo === 'txt') {
            $mimeType = mime_content_type($rutaCompleta);
            return response()->file($rutaCompleta, [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $formato->nombre_archivo . '"',
            ]);
        }

        return response()->download($rutaCompleta, $formato->nombre_archivo);
    }

    /**
     * Retorna los departamentos de un proceso (para AJAX).
     */
    public function departamentos(Request $request)
    {
        $proceso = $request->get('proceso');
        $mapa    = ProcesosDepartamento::mapa();
        $deps    = $mapa[$proceso] ?? [];
        return response()->json($deps);
    }

    /**
     * Clasifica la extensión del archivo en un tipo semántico.
     */
    public static function tipoArchivo(?string $extension): string
    {
        $ext = strtoupper((string) $extension);

        $imagenes = ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP', 'SVG', 'BMP', 'ICO', 'TIFF', 'TIF', 'AVIF'];
        $office   = ['XLS', 'XLSX', 'XLSM', 'XLSB', 'DOC', 'DOCX', 'DOCM', 'CSV', 'ODS', 'ODT', 'PPT', 'PPTX'];
        $txts     = ['TXT'];

        if (in_array($ext, $imagenes)) return 'imagen';
        if ($ext === 'PDF')            return 'pdf';
        if (in_array($ext, $office))   return 'office';
        if (in_array($ext, $txts))     return 'txt';

        return 'otro';
    }
}