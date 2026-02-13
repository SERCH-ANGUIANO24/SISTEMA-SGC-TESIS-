<?php
// app/Http/Controllers/DocumentalController.php

namespace App\Http\Controllers;

use App\Models\DocumentalFolder;
use App\Models\DocumentalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentalController extends Controller
{
    public function index(Request $request)
    {
        $folderId = $request->get('folder');
        $currentFolder = null;
        $folders = collect();
        $documents = collect();
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = DocumentalFolder::with('parent')->find($folderId);
            
            if ($currentFolder) {
                // Verificar propiedad
                if ($currentFolder->user_id != Auth::id()) {
                    abort(403);
                }
                
                // Breadcrumbs
                $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
                
                // Subcarpetas
                $folders = DocumentalFolder::where('parent_id', $folderId)
                    ->where('user_id', Auth::id())
                    ->orderBy('name')
                    ->get();
                
                // Documentos
                $documents = DocumentalDocument::where('folder_id', $folderId)
                    ->where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } else {
            // Raíz
            $folders = DocumentalFolder::whereNull('parent_id')
                ->where('user_id', Auth::id())
                ->orderBy('name')
                ->get();
            
            $documents = DocumentalDocument::whereNull('folder_id')
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('documental.index', compact('folders', 'documents', 'currentFolder', 'breadcrumbs'));
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
        $request->validate([
            'file' => 'required|file|max:102400',
            'folder_id' => 'nullable|exists:documental_folders,id'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Guardar archivo
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $path = $file->storeAs('documental/' . Auth::id(), $fileName, 'public');

        // Crear registro
        DocumentalDocument::create([
            'name' => $nameWithoutExt,
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $extension,
            'folder_id' => $request->folder_id,
            'user_id' => Auth::id(),
            'responsable' => Auth::user()->name,
            'proceso' => Auth::user()->proceso,
            'departamento' => Auth::user()->departamento,
            'estatus' => 'No Valido',
            'fecha' => now()
        ]);

        return redirect()->back()->with('success', 'Archivo subido exitosamente.');
    }

    public function getDocumentData($id)
    {
        $document = DocumentalDocument::where('user_id', Auth::id())->findOrFail($id);
        
        return response()->json([
            'name' => $document->name,
            'responsable' => $document->responsable,
            'proceso' => $document->proceso,
            'departamento' => $document->departamento,
            'estatus' => $document->estatus,
            'observaciones' => $document->observaciones,
            'fecha' => $document->fecha ? $document->fecha->format('Y-m-d') : null
        ]);
    }

    public function updateDocument(Request $request, $id)
    {
        $document = DocumentalDocument::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'proceso' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'estatus' => 'required|in:Valido,No Valido',
            'observaciones' => 'nullable|string',
            'fecha' => 'nullable|date'
        ]);

        $document->update($request->all());

        return redirect()->back()->with('success', 'Documento actualizado exitosamente.');
    }

    public function moveDocument(Request $request, $id)
    {
        $document = DocumentalDocument::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'destination_id' => 'nullable|exists:documental_folders,id'
        ]);

        $document->folder_id = $request->destination_id;
        $document->save();

        return redirect()->back()->with('success', 'Documento movido exitosamente.');
    }

    public function downloadDocument($id)
    {
        $document = DocumentalDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($document->file_path, $document->full_name);
    }

    public function viewDocument($id)
    {
        $document = DocumentalDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        if ($document->can_preview) {
            return response()->file(storage_path('app/public/' . $document->file_path));
        }

        return redirect()->back()->with('error', 'Vista previa no disponible.');
    }

    public function destroyDocument($id)
    {
        $document = DocumentalDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->back()->with('success', 'Documento eliminado exitosamente.');
    }

    public function getFoldersTree(Request $request)
    {
        $currentFolderId = $request->get('current_folder');
        
        $folders = DocumentalFolder::where('user_id', Auth::id())
            ->where('id', '!=', $currentFolderId)
            ->get()
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
        $folder = DocumentalFolder::where('user_id', Auth::id())->findOrFail($id);
        
        if ($folder->documents()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una carpeta con documentos.');
        }
        
        if ($folder->subfolders()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una carpeta con subcarpetas.');
        }
        
        $folder->delete();

        return redirect()->back()->with('success', 'Carpeta eliminada exitosamente.');
    }
}