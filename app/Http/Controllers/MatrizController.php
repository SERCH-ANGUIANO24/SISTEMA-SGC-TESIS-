<?php

namespace App\Http\Controllers;

use App\Models\MatrizFolder;
use App\Models\MatrizDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MatrizController extends Controller
{
    /**
     * Mostrar explorador en la raíz o dentro de una carpeta
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $folderId = $request->get('folder');
        $currentFolder = null;
        $folders = collect();
        $documents = collect();
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = MatrizFolder::with('parent')->find($folderId);
            
            if (!$currentFolder) {
                abort(404);
            }
            
            // SUPERADMIN Y ADMIN: Pueden acceder a cualquier carpeta
            // USUARIO NORMAL: También puede acceder a cualquier carpeta (para ver)
            // Solo verificamos que la carpeta exista, no de quién es
            
            // Breadcrumbs
            $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
            
            // DENTRO DE UNA CARPETA: TODOS ven TODAS las carpetas y archivos
            $folders = MatrizFolder::where('parent_id', $folderId)
                ->orderBy('name')
                ->get();
            
            $documents = MatrizDocument::where('folder_id', $folderId)
                ->orderBy('name')
                ->get();
        } else {
            // RAÍZ: TODOS ven TODAS las carpetas raíz
            $folders = MatrizFolder::whereNull('parent_id')
                ->orderBy('name')
                ->get();
            
            $documents = MatrizDocument::whereNull('folder_id')
                ->orderBy('name')
                ->get();
        }

        return view('matriz.index', compact('folders', 'documents', 'currentFolder', 'breadcrumbs'));
    }

    /**
     * Guardar nueva carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function storeFolder(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para crear carpetas.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:matrices_folders,id'
        ]);

        MatrizFolder::create([
            'name' => $request->name,
            'color' => $request->color ?? '#800000',
            'parent_id' => $request->parent_id,
            'user_id' => $user->id
        ]);

        return redirect()->back()->with('success', 'Carpeta creada exitosamente.');
    }

    /**
     * Subir documento - SOLO SUPERADMIN/ADMIN
     */
    public function upload(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para subir archivos.');
        }
        
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB
            'folder_id' => 'nullable|exists:matrices_folders,id',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Guardar archivo
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $path = $file->storeAs('matrices/' . $user->id, $fileName, 'public');

        // Determinar tipo de documento
        $extension = strtolower($extension);
        $tipos = [
            'pdf' => 'PDF',
            'xls' => 'Excel',
            'xlsx' => 'Excel',
            'doc' => 'Word',
            'docx' => 'Word',
            'ppt' => 'PowerPoint',
            'pptx' => 'PowerPoint',
            'csv' => 'CSV',
            'jpg' => 'Imagen',
            'jpeg' => 'Imagen',
            'png' => 'Imagen',
            'gif' => 'Imagen',
            'txt' => 'Texto',
        ];
        $tipo = $tipos[$extension] ?? 'Documento';

        // Crear registro
        MatrizDocument::create([
            'name' => $nameWithoutExt,
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $extension,
            'folder_id' => $request->folder_id,
            'user_id' => $user->id,
            'tipo_documento' => $tipo,
            'fecha_documento' => now()
        ]);

        return redirect()->back()->with('success', 'Matriz subida exitosamente.');
    }

    /**
     * Obtener datos de documento - PARA EDITAR
     */
    public function getDocumentData($id)
    {
        $user = Auth::user();
        
        if (in_array($user->role, ['superadmin', 'admin'])) {
            $document = MatrizDocument::findOrFail($id);
        } else {
            $document = MatrizDocument::findOrFail($id);
        }
        
        return response()->json([
            'name' => $document->name,
        ]);
    }

    /**
     * Renombrar documento - SOLO SUPERADMIN/ADMIN
     */
    public function updateDocument(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para renombrar matrices.');
        }
        
        $document = MatrizDocument::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $document->name = $request->name;
        $document->save();

        return redirect()->back()->with('success', 'Matriz renombrada exitosamente.');
    }

    /**
     * Mover documento - SOLO SUPERADMIN/ADMIN
     */
    public function moveDocument(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para mover matrices.');
        }
        
        $document = MatrizDocument::findOrFail($id);
        
        $request->validate([
            'destination_id' => 'nullable|exists:matrices_folders,id'
        ]);

        $document->folder_id = $request->destination_id;
        $document->save();

        return redirect()->back()->with('success', 'Matriz movida exitosamente.');
    }

    /**
     * Descargar documento - TODOS pueden descargar
     */
    public function downloadDocument($id)
    {
        $user = Auth::user();
        
        // Todos los usuarios autenticados pueden descargar
        $document = MatrizDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($document->file_path, $document->full_name);
    }

    /**
     * Ver documento en navegador - TODOS pueden ver (solo formatos permitidos)
     */
    public function viewDocument($id)
    {
        $user = Auth::user();
        
        // Todos los usuarios autenticados pueden ver
        $document = MatrizDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        $extension = strtolower($document->extension);
        $viewableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
        
        // Si no es visible o es CSV, forzar descarga
        if (!in_array($extension, $viewableExtensions) || $extension === 'csv') {
            return $this->downloadDocument($id);
        }

        return response()->file(storage_path('app/public/' . $document->file_path));
    }

    /**
     * Eliminar documento - SOLO SUPERADMIN/ADMIN
     */
    public function destroyDocument($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para eliminar matrices.');
        }
        
        $document = MatrizDocument::findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->back()->with('success', 'Matriz eliminada exitosamente.');
    }

    /**
     * Obtener árbol de carpetas - PARA MODALES DE MOVER
     */
    public function getFoldersTree(Request $request)
    {
        $user = Auth::user();
        $currentFolderId = $request->get('current_folder');
        
        if (in_array($user->role, ['superadmin', 'admin'])) {
            $folders = MatrizFolder::where('id', '!=', $currentFolderId)
                ->orderBy('name')
                ->get();
        } else {
            // Usuario normal no necesita árbol porque no puede mover
            return response()->json([]);
        }
        
        $folders = $folders->map(function($folder) {
            return [
                'id' => $folder->id,
                'full_path' => $folder->full_path
            ];
        });
        
        return response()->json($folders);
    }

    /**
     * Eliminar carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function destroyFolder($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para eliminar carpetas.');
        }
        
        $folder = MatrizFolder::findOrFail($id);
        
        // Eliminar todos los documentos de la carpeta y subcarpetas (recursivamente)
        $this->deleteFolderContents($folder);
        
        // Eliminar la carpeta
        $folder->delete();

        return redirect()->back()->with('success', 'Carpeta y todo su contenido eliminados exitosamente.');
    }

    /**
     * Renombrar carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function renameFolder(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para renombrar carpetas.');
        }
        
        $folder = MatrizFolder::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder->name = $request->name;
        $folder->save();

        return redirect()->back()->with('success', 'Carpeta renombrada exitosamente.');
    }

    /**
     * Mover carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function moveFolder(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return redirect()->back()->with('error', 'No tienes permiso para mover carpetas.');
        }
        
        $folder = MatrizFolder::findOrFail($id);
        
        $request->validate([
            'destination_id' => 'nullable|exists:matrices_folders,id'
        ]);

        // Evitar mover una carpeta dentro de sí misma o de sus subcarpetas
        if ($request->destination_id == $id) {
            return redirect()->back()->with('error', 'No puedes mover una carpeta dentro de sí misma.');
        }

        // Verificar que la carpeta destino no sea una subcarpeta de la actual
        if ($request->destination_id) {
            $destination = MatrizFolder::find($request->destination_id);
            $parent = $destination;
            while ($parent) {
                if ($parent->id == $id) {
                    return redirect()->back()->with('error', 'No puedes mover una carpeta a una de sus subcarpetas.');
                }
                $parent = $parent->parent;
            }
        }

        $folder->parent_id = $request->destination_id;
        $folder->save();

        return redirect()->back()->with('success', 'Carpeta movida exitosamente.');
    }

    // ---------- Métodos privados de ayuda ----------

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

    private function deleteFolderContents($folder)
    {
        // Eliminar documentos de esta carpeta
        foreach ($folder->documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }
        
        // Procesar subcarpetas recursivamente
        foreach ($folder->subfolders as $subfolder) {
            $this->deleteFolderContents($subfolder);
            $subfolder->delete();
        }
    }
}