<?php
// app/Http/Controllers/Auditoria/CompetenciaController.php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Competencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // ← ya tienes Auth, agrégalo junto
use Illuminate\Support\Facades\Log;

// CONTROLADOR QUE GESTIONA EL EXPLORADOR DE CARPETAS Y DOCUMENTOS DEL MÓDULO DE COMPETENCIAS
// PERMITE CREAR, RENOMBRAR, MOVER, ELIMINAR, RESTAURAR, DESCARGAR Y VISUALIZAR CARPETAS Y DOCUMENTOS
class CompetenciaController extends Controller
{
    // MÉTODO AUXILIAR PRIVADO QUE VERIFICA SI EL USUARIO ACTUAL TIENE ROL DE SUPERADMIN O ADMIN
    // SE USA PARA CONTROLAR QUIÉN PUEDE HACER MODIFICACIONES EN EL MÓDULO
    private function canModify()
    {
        return in_array(Auth::user()->role, ['superadmin', 'admin']);
    }

    public function index(Request $request)
    {
        // OBTIENE EL ID DE LA CARPETA DESDE LA URL (PARÁMETRO ?folder=ID)
        // SI NO SE PASA NINGUNO, SE MUESTRA LA RAÍZ DEL EXPLORADOR
        $parentId = $request->get('folder', null);
        $userRole = Auth::user()->role;
        
        if ($parentId) {
            // BUSCA LA CARPETA ACTUAL JUNTO CON SUS HIJOS Y DOCUMENTOS HIJOS
            $currentFolder = Competencia::with(['children', 'documentosHijos'])->findOrFail($parentId);

            // VERIFICA QUE EL ELEMENTO ENCONTRADO SEA UNA CARPETA Y NO UN DOCUMENTO
            if (!$currentFolder->isFolder()) {
                abort(404, 'El elemento solicitado no es una carpeta');
            }

            // REGISTRA EN EL HISTORIAL QUE EL USUARIO EXPLORÓ ESTA CARPETA
            \App\Helpers\HistorialVersionesHelper::ver('COMPETENCIAS', $currentFolder, 'explorar');
            
            // OBTIENE LAS SUBCARPETAS DENTRO DE LA CARPETA ACTUAL, ORDENADAS ALFABÉTICAMENTE
            $folders = Competencia::with(['children', 'documentosHijos'])
                ->folders()
                ->where('parent_id', $parentId)
                ->orderBy('nombre')
                ->get();
                
            // OBTIENE LOS DOCUMENTOS DENTRO DE LA CARPETA ACTUAL, ORDENADOS ALFABÉTICAMENTE
            $documents = Competencia::documents()
                ->where('parent_id', $parentId)
                ->orderBy('nombre')
                ->get();
                
            // CONSTRUYE EL BREADCRUMB PARA MOSTRAR LA RUTA DE NAVEGACIÓN
            $breadcrumbs = $this->getBreadcrumbs($currentFolder);
        } else {
            // REGISTRA EN EL HISTORIAL QUE EL USUARIO VISITÓ LA RAÍZ DEL EXPLORADOR
            \App\Helpers\HistorialVersionesHelper::ver('COMPETENCIAS', null, 'raiz');
            
            $currentFolder = null;
            
            // OBTIENE LAS CARPETAS RAÍZ (SIN CARPETA PADRE), ORDENADAS ALFABÉTICAMENTE
            $folders = Competencia::with(['children', 'documentosHijos'])
                ->folders()
                ->whereNull('parent_id')
                ->orderBy('nombre')
                ->get();
                
            // OBTIENE LOS DOCUMENTOS EN LA RAÍZ (SIN CARPETA PADRE), ORDENADOS ALFABÉTICAMENTE
            $documents = Competencia::documents()
                ->whereNull('parent_id')
                ->orderBy('nombre')
                ->get();
                
            // EN LA RAÍZ NO HAY BREADCRUMBS, SE RETORNA UNA COLECCIÓN VACÍA
            $breadcrumbs = collect();
        }
        
        // RETORNA LA VISTA DEL EXPLORADOR CON TODOS LOS DATOS NECESARIOS
        return view('auditoria.competencias.index', compact(
            'folders', 
            'documents', 
            'currentFolder', 
            'breadcrumbs',
            'userRole'
        ));
    }

    public function storeFolder(Request $request)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) {
            abort(403, 'No tienes permiso para acceder al módulo de Auditorías.');
        }

        // VALIDA QUE EL NOMBRE SEA OBLIGATORIO, EL COLOR SEA VÁLIDO Y EL PADRE EXISTA EN LA BD
        $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            // CREA LA NUEVA CARPETA EN LA BASE DE DATOS
            // SI NO SE ELIGE COLOR, SE USA GUINDA (#800000) POR DEFECTO
            $folder = Competencia::create([
                'nombre' => $request->nombre,
                'tipo' => 'carpeta',  // ← IMPORTANTE: se guarda como carpeta
                'color' => $request->color ?? '#800000',
                'parent_id' => $request->parent_id
            ]);

            // Registrar en historial como carpeta
            \App\Helpers\HistorialVersionesHelper::crear('COMPETENCIAS', $folder);

            return redirect()->back()->with('success', 'Carpeta creada exitosamente');
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA MENSAJE DE ERROR AL USUARIO
            Log::error('Error al crear carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la carpeta');
        }
    }

    public function uploadDocument(Request $request)
    {
        // VALIDA QUE SE HAYA ENVIADO UN ARCHIVO Y QUE NO EXCEDA 20MB
        // TAMBIÉN VALIDA QUE LA CARPETA DESTINO EXISTA SI SE PROPORCIONA
        $request->validate([
            'archivo' => 'required|file|max:20480',
            'parent_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();

            // SEPARA EL NOMBRE DEL ARCHIVO DE SU EXTENSIÓN PARA GUARDARLOS POR SEPARADO
            $nombreSinExtension = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            
            // GENERA UN NOMBRE ÚNICO PARA EL ARCHIVO FÍSICO USANDO TIMESTAMP + UNIQID
            // ESTO EVITA COLISIONES SI DOS ARCHIVOS TIENEN EL MISMO NOMBRE ORIGINAL
            $nombreArchivoFisico = time() . '_' . uniqid() . '.' . $extension;
            $ruta = $file->storeAs('competencias', $nombreArchivoFisico, 'public');

            // CREA EL REGISTRO DEL DOCUMENTO EN LA BASE DE DATOS CON TODOS SUS METADATOS
            $document = Competencia::create([
                'nombre' => $nombreSinExtension,
                'tipo' => 'documento',  // ← IMPORTANTE: se guarda como documento
                'archivo_nombre' => $nombreArchivoFisico,
                'archivo_ruta' => $ruta,
                'archivo_original' => $originalName,
                'archivo_tamano' => $file->getSize(),
                'archivo_extension' => $extension,
                'parent_id' => $request->parent_id
            ]);

            // Registrar en historial como documento
            \App\Helpers\HistorialVersionesHelper::subir('COMPETENCIAS', $document);

            return redirect()->back()->with('success', 'Documento subido exitosamente');
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA MENSAJE DE ERROR AL USUARIO
            Log::error('Error al subir documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al subir el documento');
        }
    }

    public function renameFolder(Request $request, $id)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) {
            abort(403, 'No tienes permiso para acceder al módulo de Auditorías.');
        }

        // VALIDA QUE EL NUEVO NOMBRE SEA OBLIGATORIO Y NO EXCEDA 255 CARACTERES
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        try {
            // BUSCA LA CARPETA POR ID. SI NO EXISTE, LANZA UN ERROR 404
            $carpeta = Competencia::findOrFail($id);

            // VERIFICA QUE EL ELEMENTO SEA UNA CARPETA Y NO UN DOCUMENTO
            if (!$carpeta->isFolder()) {
                return redirect()->back()->with('error', 'El elemento no es una carpeta');
            }

            // GUARDA LOS DATOS ANTERIORES DE LA CARPETA ANTES DE MODIFICARLA (PARA EL HISTORIAL)
            $datosAnteriores = $carpeta->toArray();
            $carpeta->nombre = $request->nombre;
            $carpeta->save();

            // Registrar en historial - edición de carpeta
            \App\Helpers\HistorialVersionesHelper::editar('COMPETENCIAS', $carpeta, $datosAnteriores, $carpeta->toArray());

            return redirect()->back()->with('success', 'Carpeta renombrada exitosamente');
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA MENSAJE DE ERROR AL USUARIO
            Log::error('Error al renombrar carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar la carpeta');
        }
    }

    public function renameDocument(Request $request, $id)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) {
            abort(403, 'No tienes permiso para acceder al módulo de Auditorías.');
        }

        // VALIDA QUE EL NUEVO NOMBRE SEA OBLIGATORIO Y NO EXCEDA 255 CARACTERES
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        try {
            // BUSCA EL DOCUMENTO POR ID. SI NO EXISTE, LANZA UN ERROR 404
            $documento = Competencia::findOrFail($id);

            // VERIFICA QUE EL ELEMENTO SEA UN DOCUMENTO Y NO UNA CARPETA
            if (!$documento->isDocument()) {
                return redirect()->back()->with('error', 'El elemento no es un documento');
            }
            
            // GUARDA LOS DATOS ANTERIORES DEL DOCUMENTO ANTES DE MODIFICARLO (PARA EL HISTORIAL)
            $datosAnteriores = $documento->toArray();
            $extension = $documento->archivo_extension;

            // CONSTRUYE EL NUEVO NOMBRE COMPLETO CONSERVANDO LA EXTENSIÓN ORIGINAL DEL ARCHIVO
            $nuevoNombreCompleto = $request->nombre . '.' . $extension;
            
            $documento->nombre = $request->nombre;
            $documento->archivo_original = $nuevoNombreCompleto;
            $documento->save();

            // Registrar en historial - edición de documento
            \App\Helpers\HistorialVersionesHelper::editar('COMPETENCIAS', $documento, $datosAnteriores, $documento->toArray());

            return redirect()->back()->with('success', 'Documento renombrado exitosamente');
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA MENSAJE DE ERROR AL USUARIO
            Log::error('Error al renombrar documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al renombrar el documento');
        }
    }

    public function moveFolder(Request $request, $id)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA MOVER CARPETAS
        if (!Gate::allows('auditoria-access')) {
            abort(403, 'No tienes permiso para mover carpetas.');
        }

        // VALIDA QUE EL DESTINO (SI SE PASA) EXISTA EN LA BASE DE DATOS
        $request->validate([
            'destination_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            // BUSCA LA CARPETA QUE SE QUIERE MOVER Y VERIFICA QUE SEA UNA CARPETA
            $carpeta = Competencia::findOrFail($id);
            if (!$carpeta->isFolder()) {
                return redirect()->back()->with('error', 'El elemento no es una carpeta');
            }

            // GUARDA REFERENCIAS AL ORIGEN Y DESTINO PARA EL REGISTRO EN EL HISTORIAL
            $origen = $carpeta->parent_id ? Competencia::find($carpeta->parent_id) : null;
            $destino = $request->destination_id ? Competencia::find($request->destination_id) : null;

            if ($request->destination_id) {
                $destino = Competencia::findOrFail($request->destination_id);

                // VERIFICA QUE EL DESTINO SEA UNA CARPETA Y NO UN DOCUMENTO
                if (!$destino->isFolder()) {
                    return redirect()->back()->with('error', 'El destino debe ser una carpeta');
                }

                // EVITA QUE UNA CARPETA SE MUEVA A SÍ MISMA
                if ($carpeta->id == $request->destination_id) {
                    return redirect()->back()->with('error', 'No puedes mover una carpeta a sí misma');
                }

                // VERIFICA QUE EL DESTINO NO SEA UNA SUBCARPETA DE LA CARPETA QUE SE MUEVE
                $descendantIds = $this->getAllDescendantFolderIds($carpeta->id);
                if (in_array($request->destination_id, $descendantIds)) {
                    return redirect()->back()->with('error', 'No puedes mover una carpeta a una de sus subcarpetas');
                }
            }

            // ACTUALIZA EL PADRE DE LA CARPETA AL NUEVO DESTINO Y LO GUARDA EN LA BD
            $carpeta->parent_id = $request->destination_id;
            $carpeta->save();

            // Registrar en historial - movimiento de carpeta
            \App\Helpers\HistorialVersionesHelper::mover('COMPETENCIAS', $carpeta, $origen ?? (object)['nombre' => 'Raíz'], $destino ?? (object)['nombre' => 'Raíz']);

            return redirect()->back()->with('success', 'Carpeta movida correctamente.');
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA MENSAJE DE ERROR AL USUARIO
            Log::error('Error al mover carpeta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover la carpeta');
        }
    }

    public function moveDocument(Request $request, $id)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA MOVER DOCUMENTOS
        if (!Gate::allows('auditoria-access')) {
            abort(403, 'No tienes permiso para mover documentos');
        }

        // VALIDA QUE EL DESTINO (SI SE PASA) EXISTA EN LA BASE DE DATOS
        $request->validate([
            'destination_id' => 'nullable|exists:competencias,id'
        ]);

        try {
            // BUSCA EL DOCUMENTO QUE SE QUIERE MOVER Y VERIFICA QUE SEA UN DOCUMENTO
            $documento = Competencia::findOrFail($id);
            if (!$documento->isDocument()) {
                return redirect()->back()->with('error', 'El elemento no es un documento');
            }

            // GUARDA REFERENCIAS AL ORIGEN Y DESTINO PARA EL REGISTRO EN EL HISTORIAL
            $origen = $documento->parent_id ? Competencia::find($documento->parent_id) : null;
            $destino = $request->destination_id ? Competencia::find($request->destination_id) : null;

            if ($request->destination_id) {
                $destino = Competencia::findOrFail($request->destination_id);

                // VERIFICA QUE EL DESTINO SEA UNA CARPETA Y NO UN DOCUMENTO
                if (!$destino->isFolder()) {
                    return redirect()->back()->with('error', 'El destino debe ser una carpeta');
                }
            }

            // ACTUALIZA LA CARPETA DEL DOCUMENTO AL NUEVO DESTINO Y LO GUARDA EN LA BD
            $documento->parent_id = $request->destination_id;
            $documento->save();

            // Registrar en historial - movimiento de documento
            \App\Helpers\HistorialVersionesHelper::mover('COMPETENCIAS', $documento, $origen ?? (object)['nombre' => 'Raíz'], $destino ?? (object)['nombre' => 'Raíz']);

            return redirect()->back()->with('success', 'Documento movido correctamente.');
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA MENSAJE DE ERROR AL USUARIO
            Log::error('Error al mover documento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al mover el documento');
        }
    }

    public function downloadDocument($id)
    {
        try {
            // BUSCA EL DOCUMENTO POR ID INCLUYENDO LOS ELIMINADOS (withTrashed)
            $documento = Competencia::withTrashed()->findOrFail($id);

            // VERIFICA QUE EL ELEMENTO SEA UN DOCUMENTO Y NO UNA CARPETA
            if (!$documento->isDocument()) {
                abort(404, 'El elemento no es un documento');
            }

            // VERIFICA QUE EL ARCHIVO FÍSICO EXISTA EN EL SERVIDOR ANTES DE DESCARGARLO
            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                abort(404, 'El archivo no existe en el servidor');
            }

            // REGISTRA EN EL HISTORIAL QUE EL DOCUMENTO FUE DESCARGADO
            \App\Helpers\HistorialVersionesHelper::descargar('COMPETENCIAS', $documento);

            // FUERZA LA DESCARGA DEL ARCHIVO CON SU NOMBRE ORIGINAL
            return Storage::disk('public')->download($documento->archivo_ruta, $documento->archivo_original);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA ERROR 500
            Log::error('Error al descargar: ' . $e->getMessage());
            abort(500, 'Error al descargar el archivo');
        }
    }

    public function viewDocument($id)
    {
        try {
            // BUSCA EL DOCUMENTO POR ID INCLUYENDO LOS ELIMINADOS (withTrashed)
            $documento = Competencia::withTrashed()->findOrFail($id);

            // VERIFICA QUE EL ELEMENTO SEA UN DOCUMENTO Y NO UNA CARPETA
            if (!$documento->isDocument()) {
                abort(404, 'El elemento no es un documento');
            }

            // VERIFICA QUE EL ARCHIVO FÍSICO EXISTA EN EL SERVIDOR ANTES DE VISUALIZARLO
            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                abort(404, 'El archivo no existe en el servidor');
            }

            // REGISTRA EN EL HISTORIAL QUE EL DOCUMENTO FUE VISUALIZADO
            \App\Helpers\HistorialVersionesHelper::ver('COMPETENCIAS', $documento, 'visualizar');

            // OBTIENE LA EXTENSIÓN DEL ARCHIVO EN MINÚSCULAS PARA DETERMINAR CÓMO MOSTRARLO
            $extension = strtolower($documento->archivo_extension);
            
            // LAS IMÁGENES SE MUESTRAN DIRECTAMENTE EN EL NAVEGADOR SIN CONFIGURACIÓN EXTRA
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])) {
                return Storage::disk('public')->response($documento->archivo_ruta);
            }
            
            // LOS PDF SE MUESTRAN INLINE EN EL NAVEGADOR CON SU TIPO MIME ESPECÍFICO
            if ($extension === 'pdf') {
                return Storage::disk('public')->response($documento->archivo_ruta, $documento->archivo_original, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $documento->archivo_original . '"'
                ]);
            }
            
            // LOS ARCHIVOS DE TEXTO Y CÓDIGO SE MUESTRAN COMO TEXTO PLANO EN EL NAVEGADOR
            // SE VERIFICA Y CONVIERTE LA CODIFICACIÓN A UTF-8 PARA EVITAR CARACTERES INCORRECTOS
            if (in_array($extension, ['txt', 'csv', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'])) {
                $content = Storage::disk('public')->get($documento->archivo_ruta);
                if (mb_detect_encoding($content, 'UTF-8', true) !== 'UTF-8') {
                    $content = utf8_encode($content);
                }
                return response($content)
                    ->header('Content-Type', 'text/plain; charset=utf-8')
                    ->header('Content-Disposition', 'inline; filename="' . $documento->archivo_original . '"');
            }
            
            // SI EL FORMATO NO ES PREVISUALIZABLE, SE FUERZA LA DESCARGA DEL ARCHIVO
            return Storage::disk('public')->download($documento->archivo_ruta, $documento->archivo_original);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA ERROR 500
            Log::error('Error al ver archivo: ' . $e->getMessage());
            abort(500, 'Error al visualizar el archivo');
        }
    }

    public function getFoldersTree(Request $request)
    {
        try {
            // OBTIENE EL ID DE LA CARPETA ACTUAL PARA EXCLUIRLA DEL ÁRBOL (NO PUEDE SER SU PROPIO DESTINO)
            $currentFolderId = $request->get('current_folder');

            // OBTIENE TODAS LAS CARPETAS INDEXADAS POR SU ID PARA FACILITAR LAS BÚSQUEDAS
            $allFolders = Competencia::folders()->get()->keyBy('id');
            
            $excludeIds = [];

            // SI SE PASÓ UN ID DE CARPETA VÁLIDO, EXCLUYE ESA CARPETA Y TODAS SUS DESCENDIENTES
            if ($currentFolderId && $currentFolderId !== 'null' && isset($allFolders[$currentFolderId])) {
                $excludeIds = $this->getAllDescendantFolderIds($currentFolderId);
                $excludeIds[] = $currentFolderId;
            }
            
            // FILTRA LAS CARPETAS DISPONIBLES EXCLUYENDO LAS QUE NO DEBEN APARECER EN EL ÁRBOL
            $availableFolders = $allFolders->reject(function ($folder) use ($excludeIds) {
                return in_array($folder->id, $excludeIds);
            });
            
            // CONSTRUYE EL ÁRBOL DE CARPETAS DISPONIBLES COMENZANDO DESDE LAS CARPETAS RAÍZ
            $tree = [];
            foreach ($availableFolders as $folder) {
                if ($folder->parent_id === null || !$availableFolders->has($folder->parent_id)) {
                    $this->buildTreeRecursive($folder, $availableFolders, $tree, '');
                }
            }
            return response()->json($tree);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error al obtener árbol de carpetas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar carpetas'], 500);
        }
    }

    // MÉTODO AUXILIAR RECURSIVO QUE CONSTRUYE EL ÁRBOL DE CARPETAS CON SUS RUTAS COMPLETAS
    // CADA CARPETA APARECE CON SU RUTA COMPLETA TIPO "Raíz / Subcarpeta / Carpeta Hija"
    private function buildTreeRecursive($folder, $availableFolders, &$output, $prefix)
    {
        $output[] = [
            'id' => $folder->id,
            'full_path' => $prefix . $folder->nombre,
        ];

        // BUSCA LOS HIJOS DIRECTOS DE ESTA CARPETA Y LOS ORDENA ALFABÉTICAMENTE
        $children = $availableFolders->filter(function ($f) use ($folder) {
            return $f->parent_id == $folder->id;
        })->sortBy('nombre');

        // LLAMA RECURSIVAMENTE PARA CADA HIJO, AGREGANDO EL NOMBRE DEL PADRE AL PREFIJO
        foreach ($children as $child) {
            $this->buildTreeRecursive($child, $availableFolders, $output, $prefix . $folder->nombre . ' / ');
        }
    }

    // MÉTODO AUXILIAR RECURSIVO QUE OBTIENE LOS IDs DE TODAS LAS SUBCARPETAS DE UNA CARPETA
    // SE USA PARA EVITAR MOVER UNA CARPETA DENTRO DE UNA DE SUS PROPIAS SUBCARPETAS
    private function getAllDescendantFolderIds($folderId)
    {
        $ids = [];
        $subfolders = Competencia::where('parent_id', $folderId)->where('tipo', 'carpeta')->get();
        foreach ($subfolders as $sub) {
            $ids[] = $sub->id;

            // LLAMA RECURSIVAMENTE PARA OBTENER TAMBIÉN LOS DESCENDIENTES DE CADA SUBCARPETA
            $ids = array_merge($ids, $this->getAllDescendantFolderIds($sub->id));
        }
        return $ids;
    }

    public function getDocumentData($id)
    {
        try {
            // BUSCA EL DOCUMENTO POR ID INCLUYENDO LOS ELIMINADOS (withTrashed)
            $documento = Competencia::withTrashed()->findOrFail($id);

            // VERIFICA QUE EL ELEMENTO SEA UN DOCUMENTO Y NO UNA CARPETA
            if (!$documento->isDocument()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es un documento'], 400);
            }

            // RETORNA LOS DATOS BÁSICOS DEL DOCUMENTO EN FORMATO JSON PARA EL FRONTEND
            return response()->json([
                'success' => true,
                'nombre' => $documento->nombre,
                'archivo_original' => $documento->archivo_original,
                'archivo_extension' => $documento->archivo_extension,
                'created_at' => $documento->created_at->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error al obtener datos del documento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener los datos del documento'], 500);
        }
    }

    public function destroyFolder($id)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ELIMINAR CARPETAS
        if (!Gate::allows('auditoria-access')) {
            abort(403, 'No tienes permiso para eliminar carpetas.');
        }

        try {
            // BUSCA LA CARPETA POR ID Y VERIFICA QUE SEA UNA CARPETA Y NO UN DOCUMENTO
            $carpeta = Competencia::findOrFail($id);
            if (!$carpeta->isFolder()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es una carpeta'], 400);
            }
            
            // Guardar datos para historial
            $carpetaData = $carpeta->toArray();
            
            // ELIMINA RECURSIVAMENTE TODOS LOS DOCUMENTOS Y SUBCARPETAS DENTRO DE ESTA CARPETA
            $this->deleteFolderRecursively($carpeta);
            $carpeta->delete();

            // Registrar en historial - eliminación de carpeta
            \App\Helpers\HistorialVersionesHelper::eliminar('COMPETENCIAS', $carpeta, $carpetaData);

            return response()->json(['success' => true, 'message' => 'Carpeta eliminada exitosamente']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error al eliminar carpeta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la carpeta'], 500);
        }
    }

    public function destroyDocument($id)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ELIMINAR DOCUMENTOS
        if (!Gate::allows('auditoria-access')) {
            abort(403, 'No tienes permiso para eliminar documentos.');
        }

        try {
            // BUSCA EL DOCUMENTO POR ID Y VERIFICA QUE SEA UN DOCUMENTO Y NO UNA CARPETA
            $documento = Competencia::findOrFail($id);
            if (!$documento->isDocument()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es un documento'], 400);
            }
            
            // Guardar datos para historial
            $documentoData = $documento->toArray();

            // SOFT DELETE DEL DOCUMENTO (NO ELIMINA EL ARCHIVO FÍSICO DEL SERVIDOR)
            $documento->delete();

            // Registrar en historial - eliminación de documento
            \App\Helpers\HistorialVersionesHelper::eliminar('COMPETENCIAS', $documento, $documentoData);

            return response()->json(['success' => true, 'message' => 'Documento eliminado exitosamente']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error al eliminar documento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el documento'], 500);
        }
    }

    public function restaurarFolder($id)
    {
        try {
            // BUSCA LA CARPETA POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $carpeta = Competencia::withTrashed()->findOrFail($id);
            
            // VERIFICA QUE EL ELEMENTO SEA UNA CARPETA Y NO UN DOCUMENTO
            if (!$carpeta->isFolder()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es una carpeta'], 400);
            }
            
            // VERIFICA QUE LA CARPETA ESTÉ REALMENTE ELIMINADA ANTES DE INTENTAR RESTAURARLA
            if (!$carpeta->trashed()) {
                return response()->json(['success' => false, 'message' => 'La carpeta no está eliminada'], 400);
            }
            
            // SI LA CARPETA TIENE UN PADRE, VERIFICA QUE ESE PADRE TODAVÍA EXISTA EN LA BD
            if ($carpeta->parent_id) {
                $parentExists = Competencia::withTrashed()->find($carpeta->parent_id);
                if (!$parentExists) {
                    return response()->json(['success' => false, 'message' => 'La carpeta padre ya no existe'], 400);
                }
            }
            
            // VERIFICA QUE NO EXISTA YA UNA CARPETA ACTIVA CON EL MISMO NOMBRE EN LA MISMA UBICACIÓN
            $existing = Competencia::where('nombre', $carpeta->nombre)
                ->where('tipo', 'carpeta')
                ->where('parent_id', $carpeta->parent_id)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe una carpeta activa con el mismo nombre en esta ubicación'], 400);
            }
            
            // PRIMERO: Restaurar TODOS los documentos dentro de la carpeta
            $this->restoreAllDocumentsInCompetenciaFolder($carpeta->id);
            
            // SEGUNDO: Restaurar TODAS las subcarpetas
            $this->restoreAllSubfoldersCompetencia($carpeta->id);
            
            // TERCERO: Restaurar la carpeta principal
            $carpeta->restore();
            
            // Registrar en historial - restauración de carpeta
            \App\Helpers\HistorialVersionesHelper::restaurar('COMPETENCIAS', $carpeta);
            
            return response()->json(['success' => true, 'message' => 'Carpeta restaurada correctamente con todo su contenido']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error al restaurar carpeta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar la carpeta: ' . $e->getMessage()], 500);
        }
    }

    public function restaurarDocumento($id)
    {
        try {
            // BUSCA EL DOCUMENTO POR ID INCLUYENDO LOS ELIMINADOS (withTrashed)
            $documento = Competencia::withTrashed()->findOrFail($id);
            
            // VERIFICA QUE EL ELEMENTO SEA UN DOCUMENTO Y NO UNA CARPETA
            if (!$documento->isDocument()) {
                return response()->json(['success' => false, 'message' => 'El elemento no es un documento'], 400);
            }
            
            // VERIFICA QUE EL DOCUMENTO ESTÉ REALMENTE ELIMINADO ANTES DE INTENTAR RESTAURARLO
            if (!$documento->trashed()) {
                return response()->json(['success' => false, 'message' => 'El documento no está eliminado'], 400);
            }
            
            // SI EL DOCUMENTO TIENE UNA CARPETA PADRE, VERIFICA QUE ESA CARPETA TODAVÍA EXISTA
            if ($documento->parent_id) {
                $parentExists = Competencia::withTrashed()->find($documento->parent_id);
                if (!$parentExists) {
                    return response()->json(['success' => false, 'message' => 'La carpeta padre ya no existe'], 400);
                }
            }
            
            // VERIFICA QUE NO EXISTA YA UN DOCUMENTO ACTIVO CON EL MISMO NOMBRE EN LA MISMA UBICACIÓN
            $existing = Competencia::where('nombre', $documento->nombre)
                ->where('tipo', 'documento')
                ->where('parent_id', $documento->parent_id)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe un documento activo con el mismo nombre en esta ubicación'], 400);
            }
            
            // VERIFICA QUE EL ARCHIVO FÍSICO TODAVÍA EXISTA EN EL SERVIDOR ANTES DE RESTAURAR
            if (!Storage::disk('public')->exists($documento->archivo_ruta)) {
                return response()->json(['success' => false, 'message' => 'El archivo físico no existe en el servidor'], 400);
            }
            
            // RESTAURA EL DOCUMENTO ELIMINANDO SU MARCA DE SOFT DELETE (deleted_at = NULL)
            $documento->restore();
            
            // Registrar en historial - restauración de documento
            \App\Helpers\HistorialVersionesHelper::restaurar('COMPETENCIAS', $documento);
            
            return response()->json(['success' => true, 'message' => 'Documento restaurado correctamente']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error al restaurar documento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar el documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Restaurar todos los documentos dentro de una carpeta de competencias (recursivamente)
     */
    private function restoreAllDocumentsInCompetenciaFolder($folderId)
    {
        // OBTIENE TODOS LOS DOCUMENTOS ELIMINADOS QUE PERTENECEN DIRECTAMENTE A ESTA CARPETA
        $documents = Competencia::withTrashed()
            ->documents()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($documents as $document) {
            // SOLO RESTAURA EL DOCUMENTO SI SU ARCHIVO FÍSICO TODAVÍA EXISTE EN EL SERVIDOR
            if (Storage::disk('public')->exists($document->archivo_ruta)) {
                $document->restore();
            }
        }
        
        // BUSCA SUBCARPETAS ELIMINADAS PARA RESTAURAR TAMBIÉN SUS DOCUMENTOS DE FORMA RECURSIVA
        $subfolders = Competencia::withTrashed()
            ->folders()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            $this->restoreAllDocumentsInCompetenciaFolder($subfolder->id);
        }
    }
    
    /**
     * Restaurar todas las subcarpetas (recursivamente)
     */
    private function restoreAllSubfoldersCompetencia($folderId)
    {
        // OBTIENE TODAS LAS SUBCARPETAS ELIMINADAS QUE PERTENECEN DIRECTAMENTE A ESTA CARPETA
        $subfolders = Competencia::withTrashed()
            ->folders()
            ->where('parent_id', $folderId)
            ->whereNotNull('deleted_at')
            ->get();
            
        foreach ($subfolders as $subfolder) {
            // PRIMERO RESTAURA LOS DOCUMENTOS DENTRO DE ESTA SUBCARPETA
            $this->restoreAllDocumentsInCompetenciaFolder($subfolder->id);

            // LUEGO RESTAURA LAS SUBCARPETAS HIJAS DE FORMA RECURSIVA
            $this->restoreAllSubfoldersCompetencia($subfolder->id);

            // FINALMENTE RESTAURA LA SUBCARPETA EN SÍ MISMA
            $subfolder->restore();
        }
    }

    // MÉTODO AUXILIAR RECURSIVO QUE HACE SOFT DELETE DE TODOS LOS DOCUMENTOS Y SUBCARPETAS
    // SE LLAMA ANTES DE ELIMINAR UNA CARPETA PARA LIMPIAR TODO SU CONTENIDO PRIMERO
    private function deleteFolderRecursively($folder)
    {
        // Eliminar documentos dentro de la carpeta
        foreach ($folder->documentosHijos as $documento) {
            $documento->delete();
        }
        // OBTIENE LAS SUBCARPETAS DIRECTAS Y LAS ELIMINA DE FORMA RECURSIVA
        $subfolders = Competencia::where('parent_id', $folder->id)->where('tipo', 'carpeta')->get();
        foreach ($subfolders as $subfolder) {
            $this->deleteFolderRecursively($subfolder);
            $subfolder->delete();
        }
    }

    // MÉTODO AUXILIAR QUE CONSTRUYE EL ARRAY DE BREADCRUMBS PARA LA NAVEGACIÓN
    // RECORRE LA JERARQUÍA DE CARPETAS DESDE LA ACTUAL HASTA LLEGAR A LA RAÍZ
    private function getBreadcrumbs($folder)
    {
        $breadcrumbs = collect();
        $current = $folder;
        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }
        return $breadcrumbs;
    }
}