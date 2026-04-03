<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnexosController extends Controller
{
    /**
     * Mostrar explorador en la raíz o dentro de una carpeta
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $folderId = $request->get('folder');
        $currentFolder = null;

        if ($folderId) {
            $currentFolder = Folder::with('parent')->findOrFail($folderId);
            
            // Registrar visualización de carpeta
            \App\Helpers\HistorialVersionesHelper::ver('FOLDERS', $currentFolder, 'explorar');
            
            $folders = Folder::where('parent_id', $folderId)
                             ->orderBy('name')
                             ->get();
            $documents = Document::where('folder_id', $folderId)
                                 ->whereNull('deleted_at')
                                 ->orderBy('name')
                                 ->get();
        } else {
            // Registrar visualización de raíz
            \App\Helpers\HistorialVersionesHelper::ver('FOLDERS', null, 'raiz');
            
            $folders = Folder::whereNull('parent_id')
                             ->orderBy('name')
                             ->get();
            $documents = Document::whereNull('folder_id')
                                 ->whereNull('deleted_at')
                                 ->orderBy('name')
                                 ->get();
        }

        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);

        return view('anexos.index', compact('currentFolder', 'folders', 'documents', 'breadcrumbs'));
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
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'color' => $request->color ?? '#808080',
            'parent_id' => $request->parent_id,
            'user_id' => $user->id
        ]);

        \App\Helpers\HistorialVersionesHelper::crear('FOLDERS', $folder);

        return redirect()->route('anexos.index', ['folder' => $request->parent_id])
                         ->with('success', 'Carpeta creada correctamente.');
    }

    /**
     * Subir documento - SOLO SUPERADMIN/ADMIN
     */
    public function uploadDocument(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para subir archivos.');
        }
        
        $request->validate([
            'file' => 'required|file|max:10240',
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType();
        $size = $file->getSize();

        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('anexos/' . $user->id, $fileName, 'public');

        $document = Document::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $mime,
            'size' => $size,
            'folder_id' => $request->folder_id,
            'user_id' => $user->id
        ]);

        \App\Helpers\HistorialVersionesHelper::subir('DOCUMENTS', $document);

        return redirect()->route('anexos.index', ['folder' => $request->folder_id])
                         ->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Eliminar carpeta - SOLO SUPERADMIN/ADMIN (SOFT DELETE)
     */
    public function destroyFolder($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permiso para eliminar carpetas.'
            ], 403);
        }
        
        try {
            $folder = Folder::findOrFail($id);
            $parentId = $folder->parent_id;

            // Guardar los datos ANTES de eliminar
            $folderData = $folder->toArray();

            // Soft delete todos los documentos de la carpeta
            foreach ($folder->documents as $doc) {
                $doc->delete();
            }

            // Soft delete subcarpetas recursivamente
            $this->recursiveDeleteFolders($folder);
            
            // Soft delete la carpeta
            $folder->delete();

            \App\Helpers\HistorialVersionesHelper::eliminar('FOLDERS', $folder, $folderData);

            return response()->json([
                'success' => true,
                'message' => 'Carpeta eliminada correctamente.',
                'parent_id' => $parentId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la carpeta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar documento - SOLO SUPERADMIN/ADMIN (SOFT DELETE)
     */
    public function destroyDocument($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permiso para eliminar documentos.'
            ], 403);
        }
        
        try {
            $document = Document::findOrFail($id);
            $folderId = $document->folder_id;

            // Guardar los datos ANTES de eliminar
            $documentData = $document->toArray();

            // Soft delete - NO elimina el archivo físico
            $document->delete();

            \App\Helpers\HistorialVersionesHelper::eliminar('DOCUMENTS', $document, $documentData);

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente.',
                'folder_id' => $folderId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar documento - TODOS pueden descargar
     */
    public function downloadDocument($id)
    {
        $document = Document::withTrashed()->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'El archivo no existe en el servidor.');
        }

        \App\Helpers\HistorialVersionesHelper::descargar('DOCUMENTS', $document);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /**
     * Ver documento en el navegador - TODOS pueden ver (solo formatos permitidos)
     */
    public function viewDocument($id)
    {
        $document = Document::withTrashed()->findOrFail($id);
        
        \App\Helpers\HistorialVersionesHelper::ver('DOCUMENTS', $document, 'visualizar');
        
        $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
        
        $viewableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'txt'];
        
        if (!in_array($extension, $viewableExtensions)) {
            return $this->downloadDocument($id);
        }
        
        $path = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'El archivo no existe en el servidor');
        }
        
        $contentTypes = [
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        
        return response()->file($path, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
        ]);
    }
    
    /**
     * Renombrar carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function renameFolder(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para renombrar carpetas.'], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder = Folder::findOrFail($id);
        
        $datosAnteriores = $folder->toArray();
        
        $folder->name = $request->name;
        $folder->save();

        \App\Helpers\HistorialVersionesHelper::editar('FOLDERS', $folder, $datosAnteriores, $folder->toArray());

        return redirect()->route('anexos.index', ['folder' => $folder->parent_id])
                         ->with('success', 'Carpeta renombrada correctamente.');
    }

    /**
     * Mover carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function moveFolder(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover carpetas.'], 403);
        }
        
        $request->validate([
            'destination_id' => 'nullable|exists:folders,id'
        ]);

        $folder = Folder::findOrFail($id);
        
        if ($request->destination_id == $folder->id) {
            return back()->with('error', 'No puedes mover una carpeta a sí misma.');
        }
        
        $origen = $folder->parent_id ? Folder::find($folder->parent_id) : (object)['name' => 'Raíz'];
        $destino = $request->destination_id ? Folder::find($request->destination_id) : (object)['name' => 'Raíz'];
        
        if ($request->destination_id) {
            $destination = Folder::find($request->destination_id);
            $isSubfolder = $this->isSubfolder($folder->id, $destination);
            if ($isSubfolder) {
                return back()->with('error', 'No puedes mover una carpeta a una subcarpeta.');
            }
        }
        
        $folder->parent_id = $request->destination_id;
        $folder->save();

        \App\Helpers\HistorialVersionesHelper::mover('FOLDERS', $folder, $origen, $destino);

        return redirect()->route('anexos.index', ['folder' => $request->destination_id])
                        ->with('success', 'Carpeta movida correctamente.');
    }

    /**
     * Verificar subcarpeta
     */
    private function isSubfolder($folderId, $destination)
    {
        if (!$destination) return false;
        
        $parent = $destination->parent;
        while ($parent) {
            if ($parent->id == $folderId) {
                return true;
            }
            $parent = $parent->parent;
        }
        return false;
    }

    /**
     * Obtener árbol de carpetas
     */
    public function getFoldersTree(Request $request)
    {
        $user = Auth::user();
        $currentFolderId = $request->get('current_folder');
        
        if (in_array($user->role, ['superadmin', 'admin'])) {
            $folders = Folder::where('id', '!=', $currentFolderId)
                            ->orderBy('name')
                            ->get();
        } else {
            return response()->json([]);
        }
        
        $folders = $folders->map(function($folder) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'parent_id' => $folder->parent_id,
                'full_path' => $this->getFolderPath($folder)
            ];
        });
        
        return response()->json($folders);
    }

    private function getFolderPath($folder)
    {
        $path = [];
        $current = $folder;
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        return implode(' / ', $path);
    }

    /**
     * Renombrar documento - SOLO SUPERADMIN/ADMIN
     */
    public function renameDocument(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para renombrar documentos.'], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $document = Document::findOrFail($id);
        
        $datosAnteriores = $document->toArray();
        
        $extension = pathinfo($document->original_name, PATHINFO_EXTENSION);
        $document->name = $request->name;
        $document->original_name = $request->name . '.' . $extension;
        $document->save();

        \App\Helpers\HistorialVersionesHelper::editar('DOCUMENTS', $document, $datosAnteriores, $document->toArray());

        return redirect()->back()->with('success', 'Documento renombrado correctamente.');
    }

    /**
     * Mover documento - SOLO SUPERADMIN/ADMIN
     */
    public function moveDocument(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover documentos.'], 403);
        }
        
        $request->validate([
            'destination_id' => 'nullable|exists:folders,id'
        ]);

        $document = Document::findOrFail($id);
        
        $origen = $document->folder_id ? Folder::find($document->folder_id) : (object)['name' => 'Raíz'];
        $destino = $request->destination_id ? Folder::find($request->destination_id) : (object)['name' => 'Raíz'];
        
        $document->folder_id = $request->destination_id;
        $document->save();

        \App\Helpers\HistorialVersionesHelper::mover('DOCUMENTS', $document, $origen, $destino);

        return redirect()->back()->with('success', 'Documento movido correctamente.');
    }

    private function buildBreadcrumbs($currentFolder = null)
    {
        $breadcrumbs = collect();
        $folder = $currentFolder;
        while ($folder) {
            $breadcrumbs->prepend($folder);
            $folder = $folder->parent;
        }
        return $breadcrumbs;
    }

    private function recursiveDeleteFolders(Folder $folder)
    {
        foreach ($folder->subfolders as $subfolder) {
            // Soft delete documentos de la subcarpeta
            foreach ($subfolder->documents as $doc) {
                $doc->delete();
            }
            $this->recursiveDeleteFolders($subfolder);
            $subfolder->delete();
        }
    }
}