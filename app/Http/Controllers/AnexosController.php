<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// CONTROLADOR PRINCIPAL QUE GESTIONA TODO EL EXPLORADOR DE ARCHIVOS Y CARPETAS (ANEXOS)
// MANEJA OPERACIONES CRUD PARA CARPETAS Y DOCUMENTOS CON CONTROL DE PERMISOS POR ROL
class AnexosController extends Controller
{
    /**
     * 
     * CONSTRUCTOR - VERIFICA LA  AUTENTICACIÓN
     * 
     * EL CONSTRUCTOR ASEGURA  QUE SOLO LOS USUARIOS LOGUEADOS PUEDAN ACCEDER
     * A CUALQUIER METODO DEL CONTROLADOR.
     */
    /**
     * METODO INDEX:
     * MESTRA EL EXPLORADOR EN LA RAIZ O DENTRO DE UNA CARPETA
     */

    
    public function index(Request $request)
    {   //OBTIENE EL USUARIO LOGUEADO
        $user = Auth::user();

        // OBTIENE EL ID DE LA CARPETA DESDE LA URL (PARÁMETRO ?folder=ID)
        // SI NO SE PASA NINGUNO, SE MUESTRA LA RAÍZ DEL EXPLORADOR
        $folderId = $request->get('folder');
        $currentFolder = null;

        if ($folderId) {
            // SI SE RECIBIÓ UN ID DE CARPETA, SE BUSCA ESA CARPETA EN LA BASE DE DATOS
            // TAMBIÉN CARGA EL PADRE DE LA CARPETA PARA CONSTRUIR EL BREADCRUMB
            $currentFolder = Folder::with('parent')->findOrFail($folderId);
            
            // Registrar visualización de carpeta
            \App\Helpers\HistorialVersionesHelper::ver('FOLDERS', $currentFolder, 'explorar');
            
            // OBTIENE TODAS LAS SUBCARPETAS QUE PERTENECEN A ESTA CARPETA
            $folders = Folder::where('parent_id', $folderId)
                             ->orderBy('name')
                             ->get();

            // OBTIENE LOS DOCUMENTOS DENTRO DE ESTA CARPETA QUE NO HAN SIDO ELIMINADOS (SOFT DELETE)
            $documents = Document::where('folder_id', $folderId)
                                 ->whereNull('deleted_at')
                                 ->orderBy('name')
                                 ->get();
        } else {
            // Registrar visualización de raíz SI UN USUARIO ESTA VIENDO UNA CARPETA
            \App\Helpers\HistorialVersionesHelper::ver('FOLDERS', null, 'raiz');
            
            // SI NO HAY FOLDER ID, SE MUESTRAN LAS CARPETAS RAÍZ (SIN CARPETA PADRE)
            $folders = Folder::whereNull('parent_id')
                             ->orderBy('name')
                             ->get();
            //OBTIENE LOS DOCUMENTOS DENTRO DE UNA CARPETA SOLO SI SON ELIMINADOS
            $documents = Document::whereNull('folder_id')
                                 ->whereNull('deleted_at')
                                 ->orderBy('name')
                                 ->get();
        }

        // CONSTRUYE EL ARRAY DE BREADCRUMBS PARA LA NAVEGACIÓN (RUTA VISUAL DE CARPETAS)
        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);

        // RETORNA LA VISTA DEL EXPLORADOR CON TODOS LOS DATOS NECESARIOS
        return view('anexos.index', compact('currentFolder', 'folders', 'documents', 'breadcrumbs'));
    }

    /**
     * Guardar nueva carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function storeFolder(Request $request)
    {
        $user = Auth::user();
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA CREAR CARPETAS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para crear carpetas.');
        }
        
        // VALIDA QUE EL NOMBRE SEA OBLIGATORIO, EL COLOR SEA UN HEX VÁLIDO Y EL PADRE EXISTA
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        // CREA LA NUEVA CARPETA EN LA BASE DE DATOS CON LOS DATOS DEL FORMULARIO
        // SI NO SE ELIGE COLOR, SE USA GRIS (#808080) POR DEFECTO
        $folder = Folder::create([
            'name' => $request->name,
            'color' => $request->color ?? '#808080',
            'parent_id' => $request->parent_id,
            'user_id' => $user->id
        ]);

        // REGISTRA EN EL HISTORIAL QUE SE CREÓ UNA NUEVA CARPETA
        \App\Helpers\HistorialVersionesHelper::crear('FOLDERS', $folder);

        // REDIRIGE DE VUELTA A LA CARPETA PADRE (O RAÍZ) CON MENSAJE DE ÉXITO
        return redirect()->route('anexos.index', ['folder' => $request->parent_id])
                         ->with('success', 'Carpeta creada correctamente.');
    }

    /**
     * Subir documento - SOLO SUPERADMIN/ADMIN
     */
    public function uploadDocument(Request $request)
    {
        $user = Auth::user();
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA SUBIR ARCHIVOS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            abort(403, 'No tienes permiso para subir archivos.');
        }
        
        // VALIDA QUE SE HAYA ENVIADO UN ARCHIVO Y QUE NO EXCEDA LOS 10MB (10240 KB)
        $request->validate([
            'file' => 'required|file|max:10240',
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        // OBTIENE EL ARCHIVO SUBIDO Y EXTRAE SU INFORMACIÓN BÁSICA
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType();
        $size = $file->getSize();

        // GENERA UN NOMBRE ÚNICO (UUID) PARA EL ARCHIVO EN EL SERVIDOR
        // LO ALMACENA EN LA CARPETA anexos/{id_usuario}/ EN EL DISCO PÚBLICO
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('anexos/' . $user->id, $fileName, 'public');

        // REGISTRA EL DOCUMENTO EN LA BASE DE DATOS CON TODOS SUS METADATOS
        $document = Document::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $mime,
            'size' => $size,
            'folder_id' => $request->folder_id,
            'user_id' => $user->id
        ]);

        // REGISTRA EN EL HISTORIAL QUE SE SUBIÓ UN NUEVO DOCUMENTO
        \App\Helpers\HistorialVersionesHelper::subir('DOCUMENTS', $document);

        // REDIRIGE DE VUELTA A LA CARPETA CON MENSAJE DE ÉXITO
        return redirect()->route('anexos.index', ['folder' => $request->folder_id])
                         ->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Eliminar carpeta - SOLO SUPERADMIN/ADMIN (SOFT DELETE)
     */
    public function destroyFolder($id)
    {
        $user = Auth::user();
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA ELIMINAR CARPETAS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permiso para eliminar carpetas.'
            ], 403);
        }
        
        try {
            // BUSCA LA CARPETA POR ID. SI NO EXISTE, LANZA UN ERROR 404
            $folder = Folder::findOrFail($id);
            $parentId = $folder->parent_id;

            // GUARDA LOS DATOS DE LA CARPETA ANTES DE ELIMINARLA (PARA EL HISTORIAL)
            $folderData = $folder->toArray();

            // SOFT DELETE DE TODOS LOS DOCUMENTOS DIRECTAMENTE DENTRO DE LA CARPETA
            foreach ($folder->documents as $doc) {
                $doc->delete();
            }

            // SOFT DELETE RECURSIVO DE TODAS LAS SUBCARPETAS Y SUS DOCUMENTOS
            $this->recursiveDeleteFolders($folder);
            
            // SOFT DELETE DE LA CARPETA PRINCIPAL (NO SE BORRA FÍSICAMENTE DE LA BD)
            $folder->delete();

            // REGISTRA EN EL HISTORIAL QUE SE ELIMINÓ LA CARPETA
            \App\Helpers\HistorialVersionesHelper::eliminar('FOLDERS', $folder, $folderData);

            // RETORNA JSON CON ÉXITO E ID DE LA CARPETA PADRE PARA REDIRIGIR EN EL FRONTEND
            return response()->json([
                'success' => true,
                'message' => 'Carpeta eliminada correctamente.',
                'parent_id' => $parentId
            ]);
        } catch (\Exception $e) {
            // SI OCURRE CUALQUIER ERROR, RETORNA EL MENSAJE EN FORMATO JSON CON CÓDIGO 500
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
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA ELIMINAR DOCUMENTOS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes permiso para eliminar documentos.'
            ], 403);
        }
        
        try {
            // BUSCA EL DOCUMENTO POR ID. SI NO EXISTE, LANZA UN ERROR 404
            $document = Document::findOrFail($id);
            $folderId = $document->folder_id;

            // GUARDA LOS DATOS DEL DOCUMENTO ANTES DE ELIMINARLO (PARA EL HISTORIAL)
            $documentData = $document->toArray();

            // Soft delete - NO elimina el archivo físico
            $document->delete();

            // REGISTRA EN EL HISTORIAL QUE SE ELIMINÓ EL DOCUMENTO
            \App\Helpers\HistorialVersionesHelper::eliminar('DOCUMENTS', $document, $documentData);

            // RETORNA JSON CON ÉXITO E ID DE LA CARPETA CONTENEDORA PARA EL FRONTEND
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente.',
                'folder_id' => $folderId
            ]);
        } catch (\Exception $e) {
            // SI OCURRE CUALQUIER ERROR, RETORNA EL MENSAJE EN FORMATO JSON CON CÓDIGO 500
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
        // BUSCA EL DOCUMENTO INCLUYENDO LOS ELIMINADOS (withTrashed) PARA PERMITIR DESCARGAS
        $document = Document::withTrashed()->findOrFail($id);
        
        // VERIFICA QUE EL ARCHIVO FÍSICO EXISTA EN EL SERVIDOR ANTES DE DESCARGARLO
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'El archivo no existe en el servidor.');
        }

        // REGISTRA EN EL HISTORIAL QUE EL DOCUMENTO FUE DESCARGADO
        \App\Helpers\HistorialVersionesHelper::descargar('DOCUMENTS', $document);

        // FUERZA LA DESCARGA DEL ARCHIVO CON SU NOMBRE ORIGINAL
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /**
     * Ver documento en el navegador - TODOS pueden ver (solo formatos permitidos)
     */
    public function viewDocument($id)
    {
        // BUSCA EL DOCUMENTO INCLUYENDO LOS ELIMINADOS PARA PERMITIR VISUALIZACIÓN
        $document = Document::withTrashed()->findOrFail($id);
        
        // REGISTRA EN EL HISTORIAL QUE EL DOCUMENTO FUE VISUALIZADO
        \App\Helpers\HistorialVersionesHelper::ver('DOCUMENTS', $document, 'visualizar');
        
        // EXTRAE LA EXTENSIÓN DEL ARCHIVO ORIGINAL EN MINÚSCULAS
        $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
        
        // LISTA DE EXTENSIONES QUE SE PUEDEN PREVISUALIZAR DIRECTAMENTE EN EL NAVEGADOR
        $viewableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'txt'];
        
        // SI LA EXTENSIÓN NO ES PREVISUALIZABLE, SE FUERZA LA DESCARGA EN SU LUGAR
        if (!in_array($extension, $viewableExtensions)) {
            return $this->downloadDocument($id);
        }
        
        // CONSTRUYE LA RUTA ABSOLUTA DEL ARCHIVO EN EL SERVIDOR
        $path = storage_path('app/public/' . $document->file_path);
        
        // VERIFICA QUE EL ARCHIVO EXISTA FÍSICAMENTE EN EL SERVIDOR
        if (!file_exists($path)) {
            abort(404, 'El archivo no existe en el servidor');
        }
        
        // MAPEO DE EXTENSIONES A TIPOS MIME PARA INDICARLE AL NAVEGADOR CÓMO MOSTRAR EL ARCHIVO
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
        
        // OBTIENE EL CONTENT-TYPE CORRESPONDIENTE O USA UN TIPO GENÉRICO SI NO SE ENCUENTRA
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        
        // RETORNA EL ARCHIVO COMO RESPUESTA INLINE (SE ABRE EN EL NAVEGADOR, NO SE DESCARGA)
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
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA RENOMBRAR CARPETAS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para renombrar carpetas.'], 403);
        }
        
        // VALIDA QUE EL NUEVO NOMBRE SEA OBLIGATORIO Y NO EXCEDA 255 CARACTERES
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // BUSCA LA CARPETA POR ID. SI NO EXISTE, LANZA UN ERROR 404
        $folder = Folder::findOrFail($id);
        
        // GUARDA LOS DATOS ANTERIORES DE LA CARPETA ANTES DE MODIFICARLA (PARA EL HISTORIAL)
        $datosAnteriores = $folder->toArray();
        
        // ACTUALIZA EL NOMBRE DE LA CARPETA Y LO GUARDA EN LA BASE DE DATOS
        $folder->name = $request->name;
        $folder->save();

        // REGISTRA EN EL HISTORIAL EL CAMBIO DE NOMBRE (DATOS ANTERIORES VS DATOS NUEVOS)
        \App\Helpers\HistorialVersionesHelper::editar('FOLDERS', $folder, $datosAnteriores, $folder->toArray());

        // REDIRIGE DE VUELTA A LA CARPETA PADRE CON MENSAJE DE ÉXITO
        return redirect()->route('anexos.index', ['folder' => $folder->parent_id])
                         ->with('success', 'Carpeta renombrada correctamente.');
    }

    /**
     * Mover carpeta - SOLO SUPERADMIN/ADMIN
     */
    public function moveFolder(Request $request, $id)
    {
        $user = Auth::user();
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA MOVER CARPETAS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover carpetas.'], 403);
        }
        
        // VALIDA QUE EL DESTINO (SI SE PASA) EXISTA EN LA BASE DE DATOS
        $request->validate([
            'destination_id' => 'nullable|exists:folders,id'
        ]);

        // BUSCA LA CARPETA QUE SE QUIERE MOVER
        $folder = Folder::findOrFail($id);
        
        // EVITA QUE UNA CARPETA SE MUEVA A SÍ MISMA
        if ($request->destination_id == $folder->id) {
            return back()->with('error', 'No puedes mover una carpeta a sí misma.');
        }
        
        // GUARDA REFERENCIAS AL ORIGEN Y DESTINO PARA EL REGISTRO EN EL HISTORIAL
        // SI EL ORIGEN O DESTINO ES NULL, SE USA 'RAÍZ' COMO NOMBRE
        $origen = $folder->parent_id ? Folder::find($folder->parent_id) : (object)['name' => 'Raíz'];
        $destino = $request->destination_id ? Folder::find($request->destination_id) : (object)['name' => 'Raíz'];
        
        // VERIFICA QUE NO SE ESTÉ INTENTANDO MOVER LA CARPETA DENTRO DE UNA DE SUS PROPIAS SUBCARPETAS
        if ($request->destination_id) {
            $destination = Folder::find($request->destination_id);
            $isSubfolder = $this->isSubfolder($folder->id, $destination);
            if ($isSubfolder) {
                return back()->with('error', 'No puedes mover una carpeta a una subcarpeta.');
            }
        }
        
        // ACTUALIZA EL PADRE DE LA CARPETA AL NUEVO DESTINO Y GUARDA EN BASE DE DATOS
        $folder->parent_id = $request->destination_id;
        $folder->save();

        // REGISTRA EN EL HISTORIAL EL MOVIMIENTO (ORIGEN → DESTINO)
        \App\Helpers\HistorialVersionesHelper::mover('FOLDERS', $folder, $origen, $destino);

        // REDIRIGE A LA CARPETA DESTINO CON MENSAJE DE ÉXITO
        return redirect()->route('anexos.index', ['folder' => $request->destination_id])
                        ->with('success', 'Carpeta movida correctamente.');
    }

    /**
     * Verificar subcarpeta
     */
    private function isSubfolder($folderId, $destination)
    {
        // SI EL DESTINO NO EXISTE, NO ES SUBCARPETA
        if (!$destination) return false;
        
        // RECORRE TODOS LOS PADRES DEL DESTINO HACIA ARRIBA
        // SI EN ALGÚN PUNTO ENCUENTRA LA CARPETA QUE SE QUIERE MOVER, SIGNIFICA QUE ES SUBCARPETA
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

        // OBTIENE EL ID DE LA CARPETA ACTUAL PARA EXCLUIRLA DEL ÁRBOL (NO PUEDE SER SU PROPIO DESTINO)
        $currentFolderId = $request->get('current_folder');
        
        // SOLO SUPERADMIN Y ADMIN PUEDEN VER EL ÁRBOL COMPLETO DE CARPETAS
        if (in_array($user->role, ['superadmin', 'admin'])) {
            $folders = Folder::where('id', '!=', $currentFolderId)
                            ->orderBy('name')
                            ->get();
        } else {
            // SI EL USUARIO NO TIENE PERMISOS, RETORNA UN ARRAY VACÍO
            return response()->json([]);
        }
        
        // TRANSFORMA CADA CARPETA EN UN ARRAY CON SU RUTA COMPLETA PARA MOSTRAR EN EL SELECTOR
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

    // MÉTODO AUXILIAR QUE CONSTRUYE LA RUTA COMPLETA DE UNA CARPETA (EJ: "Raíz / Documentos / Legal")
    private function getFolderPath($folder)
    {
        $path = [];
        $current = $folder;

        // RECORRE HACIA ARRIBA TODA LA JERARQUÍA DE PADRES HASTA LLEGAR A LA RAÍZ
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
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA RENOMBRAR DOCUMENTOS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para renombrar documentos.'], 403);
        }
        
        // VALIDA QUE EL NUEVO NOMBRE SEA OBLIGATORIO Y NO EXCEDA 255 CARACTERES
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // BUSCA EL DOCUMENTO POR ID. SI NO EXISTE, LANZA UN ERROR 404
        $document = Document::findOrFail($id);
        
        // GUARDA LOS DATOS ANTERIORES DEL DOCUMENTO ANTES DE MODIFICARLO (PARA EL HISTORIAL)
        $datosAnteriores = $document->toArray();
        
        // MANTIENE LA EXTENSIÓN ORIGINAL DEL ARCHIVO Y SOLO CAMBIA EL NOMBRE VISIBLE
        $extension = pathinfo($document->original_name, PATHINFO_EXTENSION);
        $document->name = $request->name;
        $document->original_name = $request->name . '.' . $extension;
        $document->save();

        // REGISTRA EN EL HISTORIAL EL CAMBIO DE NOMBRE (DATOS ANTERIORES VS DATOS NUEVOS)
        \App\Helpers\HistorialVersionesHelper::editar('DOCUMENTS', $document, $datosAnteriores, $document->toArray());

        return redirect()->back()->with('success', 'Documento renombrado correctamente.');
    }

    /**
     * Mover documento - SOLO SUPERADMIN/ADMIN
     */
    public function moveDocument(Request $request, $id)
    {
        $user = Auth::user();
        
        // VERIFICA QUE EL USUARIO TENGA ROL DE SUPERADMIN O ADMIN PARA MOVER DOCUMENTOS
        if (!in_array($user->role, ['superadmin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover documentos.'], 403);
        }
        
        // VALIDA QUE EL DESTINO (SI SE PASA) EXISTA EN LA BASE DE DATOS
        $request->validate([
            'destination_id' => 'nullable|exists:folders,id'
        ]);

        // BUSCA EL DOCUMENTO QUE SE QUIERE MOVER
        $document = Document::findOrFail($id);
        
        // GUARDA REFERENCIAS AL ORIGEN Y DESTINO PARA EL REGISTRO EN EL HISTORIAL
        // SI EL ORIGEN O DESTINO ES NULL, SE USA 'RAÍZ' COMO NOMBRE
        $origen = $document->folder_id ? Folder::find($document->folder_id) : (object)['name' => 'Raíz'];
        $destino = $request->destination_id ? Folder::find($request->destination_id) : (object)['name' => 'Raíz'];
        
        // ACTUALIZA LA CARPETA DEL DOCUMENTO AL NUEVO DESTINO Y GUARDA EN BASE DE DATOS
        $document->folder_id = $request->destination_id;
        $document->save();

        // REGISTRA EN EL HISTORIAL EL MOVIMIENTO DEL DOCUMENTO (ORIGEN → DESTINO)
        \App\Helpers\HistorialVersionesHelper::mover('DOCUMENTS', $document, $origen, $destino);

        return redirect()->back()->with('success', 'Documento movido correctamente.');
    }

    // MÉTODO AUXILIAR QUE CONSTRUYE EL ARRAY DE BREADCRUMBS PARA LA NAVEGACIÓN
    // RECORRE LA JERARQUÍA DE CARPETAS DESDE LA ACTUAL HASTA LA RAÍZ
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

    // MÉTODO AUXILIAR RECURSIVO QUE HACE SOFT DELETE DE TODAS LAS SUBCARPETAS Y SUS DOCUMENTOS
    // SE LLAMA ANTES DE ELIMINAR UNA CARPETA PADRE PARA LIMPIAR TODA SU JERARQUÍA
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