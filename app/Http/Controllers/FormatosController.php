<?php

namespace App\Http\Controllers;

use App\Models\FormatoFolder;
use App\Models\FormatoDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Agregar esta línea al inicio
use Illuminate\Support\Facades\Log; // Agregar esta línea al inicio

class FormatosController extends Controller
{
    /**
     * Mostrar la vista principal de Formatos
     */
    public function index(Request $request)
    {
        $folderId = $request->get('folder');
        $currentFolder = null;
        $folders = collect();
        $documents = collect();
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = FormatoFolder::with('parent')->find($folderId);
            
            if ($currentFolder) {
                // Verificar propiedad
                if ($currentFolder->user_id != Auth::id()) {
                    abort(403);
                }
                
                // Breadcrumbs
                $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
                
                // Subcarpetas
                $folders = FormatoFolder::where('parent_id', $folderId)
                    ->where('user_id', Auth::id())
                    ->orderBy('name')
                    ->get();
                
                // Documentos
                $documents = FormatoDocument::where('folder_id', $folderId)
                    ->where('user_id', Auth::id())
                    ->orderBy('fecha_documento', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } else {
            // Raíz - Mostrar carpetas principales (departamentos)
            $folders = FormatoFolder::whereNull('parent_id')
                ->where('user_id', Auth::id())
                ->orderBy('name')
                ->get();
            
            $documents = collect(); // En la raíz no mostramos documentos
        }

        return view('formatos.index', compact('folders', 'documents', 'currentFolder', 'breadcrumbs'));
    }

    /**
     * Construir migas de pan (breadcrumbs)
     */
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

    /**
     * Volver a la raíz
     */
    public function volver()
    {
        return redirect()->route('formatos.index');
    }

    /**
     * Crear una nueva carpeta
     */
    public function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string',
            'parent_id' => 'nullable|exists:formatos_folders,id'
        ]);

        FormatoFolder::create([
            'name' => $request->name,
            'color' => $request->color ?? '#16a34a',
            'parent_id' => $request->parent_id,
            'user_id' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Carpeta creada exitosamente.');
    }

    /**
     * RENOMBRAR CARPETA - NUEVO MÉTODO
     */
    public function renameFolder(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $folder = FormatoFolder::where('user_id', Auth::id())->findOrFail($id);
            $folder->name = $request->name;
            $folder->save();

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta.');
        }
    }

    /**
     * MOVER CARPETA - NUEVO MÉTODO
     */
    public function moveFolder(Request $request, $id)
    {
        try {
            $request->validate([
                'destination_id' => 'nullable|exists:formatos_folders,id'
            ]);

            $folder = FormatoFolder::where('user_id', Auth::id())->findOrFail($id);
            
            // Verificar que no se esté moviendo a sí misma
            if ($request->destination_id == $id) {
                return redirect()->back()->with('error', 'No puedes mover una carpeta a sí misma.');
            }

            // Verificar que la carpeta destino pertenezca al usuario
            if ($request->destination_id) {
                $destinationFolder = FormatoFolder::where('user_id', Auth::id())->find($request->destination_id);
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
     * Verificar si mover una carpeta crearía un ciclo - MÉTODO AUXILIAR
     */
    private function wouldCreateCycle($folder, $newParentId)
    {
        $parent = FormatoFolder::find($newParentId);
        
        while ($parent) {
            if ($parent->id == $folder->id) {
                return true;
            }
            $parent = $parent->parent;
        }
        
        return false;
    }

    /**
     * ELIMINAR CARPETA - MODIFICADO PARA ELIMINAR CON TODO SU CONTENIDO
     */
    public function destroyFolder($id)
    {
        try {
            DB::beginTransaction();

            $folder = FormatoFolder::where('user_id', Auth::id())->findOrFail($id);
            
            // Eliminar todo el contenido de la carpeta recursivamente
            $this->deleteFolderContents($folder);
            
            // Eliminar la carpeta
            $folder->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Carpeta eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar la carpeta.');
        }
    }

    /**
     * Eliminar el contenido de una carpeta recursivamente - MÉTODO AUXILIAR
     */
    private function deleteFolderContents($folder)
    {
        // Eliminar documentos de la carpeta
        $documents = FormatoDocument::where('folder_id', $folder->id)->get();
        foreach ($documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        // Eliminar subcarpetas recursivamente
        foreach ($folder->subfolders as $child) {
            $this->deleteFolderContents($child);
            $child->delete();
        }
    }

    /**
     * Subir un archivo (SIMPLIFICADO)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB máximo
            'folder_id' => 'required|exists:formatos_folders,id'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Guardar archivo
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $path = $file->storeAs('formatos/' . Auth::id(), $fileName, 'public');

        // Obtener el nombre del departamento de la carpeta padre
        $folder = FormatoFolder::find($request->folder_id);
        $departamento = $folder ? $folder->name : null;

        // Determinar tipo de documento por extensión
        $ext = strtolower($extension);
        $tipos = [
            'pdf' => 'PDF',
            'xls' => 'Excel',
            'xlsx' => 'Excel',
            'doc' => 'Word',
            'docx' => 'Word',
        ];
        $tipo = $tipos[$ext] ?? 'Documento';

        // Crear registro
        FormatoDocument::create([
            'name' => $nameWithoutExt,
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $extension,
            'folder_id' => $request->folder_id,
            'user_id' => Auth::id(),
            'departamento' => $departamento,
            'tipo_documento' => $tipo,
            'fecha_documento' => now()
        ]);

        return redirect()->back()->with('success', 'Archivo subido exitosamente.');
    }

    /**
     * Obtener datos de un documento para editar
     */
    public function getDocumentData($id)
    {
        $document = FormatoDocument::where('user_id', Auth::id())->findOrFail($id);
        
        return response()->json([
            'name' => $document->name,
            'departamento' => $document->departamento,
            'tipo_documento' => $document->tipo_documento,
            'fecha_documento' => $document->fecha_documento ? $document->fecha_documento->format('Y-m-d') : null
        ]);
    }

    /**
     * Actualizar un documento
     */
    public function updateDocument(Request $request, $id)
    {
        $document = FormatoDocument::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:50',
            'fecha_documento' => 'nullable|date'
        ]);

        $document->name = $request->name;
        $document->departamento = $request->departamento;
        $document->tipo_documento = $request->tipo_documento;
        $document->fecha_documento = $request->fecha_documento;
        $document->save();

        return redirect()->back()->with('success', 'Formato actualizado exitosamente.');
    }

    /**
     * Mover un documento a otra carpeta
     */
    public function moveDocument(Request $request, $id)
    {
        $document = FormatoDocument::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'destination_id' => 'nullable|exists:formatos_folders,id'
        ]);

        $document->folder_id = $request->destination_id;
        $document->save();

        return redirect()->back()->with('success', 'Formato movido exitosamente.');
    }

    /**
     * Descargar un documento
     */
    public function downloadDocument($id)
    {
        $document = FormatoDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name ?? $document->name . '.' . $document->extension);
    }

    /**
     * Ver un documento (vista previa)
     */
    public function viewDocument($id)
    {
        $document = FormatoDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        // Verificar si se puede previsualizar (PDF e imágenes)
        $previewableTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (in_array($document->mime_type, $previewableTypes)) {
            return response()->file(storage_path('app/public/' . $document->file_path));
        }

        return redirect()->back()->with('error', 'Vista previa no disponible para este tipo de archivo.');
    }

    /**
     * Eliminar un documento
     */
    public function destroyDocument($id)
    {
        $document = FormatoDocument::where('user_id', Auth::id())->findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->back()->with('success', 'Formato eliminado exitosamente.');
    }

    /**
     * Obtener árbol de carpetas para el modal de mover
     */
    public function getFoldersTree(Request $request)
    {
        try {
            $currentFolderId = $request->get('current_folder');
            
            $folders = FormatoFolder::where('user_id', Auth::id())
                ->where('id', '!=', $currentFolderId)
                ->orderBy('name')
                ->get()
                ->map(function($folder) {
                    return [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'full_path' => $this->getFolderPath($folder)
                    ];
                });
            
            return response()->json($folders);
        } catch (\Exception $e) {
            Log::error('Error al obtener árbol de carpetas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar carpetas'], 500);
        }
    }

    /**
     * Obtener la ruta completa de una carpeta - MÉTODO AUXILIAR
     */
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
}