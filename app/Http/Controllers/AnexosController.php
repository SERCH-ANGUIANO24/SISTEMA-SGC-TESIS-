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
        $folderId = $request->get('folder');
        $currentFolder = null;

        if ($folderId) {
            $currentFolder = Folder::with('parent')->findOrFail($folderId);
            $this->authorizeAccess($currentFolder);
            $folders = Folder::where('parent_id', $folderId)
                             ->where('user_id', Auth::id())
                             ->orderBy('name')
                             ->get();
            $documents = Document::where('folder_id', $folderId)
                                 ->where('user_id', Auth::id())
                                 ->orderBy('name')
                                 ->get();
        } else {
            // Raíz: carpetas sin padre y documentos sin carpeta
            $folders = Folder::whereNull('parent_id')
                             ->where('user_id', Auth::id())
                             ->orderBy('name')
                             ->get();
            $documents = Document::whereNull('folder_id')
                                 ->where('user_id', Auth::id())
                                 ->orderBy('name')
                                 ->get();
        }

        // Construir breadcrumbs
        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);

        return view('anexos.index', compact('currentFolder', 'folders', 'documents', 'breadcrumbs'));
    }

    /**
     * Guardar nueva carpeta
     */
    public function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'color' => $request->color ?? '#808080',
            'parent_id' => $request->parent_id,
            'user_id' => Auth::id()
        ]);

        return redirect()->route('anexos.index', ['folder' => $request->parent_id])
                         ->with('success', 'Carpeta creada correctamente.');
    }

    /**
     * Subir documento
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB máx
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType();
        $size = $file->getSize();

        // Generar nombre único
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('anexos/' . Auth::id(), $fileName, 'public');

        Document::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $mime,
            'size' => $size,
            'folder_id' => $request->folder_id,
            'user_id' => Auth::id()
        ]);

        return redirect()->route('anexos.index', ['folder' => $request->folder_id])
                         ->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Eliminar carpeta (y todo su contenido)
     */
    public function destroyFolder($id)
    {
        $folder = Folder::findOrFail($id);
        $this->authorizeAccess($folder);
        $parentId = $folder->parent_id;

        // Eliminar archivos del storage
        foreach ($folder->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
            $doc->delete();
        }

        // Las subcarpetas se eliminarán por cascada en BD, pero también hay que borrar sus archivos físicos
        $this->recursiveDeleteFiles($folder);
        $folder->delete();

        return redirect()->route('anexos.index', ['folder' => $parentId])
                         ->with('success', 'Carpeta eliminada.');
    }

    /**
     * Eliminar documento
     */
    public function destroyDocument($id)
    {
        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);
        $folderId = $document->folder_id;

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('anexos.index', ['folder' => $folderId])
                         ->with('success', 'Archivo eliminado.');
    }

    /**
     * Descargar documento
     */
    public function downloadDocument($id)
    {
        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);

        return Storage::disk('public')->download($document->file_path, $document->original_name,['Content-Disposition' => 'attachment; filename="' . $document->original_name . '"']);
    }

    public function viewDocument($id)
    {
        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);
        
        $path = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'El archivo no existe en el servidor');
        }
        
        $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
        
        // Forzar visualización en el navegador para TODOS los tipos de archivo
        $contentTypes = [
            // Documentos
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/plain',
            // Imágenes
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        
        // HEADER CRUCIAL: Forzar visualización en navegador
        return response()->file($path, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
        ]);
    }
    //FUNCION PARA RENOMBRAR LAS CARPETAS

    public function renameFolder(Request $request,$id){
            $request->validate([
        'name' => 'required|string|max:255'
    ]);

    $folder = Folder::findOrFail($id);
    $this->authorizeAccess($folder);
    
    $folder->name = $request->name;
    $folder->save();

    return redirect()->route('anexos.index', ['folder' => $folder->parent_id])
                     ->with('success', 'Carpeta renombrada correctamente.');

    }

    /**
 * Mover carpeta a otra ubicación
 */
    public function moveFolder(Request $request, $id)
    {
        $request->validate([
            'destination_id' => 'nullable|exists:folders,id'
        ]);

        $folder = Folder::findOrFail($id);
        $this->authorizeAccess($folder);
        
        // Validar que no se mueva a sí misma o a una subcarpeta
        if ($request->destination_id == $folder->id) {
            return back()->with('error', 'No puedes mover una carpeta a sí misma.');
        }
        
        // Validar que no se mueva a una subcarpeta
        if ($request->destination_id) {
            $destination = Folder::find($request->destination_id);
            $isSubfolder = $this->isSubfolder($folder->id, $destination);
            if ($isSubfolder) {
                return back()->with('error', 'No puedes mover una carpeta a una subcarpeta.');
            }
        }
        
        $folder->parent_id = $request->destination_id;
        $folder->save();

        return redirect()->route('anexos.index', ['folder' => $request->destination_id])
                        ->with('success', 'Carpeta movida correctamente.');
    }

    /**
     * Verificar si una carpeta es subcarpeta de otra
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
 * Obtener lista de carpetas para el modal de mover
 */
    public function getFoldersTree(Request $request)
    {
        $currentFolderId = $request->get('current_folder');
        $folders = Folder::where('user_id', Auth::id())
                        ->where('id', '!=', $currentFolderId)
                        ->orderBy('name')
                        ->get()
                        ->map(function($folder) {
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
     * Renombrar documento
     */
    public function renameDocument(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);
        
        // Mantener la extensión original
        $extension = pathinfo($document->original_name, PATHINFO_EXTENSION);
        $document->name = $request->name;
        $document->original_name = $request->name . '.' . $extension;
        $document->save();

        return redirect()->back()->with('success', 'Documento renombrado correctamente.');
    }

    /**
     * Mover documento a otra carpeta
     */
    public function moveDocument(Request $request, $id)
    {
        $request->validate([
            'destination_id' => 'nullable|exists:folders,id'
        ]);

        $document = Document::findOrFail($id);
        $this->authorizeAccess($document);
        
        $document->folder_id = $request->destination_id;
        $document->save();

        return redirect()->back()->with('success', 'Documento movido correctamente.');
    }
    // ---------- Métodos privados de ayuda ----------

    private function authorizeAccess($model)
    {
        if ($model->user_id !== Auth::id()) {
            abort(403);
        }
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

    private function recursiveDeleteFiles(Folder $folder)
    {
        foreach ($folder->subfolders as $subfolder) {
            $this->recursiveDeleteFiles($subfolder);
        }
        foreach ($folder->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }
    }
}