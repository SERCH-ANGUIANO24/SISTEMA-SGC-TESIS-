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
        
        // Si hay un folder específico, mostrar su contenido
        if ($parentId) {
            $currentFolder = Competencia::with(['children', 'documentosHijos'])->findOrFail($parentId);
            
            // Verificar que sea una carpeta
            if (!$currentFolder->isFolder()) {
                abort(404, 'El elemento solicitado no es una carpeta');
            }
            
            // Cargar carpetas - TODOS ven TODAS las carpetas
            $folders = Competencia::with(['children', 'documentosHijos'])
                ->folders()
                ->where('parent_id', $parentId)
                ->orderBy('nombre')
                ->get();
                
            // DOCUMENTOS - TODOS ven TODOS los documentos
            $documents = Competencia::documents()
                ->where('parent_id', $parentId)
                ->orderBy('nombre')
                ->get();
                
            $breadcrumbs = $this->getBreadcrumbs($currentFolder);
        } else {
            // Mostrar carpetas raíz y documentos sin carpeta
            $currentFolder = null;
            
            // Cargar carpetas raíz - TODOS ven TODAS las carpetas
            $folders = Competencia::with(['children', 'documentosHijos'])
                ->folders()
                ->whereNull('parent_id')
                ->orderBy('nombre')
                ->get();
                
            // DOCUMENTOS - TODOS ven TODOS los documentos
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
        // Solo superadmin y admin pueden crear carpetas
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para crear carpetas.'
                ], 403);
            }
            return redirect()->back()->with('error', 'No tienes permiso para crear carpetas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $carpeta = Competencia::create([
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
     */
    public function uploadDocument(Request $request)
    {
        // Solo usuarios normales pueden subir archivos
        if (in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los usuarios pueden subir archivos.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Solo los usuarios pueden subir archivos.');
        }

        $request->validate([
            'archivo' => 'required|file|max:20480', // 20MB max
            'parent_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $nombreBase = pathinfo($originalName, PATHINFO_FILENAME);
            
            // Generar nombre único para el archivo
            $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
            
            // Guardar archivo
            $ruta = $file->storeAs('competencias', $nombreArchivo, 'public');

            // Crear registro - SIN user_id
            $documento = Competencia::create([
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
     * Rename a folder.
     */
    public function renameFolder(Request $request, $id)
    {
        // Solo superadmin y admin pueden renombrar carpetas
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para renombrar carpetas.'
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        try {
            $carpeta = Competencia::findOrFail($id);
            
            if (!$carpeta->isFolder()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El elemento no es una carpeta'
                ], 400);
            }
            
            $carpeta->nombre = $request->nombre;
            $carpeta->save();

            return response()->json([
                'success' => true,
                'message' => 'Carpeta renombrada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al renombrar la carpeta'
            ], 500);
        }
    }

    /**
     * Rename a document.
     */
    public function renameDocument(Request $request, $id)
    {
        // Solo superadmin y admin pueden renombrar documentos
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para renombrar documentos.'
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        try {
            $documento = Competencia::findOrFail($id);
            
            if (!$documento->isDocument()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El elemento no es un documento'
                ], 400);
            }
            
            $documento->nombre = $request->nombre;
            $documento->save();

            return response()->json([
                'success' => true,
                'message' => 'Documento renombrado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al renombrar documento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al renombrar el documento'
            ], 500);
        }
    }

    /**
     * Move a folder.
     */
    public function moveFolder(Request $request, $id)
    {
        // Solo superadmin y admin pueden mover carpetas
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para mover carpetas.'
            ], 403);
        }

        $request->validate([
            'destination_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $carpeta = Competencia::findOrFail($id);
            
            if (!$carpeta->isFolder()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El elemento no es una carpeta'
                ], 400);
            }
            
            // Verificar que el destino sea una carpeta si no es null
            if ($request->destination_id) {
                $destino = Competencia::findOrFail($request->destination_id);
                if (!$destino->isFolder()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El destino debe ser una carpeta'
                    ], 400);
                }
            }

            $carpeta->parent_id = $request->destination_id;
            $carpeta->save();

            return response()->json([
                'success' => true,
                'message' => 'Carpeta movida exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al mover carpeta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al mover la carpeta'
            ], 500);
        }
    }

    /**
     * Move a document.
     */
    public function moveDocument(Request $request, $id)
    {
        // Solo superadmin y admin pueden mover documentos
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para mover documentos.'
            ], 403);
        }

        $request->validate([
            'destination_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $documento = Competencia::findOrFail($id);
            
            if (!$documento->isDocument()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El elemento no es un documento'
                ], 400);
            }
            
            // Verificar que el destino sea una carpeta si no es null
            if ($request->destination_id) {
                $destino = Competencia::findOrFail($request->destination_id);
                if (!$destino->isFolder()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El destino debe ser una carpeta'
                    ], 400);
                }
            }

            $documento->parent_id = $request->destination_id;
            $documento->save();

            return response()->json([
                'success' => true,
                'message' => 'Documento movido exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al mover documento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al mover el documento'
            ], 500);
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
            
            // Para imágenes, mostrar directamente
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])) {
                return Storage::disk('public')->response($documento->archivo_ruta);
            }
            
            // Para PDF, mostrar en el navegador
            if ($extension === 'pdf') {
                return Storage::disk('public')->response($documento->archivo_ruta, $documento->archivo_original, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $documento->archivo_original . '"'
                ]);
            }
            
            // Para archivos de texto
            if (in_array($extension, ['txt', 'csv', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'])) {
                $content = Storage::disk('public')->get($documento->archivo_ruta);
                
                // Detectar codificación
                if (mb_detect_encoding($content, 'UTF-8', true) !== 'UTF-8') {
                    $content = utf8_encode($content);
                }
                
                return response($content)
                    ->header('Content-Type', 'text/plain; charset=utf-8')
                    ->header('Content-Disposition', 'inline; filename="' . $documento->archivo_original . '"');
            }
            
            // Para otros tipos, forzar descarga
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
            
            // TODOS ven TODAS las carpetas
            $folders = Competencia::folders()
                ->whereNull('parent_id')
                ->with('children')
                ->get();
                
            $tree = $this->buildTree($folders, '', $currentFolderId);
            
            return response()->json($tree);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener árbol de carpetas: ' . $e->getMessage());
            return response()->json([], 500);
        }
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
     * Remove a folder.
     */
    public function destroyFolder($id)
    {
        // Solo superadmin y admin pueden eliminar carpetas
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
            
            // Eliminar contenido recursivamente
            $this->deleteFolderRecursively($carpeta);
            
            // Eliminar la carpeta
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
     * Remove a document.
     */
    public function destroyDocument($id)
    {
        // Solo superadmin y admin pueden eliminar documentos
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
            
            // Eliminar archivo físico
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
     * Eliminar recursivamente todo el contenido de una carpeta
     */
    private function deleteFolderRecursively($folder)
    {
        // Eliminar documentos dentro de la carpeta
        foreach ($folder->documentosHijos as $documento) {
            if (Storage::disk('public')->exists($documento->archivo_ruta)) {
                Storage::disk('public')->delete($documento->archivo_ruta);
            }
            $documento->delete();
        }
        
        // Eliminar subcarpetas recursivamente
        foreach ($folder->children as $subfolder) {
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

    /**
     * Build tree for folders select.
     */
    private function buildTree($folders, $prefix = '', $excludeId = null)
    {
        $tree = [];
        
        foreach ($folders as $folder) {
            if ($folder->id == $excludeId) continue;
            
            $tree[] = [
                'id' => $folder->id,
                'full_path' => $prefix . $folder->nombre,
                'children' => $this->buildTree($folder->children, $prefix . $folder->nombre . ' / ', $excludeId)
            ];
        }
        
        return $tree;
    }
}