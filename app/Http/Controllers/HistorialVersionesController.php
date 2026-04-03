<?php

namespace App\Http\Controllers;

use App\Models\HistorialVersiones;
use App\Models\User;
use App\Helpers\HistorialVersionesHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

class HistorialVersionesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if ($request->route()->getName() === 'historial-versiones.mis-actividades') {
                return $next($request);
            }

            if ($user->role !== 'superadmin') {
                if ($request->ajax()) {
                    return response()->json(['error' => 'No autorizado'], 403);
                }
                abort(403, 'Solo el superadministrador puede acceder al historial completo.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = HistorialVersiones::with('usuario');

        // --- EXCLUIR REGISTROS NO DESEADOS ---
        $query->whereNotIn('modulo', ['HISTORIAL', 'DASHBOARD']);
        $query->where('descripcion', 'not like', '%visualizó%');
        // --------------------------------------

        $accion = $request->get('accion');
        $usuario_id = $request->get('usuario_id');

        if ($accion && $accion !== 'todos') {
            $query->where('accion', $accion);
        }

        if ($usuario_id && $usuario_id !== 'todos') {
            $query->where('usuario_id', $usuario_id);
        }

        $actividades = $query->orderByDesc('created_at')->paginate(20);

        $estadisticas = [
            'total_hoy' => HistorialVersiones::whereDate('created_at', today())->count(),
            'total_semana' => HistorialVersiones::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_mes' => HistorialVersiones::whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)
                                            ->count(),
            'total_general' => HistorialVersiones::count(),
        ];

        $usuarios = User::orderBy('name')->get(['id', 'name']);

        $acciones = ['CREAR', 'EDITAR', 'ELIMINAR', 'SUBIR', 'MOVER', 'VER', 'DESCARGAR', 'RESTAURAR'];

        return view('historial_versiones.index', compact(
            'actividades',
            'estadisticas',
            'usuarios',
            'acciones',
            'accion',
            'usuario_id'
        ));
    }

    public function misActividades()
    {
        $user = auth()->user();

        $actividades = HistorialVersiones::where('usuario_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalHoy = HistorialVersiones::where('usuario_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        $totalSemana = HistorialVersiones::where('usuario_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $totalMes = HistorialVersiones::where('usuario_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('historial_versiones.mis-actividades', compact('actividades', 'totalHoy', 'totalSemana', 'totalMes'));
    }

    public function show($id)
    {
        $actividad = HistorialVersiones::findOrFail($id);

        if ($actividad->detalle_url) {
            return redirect($actividad->detalle_url);
        }

        $modulo = strtoupper($actividad->modulo);

        $rutasModulo = [
            'ANEXOS' => 'anexos.index',
            'DOCUMENTAL' => 'documental.index',
            'MATRIZ' => 'matriz.index',
            'MATRICES_DOCUMENTS' => 'matriz.index',
            'FORMATOS' => 'formatos.index',
            'INFORMES_AUDITORIA' => 'informes-auditoria.index',
            'AUDITORIAS' => 'auditoria.plan.index',
            'SOLICITUDES_MEJORA' => 'auditoria.solicitudes.index',
            'COMPETENCIAS' => 'auditoria.competencias.index',
            'USUARIOS' => 'admin.usuarios.index',
            'AVISOS' => 'avisos.index',
            'FOLDERS' => 'anexos.index',
            'DOCUMENTS' => 'anexos.index',
            'DOCUMENTAL_DOCUMENTS' => 'documental.index',
            'DOCUMENTALFOLDER' => 'documental.index',
        ];

        if (isset($rutasModulo[$modulo]) && Route::has($rutasModulo[$modulo])) {
            return redirect()->route($rutasModulo[$modulo]);
        }

        return redirect()->route('historial-versiones.index')
            ->with('info', 'No se pudo determinar el módulo de destino.');
    }

    /**
     * BORRAR TODO EL HISTORIAL
     */
    public function borrarTodo()
    {
        try {
            // Verificar permisos - solo superadmin
            if (auth()->user()->role !== 'superadmin') {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['error' => 'No autorizado'], 403);
                }
                return redirect()->back()->with('error', 'No autorizado para realizar esta acción.');
            }

            // Excluir los mismos registros que excluyes en el index
            $deleted = HistorialVersiones::whereNotIn('modulo', ['HISTORIAL', 'DASHBOARD'])
                ->where('descripcion', 'not like', '%visualizó%')
                ->delete();

            // Registrar la acción de borrado masivo en el historial
            $historial = new HistorialVersiones();
            $historial->usuario_id = auth()->id();
            $historial->modulo = 'HISTORIAL';
            $historial->accion = 'BORRAR_TODO';
            $historial->descripcion = "Eliminó $deleted registros del historial de versiones";
            $historial->usuario_nombre = auth()->user()->name;
            $historial->ip_address = request()->ip();
            $historial->user_agent = request()->userAgent();
            $historial->save();

            // Respuesta según el tipo de petición
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Se han eliminado $deleted registros del historial",
                    'deleted_count' => $deleted
                ]);
            }

            return redirect()->route('historial-versiones.index')
                ->with('success', "Se han eliminado $deleted registros del historial.");
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al borrar todo el historial: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => 'Error al borrar el historial: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al borrar el historial: ' . $e->getMessage());
        }
    }

    /**
     * RESTAURAR ELEMENTO ELIMINADO - CORREGIDO
     */
    public function restaurar($id)
    {
        $historial = HistorialVersiones::findOrFail($id);

        // Solo se pueden restaurar elementos eliminados
        if ($historial->accion !== 'ELIMINAR') {
            return redirect()->back()->with('error', 'Solo se pueden restaurar elementos eliminados.');
        }

        // ============================================
        // RESTAURAR FORMATOS (LISTA MAESTRA) - CORREGIDO
        // ============================================
        if ($historial->modulo === 'FORMATOS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                $registroId = $historial->registro_id;
                
                if (!$registroId && isset($datosAnteriores['id'])) {
                    $registroId = $datosAnteriores['id'];
                }
                
                if (!$registroId) {
                    return redirect()->back()->with('error', 'No se pudo identificar el ID del formato a restaurar.');
                }
                
                $formato = \App\Models\Formato::withTrashed()->find($registroId);
                
                if (!$formato) {
                    if (isset($datosAnteriores['nombre_archivo'])) {
                        $formato = \App\Models\Formato::withTrashed()
                            ->where('nombre_archivo', $datosAnteriores['nombre_archivo'])
                            ->first();
                    }
                }
                
                if (!$formato) {
                    return redirect()->back()->with('error', 'El formato no existe en la base de datos.');
                }
                
                if (!$formato->trashed()) {
                    return redirect()->back()->with('info', 'El formato no estaba eliminado.');
                }
                
                // Verificar si ya existe un formato activo con el mismo nombre
                $existing = \App\Models\Formato::where('nombre_archivo', $formato->nombre_archivo)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $formato->id)
                    ->first();
                
                $nombreModificado = false;
                    
                if ($existing) {
                    $pathinfo = pathinfo($formato->nombre_archivo);
                    $nombreSinExtension = $pathinfo['filename'];
                    $extension = $pathinfo['extension'] ?? '';
                    $nuevoNombre = $nombreSinExtension . '_restaurado_' . date('Ymd_His') . '.' . $extension;
                    $formato->nombre_archivo = $nuevoNombre;
                    $nombreModificado = true;
                }
                
                // Deshabilitar eventos temporalmente
                $oldBulkRestoring = \App\Models\Formato::isBulkRestoring();
                \App\Models\Formato::setBulkRestoring(true);
                
                try {
                    $formato->restore();
                    if ($nombreModificado) {
                        $formato->save();
                    }
                } finally {
                    \App\Models\Formato::setBulkRestoring($oldBulkRestoring);
                }
                
                if ($nombreModificado) {
                    $mensaje = 'Formato restaurado correctamente, pero el nombre fue modificado porque ya existía uno activo con el mismo nombre. Nuevo nombre: ' . $formato->nombre_archivo;
                } else {
                    $mensaje = 'Formato restaurado correctamente.';
                }
                
                if ($formato->ruta_archivo && !Storage::disk('public')->exists($formato->ruta_archivo)) {
                    return redirect()->route('formatos.index')
                        ->with('warning', 'Formato restaurado, pero el archivo físico no existe en el servidor.');
                }
                
                return redirect()->route('formatos.index')->with('success', $mensaje);
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar el formato: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR CARPETAS DE GESTIÓN DOCUMENTAL
        // ============================================
        if ($historial->modulo === 'DOCUMENTALFOLDER') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos de la carpeta para restaurar.');
                }
                
                $folder = \App\Models\DocumentalFolder::withTrashed()->find($datosAnteriores['id']);
                
                if (!$folder) {
                    return redirect()->back()->with('error', 'La carpeta no existe en la base de datos.');
                }
                
                if (!$folder->trashed()) {
                    return redirect()->back()->with('info', 'La carpeta no estaba eliminada.');
                }
                
                if ($folder->parent_id) {
                    $parentExists = \App\Models\DocumentalFolder::withTrashed()->find($folder->parent_id);
                    if (!$parentExists || $parentExists->trashed()) {
                        return redirect()->back()->with('error', 'La carpeta padre está eliminada. Restaura primero la carpeta padre.');
                    }
                }
                
                $existingFolder = \App\Models\DocumentalFolder::where('name', $folder->name)
                    ->where('parent_id', $folder->parent_id)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existingFolder) {
                    return redirect()->back()->with('error', 'Ya existe una carpeta activa con el mismo nombre en esta ubicación.');
                }
                
                $folder->restore();
                
                $redirectUrl = route('documental.index');
                if ($folder->parent_id) {
                    $redirectUrl = route('documental.index', ['folder' => $folder->parent_id]);
                }
                
                return redirect($redirectUrl)->with('success', 'Carpeta restaurada correctamente.');
                    
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al restaurar carpeta: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Error al restaurar la carpeta: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR DOCUMENTOS DE GESTIÓN DOCUMENTAL
        // ============================================
        if ($historial->modulo === 'DOCUMENTAL_DOCUMENTS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                $registroId = $historial->registro_id;
                
                if (!$registroId && isset($datosAnteriores['id'])) {
                    $registroId = $datosAnteriores['id'];
                }
                
                if (!$registroId) {
                    return redirect()->back()->with('error', 'No se pudo identificar el ID del documento a restaurar.');
                }
                
                $document = \App\Models\DocumentalDocument::withTrashed()->find($registroId);
                
                if (!$document && isset($datosAnteriores['name'])) {
                    $document = \App\Models\DocumentalDocument::withTrashed()
                        ->where('name', $datosAnteriores['name'])
                        ->first();
                }
                
                if (!$document) {
                    return redirect()->back()->with('error', 'El documento no existe en la base de datos.');
                }
                
                if (!$document->trashed()) {
                    return redirect()->back()->with('info', 'El documento no estaba eliminado.');
                }
                
                if ($document->folder_id) {
                    $folderExists = \App\Models\DocumentalFolder::withTrashed()->find($document->folder_id);
                    if (!$folderExists || $folderExists->trashed()) {
                        return redirect()->back()->with('error', 'La carpeta donde estaba el documento está eliminada.');
                    }
                }
                
                $existingDocument = \App\Models\DocumentalDocument::where('name', $document->name)
                    ->where('folder_id', $document->folder_id)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existingDocument) {
                    return redirect()->back()->with('error', 'Ya existe un documento activo con el mismo nombre.');
                }
                
                $archivoExiste = Storage::disk('public')->exists($document->file_path);
                $document->restore();
                
                $redirectUrl = route('documental.index');
                if ($document->folder_id) {
                    $redirectUrl = route('documental.index', ['folder' => $document->folder_id]);
                }
                
                if (!$archivoExiste) {
                    return redirect($redirectUrl)
                        ->with('warning', 'Documento restaurado, pero el archivo físico no existe en el servidor.');
                }
                
                return redirect($redirectUrl)
                    ->with('success', 'Documento restaurado correctamente.');
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar el documento: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR AUDITORÍAS
        // ============================================
        if ($historial->modulo === 'AUDITORIAS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos para restaurar la auditoría.');
                }
                
                $auditoria = \App\Models\Auditoria::withTrashed()->find($datosAnteriores['id']);
                
                if (!$auditoria) {
                    return redirect()->back()->with('error', 'La auditoría no existe en la base de datos.');
                }
                
                if (!$auditoria->trashed()) {
                    return redirect()->back()->with('info', 'La auditoría no estaba eliminada.');
                }
                
                $existingAuditoria = \App\Models\Auditoria::where('nombre_auditoria', $auditoria->nombre_auditoria)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existingAuditoria) {
                    return redirect()->back()->with('error', 'Ya existe una auditoría activa con el mismo nombre.');
                }
                
                $auditoria->restore();
                HistorialVersionesHelper::restaurar('AUDITORIAS', $auditoria);
                
                return redirect()->route('auditoria.plan.index')
                    ->with('success', 'Auditoría restaurada correctamente.');
                    
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar la auditoría: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR DOCUMENTOS DE ANEXOS
        // ============================================
        if ($historial->modulo === 'DOCUMENTS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos del documento para restaurar.');
                }
                
                $document = \App\Models\Document::withTrashed()->find($datosAnteriores['id']);
                
                if (!$document) {
                    return redirect()->back()->with('error', 'El documento no existe en la base de datos.');
                }
                
                if (!$document->trashed()) {
                    return redirect()->back()->with('info', 'El documento no estaba eliminado.');
                }
                
                if ($document->folder_id) {
                    $folderExists = \App\Models\Folder::withTrashed()->find($document->folder_id);
                    if (!$folderExists || $folderExists->trashed()) {
                        return redirect()->back()->with('error', 'La carpeta donde se eliminó el documento está eliminada.');
                    }
                }
                
                $existingDocument = \App\Models\Document::where('name', $document->name)
                    ->where('folder_id', $document->folder_id)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existingDocument) {
                    return redirect()->back()->with('error', 'Ya existe un documento activo con el mismo nombre.');
                }
                
                if (!Storage::disk('public')->exists($document->file_path)) {
                    return redirect()->back()->with('error', 'El archivo físico no existe en el servidor.');
                }
                
                $document->restore();
                HistorialVersionesHelper::restaurar('DOCUMENTS', $document);
                
                if ($document->folder_id) {
                    return redirect()->route('anexos.index', ['folder' => $document->folder_id])
                        ->with('success', 'Documento restaurado correctamente.');
                } else {
                    return redirect()->route('anexos.index')
                        ->with('success', 'Documento restaurado correctamente.');
                }
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar el documento: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR CARPETAS DE ANEXOS
        // ============================================
        if ($historial->modulo === 'FOLDERS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos de la carpeta para restaurar.');
                }
                
                $folder = \App\Models\Folder::withTrashed()->find($datosAnteriores['id']);
                
                if (!$folder) {
                    return redirect()->back()->with('error', 'La carpeta no existe en la base de datos.');
                }
                
                if (!$folder->trashed()) {
                    return redirect()->back()->with('info', 'La carpeta no estaba eliminada.');
                }
                
                if ($folder->parent_id) {
                    $parentExists = \App\Models\Folder::withTrashed()->find($folder->parent_id);
                    if (!$parentExists) {
                        return redirect()->back()->with('error', 'La carpeta padre ya no existe.');
                    }
                }
                
                $existingFolder = \App\Models\Folder::where('name', $folder->name)
                    ->where('parent_id', $folder->parent_id)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existingFolder) {
                    return redirect()->back()->with('error', 'Ya existe una carpeta activa con el mismo nombre.');
                }
                
                $this->restoreAllDocumentsInFolder($folder->id);
                $this->restoreAllSubfolders($folder->id);
                $folder->restore();
                HistorialVersionesHelper::restaurar('FOLDERS', $folder);
                
                return redirect()->route('anexos.index', ['folder' => $folder->parent_id])
                    ->with('success', 'Carpeta restaurada correctamente con todo su contenido.');
                    
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar la carpeta: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR SOLICITUDES DE MEJORA
        // ============================================
        if ($historial->modulo === 'SOLICITUDES_MEJORA') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos de la solicitud para restaurar.');
                }
                
                $solicitud = \App\Models\SolicitudMejora::withTrashed()->find($datosAnteriores['id']);
                
                if (!$solicitud) {
                    return redirect()->back()->with('error', 'La solicitud no existe en la base de datos.');
                }
                
                if (!$solicitud->trashed()) {
                    return redirect()->back()->with('info', 'La solicitud no estaba eliminada.');
                }
                
                if ($solicitud->folio_solicitud) {
                    $existing = \App\Models\SolicitudMejora::where('folio_solicitud', $solicitud->folio_solicitud)
                        ->whereNull('deleted_at')
                        ->first();
                        
                    if ($existing) {
                        return redirect()->back()->with('error', 'Ya existe una solicitud activa con el mismo folio.');
                    }
                }
                
                $solicitud->restore();
                HistorialVersionesHelper::restaurar('SOLICITUDES_MEJORA', $solicitud);
                
                return redirect('/auditoria/solicitudes')->with('success', 'Solicitud de mejora restaurada correctamente.');
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar la solicitud: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR INFORMES DE AUDITORÍA
        // ============================================
        if ($historial->modulo === 'INFORMES_AUDITORIA') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos del informe para restaurar.');
                }
                
                $informe = \App\Models\InformeAuditoria::withTrashed()->find($datosAnteriores['id']);
                
                if (!$informe) {
                    return redirect()->back()->with('error', 'El informe no existe en la base de datos.');
                }
                
                if (!$informe->trashed()) {
                    return redirect()->back()->with('info', 'El informe no estaba eliminada.');
                }
                
                $existing = \App\Models\InformeAuditoria::where('nombre_informe', $informe->nombre_informe)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existing) {
                    return redirect()->back()->with('error', 'Ya existe un informe activo con el mismo nombre.');
                }
                
                if ($informe->documento_path && !Storage::disk('public')->exists($informe->documento_path)) {
                    return redirect()->back()->with('error', 'El archivo físico no existe en el servidor.');
                }
                
                $informe->restore();
                HistorialVersionesHelper::restaurar('INFORMES_AUDITORIA', $informe);
                
                return redirect()->route('informes-auditoria.index')
                    ->with('success', 'Informe de auditoría restaurado correctamente.');
                    
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar el informe: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR COMPETENCIAS
        // ============================================
        if ($historial->modulo === 'COMPETENCIAS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos de la competencia para restaurar.');
                }
                
                $competencia = \App\Models\Competencia::withTrashed()->find($datosAnteriores['id']);
                
                if (!$competencia) {
                    return redirect()->back()->with('error', 'La competencia no existe en la base de datos.');
                }
                
                if (!$competencia->trashed()) {
                    return redirect()->back()->with('info', 'La competencia no estaba eliminada.');
                }
                
                if ($competencia->isFolder()) {
                    if ($competencia->parent_id) {
                        $parentExists = \App\Models\Competencia::withTrashed()->find($competencia->parent_id);
                        if (!$parentExists) {
                            return redirect()->back()->with('error', 'La carpeta padre ya no existe.');
                        }
                    }
                    
                    $existing = \App\Models\Competencia::where('nombre', $competencia->nombre)
                        ->where('tipo', 'carpeta')
                        ->where('parent_id', $competencia->parent_id)
                        ->whereNull('deleted_at')
                        ->first();
                        
                    if ($existing) {
                        return redirect()->back()->with('error', 'Ya existe una carpeta activa con el mismo nombre.');
                    }
                    
                    $this->restoreAllDocumentsInCompetenciaFolder($competencia->id);
                    $this->restoreAllSubfoldersCompetencia($competencia->id);
                    $competencia->restore();
                    HistorialVersionesHelper::restaurar('COMPETENCIAS', $competencia);
                    
                    return redirect()->route('auditoria.competencias.index', ['folder' => $competencia->parent_id])
                        ->with('success', 'Carpeta restaurada correctamente con todo su contenido.');
                    
                } else {
                    if ($competencia->parent_id) {
                        $parentExists = \App\Models\Competencia::withTrashed()->find($competencia->parent_id);
                        if (!$parentExists) {
                            return redirect()->back()->with('error', 'La carpeta padre ya no existe.');
                        }
                    }
                    
                    $existing = \App\Models\Competencia::where('nombre', $competencia->nombre)
                        ->where('tipo', 'documento')
                        ->where('parent_id', $competencia->parent_id)
                        ->whereNull('deleted_at')
                        ->first();
                        
                    if ($existing) {
                        return redirect()->back()->with('error', 'Ya existe un documento activo con el mismo nombre.');
                    }
                    
                    if (!Storage::disk('public')->exists($competencia->archivo_ruta)) {
                        return redirect()->back()->with('error', 'El archivo físico no existe en el servidor.');
                    }
                    
                    $competencia->restore();
                    HistorialVersionesHelper::restaurar('COMPETENCIAS', $competencia);
                    
                    return redirect()->route('auditoria.competencias.index', ['folder' => $competencia->parent_id])
                        ->with('success', 'Documento restaurado correctamente.');
                }
                    
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar la competencia: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR USUARIOS
        // ============================================
        if ($historial->modulo === 'USUARIOS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos del usuario para restaurar.');
                }
                
                $usuario = \App\Models\User::withTrashed()->find($datosAnteriores['id']);
                
                if (!$usuario) {
                    return redirect()->back()->with('error', 'El usuario no existe en la base de datos.');
                }
                
                if (!$usuario->trashed()) {
                    return redirect()->back()->with('info', 'El usuario no estaba eliminado.');
                }
                
                $usuario->restore();
                HistorialVersionesHelper::restaurar('USUARIOS', $usuario);
                
                return redirect()->route('admin.usuarios.index')
                    ->with('success', 'Usuario restaurado correctamente.');
                    
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar el usuario: ' . $e->getMessage());
            }
        }

        // ============================================
        // RESTAURAR AVISOS
        // ============================================
        if ($historial->modulo === 'AVISOS') {
            try {
                $datosAnteriores = $historial->datos_anteriores;
                
                if (is_string($datosAnteriores)) {
                    $datosAnteriores = json_decode($datosAnteriores, true);
                }
                
                if (!$datosAnteriores || !isset($datosAnteriores['id'])) {
                    return redirect()->back()->with('error', 'No hay datos válidos del aviso para restaurar.');
                }
                
                $aviso = \App\Models\Aviso::withTrashed()->find($datosAnteriores['id']);
                
                if (!$aviso) {
                    return redirect()->back()->with('error', 'El aviso no existe en la base de datos.');
                }
                
                if (!$aviso->trashed()) {
                    return redirect()->back()->with('info', 'El aviso no estaba eliminado.');
                }
                
                $existing = \App\Models\Aviso::where('titulo', $aviso->titulo)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existing) {
                    return redirect()->back()->with('error', 'Ya existe un aviso activo con el mismo título.');
                }
                
                $archivoExiste = true;
                if ($aviso->archivo_path && !Storage::disk('public')->exists($aviso->archivo_path)) {
                    $archivoExiste = false;
                }
                
                $aviso->restore();
                HistorialVersionesHelper::restaurar('AVISOS', $aviso);
                
                if ($archivoExiste) {
                    return redirect('/avisos')->with('success', 'Aviso restaurado correctamente.');
                } else {
                    return redirect('/avisos')->with('warning', 'Aviso restaurado, pero el archivo físico no existe en el servidor.');
                }
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al restaurar el aviso: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No se puede restaurar este tipo de elemento. Módulo: ' . $historial->modulo);
    }

    /**
     * Restaurar todos los documentos dentro de una carpeta de ANEXOS
     */
    private function restoreAllDocumentsInFolder($folderId)
    {
        $documents = \App\Models\Document::withTrashed()
            ->where('folder_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                $document->restore();
            }
        }
    }
    
    /**
     * Restaurar todas las subcarpetas de ANEXOS
     */
    private function restoreAllSubfolders($folderId)
    {
        $subfolders = \App\Models\Folder::withTrashed()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            $this->restoreAllDocumentsInFolder($subfolder->id);
            $this->restoreAllSubfolders($subfolder->id);
            $subfolder->restore();
        }
    }

    /**
     * Restaurar todos los documentos dentro de una carpeta de COMPETENCIAS
     */
    private function restoreAllDocumentsInCompetenciaFolder($folderId)
    {
        $documents = \App\Models\Competencia::withTrashed()
            ->documents()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($documents as $document) {
            if (Storage::disk('public')->exists($document->archivo_ruta)) {
                $document->restore();
            }
        }
        
        $subfolders = \App\Models\Competencia::withTrashed()
            ->folders()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            $this->restoreAllDocumentsInCompetenciaFolder($subfolder->id);
        }
    }
    
    /**
     * Restaurar todas las subcarpetas de COMPETENCIAS
     */
    private function restoreAllSubfoldersCompetencia($folderId)
    {
        $subfolders = \App\Models\Competencia::withTrashed()
            ->folders()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            $this->restoreAllDocumentsInCompetenciaFolder($subfolder->id);
            $this->restoreAllSubfoldersCompetencia($subfolder->id);
            $subfolder->restore();
        }
    }

    public function datosGraficos(Request $request)
    {
        $dias = $request->get('dias', 30);
        $data = [];
        $labels = [];

        for ($i = $dias; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            $data[] = HistorialVersiones::whereDate('created_at', $fecha)->count();
        }

        return response()->json(['labels' => $labels, 'data' => $data]);
    }

    public function exportar(Request $request)
    {
        return redirect()->back()->with('info', 'Función de exportación en desarrollo');
    }

    public function limpiar(Request $request)
    {
        if (auth()->user()->role !== 'superadmin') {
            return redirect()->back()->with('error', 'No autorizado');
        }

        $dias = $request->get('dias', 90);
        $fechaLimite = now()->subDays($dias);

        $eliminados = HistorialVersiones::where('created_at', '<', $fechaLimite)->delete();

        return redirect()->back()->with('success', "Se eliminaron {$eliminados} registros antiguos");
    }
}