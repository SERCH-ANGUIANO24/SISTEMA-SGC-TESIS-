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

        return Storage::disk('public')->download($document->file_path, $document->original_name);
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