<?php

namespace App\Http\Controllers;

use App\Models\MatrizFolder;
use App\Models\MatrizDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MatrizController extends Controller
{
    public function index(Request $request)
    {
        $folderId = $request->get('folder');
        $currentFolder = null;
        $folders = collect();
        $documents = collect();
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = MatrizFolder::with('parent')->find($folderId);
            
            if ($currentFolder) {
                // Verificar propiedad
                if ($currentFolder->user_id != Auth::id()) {
                    abort(403);
                }
                
                // Breadcrumbs
                $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
                
                // Subcarpetas
                $folders = MatrizFolder::where('parent_id', $folderId)
                    ->where('user_id', Auth::id())
                    ->orderBy('name')
                    ->get();
                
                // Documentos
                $documents = MatrizDocument::where('folder_id', $folderId)
                    ->where('user_id', Auth::id())
                    ->orderBy('fecha_documento', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } else {
            // Raíz
            $folders = MatrizFolder::whereNull('parent_id')
                ->where('user_id', Auth::id())
                ->orderBy('name')
                ->get();
            
            $documents = MatrizDocument::whereNull('folder_id')
                ->where('user_id', Auth::id())
                ->orderBy('fecha_documento', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('matriz.index', compact('folders', 'documents', 'currentFolder', 'breadcrumbs'));
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
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:matrices_folders,id'
        ]);

        MatrizFolder::create([
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
            'file' => 'required|file|max:102400', // 100MB
            'folder_id' => 'nullable|exists:matrices_folders,id',
            'tipo_documento' => 'nullable|string|max:50',
            'fecha_documento' => 'nullable|date'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Guardar archivo
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $path = $file->storeAs('matrices/' . Auth::id(), $fileName, 'public');

        // Determinar tipo de documento si no se especificó
        $tipo = $request->tipo_documento;
        if (!$tipo) {
            $extension = strtolower($extension);
            $tipos = [
                'pdf' => 'PDF',
                'xls' => 'Excel',
                'xlsx' => 'Excel',
                'doc' => 'Word',
                'docx' => 'Word',
                'ppt' => 'PowerPoint',
                'pptx' => 'PowerPoint',
            ];
            $tipo = $tipos[$extension] ?? 'Documento';
        }

        // Crear registro
        MatrizDocument::create([
            'name' => $nameWithoutExt,
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $extension,
            'folder_id' => $request->folder_id,
            'user_id' => Auth::id(),
            'tipo_documento' => $tipo,
            'fecha_documento' => $request->fecha_documento ?? now()
        ]);

        return redirect()->back()->with('success', 'Matriz subida exitosamente.');
    }

    public function getDocumentData($id)
    {
        $document = MatrizDocument::where('user_id', Auth::id())->findOrFail($id);
        
        return response()->json([
            'name' => $document->name,
            'tipo_documento' => $document->tipo_documento,
            'fecha_documento' => $document->fecha_documento ? $document->fecha_documento->format('Y-m-d') : null
        ]);
    }

    public function updateDocument(Request $request, $id)
    {
        $document = MatrizDocument::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',

        ]);

        $document->name = $request->name;
        $document->save();

        return redirect()->back()->with('success', 'Matriz renombrada exitosamente.');
    }

    public function moveDocument(Request $request, $id)
    {
        $document = MatrizDocument::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'destination_id' => 'nullable|exists:matrices_folders,id'
        ]);

        $document->folder_id = $request->destination_id;
        $document->save();

        return redirect()->back()->with('success', 'Matriz movida exitosamente.');
    }

    public function downloadDocument($id)
    {
        $document = MatrizDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($document->file_path, $document->full_name);
    }

    public function viewDocument($id)
    {
        $document = MatrizDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        if ($document->can_preview) {
            return response()->file(storage_path('app/public/' . $document->file_path));
        }

        return redirect()->back()->with('error', 'Vista previa no disponible para este tipo de archivo.');
    }

    public function destroyDocument($id)
    {
        $document = MatrizDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->back()->with('success', 'Matriz eliminada exitosamente.');
    }

    public function getFoldersTree(Request $request)
    {
        $currentFolderId = $request->get('current_folder');
        
        $folders = MatrizFolder::where('user_id', Auth::id())
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
        $folder = MatrizFolder::where('user_id', Auth::id())->findOrFail($id);
        
        // Eliminar todos los documentos de la carpeta y subcarpetas (recursivamente)
        $this->deleteFolderContents($folder);
        
        // Eliminar la carpeta
        $folder->delete();

        return redirect()->back()->with('success', 'Carpeta y todo su contenido eliminados exitosamente.');
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

        /**
     * Renombrar carpeta
     */
    public function renameFolder(Request $request, $id)
    {
        $folder = MatrizFolder::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder->name = $request->name;
        $folder->save();

        return redirect()->back()->with('success', 'Carpeta renombrada exitosamente.');
    }

    /**
     * Mover carpeta
     */
    public function moveFolder(Request $request, $id)
    {
        $folder = MatrizFolder::where('user_id', Auth::id())->findOrFail($id);
        
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

    // Búsqueda de matrices
    public function search(Request $request)
    {
        $query = MatrizDocument::where('user_id', Auth::id());
        
        if ($request->filled('nombre')) {
            $query->where('name', 'LIKE', '%' . $request->nombre . '%');
        }
        
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_documento', [$request->fecha_inicio, $request->fecha_fin]);
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo_documento', $request->tipo);
        }
        
        $documents = $query->orderBy('fecha_documento', 'desc')->get();
        
        if ($request->ajax()) {
            return response()->json($documents);
        }
        
        return view('matriz.search', compact('documents'));
    }
}