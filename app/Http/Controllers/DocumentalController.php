<?php
// app/Http/Controllers/DocumentalController.php

namespace App\Http\Controllers;

use App\Models\DocumentalFolder;
use App\Models\DocumentalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DocumentalController extends Controller
{
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
                $breadcrumbs = $this->buildBreadcrumbs($currentFolder);

                $folders = DocumentalFolder::where('parent_id', $folderId)
                    ->orderBy('name')
                    ->get();

                $documentsQuery = DocumentalDocument::where('folder_id', $folderId);

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
        } else {
            $folders = DocumentalFolder::whereNull('parent_id')
                ->orderBy('name')
                ->get();

            $documentsQuery = DocumentalDocument::whereNull('folder_id');

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

        // Valores únicos para filtros (solo docs de admin)
        $baseQuery    = $folderId
            ? DocumentalDocument::where('folder_id', $folderId)
            : DocumentalDocument::whereNull('folder_id');

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

        // ── Procesos y departamentos dinámicos para el modal de subir archivo ──
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

    private function buildBreadcrumbs($folder)
    {
        $breadcrumbs = [];
        $current = $folder;

        while ($current) {
            array_unshift($breadcrumbs, [
                'id' => $current->id,
                'name' => $current->name
            ]);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    public function storeFolder(Request $request)
    {
        // Solo superadmin y admin pueden crear carpetas
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para crear carpetas.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string',
            'parent_id' => 'nullable|exists:documental_folders,id'
        ]);

        DocumentalFolder::create([
            'name' => $request->name,
            'color' => $request->color ?? '#800000',
            'parent_id' => $request->parent_id,
            'user_id' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Carpeta creada exitosamente.');
    }

    public function upload(Request $request)
    {
        $isAdmin = in_array(Auth::user()->role, ['superadmin', 'admin']);
 
        // Validación base para todos
        $request->validate([
            'file'           => 'required|file|max:102400',
            'folder_id'      => 'nullable|exists:documental_folders,id',
            'tipo_documento' => 'required|in:Formato,Procedimiento',
        ]);
 
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
 
        // proceso y departamento del perfil del usuario (admin o no)
        $proceso      = Auth::user()->proceso;
        $departamento = Auth::user()->departamento;
 
        // Guardar en Gestión Documental
        DocumentalDocument::create([
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
 
        // Admin/Superadmin: registrar en Lista Maestra SIN copiar el archivo
        // Se usa la misma ruta del archivo subido
        if ($isAdmin) {
            \App\Models\Formato::create([
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
 
            return redirect()->back()->with('success', 'Archivo subido y enviado al módulo de Lista Maestra exitosamente.');
        }
 
        return redirect()->back()->with('success', 'Archivo subido exitosamente.');
    }

     public function getDocumentData($id)
    {
        $query = DocumentalDocument::query();
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
    public function updateDocument(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para editar documentos.');
        }
 
        $document        = DocumentalDocument::findOrFail($id);
        $uploaderRole    = $document->user->role ?? null;
        $uploadedByAdmin = in_array($uploaderRole, ['superadmin', 'admin']);
 
        // ── Documento subido por admin/superadmin: solo renombrar ──
        if ($uploadedByAdmin) {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
 
            $document->update(['name' => $request->name]);
 
            return redirect()->back()->with('success', 'Documento renombrado exitosamente.');
 
        // ── Documento subido por usuario ──
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
 
            // ── Enviar a Lista Maestra si Válido tipo Formato ──
            // Solo se registra la información, NO se copia el archivo
            if (
                $request->estatus === 'Valido'
                && $request->filled('clave_formato')
                && $request->filled('codigo_procedimiento')
                && $request->filled('version_procedimiento')
            ) {
                \App\Models\Formato::create([
                    'proceso'               => $document->proceso,
                    'departamento'          => $document->departamento,
                    'clave_formato'         => $request->clave_formato,
                    'codigo_procedimiento'  => $request->codigo_procedimiento,
                    'version_procedimiento' => $request->version_procedimiento,
                    'nombre_archivo'        => $document->original_name,
                    'ruta_archivo'          => $document->file_path,
                    'extension_archivo'     => strtoupper($document->extension),
                    'tamanio_archivo'       => $document->size,
                    'tipo_documento'        => 'Formato',
                ]);
 
                return redirect()->back()->with('success', 'Documento validado y enviado al módulo de Lista Maestra exitosamente.');
            }
 
            // ── Enviar a Lista Maestra si Válido tipo Procedimiento ──
            // Solo se registra la información, NO se copia el archivo
            if (
                $request->estatus === 'Valido'
                && !$request->filled('clave_formato')
                && $request->filled('codigo_procedimiento')
                && $request->filled('version_procedimiento')
            ) {
                \App\Models\Formato::create([
                    'proceso'               => $document->proceso,
                    'departamento'          => $document->departamento,
                    'clave_formato'         => null,
                    'codigo_procedimiento'  => $request->codigo_procedimiento,
                    'version_procedimiento' => $request->version_procedimiento,
                    'nombre_archivo'        => $document->original_name,
                    'ruta_archivo'          => $document->file_path,
                    'extension_archivo'     => strtoupper($document->extension),
                    'tamanio_archivo'       => $document->size,
                    'tipo_documento'        => 'Procedimiento',
                ]);
 
                return redirect()->back()->with('success', 'Procedimiento validado y enviado al módulo de Lista Maestra exitosamente.');
            }
        }
 
        return redirect()->back()->with('success', 'Documento actualizado exitosamente.');
    }

    public function moveDocument(Request $request, $id)
    {
        // Solo superadmin y admin pueden mover documentos
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para mover documentos.');
        }

        $document = DocumentalDocument::findOrFail($id);

        $request->validate([
            'destination_id' => 'nullable|exists:documental_folders,id'
        ]);

        $document->folder_id = $request->destination_id;
        $document->save();

        return redirect()->back()->with('success', 'Documento movido exitosamente.');
    }

    public function downloadDocument($id)
    {
        // Todos pueden descargar cualquier documento de la carpeta
        $document = DocumentalDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($document->file_path, $document->name . '.' . $document->extension);
    }

    public function viewDocument($id)
    {
        // Todos pueden ver cualquier documento de la carpeta
        $document = DocumentalDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        $extension = strtolower($document->extension);
        $path = storage_path('app/public/' . $document->file_path);

        if (in_array($extension, ['txt', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'])) {
            $content = file_get_contents($path);
            if (mb_detect_encoding($content, 'UTF-8', true) !== 'UTF-8') {
                $content = utf8_encode($content);
            }
            return response($content)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('Content-Disposition', 'inline; filename="' . $document->original_name . '"');
        }

        if ($extension === 'pdf') {
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'])) {
            return response()->file($path, [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroyDocument($id)
    {
        // Solo superadmin y admin pueden eliminar
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar documentos.');
        }

        try {
            $document = DocumentalDocument::findOrFail($id);

            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el archivo.'
            ], 500);
        }
    }

    public function getFoldersTree(Request $request)
    {
        $currentFolderId = $request->get('current_folder');

        // Admin ve todas las carpetas
        $foldersQuery = DocumentalFolder::where('id', '!=', $currentFolderId);

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

    public function destroyFolder($id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar carpetas.');
        }

        try {
            $folder = DocumentalFolder::findOrFail($id);
            $this->deleteFolderRecursively($folder);
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

    private function deleteFolderRecursively($folder)
    {
        foreach ($folder->documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        foreach ($folder->subfolders as $subfolder) {
            $this->deleteFolderRecursively($subfolder);
            $subfolder->delete();
        }
    }

    public function renameFolder(Request $request, $id)
    {
        // Solo superadmin y admin pueden renombrar
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para renombrar carpetas.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $folder = DocumentalFolder::findOrFail($id);
            $folder->name = $request->name;
            $folder->save();

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta.');
        }
    }

    public function moveFolder(Request $request, $id)
    {
        // Solo superadmin y admin pueden mover
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para mover carpetas.');
        }

        try {
            $request->validate([
                'destination_id' => 'nullable|exists:documental_folders,id'
            ]);

            $folder = DocumentalFolder::findOrFail($id);

            if ($request->destination_id == $id) {
                return redirect()->back()->with('error', 'No puedes mover una carpeta a sí misma.');
            }

            if ($request->destination_id) {
                $destinationFolder = DocumentalFolder::find($request->destination_id);
                if (!$destinationFolder) {
                    return redirect()->back()->with('error', 'La carpeta destino no es válida.');
                }

                if ($this->wouldCreateCycle($folder, $request->destination_id)) {
                    return redirect()->back()->with('error', 'No puedes mover una carpeta a una subcarpeta de sí misma.');
                }
            }

            $folder->parent_id = $request->destination_id;
            $folder->save();

            return redirect()->back()->with('success', 'Carpeta movida exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al mover carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover la carpeta.');
        }
    }

    private function wouldCreateCycle($folder, $newParentId)
    {
        $parent = DocumentalFolder::find($newParentId);

        while ($parent) {
            if ($parent->id == $folder->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }
}