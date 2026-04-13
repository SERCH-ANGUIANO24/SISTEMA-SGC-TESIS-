<?php

namespace App\Http\Controllers;

use App\Models\DocumentalFolder;
use App\Models\DocumentalDocument;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Helpers\HistorialVersionesHelper;

/*
|--------------------------------------------------------------------------
| CONTROLADOR: DOCUMENTAL (GESTIÓN DOCUMENTAL)
|--------------------------------------------------------------------------
| SE ENCARGA DE GESTIONAR CARPETAS Y DOCUMENTOS DEL SISTEMA:
| SUBIR, VER, EDITAR, MOVER, ELIMINAR Y RESTAURAR ARCHIVOS.
*/

class DocumentalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: index
    |--------------------------------------------------------------------------
    | MUESTRA LA PANTALLA PRINCIPAL DE GESTIÓN DOCUMENTAL.
    | SI SE PASA UN FOLDER ID, MUESTRA EL CONTENIDO DE ESA CARPETA.
    | SI NO, MUESTRA LAS CARPETAS Y DOCUMENTOS DE LA RAÍZ.
    | TAMBIÉN CARGA LOS FILTROS DE VERSIÓN, CÓDIGO Y CLAVE.
    */
    public function index(Request $request)
    {
        $folderId      = $request->get('folder');
        $currentFolder = null;
        $folders       = collect();
        $documents     = collect();
        $breadcrumbs   = [];
        $userRole      = Auth::user()->role;
        $userId        = Auth::id();

        if ($folderId) {
            $currentFolder = DocumentalFolder::with('parent')->find($folderId);

            if ($currentFolder) {
                // Construir breadcrumbs con protección contra ciclos
                $breadcrumbs = $this->buildBreadcrumbsSafe($currentFolder);

                // SOLO carpetas NO eliminadas
                $folders = DocumentalFolder::where('parent_id', $folderId)
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->get();

                // SOLO documentos NO eliminados
                $documentsQuery = DocumentalDocument::where('folder_id', $folderId)
                    ->whereNull('deleted_at');

                if ($request->filled('version')) {
                    $documentsQuery->where('version_procedimiento', $request->get('version'));
                }
                if ($request->filled('codigo')) {
                    $documentsQuery->where('codigo_procedimiento', $request->get('codigo'));
                }
                if ($request->filled('clave')) {
                    $documentsQuery->where('clave_formato', $request->get('clave'));
                }

                $documents = $documentsQuery->orderBy('created_at', 'desc')->get();
            } else {
                // La carpeta no existe, redirigir al índice
                return redirect()->route('documental.index')->with('error', 'La carpeta solicitada no existe.');
            }
        } else {
            // SOLO carpetas raíz NO eliminadas
            $folders = DocumentalFolder::whereNull('parent_id')
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get();

            // SOLO documentos raíz NO eliminados
            $documentsQuery = DocumentalDocument::whereNull('folder_id')
                ->whereNull('deleted_at');

            if (!in_array($userRole, ['superadmin', 'admin'])) {
                $documentsQuery->where('user_id', $userId);
            }

            if ($request->filled('version')) {
                $documentsQuery->where('version_procedimiento', $request->get('version'));
            }
            if ($request->filled('codigo')) {
                $documentsQuery->where('codigo_procedimiento', $request->get('codigo'));
            }
            if ($request->filled('clave')) {
                $documentsQuery->where('clave_formato', $request->get('clave'));
            }

            $documents = $documentsQuery->orderBy('created_at', 'desc')->get();
        }

        $baseQuery    = $folderId
            ? DocumentalDocument::where('folder_id', $folderId)->whereNull('deleted_at')
            : DocumentalDocument::whereNull('folder_id')->whereNull('deleted_at');

        $adminUserIds = \App\Models\User::whereIn('role', ['superadmin', 'admin'])->pluck('id');

        $versionesUnicas = (clone $baseQuery)
            ->whereIn('user_id', $adminUserIds)
            ->whereNotNull('version_procedimiento')
            ->distinct()->pluck('version_procedimiento')->sort()->values();

        $codigosUnicos = (clone $baseQuery)
            ->whereIn('user_id', $adminUserIds)
            ->whereNotNull('codigo_procedimiento')
            ->distinct()->pluck('codigo_procedimiento')->sort()->values();

        $clavesUnicas = (clone $baseQuery)
            ->whereIn('user_id', $adminUserIds)
            ->whereNotNull('clave_formato')
            ->distinct()->pluck('clave_formato')->sort()->values();

        $procesosEstandar = [
            'Planeación'                          => ['Rectoría', 'Dirección Académica', 'Dirección de Administración y Finanzas'],
            'Preinscripción'                      => ['Servicios Escolares'],
            'Inscripción'                         => ['Servicios Escolares'],
            'Reinscripción'                       => ['Servicios Escolares'],
            'Titulación'                          => ['Servicios Escolares'],
            'Enseñanza/Aprendizaje'               => ['Dirección Académica'],
            'Contratación o Control de Personal'  => ['Recursos Humanos'],
            'Vinculación'                         => ['Vinculación'],
            'TI'                                  => ['Sistemas Computacionales'],
            'Gestión de Recursos'                 => ['Recursos Financieros', 'Almacén'],
            'Laboratorios y Talleres'             => ['Encargado/a de Laboratorios'],
            'Centro de Información'               => ['Biblioteca'],
            'Sistema de Gestión de la Calidad'   => ['Rectoría', 'Auditoría', 'Coordinador del SGC'],
        ];

        $usuariosProcesos = \App\Models\User::whereNotNull('proceso')
            ->whereNotNull('departamento')
            ->select('proceso', 'departamento')
            ->distinct()
            ->get();

        $procesosCustomData = collect();
        try {
            $procesosCustomData = \App\Models\ProcesoCustom::select('proceso', 'departamento')->get();
        } catch (\Exception $e) {
            // Si la tabla/modelo no existe, ignorar
        }

        $procesosDepartamentos = $procesosEstandar;

        foreach ($usuariosProcesos as $up) {
            $p = trim($up->proceso);
            $d = trim($up->departamento);
            if (!$p || !$d) continue;
            if (!isset($procesosDepartamentos[$p])) {
                $procesosDepartamentos[$p] = [];
            }
            if (!in_array($d, $procesosDepartamentos[$p])) {
                $procesosDepartamentos[$p][] = $d;
            }
        }

        foreach ($procesosCustomData as $pc) {
            $p = trim($pc->proceso);
            $d = trim($pc->departamento);
            if (!$p || !$d) continue;
            if (!isset($procesosDepartamentos[$p])) {
                $procesosDepartamentos[$p] = [];
            }
            if (!in_array($d, $procesosDepartamentos[$p])) {
                $procesosDepartamentos[$p][] = $d;
            }
        }

        ksort($procesosDepartamentos);

        return view('documental.index', compact(
            'folders',
            'documents',
            'currentFolder',
            'breadcrumbs',
            'userRole',
            'versionesUnicas',
            'codigosUnicos',
            'clavesUnicas',
            'procesosDepartamentos'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: buildBreadcrumbsSafe
    |--------------------------------------------------------------------------
    | CONSTRUYE EL RASTRO DE NAVEGACIÓN (MIGAS DE PAN) DE FORMA SEGURA.
    | DETECTA Y PREVIENE CICLOS INFINITOS ENTRE CARPETAS.
    | TIENE UN LÍMITE MÁXIMO DE 50 NIVELES DE PROFUNDIDAD.
    */
    private function buildBreadcrumbsSafe($folder)
    {
        $breadcrumbs = [];
        $current = $folder;
        $visitedIds = []; // Prevenir ciclos
        
        $maxDepth = 50; // Límite de seguridad
        $depth = 0;
        
        while ($current && $depth < $maxDepth) {
            // Detectar ciclos
            if (in_array($current->id, $visitedIds)) {
                Log::warning('Ciclo detectado en breadcrumbs para carpeta ID: ' . $current->id);
                break;
            }
            
            $visitedIds[] = $current->id;
            
            array_unshift($breadcrumbs, [
                'id' => $current->id,
                'name' => $current->name
            ]);
            
            // Intentar obtener el padre
            try {
                if ($current->parent_id) {
                    $current = $current->parent;
                    if (!$current) {
                        break;
                    }
                } else {
                    $current = null;
                }
            } catch (\Exception $e) {
                Log::error('Error al obtener padre de carpeta: ' . $e->getMessage());
                break;
            }
            
            $depth++;
        }
        
        if ($depth >= $maxDepth) {
            Log::warning('Profundidad máxima excedida en breadcrumbs para carpeta ID: ' . ($folder->id ?? 'unknown'));
        }
        
        return $breadcrumbs;
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: buildBreadcrumbs
    |--------------------------------------------------------------------------
    | VERSIÓN ANTERIOR (DEPRECADA) - SE MANTIENE POR COMPATIBILIDAD.
    | INTERNAMENTE LLAMA A buildBreadcrumbsSafe.
    */
    private function buildBreadcrumbs($folder)
    {
        return $this->buildBreadcrumbsSafe($folder);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: storeFolder
    |--------------------------------------------------------------------------
    | CREA UNA NUEVA CARPETA EN EL SISTEMA.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | VERIFICA QUE LA CARPETA PADRE NO ESTÉ ELIMINADA.
    */
    public function storeFolder(Request $request)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para crear carpetas.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string',
            'parent_id' => 'nullable|exists:documental_folders,id'
        ]);

        // Verificar que la carpeta padre no esté eliminada
        if ($request->parent_id) {
            $parentExists = DocumentalFolder::where('id', $request->parent_id)
                ->whereNull('deleted_at')
                ->exists();
            if (!$parentExists) {
                return redirect()->back()->with('error', 'La carpeta padre no existe o está eliminada.');
            }
        }

        $folder = DocumentalFolder::create([
            'name' => $request->name,
            'color' => $request->color ?? '#800000',
            'parent_id' => $request->parent_id,
            'user_id' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Carpeta creada exitosamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: renameFolder
    |--------------------------------------------------------------------------
    | CAMBIA EL NOMBRE DE UNA CARPETA EXISTENTE.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | SI OCURRE UN ERROR, LO REGISTRA Y DEVUELVE MENSAJE DE FALLO.
    */
    public function renameFolder(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para renombrar carpetas.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $folder = DocumentalFolder::whereNull('deleted_at')->findOrFail($id);
            $folder->name = $request->name;
            $folder->save();

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: moveFolder
    |--------------------------------------------------------------------------
    | MUEVE UNA CARPETA A OTRA UBICACIÓN DENTRO DEL SISTEMA.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | VERIFICA QUE NO SE CREEN CICLOS (UNA CARPETA DENTRO DE SÍ MISMA).
    | REGISTRA EL MOVIMIENTO EN EL HISTORIAL DE VERSIONES.
    */
    public function moveFolder(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para mover carpetas.');
        }

        try {
            $request->validate([
                'destination_id' => 'nullable|exists:documental_folders,id'
            ]);

            $folder = DocumentalFolder::whereNull('deleted_at')->findOrFail($id);
            $origen = $folder->parent_id ? DocumentalFolder::whereNull('deleted_at')->find($folder->parent_id) : null;

            if ($request->destination_id == $id) {
                return redirect()->back()->with('error', 'No puedes mover una carpeta a sí misma.');
            }

            if ($request->destination_id) {
                $destinationFolder = DocumentalFolder::whereNull('deleted_at')->find($request->destination_id);
                if (!$destinationFolder) {
                    return redirect()->back()->with('error', 'La carpeta destino no es válida.');
                }

                if ($this->wouldCreateCycle($folder, $request->destination_id)) {
                    return redirect()->back()->with('error', 'No puedes mover una carpeta a una subcarpeta de sí misma.');
                }
            }

            $destino = $request->destination_id ? DocumentalFolder::whereNull('deleted_at')->find($request->destination_id) : null;
            
            $folder->parent_id = $request->destination_id;
            $folder->save();

            HistorialVersionesHelper::mover('DOCUMENTALFOLDER', $folder, $origen, $destino);

            return redirect()->back()->with('success', 'Carpeta movida exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al mover carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover la carpeta.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: destroyFolder
    |--------------------------------------------------------------------------
    | ELIMINA UNA CARPETA (SOFT DELETE) Y TODO SU CONTENIDO.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | EL TRAIT REGISTRA LA ELIMINACIÓN AUTOMÁTICAMENTE EN EL HISTORIAL.
    */
    public function destroyFolder($id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar carpetas.');
        }

        try {
            $folder = DocumentalFolder::whereNull('deleted_at')->findOrFail($id);
            
            // ✅ Solo soft delete - El Trait registra automáticamente
            $folder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Carpeta y todo su contenido eliminados exitosamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar carpeta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la carpeta.'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: limpiarCache
    |--------------------------------------------------------------------------
    | LIMPIA LA CACHÉ DE GESTIÓN DOCUMENTAL DEL USUARIO ACTUAL.
    | SI SE PASA UN FOLDER ID, TAMBIÉN LIMPIA LA CACHÉ DE ESA CARPETA.
    | DEVUELVE JSON DE ÉXITO.
    */
    public function limpiarCache(Request $request)
    {
        $folderId = $request->get('folder');
        
        // Limpiar caché específica
        Cache::forget('documental_folders_' . auth()->id());
        Cache::forget('documental_documents_' . auth()->id());
        
        if ($folderId) {
            Cache::forget('documental_folder_contents_' . $folderId);
        }
        
        // Limpiar sesión
        session()->forget('documental_folders_cache');
        
        return response()->json(['success' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: upload
    |--------------------------------------------------------------------------
    | SUBE UN NUEVO ARCHIVO AL SISTEMA DE GESTIÓN DOCUMENTAL.
    | ADMINS: EL DOCUMENTO SE MARCA COMO "VÁLIDO" Y SE ENVÍA A LISTA MAESTRA.
    |         ADEMÁS SE NOTIFICA A TODOS LOS USUARIOS DEL SISTEMA.
    | USUARIOS NORMALES: EL DOCUMENTO SE MARCA COMO "PENDIENTE"
    |                    Y SE NOTIFICA A LOS ADMINISTRADORES PARA REVISIÓN.
    */
    public function upload(Request $request)
    {
        $isAdmin = in_array(Auth::user()->role, ['superadmin', 'admin']);

        $request->validate([
            'file'           => 'required|file|max:102400',
            'folder_id'      => 'nullable|exists:documental_folders,id',
            'tipo_documento' => 'required|in:Formato,Procedimiento',
        ]);

        // Verificar que la carpeta destino no esté eliminada
        if ($request->folder_id) {
            $folderExists = DocumentalFolder::where('id', $request->folder_id)
                ->whereNull('deleted_at')
                ->exists();
            if (!$folderExists) {
                return redirect()->back()->with('error', 'La carpeta destino no existe o está eliminada.');
            }
        }

        if ($isAdmin) {
            $request->validate([
                'clave_formato'         => 'nullable|string|max:255',
                'codigo_procedimiento'  => 'nullable|string|max:255',
                'version_procedimiento' => 'nullable|string|max:255',
            ]);
        }

        $file           = $request->file('file');
        $originalName   = $file->getClientOriginalName();
        $extension      = $file->getClientOriginalExtension();
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $fileName       = time() . '_' . uniqid() . '.' . $extension;
        $path           = $file->storeAs('documental/' . Auth::id(), $fileName, 'public');

        $proceso      = Auth::user()->proceso;
        $departamento = Auth::user()->departamento;

        $document = DocumentalDocument::create([
            'name'                  => $nameWithoutExt,
            'original_name'         => $originalName,
            'file_path'             => $path,
            'mime_type'             => $file->getMimeType(),
            'size'                  => $file->getSize(),
            'extension'             => $extension,
            'folder_id'             => $request->folder_id,
            'user_id'               => Auth::id(),
            'responsable'           => Auth::user()->name,
            'proceso'               => $proceso,
            'departamento'          => $departamento,
            'estatus'               => $isAdmin ? 'Valido' : 'Pendiente',
            'fecha'                 => now(),
            'tipo_documento'        => $request->tipo_documento,
            'clave_formato'         => $isAdmin && $request->filled('clave_formato')         ? $request->clave_formato         : null,
            'codigo_procedimiento'  => $isAdmin && $request->filled('codigo_procedimiento')  ? $request->codigo_procedimiento  : null,
            'version_procedimiento' => $isAdmin && $request->filled('version_procedimiento') ? $request->version_procedimiento : null,
        ]);

        HistorialVersionesHelper::subir('DOCUMENTAL_DOCUMENTS', $document);

        // ── URL destino (con carpeta o raíz) ──────────────────────────
        $urlDestino = $request->folder_id
            ? route('documental.index', ['folder' => $request->folder_id])
            : route('documental.index');

        // ── ADMIN/SUPERADMIN: Lista Maestra + notificar a todos ───────
        if ($isAdmin) {
            $formato = \App\Models\Formato::create([
                'proceso'               => $proceso,
                'departamento'          => $departamento,
                'clave_formato'         => $request->filled('clave_formato')         ? $request->clave_formato         : null,
                'codigo_procedimiento'  => $request->filled('codigo_procedimiento')  ? $request->codigo_procedimiento  : null,
                'version_procedimiento' => $request->filled('version_procedimiento') ? $request->version_procedimiento : null,
                'nombre_archivo'        => $originalName,
                'ruta_archivo'          => $path,
                'extension_archivo'     => strtoupper($extension),
                'tamanio_archivo'       => $file->getSize(),
                'tipo_documento'        => $request->tipo_documento,
            ]);

            HistorialVersionesHelper::subir('FORMATOS', $formato, [], true);

            // Notificar a TODOS de que hay un nuevo documento disponible
            $notif = app(NotificacionService::class);
            $notif->enviarATodos(
                titulo:     'Nuevo documento disponible: ' . $nameWithoutExt,
                mensaje:    Auth::user()->name . ' ha subido el archivo "' . $nameWithoutExt . '" ' .
                            'y está disponible en Gestión Documental.',
                tipo:       'info',
                icono:      'bi-file-earmark-arrow-up',
                url:        $urlDestino,
                email:      true,
                docId:      null,
                tipoEvento: 'publicado'
            );

            return redirect()->back()->with('success', 'Archivo subido y enviado al módulo de Lista Maestra exitosamente.');
        }

        // ── USUARIO NORMAL: notificar a admins ────────────────────────
        if (!$isAdmin) {
            $notif  = app(NotificacionService::class);
            $admins = \App\Models\User::whereIn('role', ['superadmin', 'admin'])->get();
            foreach ($admins as $admin) {
                $notif->enviar(
                    userId:     $admin->id,
                    titulo:     'Documento pendiente de validación',
                    mensaje:    Auth::user()->name . ' subió el archivo "' . $nameWithoutExt . '".' . PHP_EOL .
                                'Es necesario revisarlo en Gestión Documental.',
                    tipo:       'info',
                    icono:      'bi-file-earmark-arrow-up',
                    url:        $urlDestino,
                    email:      true,
                    docId:      null,
                    tipoEvento: 'subida'
                );
            }
        }

        return redirect()->back()->with('success', 'Archivo subido exitosamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: getDocumentData
    |--------------------------------------------------------------------------
    | DEVUELVE EN JSON LOS DATOS DE UN DOCUMENTO PARA EDITARLO.
    | SI EL USUARIO NO ES ADMIN, SOLO PUEDE VER SUS PROPIOS DOCUMENTOS.
    | TAMBIÉN INDICA SI EL DOCUMENTO FUE SUBIDO POR UN ADMINISTRADOR.
    */
    public function getDocumentData($id)
    {
        $query = DocumentalDocument::whereNull('deleted_at');
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            $query->where('user_id', Auth::id());
        }
        $document = $query->with('user')->findOrFail($id);

        $uploaderRole    = $document->user->role ?? null;
        $uploadedByAdmin = in_array($uploaderRole, ['superadmin', 'admin']);

        return response()->json([
            'name'                  => $document->name,
            'responsable'           => $document->responsable,
            'proceso'               => $document->proceso,
            'departamento'          => $document->departamento,
            'clave_formato'         => $document->clave_formato,
            'codigo_procedimiento'  => $document->codigo_procedimiento,
            'version_procedimiento' => $document->version_procedimiento,
            'estatus'               => $document->estatus,
            'observaciones'         => $document->observaciones,
            'fecha'                 => $document->created_at
                                        ? $document->created_at->setTimezone(config('app.timezone'))->format('Y-m-d\TH:i')
                                        : null,
            'original_name'         => $document->original_name,
            'extension'             => $document->extension,
            'uploaded_by_admin'     => $uploadedByAdmin,
            'tipo_documento'        => $document->tipo_documento,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: updateDocument
    |--------------------------------------------------------------------------
    | ACTUALIZA LOS DATOS DE UN DOCUMENTO EXISTENTE.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | SI EL DOC FUE SUBIDO POR ADMIN → SOLO SE PUEDE RENOMBRAR.
    | SI FUE SUBIDO POR USUARIO → SE PUEDE EDITAR TODO (ESTATUS, PROCESO, ETC.)
    | SI SE VALIDA COMO "VÁLIDO" Y TIENE CÓDIGO → SE ENVÍA A LISTA MAESTRA.
    | ENVÍA NOTIFICACIONES AL USUARIO SEGÚN EL ESTATUS ASIGNADO.
    */
    public function updateDocument(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para editar documentos.');
        }

        $document = DocumentalDocument::whereNull('deleted_at')->findOrFail($id);
        $uploaderRole    = $document->user->role ?? null;
        $uploadedByAdmin = in_array($uploaderRole, ['superadmin', 'admin']);

        if ($uploadedByAdmin) {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $document->name = $request->name;
            $document->save();

            return redirect()->back()->with('success', 'Documento renombrado exitosamente.');

        } else {
            $request->validate([
                'name'                  => 'required|string|max:255',
                'responsable'           => 'nullable|string|max:255',
                'proceso'               => 'nullable|string|max:255',
                'departamento'          => 'nullable|string|max:255',
                'estatus'               => 'required|in:Pendiente,Valido,No Valido',
                'observaciones'         => 'nullable|string',
                'clave_formato'         => 'nullable|string|max:255',
                'codigo_procedimiento'  => 'nullable|string|max:255',
                'version_procedimiento' => 'nullable|string|max:255',
            ]);

            // ── GUARDAR ESTATUS ANTERIOR PARA COMPARAR ─────────────────
            $estatusAnterior = $document->estatus;

            $data = $request->only([
                'name', 'responsable', 'proceso', 'departamento',
                'estatus', 'observaciones',
            ]);

            if ($request->estatus === 'Valido') {
                $data['observaciones'] = null;

                if ($request->filled('clave_formato')) {
                    $data['clave_formato']         = $request->clave_formato;
                    $data['codigo_procedimiento']  = $request->codigo_procedimiento;
                    $data['version_procedimiento'] = $request->version_procedimiento;
                }

                if (!$request->filled('clave_formato') && $request->filled('codigo_procedimiento')) {
                    $data['codigo_procedimiento']  = $request->codigo_procedimiento;
                    $data['version_procedimiento'] = $request->version_procedimiento;
                }
            }

            unset($data['fecha']);
            $document->update($data);

            // ── NOTIFICACIONES (solo si el estatus cambió) ─────────────
            if ($estatusAnterior !== $request->estatus) {
                $notif = app(NotificacionService::class);

                // CASO 1: Admin marcó como Válido
                if ($request->estatus === 'Valido') {

                    // Notif al usuario que subió el documento
                    $notif->enviar(
                        userId:     $document->user_id,
                        titulo:     'Tu documento ha sido aprobado ✓',
                        mensaje:    'El documento "' . $document->name . '" fue revisado y ' .
                                    'aprobado por ' . Auth::user()->name . '. ' .
                                    'Ya está disponible en el sistema.',
                        tipo:       'exito',
                        icono:      'bi-file-earmark-check',
                        url:        $document->folder_id
                                    ? route('documental.index', ['folder' => $document->folder_id])
                                    : route('documental.index'),
                        email:      true,
                        docId:      (string) $document->id,
                        tipoEvento: 'aprobado'
                    );

                    // Notif a TODOS los usuarios
                    $notif->enviarATodos(
                        titulo:     'Nuevo documento disponible: ' . $document->name,
                        mensaje:    'El documento "' . $document->name . '" ha sido autorizado ' .
                                    'y está disponible en Gestión Documental.',
                        tipo:       'info',
                        icono:      'bi-file-earmark-check2',
                        url:        $document->folder_id
                                    ? route('documental.index', ['folder' => $document->folder_id])
                                    : route('documental.index'),
                        email:      true,
                        docId:      (string) $document->id,
                        tipoEvento: 'publicado'
                    );
                }

                // CASO 2: Admin marcó como No Válido
                if ($request->estatus === 'No Valido') {

                    $notif->enviar(
                        userId:     $document->user_id,
                        titulo:     'Tu documento requiere correcciones',
                        mensaje:    'El documento "' . $document->name . '" fue revisado por ' .
                                    Auth::user()->name . ' y no fue aprobado.' . PHP_EOL .
                                    'Observaciones: ' . $request->observaciones,
                        tipo:       'error',
                        icono:      'bi-file-earmark-x',
                        url:        $document->folder_id
                                    ? route('documental.index', ['folder' => $document->folder_id])
                                    : route('documental.index'),
                        email:      true,
                        docId:      (string) $document->id,
                        tipoEvento: 'rechazado'
                    );
                }
            }
            // ── FIN NOTIFICACIONES ──────────────────────────────────────

            if (
                $request->estatus === 'Valido'
                && $request->filled('codigo_procedimiento')
                && $request->filled('version_procedimiento')
            ) {
                $tipoDocumento = $request->filled('clave_formato') ? 'Formato' : 'Procedimiento';
                
                $formato = \App\Models\Formato::create([
                    'proceso'               => $document->proceso,
                    'departamento'          => $document->departamento,
                    'clave_formato'         => $request->filled('clave_formato') ? $request->clave_formato : null,
                    'codigo_procedimiento'  => $request->codigo_procedimiento,
                    'version_procedimiento' => $request->version_procedimiento,
                    'nombre_archivo'        => $document->original_name,
                    'ruta_archivo'          => $document->file_path,
                    'extension_archivo'     => strtoupper($document->extension),
                    'tamanio_archivo'       => $document->size,
                    'tipo_documento'        => $tipoDocumento,
                ]);

                HistorialVersionesHelper::subir('FORMATOS', $formato, [], true);

                return redirect()->back()->with('success', 'Documento validado y enviado al módulo de Lista Maestra exitosamente.');
            }
        }

        return redirect()->back()->with('success', 'Documento actualizado exitosamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: moveDocument
    |--------------------------------------------------------------------------
    | MUEVE UN DOCUMENTO A OTRA CARPETA DENTRO DEL SISTEMA.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | VERIFICA QUE LA CARPETA DESTINO EXISTA Y NO ESTÉ ELIMINADA.
    | REGISTRA EL MOVIMIENTO EN EL HISTORIAL DE VERSIONES.
    */
    public function moveDocument(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para mover documentos.');
        }

        $document = DocumentalDocument::whereNull('deleted_at')->findOrFail($id);
        $origen = $document->folder_id ? DocumentalFolder::whereNull('deleted_at')->find($document->folder_id) : null;

        $request->validate([
            'destination_id' => 'nullable|exists:documental_folders,id'
        ]);

        // Verificar que la carpeta destino no esté eliminada
        if ($request->destination_id) {
            $destExists = DocumentalFolder::where('id', $request->destination_id)
                ->whereNull('deleted_at')
                ->exists();
            if (!$destExists) {
                return redirect()->back()->with('error', 'La carpeta destino no existe o está eliminada.');
            }
        }

        $destino = $request->destination_id ? DocumentalFolder::whereNull('deleted_at')->find($request->destination_id) : null;
        
        $document->folder_id = $request->destination_id;
        $document->save();

        HistorialVersionesHelper::mover('DOCUMENTAL_DOCUMENTS', $document, $origen, $destino);

        return redirect()->back()->with('success', 'Documento movido exitosamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: downloadDocument
    |--------------------------------------------------------------------------
    | DESCARGA EL ARCHIVO AL DISPOSITIVO DEL USUARIO (NO LO MUESTRA).
    | SI EL ARCHIVO NO EXISTE EN EL SERVIDOR → DEVUELVE ERROR.
    | REGISTRA LA DESCARGA EN EL HISTORIAL DE VERSIONES.
    */
    public function downloadDocument($id)
    {
        $document = DocumentalDocument::whereNull('deleted_at')->findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        // Registrar en historial
        HistorialVersionesHelper::descargar('DOCUMENTAL_DOCUMENTS', $document);

        // Forzar descarga
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: viewDocument
    |--------------------------------------------------------------------------
    | MUESTRA EL ARCHIVO EN EL NAVEGADOR (SIN DESCARGARLO).
    | SI EL ARCHIVO NO ESTÁ EN SU RUTA ORIGINAL, LO BUSCA EN OTRAS UBICACIONES.
    | SI NO SE ENCUENTRA EN NINGÚN LADO, GENERA UN CONTENIDO DE RESPALDO.
    | REGISTRA LA VISUALIZACIÓN EN EL HISTORIAL (SOLO LA PRIMERA VEZ).
    */
    public function viewDocument($id)
    {
        try {
            Log::info('Intentando visualizar documento ID: ' . $id);
            
            // Permitir ver documentos eliminados (para restauraciones)
            $document = DocumentalDocument::withTrashed()->findOrFail($id);
            
            Log::info('Documento encontrado: ' . $document->name . ' - Ruta: ' . $document->file_path);

            if (!$document->file_path) {
                Log::error('Documento sin file_path: ' . $id);
                abort(404, 'No hay archivo asociado a este documento');
            }

            // Construir ruta completa
            $path = storage_path('app/public/' . $document->file_path);
            
            Log::info('Ruta completa esperada: ' . $path);

            // Verificar si el archivo existe
            if (!file_exists($path)) {
                Log::warning('Archivo no encontrado en ruta esperada: ' . $path);
                
                // Buscar el archivo en ubicaciones alternativas
                $nuevaRuta = $this->buscarArchivoDocumental($document);
                
                if ($nuevaRuta) {
                    $path = storage_path('app/public/' . $nuevaRuta);
                    Log::info('Archivo encontrado en ubicación alternativa: ' . $path);
                    
                    // Actualizar la ruta en la base de datos
                    $document->file_path = $nuevaRuta;
                    $document->save();
                    Log::info('Ruta actualizada en BD a: ' . $nuevaRuta);
                } else {
                    // No se encontró el archivo, crear contenido de respaldo
                    $contenidoRespaldo = $this->crearContenidoRespaldo($document);
                    
                    // Mostrar el contenido de respaldo como texto plano
                    return response($contenidoRespaldo, 200)
                        ->header('Content-Type', 'text/plain; charset=utf-8');
                }
            }

            $extension = strtolower($document->extension);
            $mimeType = $this->getMimeType($extension, $document->mime_type);
            
            Log::info('Extensión: ' . $extension . ' - MimeType: ' . $mimeType);
            
            // Obtener el contenido del archivo
            $content = file_get_contents($path);
            
            // Para archivos de texto, asegurar encoding UTF-8
            if (in_array($extension, ['txt', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md', 'log'])) {
                if (mb_detect_encoding($content, 'UTF-8', true) !== 'UTF-8') {
                    $content = utf8_encode($content);
                }
            }
            
            // REGISTRAR EN HISTORIAL (solo la primera vez que se ve)
            // Usamos un flag en sesión para no registrar cada vez que se recarga
            $viewKey = 'document_viewed_' . $document->id;
            if (!session()->has($viewKey)) {
                HistorialVersionesHelper::ver('DOCUMENTAL_DOCUMENTS', $document);
                session()->put($viewKey, true);
            }
            
            // Devolver el archivo para MOSTRAR en el navegador (inline)
            return response($content)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $document->original_name . '"')
                ->header('Cache-Control', 'private, max-age=3600')
                ->header('X-Content-Type-Options', 'nosniff');
            
        } catch (\Exception $e) {
            Log::error('Error al ver documento: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            $errorMsg = "Error al visualizar el documento.\n";
            $errorMsg .= "ID: " . $id . "\n";
            $errorMsg .= "Mensaje: " . $e->getMessage() . "\n";
            $errorMsg .= "Por favor, contacte al administrador.";
            
            return response($errorMsg, 500)
                ->header('Content-Type', 'text/plain; charset=utf-8');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: getMimeType
    |--------------------------------------------------------------------------
    | DEVUELVE EL TIPO MIME CORRECTO SEGÚN LA EXTENSIÓN DEL ARCHIVO.
    | ESTO PERMITE QUE EL NAVEGADOR SEPA CÓMO MOSTRAR EL ARCHIVO.
    | SI NO RECONOCE LA EXTENSIÓN, USA EL MIME DEL DOCUMENTO O UNO GENÉRICO.
    */
    private function getMimeType($extension, $defaultMime = null)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        
        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }
        
        return $defaultMime ?: 'application/octet-stream';
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: buscarArchivoDocumental
    |--------------------------------------------------------------------------
    | BUSCA UN ARCHIVO EN UBICACIONES ALTERNATIVAS CUANDO NO SE ENCUENTRA
    | EN SU RUTA ORIGINAL. ÚTIL PARA DOCUMENTOS RESTAURADOS O MOVIDOS.
    | DEVUELVE LA NUEVA RUTA SI LO ENCUENTRA, O NULL SI NO LO ENCUENTRA.
    */
    private function buscarArchivoDocumental($document)
    {
        $userId = $document->user_id;
        $documentId = $document->id;
        $nombreOriginal = pathinfo($document->original_name, PATHINFO_FILENAME);
        
        // Buscar por patrón de nombre (time()_uniqid.extensión)
        $basePath = storage_path('app/public/documental/' . $userId . '/');
        
        if (is_dir($basePath)) {
            // Buscar archivos que contengan el ID del documento
            $files = glob($basePath . '*' . $documentId . '*');
            if (!empty($files)) {
                return 'documental/' . $userId . '/' . basename($files[0]);
            }
            
            // Buscar archivos que contengan el nombre original
            $files = glob($basePath . '*' . $nombreOriginal . '*');
            if (!empty($files)) {
                return 'documental/' . $userId . '/' . basename($files[0]);
            }
            
            // Buscar archivos con el nombre original exacto
            $files = glob($basePath . $document->original_name);
            if (!empty($files)) {
                return 'documental/' . $userId . '/' . basename($files[0]);
            }
            
            // Buscar cualquier archivo en la carpeta (último recurso)
            $files = glob($basePath . '*');
            if (!empty($files)) {
                Log::info('Posibles archivos en carpeta: ' . json_encode(array_map('basename', $files)));
                return 'documental/' . $userId . '/' . basename($files[0]);
            }
        }
        
        // Buscar en toda la carpeta public/documental
        $allFiles = glob(storage_path('app/public/documental/*/*'));
        foreach ($allFiles as $file) {
            if (strpos($file, (string)$documentId) !== false) {
                return str_replace(storage_path('app/public/'), '', $file);
            }
        }
        
        // Buscar por nombre original
        foreach ($allFiles as $file) {
            if (strpos($file, $nombreOriginal) !== false) {
                return str_replace(storage_path('app/public/'), '', $file);
            }
        }
        
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: crearContenidoRespaldo
    |--------------------------------------------------------------------------
    | GENERA UN TEXTO DE RESPALDO CUANDO EL ARCHIVO FÍSICO NO EXISTE.
    | INCLUYE LOS DATOS DEL DOCUMENTO Y SU HISTORIAL DE VERSIONES.
    | SE USA PRINCIPALMENTE PARA DOCUMENTOS RESTAURADOS SIN ARCHIVO.
    */
    private function crearContenidoRespaldo($document)
    {
        $contenido = "========================================\n";
        $contenido .= "DOCUMENTO RESTAURADO - CONTENIDO DE RESPALDO\n";
        $contenido .= "========================================\n\n";
        $contenido .= "Nombre del documento: " . ($document->original_name ?? $document->name) . "\n";
        $contenido .= "ID del documento: " . $document->id . "\n";
        $contenido .= "Fecha de creación: " . ($document->created_at ?? 'No disponible') . "\n";
        $contenido .= "Fecha de restauración: " . now() . "\n";
        $contenido .= "Usuario que subió: " . ($document->user->name ?? 'No disponible') . "\n";
        $contenido .= "Proceso: " . ($document->proceso ?? 'No disponible') . "\n";
        $contenido .= "Departamento: " . ($document->departamento ?? 'No disponible') . "\n";
        $contenido .= "Tipo de documento: " . ($document->tipo_documento ?? 'No disponible') . "\n";
        $contenido .= "Estatus: " . ($document->estatus ?? 'No disponible') . "\n";
        $contenido .= "\n========================================\n";
        $contenido .= "CONTENIDO DEL ARCHIVO\n";
        $contenido .= "========================================\n\n";
        $contenido .= "El archivo original no se encuentra en el servidor.\n";
        $contenido .= "Este es un mensaje de respaldo generado automáticamente.\n\n";
        $contenido .= "HISTORIAL DEL DOCUMENTO:\n";
        $contenido .= "----------------------------------------\n";
        
        // Intentar obtener historial del documento
        try {
            $historial = \App\Models\HistorialVersiones::where('modulo', 'DOCUMENTAL_DOCUMENTS')
                ->where('registro_id', $document->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            foreach ($historial as $h) {
                $contenido .= "• " . $h->created_at . " - " . $h->accion . " - " . $h->descripcion . "\n";
            }
        } catch (\Exception $e) {
            $contenido .= "No se pudo recuperar el historial.\n";
        }
        
        $contenido .= "\n========================================\n";
        $contenido .= "FIN DEL DOCUMENTO RESTAURADO\n";
        $contenido .= "========================================\n";
        
        return $contenido;
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: destroyDocument
    |--------------------------------------------------------------------------
    | ELIMINA UN DOCUMENTO (SOFT DELETE) DEL SISTEMA.
    | SOLO PUEDEN HACERLO ADMINISTRADORES Y SUPERADMINS.
    | EL TRAIT REGISTRA LA ELIMINACIÓN AUTOMÁTICAMENTE EN EL HISTORIAL.
    | DEVUELVE JSON DE ÉXITO O ERROR.
    */
    public function destroyDocument($id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar documentos.');
        }

        try {
            $document = DocumentalDocument::whereNull('deleted_at')->findOrFail($id);
            
            // ✅ Solo softDelete - El Trait registra automáticamente
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar documento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: getFoldersTree
    |--------------------------------------------------------------------------
    | DEVUELVE EN JSON EL ÁRBOL DE CARPETAS DISPONIBLES PARA MOVER ARCHIVOS.
    | EXCLUYE LA CARPETA ACTUAL Y LAS CARPETAS ELIMINADAS.
    | SI NO ES ADMIN, SOLO MUESTRA LAS CARPETAS DEL USUARIO.
    */
    public function getFoldersTree(Request $request)
    {
        $currentFolderId = $request->get('current_folder');

        $foldersQuery = DocumentalFolder::where('id', '!=', $currentFolderId)
            ->whereNull('deleted_at');

        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            $foldersQuery->where('user_id', Auth::id());
        }

        $folders = $foldersQuery->get()
            ->map(function($folder) {
                return [
                    'id' => $folder->id,
                    'full_path' => $folder->full_path
                ];
            });

        return response()->json($folders);
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN: wouldCreateCycle
    |--------------------------------------------------------------------------
    | VERIFICA SI MOVER UNA CARPETA A UN NUEVO PADRE CREARÍA UN CICLO.
    | EJEMPLO: MOVER "CARPETA A" DENTRO DE "SUBCARPETA DE A" ES UN CICLO.
    | DEVUELVE TRUE SI HABRÍA CICLO, FALSE SI ES SEGURO MOVER.
    */
    private function wouldCreateCycle($folder, $newParentId)
    {
        $parent = DocumentalFolder::find($newParentId);
        $visitedIds = [$folder->id];
        
        while ($parent) {
            if (in_array($parent->id, $visitedIds)) {
                return true;
            }
            $visitedIds[] = $parent->id;
            $parent = $parent->parent;
        }

        return false;
    } 
}