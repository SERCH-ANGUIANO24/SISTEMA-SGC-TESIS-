<?php

namespace App\Http\Controllers;

use App\Models\Formato;
use App\Models\ProcesosDepartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Helpers\HistorialVersionesHelper;

/*
|--------------------------------------------------------------------------
| CONTROLADOR: FORMATOS (LISTA MAESTRA DE DOCUMENTOS)
|--------------------------------------------------------------------------
| SE ENCARGA DE GESTIONAR LOS FORMATOS Y PROCEDIMIENTOS DEL SISTEMA:
| MOSTRARLOS, SUBIRLOS, EDITARLOS, ELIMINARLOS Y DESCARGARLOS.
*/

class FormatoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: index
    |--------------------------------------------------------------------------
    | MUESTRA LA LISTA DE TODOS LOS FORMATOS CON FILTROS DE BÚSQUEDA.
    | SE PUEDE FILTRAR POR NOMBRE, VERSIÓN, CÓDIGO, CLAVE, PROCESO,
    | DEPARTAMENTO Y TIPO DE DOCUMENTO.
    | TAMBIÉN CARGA LOS VALORES ÚNICOS PARA LOS SELECTORES DE FILTROS.
    */
    public function index(Request $request)
    {
        $query = Formato::query();

        if ($request->filled('nombre')) {
            $query->where('nombre_archivo', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('version')) {
            $query->where('version_procedimiento', $request->version);
        }

        if ($request->filled('codigo')) {
            $query->where('codigo_procedimiento', $request->codigo);
        }

        if ($request->filled('clave')) {
            $query->where('clave_formato', $request->clave);
        }

        if ($request->filled('proceso')) {
            $query->where('proceso', $request->proceso);
        }

        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }
        
        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        $formatos = $query->orderBy('created_at', 'desc')->get();

        $procesosYDepartamentos = ProcesosDepartamento::mapa();

        $procesosDinamicos = ProcesosDepartamento::select('proceso')
            ->distinct()
            ->pluck('proceso')
            ->toArray();

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

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: store
    |--------------------------------------------------------------------------
    | SUBE UN NUEVO FORMATO AL SISTEMA.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | GUARDA EL ARCHIVO EN EL SERVIDOR Y REGISTRA SUS DATOS EN LA BASE DE DATOS.
    | SI LA CLAVE DE FORMATO ESTÁ REPETIDA → AVISA CON UN MENSAJE DE ADVERTENCIA.
    | REGISTRA LA SUBIDA EN EL HISTORIAL DE VERSIONES.
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
            'tipo_documento'        => 'Formato',
        ]);

        HistorialVersionesHelper::subir('FORMATOS', $formato);

        if ($claveRepetida) {
            return redirect()->route('formatos.index')
                ->with('warning', 'Archivo subido correctamente, pero LA CLAVE DE FORMATO ESTÁ REPETIDA, MODIFÍCALA.')
                ->with('formato_id_editar', $formato->id);
        }

        return redirect()->route('formatos.index')
            ->with('success', 'Formato subido correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: update
    |--------------------------------------------------------------------------
    | ACTUALIZA LOS DATOS DE UN FORMATO EXISTENTE.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | SI SE SUBE UN ARCHIVO NUEVO → ELIMINA EL ANTERIOR Y GUARDA EL NUEVO.
    | SI SE CAMBIA SOLO EL NOMBRE → ACTUALIZA EL NOMBRE SIN TOCAR EL ARCHIVO.
    | SI LA CLAVE DE FORMATO ESTÁ REPETIDA → AVISA CON UN MENSAJE DE ADVERTENCIA.
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
            if (Storage::disk('public')->exists($formato->ruta_archivo)) {
                Storage::disk('public')->delete($formato->ruta_archivo);
            }

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
            ->with('success', 'Formato actualizado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: destroy
    |--------------------------------------------------------------------------
    | ELIMINA UN FORMATO DEL SISTEMA.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | SI LA PETICIÓN ES AJAX → DEVUELVE JSON.
    | SI NO ES AJAX          → REDIRIGE CON MENSAJE DE ÉXITO O ERROR.
    */
    public function destroy(Formato $formato)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar archivos.'
                ], 403);
            }
            abort(403, 'No tienes permiso para eliminar archivos.');
        }

        try {
            $nombreArchivo = $formato->nombre_archivo;
            $formato->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Formato eliminado correctamente.',
                    'nombre' => $nombreArchivo
                ]);
            }

            return redirect()->route('formatos.index')
                ->with('success', 'Formato eliminado correctamente.');
                
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el formato: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('formatos.index')
                ->with('error', 'Error al eliminar el formato: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: download
    |--------------------------------------------------------------------------
    | DESCARGA EL ARCHIVO DEL FORMATO AL DISPOSITIVO DEL USUARIO.
    | SI EL ARCHIVO NO EXISTE EN EL SERVIDOR → DEVUELVE ERROR.
    | REGISTRA LA DESCARGA EN EL HISTORIAL DE VERSIONES.
    */
    public function download(Formato $formato)
    {
        $rutaCompleta = storage_path('app/public/' . $formato->ruta_archivo);

        if (!file_exists($rutaCompleta)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }

        HistorialVersionesHelper::descargar('FORMATOS', $formato);

        return response()->download($rutaCompleta, $formato->nombre_archivo);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: show
    |--------------------------------------------------------------------------
    | MUESTRA EL ARCHIVO DEL FORMATO EN EL NAVEGADOR (SIN DESCARGARLO).
    | SI EL ARCHIVO NO EXISTE → DEVUELVE ERROR.
    | SI ES IMAGEN, PDF O TXT → LO MUESTRA DIRECTAMENTE EN EL NAVEGADOR.
    | SI ES OTRO TIPO (OFFICE, ETC.) → LO DESCARGA AUTOMÁTICAMENTE.
    | REGISTRA LA VISUALIZACIÓN EN EL HISTORIAL DE VERSIONES.
    */
    public function show(Formato $formato)
    {
        $rutaCompleta = storage_path('app/public/' . $formato->ruta_archivo);

        if (!file_exists($rutaCompleta)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }

        HistorialVersionesHelper::ver('FORMATOS', $formato, 'ver_archivo');

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

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: departamentos
    |--------------------------------------------------------------------------
    | DEVUELVE EN JSON LOS DEPARTAMENTOS QUE PERTENECEN A UN PROCESO.
    | USADA PARA CARGAR DINÁMICAMENTE EL SELECTOR DE DEPARTAMENTOS
    | CUANDO EL USUARIO ELIGE UN PROCESO EN EL FORMULARIO.
    */
    public function departamentos(Request $request)
    {
        $proceso = $request->get('proceso');
        $mapa    = ProcesosDepartamento::mapa();
        $deps    = $mapa[$proceso] ?? [];
        return response()->json($deps);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: tipoArchivo
    |--------------------------------------------------------------------------
    | DETERMINA EL TIPO DE ARCHIVO SEGÚN SU EXTENSIÓN.
    | DEVUELVE UNO DE ESTOS VALORES:
    |   · "imagen"  → JPG, PNG, GIF, SVG, ETC.
    |   · "pdf"     → PDF
    |   · "office"  → EXCEL, WORD, POWERPOINT, ETC.
    |   · "txt"     → TXT
    |   · "otro"    → CUALQUIER OTRA EXTENSIÓN
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