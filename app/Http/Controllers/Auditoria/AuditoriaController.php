<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

// CONTROLADOR QUE GESTIONA TODAS LAS OPERACIONES DEL MÓDULO DE AUDITORÍAS
// PERMITE VER, CREAR, EDITAR, ELIMINAR, RESTAURAR, DESCARGAR Y VISUALIZAR ARCHIVOS DE AUDITORÍA
class AuditoriaController extends Controller
{
    public function index()
    {
        try {
            // REGISTRA EN EL HISTORIAL QUE ALGUIEN VISITÓ LA PÁGINA PRINCIPAL DE AUDITORÍAS
            \App\Helpers\HistorialVersionesHelper::ver('AUDITORIAS', null, 'index');

            // OBTIENE TODOS LOS AÑOS DISTINTOS EN QUE HAY AUDITORÍAS REGISTRADAS
            // SE ORDENAN DE MÁS RECIENTE A MÁS ANTIGUO PARA MOSTRARLOS EN EL FILTRO
            $anios = Auditoria::select('anio')
                ->distinct()
                ->orderBy('anio', 'desc')
                ->pluck('anio');
            
            // RETORNA LA VISTA PRINCIPAL DEL PLAN DE AUDITORÍAS CON LA LISTA DE AÑOS
            return view('auditoria.plan.index', compact('anios'));
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA LA VISTA CON AÑOS VACÍOS
            Log::error('Error en index: ' . $e->getMessage());
            return view('auditoria.plan.index', ['anios' => []]);
        }
    }

    public function getData(Request $request)
    {
        try {
            // INICIA UNA CONSULTA BASE SOBRE EL MODELO DE AUDITORÍAS
            $query = Auditoria::query();

            // SI SE ENVIÓ UN FILTRO DE AÑO, LO APLICA A LA CONSULTA
            if ($request->filled('anio')) {
                $query->where('anio', $request->anio);
            }

            // SI SE ENVIÓ UN FILTRO DE TIPO (INTERNA/EXTERNA), LO APLICA A LA CONSULTA
            if ($request->filled('tipo')) {
                $query->where('tipo_auditoria', $request->tipo);
            }

            // OBTIENE LOS RESULTADOS ORDENADOS POR FECHA DE INICIO DE MÁS RECIENTE A MÁS ANTIGUO
            $auditorias = $query->orderBy('fecha_inicio', 'desc')->get();

            // RETORNA LOS DATOS EN FORMATO JSON PARA SER CONSUMIDOS POR EL FRONTEND
            return response()->json($auditorias);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE DE ERROR EN JSON
            Log::error('Error en getData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
            if (!Gate::allows('auditoria-access')) { abort(403); }
            Log::info('Datos recibidos:', $request->all());
            
            // VALIDA TODOS LOS CAMPOS OBLIGATORIOS DEL FORMULARIO DE CREACIÓN
            // EL TIPO SOLO PUEDE SER 'Interna' O 'Externa'
            // LA FECHA FIN NO PUEDE SER ANTERIOR A LA FECHA INICIO
            $validated = $request->validate([
                'nombre_auditoria' => 'required|string|max:255',
                'tipo_auditoria' => 'required|in:Interna,Externa',
                'auditor_lider' => 'required|string|max:255',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'anio' => 'required|integer|min:2000|max:2100',
                'auditores' => 'nullable|string'
            ]);

            // Asignar fecha_auditoria con el valor de fecha_inicio para mantener compatibilidad
            $validated['fecha_auditoria'] = $validated['fecha_inicio'];

            // SI SE SUBIÓ UN ARCHIVO DE PLAN DE AUDITORÍA, LO PROCESA Y GUARDA EN EL SERVIDOR
            if ($request->hasFile('archivo_plan')) {
                $file = $request->file('archivo_plan');
                
                // VALIDA QUE EL ARCHIVO NO EXCEDA 20MB Y SEA DE UN TIPO PERMITIDO
                $request->validate([
                    'archivo_plan' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt'
                ]);

                // GENERA UN NOMBRE ÚNICO PARA EL ARCHIVO USANDO EL TIMESTAMP ACTUAL
                // LIMPIA EL NOMBRE ORIGINAL PARA EVITAR CARACTERES ESPECIALES PELIGROSOS
                $nombreOriginal = $file->getClientOriginalName();
                $nombreArchivo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nombreOriginal);
                
                // CREA LA CARPETA DE DESTINO SI NO EXISTE
                $uploadPath = public_path('auditorias');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                // MUEVE EL ARCHIVO A LA CARPETA DE AUDITORÍAS EN EL SERVIDOR
                $file->move($uploadPath, $nombreArchivo);
                
                // GUARDA LA RUTA Y EL NOMBRE ORIGINAL DEL ARCHIVO EN LOS DATOS VALIDADOS
                $validated['archivo_path'] = 'auditorias/' . $nombreArchivo;
                $validated['archivo_nombre'] = $nombreOriginal;
            }

            // CREA EL REGISTRO DE LA AUDITORÍA EN LA BASE DE DATOS
            $auditoria = Auditoria::create($validated);

            // REGISTRA EN EL HISTORIAL QUE SE SUBIÓ/CREÓ UNA NUEVA AUDITORÍA
            \App\Helpers\HistorialVersionesHelper::subir('AUDITORIAS', $auditoria);

            // ── NOTIFICACIÓN: nueva auditoría agendada ─────────────────────
            // ENVÍA UNA NOTIFICACIÓN A TODOS LOS USUARIOS DEL SISTEMA INFORMANDO LA NUEVA AUDITORÍA
            // INCLUYE EL TIPO, AUDITOR LÍDER Y EL PERÍODO DE LA AUDITORÍA EN EL MENSAJE
            $notif = app(\App\Services\NotificacionService::class);
            $notif->enviarATodos(
                titulo:     'Nueva auditoría agendada: ' . $auditoria->nombre_auditoria,
                mensaje:    'Se ha programado una nueva auditoría en el sistema.' . PHP_EOL . PHP_EOL .
                            'Tipo de auditoría: ' . $auditoria->tipo_auditoria . PHP_EOL .
                            'Auditor Líder: ' . $auditoria->auditor_lider . PHP_EOL .
                            'Período: ' . \Carbon\Carbon::parse($auditoria->fecha_inicio)->format('d/m/Y') .
                            ' al ' . \Carbon\Carbon::parse($auditoria->fecha_fin)->format('d/m/Y') . PHP_EOL . PHP_EOL .
                            'Puedes consultar el plan de auditoría completo en la plataforma.',
                tipo:       'info',
                icono:      'bi-clipboard-check',
                url:        route('auditoria.plan.index'),
                email:      true,
                docId:      (string) $auditoria->id,
                tipoEvento: 'nueva_auditoria'
            );
            // ── FIN NOTIFICACIÓN ───────────────────────────────────────────

            // RETORNA RESPUESTA JSON CON ÉXITO Y LOS DATOS DE LA AUDITORÍA RECIÉN CREADA
            return response()->json([
                'success' => true, 
                'data' => $auditoria,
                'message' => 'Auditoría guardada correctamente'
            ]);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error en store: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
            if (!Gate::allows('auditoria-access')) { abort(403); }

            // BUSCA LA AUDITORÍA POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $auditoria = Auditoria::withTrashed()->findOrFail($id);

            // GUARDA LOS DATOS ACTUALES ANTES DE MODIFICARLOS (PARA EL HISTORIAL)
            $datosAnteriores = $auditoria->toArray();

            // VALIDA TODOS LOS CAMPOS OBLIGATORIOS DEL FORMULARIO DE EDICIÓN
            $validated = $request->validate([
                'nombre_auditoria' => 'required|string|max:255',
                'tipo_auditoria' => 'required|in:Interna,Externa',
                'auditor_lider' => 'required|string|max:255',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'anio' => 'required|integer|min:2000|max:2100',
                'auditores' => 'nullable|string'
            ]);

            // Asignar fecha_auditoria con el valor de fecha_inicio para mantener compatibilidad
            $validated['fecha_auditoria'] = $validated['fecha_inicio'];

            // SI SE SUBIÓ UN NUEVO ARCHIVO DE PLAN, LO PROCESA Y REEMPLAZA AL ANTERIOR
            if ($request->hasFile('archivo_plan')) {
                // VALIDA QUE EL NUEVO ARCHIVO SEA DE UN TIPO PERMITIDO Y NO EXCEDA 20MB
                $request->validate([
                    'archivo_plan' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt'
                ]);

                // SI YA HABÍA UN ARCHIVO ANTERIOR, LO ELIMINA FÍSICAMENTE DEL SERVIDOR
                if ($auditoria->archivo_path) {
                    $rutaAnterior = public_path($auditoria->archivo_path);
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                
                // GENERA UN NOMBRE ÚNICO Y MUEVE EL NUEVO ARCHIVO AL SERVIDOR
                $file = $request->file('archivo_plan');
                $nombreOriginal = $file->getClientOriginalName();
                $nombreArchivo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nombreOriginal);
                
                $file->move(public_path('auditorias'), $nombreArchivo);
                
                // ACTUALIZA LA RUTA Y EL NOMBRE DEL ARCHIVO EN LOS DATOS VALIDADOS
                $validated['archivo_path'] = 'auditorias/' . $nombreArchivo;
                $validated['archivo_nombre'] = $nombreOriginal;
            }

            // ACTUALIZA EL REGISTRO DE LA AUDITORÍA EN LA BASE DE DATOS
            $auditoria->update($validated);

            // REGISTRA EN EL HISTORIAL EL CAMBIO REALIZADO (DATOS ANTERIORES VS DATOS NUEVOS)
            \App\Helpers\HistorialVersionesHelper::editar('AUDITORIAS', $auditoria, $datosAnteriores, $auditoria->toArray());

            // RETORNA RESPUESTA JSON CON ÉXITO Y LOS DATOS ACTUALIZADOS DE LA AUDITORÍA
            return response()->json([
                'success' => true, 
                'data' => $auditoria,
                'message' => 'Auditoría actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error en update: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
            if (!Gate::allows('auditoria-access')) { abort(403); }

            // BUSCA LA AUDITORÍA POR ID. SI NO EXISTE, LANZA UN ERROR 404
            $auditoria = Auditoria::findOrFail($id);
            
            // GUARDA LOS DATOS DE LA AUDITORÍA ANTES DE ELIMINARLA (PARA EL HISTORIAL)
            $datosAuditoria = $auditoria->toArray();

            // Soft delete - NO elimina el archivo físico
            $auditoria->delete();

            // REGISTRA EN EL HISTORIAL QUE SE ELIMINÓ LA AUDITORÍA
            \App\Helpers\HistorialVersionesHelper::eliminar('AUDITORIAS', $auditoria, $datosAuditoria);

            // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE LA AUDITORÍA FUE ELIMINADA
            return response()->json(['success' => true, 'message' => 'Auditoría eliminada correctamente']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error en destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        try {
            // BUSCA LA AUDITORÍA POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $auditoria = Auditoria::withTrashed()->findOrFail($id);
            
            // VERIFICA QUE LA AUDITORÍA TENGA UN ARCHIVO ASOCIADO ANTES DE INTENTAR DESCARGARLO
            if (!$auditoria->archivo_path) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            // CONSTRUYE LA RUTA FÍSICA COMPLETA DEL ARCHIVO EN EL SERVIDOR
            $path = public_path($auditoria->archivo_path);
            
            // VERIFICA QUE EL ARCHIVO EXISTA FÍSICAMENTE EN EL SERVIDOR
            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no encontrado en el servidor'], 404);
            }

            // REGISTRA EN EL HISTORIAL QUE EL ARCHIVO FUE DESCARGADO
            \App\Helpers\HistorialVersionesHelper::descargar('AUDITORIAS', $auditoria);

            // FUERZA LA DESCARGA DEL ARCHIVO CON SU NOMBRE ORIGINAL
            return response()->download($path, $auditoria->archivo_nombre);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE DE ERROR EN JSON
            Log::error('Error en download: ' . $e->getMessage());
            return response()->json(['error' => 'Error al descargar: ' . $e->getMessage()], 500);
        }
    }

    public function verArchivo($id)
    {
        try {
            // BUSCA LA AUDITORÍA POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $auditoria = Auditoria::withTrashed()->findOrFail($id);
            
            // VERIFICA QUE LA AUDITORÍA TENGA UN ARCHIVO ASOCIADO ANTES DE INTENTAR VISUALIZARLO
            if (!$auditoria->archivo_path) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            // CONSTRUYE LA RUTA FÍSICA COMPLETA DEL ARCHIVO EN EL SERVIDOR
            $path = public_path($auditoria->archivo_path);
            
            // VERIFICA QUE EL ARCHIVO EXISTA FÍSICAMENTE EN EL SERVIDOR
            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no encontrado en el servidor'], 404);
            }

            // REGISTRA EN EL HISTORIAL QUE EL ARCHIVO FUE VISUALIZADO
            \App\Helpers\HistorialVersionesHelper::ver('AUDITORIAS', $auditoria, 'ver_archivo');

            // OBTIENE LA EXTENSIÓN DEL ARCHIVO EN MINÚSCULAS PARA DETERMINAR CÓMO MOSTRARLO
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            
            // SI LA EXTENSIÓN ES PREVISUALIZABLE EN EL NAVEGADOR, SE MUESTRA INLINE
            if (in_array($extension, ['pdf', 'txt', 'jpg', 'jpeg', 'png', 'gif'])) {
                if ($extension === 'pdf') {
                    // LOS PDF SE MUESTRAN CON SU TIPO MIME ESPECÍFICO
                    return response()->file($path, ['Content-Type' => 'application/pdf']);
                } elseif ($extension === 'txt') {
                    // LOS ARCHIVOS DE TEXTO SE MUESTRAN CON SU TIPO MIME ESPECÍFICO
                    return response()->file($path, ['Content-Type' => 'text/plain']);
                } else {
                    // LAS IMÁGENES SE MUESTRAN DIRECTAMENTE SIN ESPECIFICAR CONTENT-TYPE
                    return response()->file($path);
                }
            } else {
                // SI EL FORMATO NO ES PREVISUALIZABLE, SE FUERZA LA DESCARGA DEL ARCHIVO
                return response()->download($path, $auditoria->archivo_nombre);
            }
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE DE ERROR EN JSON
            Log::error('Error en verArchivo: ' . $e->getMessage());
            return response()->json(['error' => 'Error al visualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * RESTAURAR AUDITORÍA ELIMINADA
     */
    public function restaurar($id)
    {
        try {
            // BUSCA LA AUDITORÍA POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $auditoria = Auditoria::withTrashed()->findOrFail($id);
            
            // VERIFICA QUE LA AUDITORÍA ESTÉ REALMENTE ELIMINADA ANTES DE RESTAURARLA
            if (!$auditoria->trashed()) {
                return response()->json(['success' => false, 'message' => 'La auditoría no está eliminada'], 400);
            }
            
            // VERIFICA QUE NO EXISTA YA UNA AUDITORÍA ACTIVA CON EL MISMO NOMBRE
            // ESTO EVITA DUPLICADOS AL RESTAURAR
            $existing = Auditoria::where('nombre_auditoria', $auditoria->nombre_auditoria)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe una auditoría activa con el mismo nombre'], 400);
            }
            
            // RESTAURA LA AUDITORÍA ELIMINANDO SU MARCA DE SOFT DELETE (deleted_at = NULL)
            $auditoria->restore();
            
            // REGISTRA EN EL HISTORIAL QUE LA AUDITORÍA FUE RESTAURADA
            \App\Helpers\HistorialVersionesHelper::restaurar('AUDITORIAS', $auditoria);
            
            // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE LA AUDITORÍA FUE RESTAURADA
            return response()->json(['success' => true, 'message' => 'Auditoría restaurada correctamente']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error en restaurar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar: ' . $e->getMessage()], 500);
        }
    }

    public function getChartData()
    {
        try {
            // OBTIENE EL CONTEO DE AUDITORÍAS AGRUPADAS POR AÑO Y MES
            // SE LIMITA A LOS ÚLTIMOS 12 MESES PARA MOSTRAR EN LA GRÁFICA DEL DASHBOARD
            $data = Auditoria::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();
                
            // RETORNA LOS DATOS EN FORMATO JSON PARA SER CONSUMIDOS POR LA GRÁFICA DEL FRONTEND
            return response()->json($data);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE DE ERROR EN JSON
            Log::error('Error en getChartData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}