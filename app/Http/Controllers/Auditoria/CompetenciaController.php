<?php
// app/Http/Controllers/Auditoria/CompetenciaController.php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Competencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompetenciaController extends Controller
{
    private function canModify()
    {
        return in_array(Auth::user()->role, ['superadmin', 'admin']);
    }

    public function index(Request $request)
    {
        $parentId = $request->get('folder', null);
        $userRole = Auth::user()->role;
        
        if ($parentId) {
            $currentFolder = Competencia::with(['children', 'documentosHijos'])->findOrFail($parentId);
            if (!$currentFolder->isFolder()) {
                abort(404, 'El elemento solicitado no es una carpeta');
            }
            \App\Helpers\HistorialVersionesHelper::ver('COMPETENCIAS', $currentFolder, 'explorar');
            
            $folders = Competencia::with(['children', 'documentosHijos'])
                ->folders()
                ->where('parent_id', $parentId)
                ->orderBy('nombre')
                ->get();
                
            $documents = Competencia::documents()
                ->where('parent_id', $parentId)
                ->orderBy('nombre')
                ->get();
                
            $breadcrumbs = $this->getBreadcrumbs($currentFolder);
        } else {
            \App\Helpers\HistorialVersionesHelper::ver('COMPETENCIAS', null, 'raiz');
            
            $currentFolder = null;
            
            $folders = Competencia::with(['children', 'documentosHijos'])
                ->folders()
                ->whereNull('parent_id')
                ->orderBy('nombre')
                ->get();
                
            $documents = Competencia::documents()
                ->whereNull('parent_id')
                ->orderBy('nombre')
                ->get();
                
            $breadcrumbs = collect();
        }
        
        return view('auditoria.competencias.index', compact(
            'folders', 
            'documents', 
            'currentFolder', 
            'breadcrumbs',
            'userRole'
        ));
    }

    public function storeFolder(Request $request)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para crear carpetas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $folder = Competencia::create([
                'nombre' => $request->nombre,
                'tipo' => 'carpeta',  // ← IMPORTANTE: se guarda como carpeta
                'color' => $request->color ?? '#800000',
                'parent_id' => $request->parent_id
            ]);

            // Registrar en historial como carpeta
            \App\Helpers\HistorialVersionesHelper::crear('COMPETENCIAS', $folder);

            return redirect()->back()->with('success', 'Carpeta creada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al crear carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la carpeta');
        }
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:20480',
            'parent_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();
            $nombreSinExtension = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            
            $nombreArchivoFisico = time() . '_' . uniqid() . '.' . $extension;
            $ruta = $file->storeAs('competencias', $nombreArchivoFisico, 'public');

            $document = Competencia::create([
                'nombre' => $nombreSinExtension,
                'tipo' => 'documento',  // ← IMPORTANTE: se guarda como documento
                'archivo_nombre' => $nombreArchivoFisico,
                'archivo_ruta' => $ruta,
                'archivo_original' => $originalName,
                'archivo_tamano' => $file->getSize(),
                'archivo_extension' => $extension,
                'parent_id' => $request->parent_id
            ]);

            // Registrar en historial como documento
            \App\Helpers\HistorialVersionesHelper::subir('COMPETENCIAS', $document);

            return redirect()->back()->with('success', 'Documento subido exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al subir documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al subir el documento');
        }
    }

    public function renameFolder(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para renombrar carpetas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        try {
            $carpeta = Competencia::findOrFail($id);
            if (!$carpeta->isFolder()) {
                return redirect()->back()->with('error', 'El elemento no es una carpeta');
            }
            $datosAnteriores = $carpeta->toArray();
            $carpeta->nombre = $request->nombre;
            $carpeta->save();

            // Registrar en historial - edición de carpeta
            \App\Helpers\HistorialVersionesHelper::editar('COMPETENCIAS', $carpeta, $datosAnteriores, $carpeta->toArray());

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta');
        }
    }

    public function renameDocument(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para renombrar documentos.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        try {
            $documento = Competencia::findOrFail($id);
            if (!$documento->isDocument()) {
                return redirect()->back()->with('error', 'El elemento no es un documento');
            }
            
            $datosAnteriores = $documento->toArray();
            $extension = $documento->archivo_extension;
            $nuevoNombreCompleto = $request->nombre . '.' . $extension;
            
            $documento->nombre = $request->nombre;
            $documento->archivo_original = $nuevoNombreCompleto;
            $documento->save();

            // Registrar en historial - edición de documento
            \App\Helpers\HistorialVersionesHelper::editar('COMPETENCIAS', $documento, $datosAnteriores, $documento->toArray());

            return redirect()->back()->with('success', 'Documento renombrado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al renombrar documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar el documento');
        }
    }

    public function moveFolder(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para mover carpetas.');
        }

        $request->validate([
            'destination_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $carpeta = Competencia::findOrFail($id);
            if (!$carpeta->isFolder()) {
                return redirect()->back()->with('error', 'El elemento no es una carpeta');
            }

            $origen = $carpeta->parent_id ? Competencia::find($carpeta->parent_id) : null;
            $destino = $request->destination_id ? Competencia::find($request->destination_id) : null;

            if ($request->destination_id) {
                $destino = Competencia::findOrFail($request->destination_id);
                if (!$destino->isFolder()) {
                    return redirect()->back()->with('error', 'El destino debe ser una carpeta');
                }
                if ($carpeta->id == $request->destination_id) {
                    return redirect()->back()->with('error', 'No puedes mover una carpeta a sí misma');
                }
                $descendantIds = $this->getAllDescendantFolderIds($carpeta->id);
                if (in_array($request->destination_id, $descendantIds)) {
                    return redirect()->back()->with('error', 'No puedes mover una carpeta a una de sus subcarpetas');
                }
            }

            $carpeta->parent_id = $request->destination_id;
            $carpeta->save();

            // Registrar en historial - movimiento de carpeta
            \App\Helpers\HistorialVersionesHelper::mover('COMPETENCIAS', $carpeta, $origen ?? (object)['nombre' => 'Raíz'], $destino ?? (object)['nombre' => 'Raíz']);

            return redirect()->back()->with('success', 'Carpeta movida correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al mover carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover la carpeta');
        }
    }

    public function moveDocument(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para mover documentos.');
        }

        $request->validate([
            'destination_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $documento = Competencia::findOrFail($id);
            if (!$documento->isDocument()) {
                return redirect()->back()->with('error', 'El elemento no es un documento');
            }

            $origen = $documento->parent_id ? Competencia::find($documento->parent_id) : null;
            $destino = $request->destination_id ? Competencia::find($request->destination_id) : null;

            if ($request->destination_id) {
                $destino = Competencia::findOrFail($request->destination_id);
                if (!$destino->isFolder()) {
                    return redirect()->back()->with('error', 'El destino debe ser una carpeta');
                }
            }

            $documento->parent_id = $request->destination_id;
            $documento->save();

            // Registrar en historial - movimiento de documento
            \App\Helpers\HistorialVersionesHelper::mover('COMPETENCIAS', $documento, $origen ?? (object)['nombre' => 'Raíz'], $destino ?? (object)['nombre' => 'Raíz']);

            return redirect()->back()->with('success', 'Documento movido correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al mover documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover el documento');
        }
    }

    public function downloadDocument($id)
    {
        try {
            $documento = Competencia::withTrashed()->findOrFail($id);
            if (!$documento->isDocument()) {
                abort(404, 'El elemento no es un documento');
            }
            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                abort(404, 'El archivo no existe en el servidor');
            }

            // Registrar en historial - descarga
            \App\Helpers\HistorialVersionesHelper::descargar('COMPETENCIAS', $documento);

            return Storage::disk('public')->download($documento->archivo_ruta, $documento->archivo_original);
        } catch (\Exception $e) {
            Log::error('Error al descargar: ' . $e->getMessage());
            abort(500, 'Error al descargar el archivo');
        }
    }

    public function viewDocument($id)
    {
        try {
            $documento = Competencia::withTrashed()->findOrFail($id);
            if (!$documento->isDocument()) {
                abort(404, 'El elemento no es un documento');
            }
            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                abort(404, 'El archivo no existe en el servidor');
            }

            // Registrar en historial - visualización
            \App\Helpers\HistorialVersionesHelper::ver('COMPETENCIAS', $documento, 'visualizar');

            $extension = strtolower($documento->archivo_extension);
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])) {
                return Storage::disk('public')->response($documento->archivo_ruta);
            }
            
            if ($extension === 'pdf') {
                return Storage::disk('public')->response($documento->archivo_ruta, $documento->archivo_original, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $documento->archivo_original . '"'
                ]);
            }
            
            if (in_array($extension, ['txt', 'csv', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'])) {
                $content = Storage::disk('public')->get($documento->archivo_ruta);
                if (mb_detect_encoding($content, 'UTF-8', true) !== 'UTF-8') {
                    $content = utf8_encode($content);
                }
                return response($content)
                    ->header('Content-Type', 'text/plain; charset=utf-8')
                    ->header('Content-Disposition', 'inline; filename="' . $documento->archivo_original . '"');
            }
            
            return Storage::disk('public')->download($documento->archivo_ruta, $documento->archivo_original);
        } catch (\Exception $e) {
            Log::error('Error al ver archivo: ' . $e->getMessage());
            abort(500, 'Error al visualizar el archivo');
        }
    }

    public function getFoldersTree(Request $request)
    {
        try {
            $currentFolderId = $request->get('current_folder');
            $allFolders = Competencia::folders()->get()->keyBy('id');
            
            $excludeIds = [];
            if ($currentFolderId && $currentFolderId !== 'null' && isset($allFolders[$currentFolderId])) {
                $excludeIds = $this->getAllDescendantFolderIds($currentFolderId);
                $excludeIds[] = $currentFolderId;
            }
            
            $availableFolders = $allFolders->reject(function ($folder) use ($excludeIds) {
                return in_array($folder->id, $excludeIds);
            });
            
            $tree = [];
            foreach ($availableFolders as $folder) {
                if ($folder->parent_id === null || !$availableFolders->has($folder->parent_id)) {
                    $this->buildTreeRecursive($folder, $availableFolders, $tree, '');
                }
            }
            return response()->json($tree);
        } catch (\Exception $e) {
            Log::error('Error al obtener árbol de carpetas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar carpetas'], 500);
        }
    }

    private function buildTreeRecursive($folder, $availableFolders, &$output, $prefix)
    {
        $output[] = [
            'id' => $folder->id,
            'full_path' => $prefix . $folder->nombre,
        ];
        $children = $availableFolders->filter(function ($f) use ($folder) {
            return $f->parent_id == $folder->id;
        })->sortBy('nombre');
        foreach ($children as $child) {
            $this->buildTreeRecursive($child, $availableFolders, $output, $prefix . $folder->nombre . ' / ');
        }
    }

    private function getAllDescendantFolderIds($folderId)
    {
        $ids = [];
        $subfolders = Competencia::where('parent_id', $folderId)->where('tipo', 'carpeta')->get();
        foreach ($subfolders as $sub) {
            $ids[] = $sub->id;
            $ids = array_merge($ids, $this->getAllDescendantFolderIds($sub->id));
        }
        return $ids;
    }

    public function getDocumentData($id)
    {
        try {
            $documento = Competencia::withTrashed()->findOrFail($id);
            if (!$documento->isDocument()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es un documento'], 400);
            }
            return response()->json([
                'success' => true,
                'nombre' => $documento->nombre,
                'archivo_original' => $documento->archivo_original,
                'archivo_extension' => $documento->archivo_extension,
                'created_at' => $documento->created_at->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos del documento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener los datos del documento'], 500);
        }
    }

    public function destroyFolder($id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar carpetas.'], 403);
        }

        try {
            $carpeta = Competencia::findOrFail($id);
            if (!$carpeta->isFolder()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es una carpeta'], 400);
            }
            
            // Guardar datos para historial
            $carpetaData = $carpeta->toArray();
            
            // Eliminar recursivamente
            $this->deleteFolderRecursively($carpeta);
            $carpeta->delete();

            // Registrar en historial - eliminación de carpeta
            \App\Helpers\HistorialVersionesHelper::eliminar('COMPETENCIAS', $carpeta, $carpetaData);

            return response()->json(['success' => true, 'message' => 'Carpeta eliminada exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar carpeta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la carpeta'], 500);
        }
    }

    public function destroyDocument($id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar documentos.'], 403);
        }

        try {
            $documento = Competencia::findOrFail($id);
            if (!$documento->isDocument()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es un documento'], 400);
            }
            
            // Guardar datos para historial
            $documentoData = $documento->toArray();
            $documento->delete();

            // Registrar en historial - eliminación de documento
            \App\Helpers\HistorialVersionesHelper::eliminar('COMPETENCIAS', $documento, $documentoData);

            return response()->json(['success' => true, 'message' => 'Documento eliminado exitosamente']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el documento'], 500);
        }
    }

    public function restaurarFolder($id)
    {
        try {
            $carpeta = Competencia::withTrashed()->findOrFail($id);
            
            if (!$carpeta->isFolder()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es una carpeta'], 400);
            }
            
            if (!$carpeta->trashed()) {
                return response()->json(['success' => false, 'message' => 'La carpeta no está eliminada'], 400);
            }
            
            if ($carpeta->parent_id) {
                $parentExists = Competencia::withTrashed()->find($carpeta->parent_id);
                if (!$parentExists) {
                    return response()->json(['success' => false, 'message' => 'La carpeta padre ya no existe'], 400);
                }
            }
            
            $existing = Competencia::where('nombre', $carpeta->nombre)
                ->where('tipo', 'carpeta')
                ->where('parent_id', $carpeta->parent_id)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe una carpeta activa con el mismo nombre en esta ubicación'], 400);
            }
            
            // PRIMERO: Restaurar TODOS los documentos dentro de la carpeta
            $this->restoreAllDocumentsInCompetenciaFolder($carpeta->id);
            
            // SEGUNDO: Restaurar TODAS las subcarpetas
            $this->restoreAllSubfoldersCompetencia($carpeta->id);
            
            // TERCERO: Restaurar la carpeta principal
            $carpeta->restore();
            
            // Registrar en historial - restauración de carpeta
            \App\Helpers\HistorialVersionesHelper::restaurar('COMPETENCIAS', $carpeta);
            
            return response()->json(['success' => true, 'message' => 'Carpeta restaurada correctamente con todo su contenido']);
        } catch (\Exception $e) {
            Log::error('Error al restaurar carpeta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar la carpeta: ' . $e->getMessage()], 500);
        }
    }

    public function restaurarDocumento($id)
    {
        try {
            $documento = Competencia::withTrashed()->findOrFail($id);
            
            if (!$documento->isDocument()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es un documento'], 400);
            }
            
            if (!$documento->trashed()) {
                return response()->json(['success' => false, 'message' => 'El documento no está eliminado'], 400);
            }
            
            if ($documento->parent_id) {
                $parentExists = Competencia::withTrashed()->find($documento->parent_id);
                if (!$parentExists) {
                    return response()->json(['success' => false, 'message' => 'La carpeta padre ya no existe'], 400);
                }
            }
            
            $existing = Competencia::where('nombre', $documento->nombre)
                ->where('tipo', 'documento')
                ->where('parent_id', $documento->parent_id)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe un documento activo con el mismo nombre en esta ubicación'], 400);
            }
            
            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                return response()->json(['success' => false, 'message' => 'El archivo físico no existe en el servidor'], 400);
            }
            
            $documento->restore();
            
            // Registrar en historial - restauración de documento
            \App\Helpers\HistorialVersionesHelper::restaurar('COMPETENCIAS', $documento);
            
            return response()->json(['success' => true, 'message' => 'Documento restaurado correctamente']);
        } catch (\Exception $e) {
            Log::error('Error al restaurar documento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar el documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Restaurar todos los documentos dentro de una carpeta de competencias (recursivamente)
     */
    private function restoreAllDocumentsInCompetenciaFolder($folderId)
    {
        // Restaurar documentos directamente en esta carpeta
        $documents = Competencia::withTrashed()
            ->documents()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($documents as $document) {
            if (Storage::disk('public')->exists($document->archivo_ruta)) {
                $document->restore();
            }
        }
        
        // Buscar subcarpetas y restaurar sus documentos
        $subfolders = Competencia::withTrashed()
            ->folders()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            $this->restoreAllDocumentsInCompetenciaFolder($subfolder->id);
        }
    }
    
    /**
     * Restaurar todas las subcarpetas (recursivamente)
     */
    private function restoreAllSubfoldersCompetencia($folderId)
    {
        $subfolders = Competencia::withTrashed()
            ->folders()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            // Primero restaurar documentos dentro de esta subcarpeta
            $this->restoreAllDocumentsInCompetenciaFolder($subfolder->id);
            // Luego restaurar subcarpetas hijas
            $this->restoreAllSubfoldersCompetencia($subfolder->id);
            // Finalmente restaurar la subcarpeta
            $subfolder->restore();
        }
    }

    private function deleteFolderRecursively($folder)
    {
        // Eliminar documentos dentro de la carpeta
        foreach ($folder->documentosHijos as $documento) {
            $documento->delete();
        }
        // Eliminar subcarpetas
        $subfolders = Competencia::where('parent_id', $folder->id)->where('tipo', 'carpeta')->get();
        foreach ($subfolders as $subfolder) {
            $this->deleteFolderRecursively($subfolder);
            $subfolder->delete();
        }
    }

    private function getBreadcrumbs($folder)
    {
        $breadcrumbs = collect();
        $current = $folder;
        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }
        return $breadcrumbs;
    }
}