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
    /**
     * Verificar si el usuario puede modificar (superadmin o admin)
     */
    private function canModify()
    {
        return in_array(Auth::user()->role, ['superadmin', 'admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentId = $request->get('folder', null);
        $userRole = Auth::user()->role;
        
        if ($parentId) {
            $currentFolder = Competencia::with(['children', 'documentosHijos'])->findOrFail($parentId);
            
            if (!$currentFolder->isFolder()) {
                abort(404, 'El elemento solicitado no es una carpeta');
            }
            
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

    /**
     * Store a new folder.
     */
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
            Competencia::create([
                'nombre' => $request->nombre,
                'tipo' => 'carpeta',
                'color' => $request->color ?? '#800000',
                'parent_id' => $request->parent_id
            ]);

            return redirect()->back()->with('success', 'Carpeta creada exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error al crear carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la carpeta');
        }
    }

    /**
     * Upload a new document.
     * AHORA TODOS LOS USUARIOS PUEDEN SUBIR ARCHIVOS (admin, superadmin y usuarios normales)
     */
    public function uploadDocument(Request $request)
    {
        // Eliminada la restricción que impedía a admin/superadmin subir archivos
        // Todos los usuarios autenticados pueden subir archivos

        $request->validate([
            'archivo' => 'required|file|max:20480',
            'parent_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $nombreBase = pathinfo($originalName, PATHINFO_FILENAME);
            
            $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
            $ruta = $file->storeAs('competencias', $nombreArchivo, 'public');

            Competencia::create([
                'nombre' => $nombreBase,
                'tipo' => 'documento',
                'archivo_nombre' => $nombreArchivo,
                'archivo_ruta' => $ruta,
                'archivo_original' => $originalName,
                'archivo_tamano' => $file->getSize(),
                'archivo_extension' => $extension,
                'parent_id' => $request->parent_id
            ]);

            return redirect()->back()->with('success', 'Documento subido exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error al subir documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al subir el documento');
        }
    }

    /**
     * Rename a folder (ahora con mensaje flash).
     */
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
            
            $carpeta->nombre = $request->nombre;
            $carpeta->save();

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta');
        }
    }

    /**
     * Rename a document (ahora con mensaje flash).
     */
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
            
            $documento->nombre = $request->nombre;
            $documento->save();

            return redirect()->back()->with('success', 'Documento renombrado exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error al renombrar documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar el documento');
        }
    }

    /**
     * Move a folder (con mensaje flash).
     */
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

            return redirect()->back()->with('success', 'Carpeta movida correctamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al mover carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover la carpeta');
        }
    }

    /**
     * Move a document (con mensaje flash).
     */
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
            
            if ($request->destination_id) {
                $destino = Competencia::findOrFail($request->destination_id);
                if (!$destino->isFolder()) {
                    return redirect()->back()->with('error', 'El destino debe ser una carpeta');
                }
            }

            $documento->parent_id = $request->destination_id;
            $documento->save();

            return redirect()->back()->with('success', 'Documento movido correctamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al mover documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover el documento');
        }
    }

    /**
     * Download a document.
     */
    public function downloadDocument($id)
    {
        try {
            $documento = Competencia::findOrFail($id);
            
            if (!$documento->isDocument()) {
                abort(404, 'El elemento no es un documento');
            }

            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                abort(404, 'El archivo no existe en el servidor');
            }

            return Storage::disk('public')->download(
                $documento->archivo_ruta, 
                $documento->archivo_original
            );
            
        } catch (\Exception $e) {
            Log::error('Error al descargar: ' . $e->getMessage());
            abort(500, 'Error al descargar el archivo');
        }
    }

    /**
     * View a document.
     */
    public function viewDocument($id)
    {
        try {
            $documento = Competencia::findOrFail($id);
            
            if (!$documento->isDocument()) {
                abort(404, 'El elemento no es un documento');
            }

            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                abort(404, 'El archivo no existe en el servidor');
            }

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

    /**
     * Get folders tree for move modal.
     */
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

    /**
     * Construir árbol recursivamente para el selector de carpetas.
     */
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

    /**
     * Obtener todos los IDs de subcarpetas (solo carpetas) de una carpeta dada.
     */
    private function getAllDescendantFolderIds($folderId)
    {
        $ids = [];
        $subfolders = Competencia::where('parent_id', $folderId)
            ->where('tipo', 'carpeta')
            ->get();
        
        foreach ($subfolders as $sub) {
            $ids[] = $sub->id;
            $ids = array_merge($ids, $this->getAllDescendantFolderIds($sub->id));
        }
        
        return $ids;
    }

    /**
     * Get document data for editing.
     */
    public function getDocumentData($id)
    {
        try {
            $documento = Competencia::findOrFail($id);
            
            if (!$documento->isDocument()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El elemento no es un documento'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'nombre' => $documento->nombre,
                'archivo_extension' => $documento->archivo_extension,
                'created_at' => $documento->created_at->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener datos del documento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del documento'
            ], 500);
        }
    }

    /**
     * Remove a folder (con SweetAlert, se mantiene JSON).
     */
    public function destroyFolder($id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar carpetas.'
            ], 403);
        }

        try {
            $carpeta = Competencia::findOrFail($id);
            
            if (!$carpeta->isFolder()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El elemento no es una carpeta'
                ], 400);
            }
            
            $this->deleteFolderRecursively($carpeta);
            $carpeta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Carpeta eliminada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar carpeta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la carpeta'
            ], 500);
        }
    }

    /**
     * Remove a document (con SweetAlert, se mantiene JSON).
     */
    public function destroyDocument($id)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar documentos.'
            ], 403);
        }

        try {
            $documento = Competencia::findOrFail($id);
            
            if (!$documento->isDocument()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El elemento no es un documento'
                ], 400);
            }
            
            if (Storage::disk('public')->exists($documento->archivo_ruta)) {
                Storage::disk('public')->delete($documento->archivo_ruta);
            }
            
            $documento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar documento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento'
            ], 500);
        }
    }

    /**
     * Eliminar recursivamente todo el contenido de una carpeta.
     */
    private function deleteFolderRecursively($folder)
    {
        foreach ($folder->documentosHijos as $documento) {
            if (Storage::disk('public')->exists($documento->archivo_ruta)) {
                Storage::disk('public')->delete($documento->archivo_ruta);
            }
            $documento->delete();
        }
        
        $subfolders = Competencia::where('parent_id', $folder->id)
            ->where('tipo', 'carpeta')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            $this->deleteFolderRecursively($subfolder);
            $subfolder->delete();
        }
    }

    /**
     * Build breadcrumbs for navigation.
     */
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