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
            'fecha' => $document->fecha ? $document->fecha->format('Y-m-d\TH:i') : null
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

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /**
     * Vista previa de documentos
     */
    public function viewDocument($id)
    {
        $document = DocumentalDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        $extension = strtolower($document->extension);
        $path = storage_path('app/public/' . $document->file_path);

        // Para archivos de texto, mostrar con formato adecuado
        if (in_array($extension, ['txt', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'])) {
            $content = file_get_contents($path);
            
            // Detectar si es UTF-8 o necesitamos convertir
            if (mb_detect_encoding($content, 'UTF-8', true) !== 'UTF-8') {
                $content = utf8_encode($content);
            }
            
            return response($content)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('Content-Disposition', 'inline; filename="' . $document->original_name . '"');
        }

        // Para PDF, mostrar en el navegador
        if ($extension === 'pdf') {
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);
        }

        // Para imágenes, mostrar en el navegador
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'])) {
            return response()->file($path, [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);
        }

        // Para otros tipos, forzar descarga
        return Storage::disk('public')->download($document->file_path, $document->original_name);
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

    /**
     * Eliminar carpeta y todo su contenido recursivamente
     */
    public function destroyFolder($id)
    {
        try {
            $folder = DocumentalFolder::where('user_id', Auth::id())->findOrFail($id);
            
            // Eliminar todo el contenido de la carpeta recursivamente
            $this->deleteFolderRecursively($folder);
            
            // Finalmente eliminar la carpeta
            $folder->delete();

            return redirect()->back()->with('success', 'Carpeta y todo su contenido eliminados exitosamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar la carpeta: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar recursivamente todo el contenido de una carpeta
     */
    private function deleteFolderRecursively($folder)
    {
        // Eliminar todos los documentos de la carpeta
        foreach ($folder->documents as $document) {
            // Eliminar archivo físico
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            // Eliminar registro de la base de datos
            $document->delete();
        }
        
        // Eliminar subcarpetas recursivamente
        foreach ($folder->subfolders as $subfolder) {
            $this->deleteFolderRecursively($subfolder);
            $subfolder->delete();
        }
    }

    /**
     * Renombrar una carpeta
     */
    public function renameFolder(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $folder = DocumentalFolder::where('user_id', Auth::id())->findOrFail($id);
            $folder->name = $request->name;
            $folder->save();

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta.');
        }
    }

    /**
     * Mover una carpeta
     */
    public function moveFolder(Request $request, $id)
    {
        try {
            $request->validate([
                'destination_id' => 'nullable|exists:documental_folders,id'
            ]);

            $folder = DocumentalFolder::where('user_id', Auth::id())->findOrFail($id);
            
            // Verificar que no se esté moviendo a sí misma
            if ($request->destination_id == $id) {
                return redirect()->back()->with('error', 'No puedes mover una carpeta a sí misma.');
            }

            // Verificar que la carpeta destino pertenezca al usuario
            if ($request->destination_id) {
                $destinationFolder = DocumentalFolder::where('user_id', Auth::id())->find($request->destination_id);
                if (!$destinationFolder) {
                    return redirect()->back()->with('error', 'La carpeta destino no es válida.');
                }

                // Verificar que no se está creando un ciclo
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

    /**
     * Verificar si mover una carpeta crearía un ciclo
     */
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