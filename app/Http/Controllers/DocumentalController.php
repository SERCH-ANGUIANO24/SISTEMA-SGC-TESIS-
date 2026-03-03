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
        $userRole = Auth::user()->role;
        $userId = Auth::id();

        if ($folderId) {
            $currentFolder = DocumentalFolder::with('parent')->find($folderId);
            
            if ($currentFolder) {
                // Breadcrumbs
                $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
                
                // SUBCARPETAS - TODOS pueden ver TODAS las subcarpetas
                $folders = DocumentalFolder::where('parent_id', $folderId)
                    ->orderBy('name')
                    ->get();
                
                // DOCUMENTOS - Usuarios solo ven sus documentos, admin ve todos
                $documentsQuery = DocumentalDocument::where('folder_id', $folderId);
                
                // Si es usuario normal, solo ve sus propios documentos
                if (!in_array($userRole, ['superadmin', 'admin'])) {
                    $documentsQuery->where('user_id', $userId);
                }
                
                $documents = $documentsQuery->orderBy('created_at', 'desc')->get();
            }
        } else {
            // RAÍZ - TODOS pueden ver TODAS las carpetas de todos
            $folders = DocumentalFolder::whereNull('parent_id')
                ->orderBy('name')
                ->get();
            
            // DOCUMENTOS EN RAÍZ - Usuarios solo ven sus documentos, admin ve todos
            $documentsQuery = DocumentalDocument::whereNull('folder_id');
            
            // Si es usuario normal, solo ve sus propios documentos
            if (!in_array($userRole, ['superadmin', 'admin'])) {
                $documentsQuery->where('user_id', $userId);
            }
            
            $documents = $documentsQuery->orderBy('created_at', 'desc')->get();
        }

        return view('documental.index', compact(
            'folders', 
            'documents', 
            'currentFolder', 
            'breadcrumbs',
            'userRole'
        ));
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
        // Solo superadmin y admin pueden crear carpetas
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para crear carpetas.');
        }

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
        // Solo usuarios normales pueden subir archivos
        if (in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'Solo los usuarios pueden subir archivos.');
        }

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

        // Crear registro - CAMBIADO DE 'No Valido' A 'Pendiente'
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
            'estatus' => 'Pendiente', // ← CAMBIADO AQUÍ
            'fecha' => now()
        ]);

        return redirect()->back()->with('success', 'Archivo subido exitosamente.');
    }

    public function getDocumentData($id)
    {
        // Admin puede ver datos de cualquier documento
        $query = DocumentalDocument::query();
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            $query->where('user_id', Auth::id());
        }
        $document = $query->findOrFail($id);
        
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
        // Solo superadmin y admin pueden validar
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para validar documentos.');
        }

        $document = DocumentalDocument::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'proceso' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'estatus' => 'required|in:Pendiente,Valido,No Valido', // ← AGREGADO Pendiente
            'observaciones' => 'nullable|string',
            'fecha' => 'nullable|date'
        ]);

        $data = $request->all();
        
        // Si el estatus es "Valido", limpiar observaciones
        if ($request->estatus === 'Valido') {
            $data['observaciones'] = null;
        }
        
        $document->update($data);

        return redirect()->back()->with('success', 'Documento actualizado exitosamente.');
    }

    public function moveDocument(Request $request, $id)
    {
        // Solo superadmin y admin pueden mover documentos
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para mover documentos.');
        }

        $document = DocumentalDocument::findOrFail($id);
        
        $request->validate([
            'destination_id' => 'nullable|exists:documental_folders,id'
        ]);

        $document->folder_id = $request->destination_id;
        $document->save();

        return redirect()->back()->with('success', 'Documento movido exitosamente.');
    }

    public function downloadDocument($id)
    {
        // Todos pueden descargar
        $query = DocumentalDocument::query();
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            $query->where('user_id', Auth::id());
        }
        $document = $query->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function viewDocument($id)
    {
        // Todos pueden ver
        $query = DocumentalDocument::query();
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            $query->where('user_id', Auth::id());
        }
        $document = $query->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        $extension = strtolower($document->extension);
        $path = storage_path('app/public/' . $document->file_path);

        if (in_array($extension, ['txt', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'])) {
            $content = file_get_contents($path);
            
            if (mb_detect_encoding($content, 'UTF-8', true) !== 'UTF-8') {
                $content = utf8_encode($content);
            }
            
            return response($content)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('Content-Disposition', 'inline; filename="' . $document->original_name . '"');
        }

        if ($extension === 'pdf') {
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'])) {
            return response()->file($path, [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroyDocument($id)
    {
        // Solo superadmin y admin pueden eliminar
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar documentos.');
        }

        $document = DocumentalDocument::findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->back()->with('success', 'Documento eliminado exitosamente.');
    }

    public function getFoldersTree(Request $request)
    {
        $currentFolderId = $request->get('current_folder');
        
        // Admin ve todas las carpetas
        $foldersQuery = DocumentalFolder::where('id', '!=', $currentFolderId);
        
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            $foldersQuery->where('user_id', Auth::id());
        }
        
        $folders = $foldersQuery->get()
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
        // Solo superadmin y admin pueden eliminar
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para eliminar carpetas.');
        }

        try {
            $folder = DocumentalFolder::findOrFail($id);
            
            $this->deleteFolderRecursively($folder);
            
            $folder->delete();

            return redirect()->back()->with('success', 'Carpeta y todo su contenido eliminados exitosamente.');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar la carpeta: ' . $e->getMessage());
        }
    }

    private function deleteFolderRecursively($folder)
    {
        foreach ($folder->documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }
        
        foreach ($folder->subfolders as $subfolder) {
            $this->deleteFolderRecursively($subfolder);
            $subfolder->delete();
        }
    }

    public function renameFolder(Request $request, $id)
    {
        // Solo superadmin y admin pueden renombrar
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para renombrar carpetas.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $folder = DocumentalFolder::findOrFail($id);
            $folder->name = $request->name;
            $folder->save();

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta.');
        }
    }

    public function moveFolder(Request $request, $id)
    {
        // Solo superadmin y admin pueden mover
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para mover carpetas.');
        }

        try {
            $request->validate([
                'destination_id' => 'nullable|exists:documental_folders,id'
            ]);

            $folder = DocumentalFolder::findOrFail($id);
            
            if ($request->destination_id == $id) {
                return redirect()->back()->with('error', 'No puedes mover una carpeta a sí misma.');
            }

            if ($request->destination_id) {
                $destinationFolder = DocumentalFolder::find($request->destination_id);
                if (!$destinationFolder) {
                    return redirect()->back()->with('error', 'La carpeta destino no es válida.');
                }

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