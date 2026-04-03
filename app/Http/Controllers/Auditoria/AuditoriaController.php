<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditoriaController extends Controller
{
    public function index()
    {
        try {
            \App\Helpers\HistorialVersionesHelper::ver('AUDITORIAS', null, 'index');

            $anios = Auditoria::select('anio')
                ->distinct()
                ->orderBy('anio', 'desc')
                ->pluck('anio');
            
            return view('auditoria.plan.index', compact('anios'));
        } catch (\Exception $e) {
            Log::error('Error en index: ' . $e->getMessage());
            return view('auditoria.plan.index', ['anios' => []]);
        }
    }

    public function getData(Request $request)
    {
        try {
            $query = Auditoria::query();

            if ($request->filled('anio')) {
                $query->where('anio', $request->anio);
            }

            if ($request->filled('tipo')) {
                $query->where('tipo_auditoria', $request->tipo);
            }

            $auditorias = $query->orderBy('fecha_inicio', 'desc')->get();

            return response()->json($auditorias);
        } catch (\Exception $e) {
            Log::error('Error en getData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Datos recibidos:', $request->all());
            
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

            if ($request->hasFile('archivo_plan')) {
                $file = $request->file('archivo_plan');
                
                $request->validate([
                    'archivo_plan' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt'
                ]);

                $nombreOriginal = $file->getClientOriginalName();
                $nombreArchivo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nombreOriginal);
                
                $uploadPath = public_path('auditorias');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                $file->move($uploadPath, $nombreArchivo);
                
                $validated['archivo_path'] = 'auditorias/' . $nombreArchivo;
                $validated['archivo_nombre'] = $nombreOriginal;
            }

            $auditoria = Auditoria::create($validated);

            \App\Helpers\HistorialVersionesHelper::subir('AUDITORIAS', $auditoria);

            // ── NOTIFICACIÓN: nueva auditoría agendada ─────────────────────
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

            return response()->json([
                'success' => true, 
                'data' => $auditoria,
                'message' => 'Auditoría guardada correctamente'
            ]);
        } catch (\Exception $e) {
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
            $auditoria = Auditoria::withTrashed()->findOrFail($id);

            $datosAnteriores = $auditoria->toArray();

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

            if ($request->hasFile('archivo_plan')) {
                $request->validate([
                    'archivo_plan' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt'
                ]);

                if ($auditoria->archivo_path) {
                    $rutaAnterior = public_path($auditoria->archivo_path);
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                
                $file = $request->file('archivo_plan');
                $nombreOriginal = $file->getClientOriginalName();
                $nombreArchivo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nombreOriginal);
                
                $file->move(public_path('auditorias'), $nombreArchivo);
                
                $validated['archivo_path'] = 'auditorias/' . $nombreArchivo;
                $validated['archivo_nombre'] = $nombreOriginal;
            }

            $auditoria->update($validated);

            \App\Helpers\HistorialVersionesHelper::editar('AUDITORIAS', $auditoria, $datosAnteriores, $auditoria->toArray());

            return response()->json([
                'success' => true, 
                'data' => $auditoria,
                'message' => 'Auditoría actualizada correctamente'
            ]);
        } catch (\Exception $e) {
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
            $auditoria = Auditoria::findOrFail($id);
            
            $datosAuditoria = $auditoria->toArray();

            // Soft delete - NO elimina el archivo físico
            $auditoria->delete();

            \App\Helpers\HistorialVersionesHelper::eliminar('AUDITORIAS', $auditoria, $datosAuditoria);

            return response()->json(['success' => true, 'message' => 'Auditoría eliminada correctamente']);
        } catch (\Exception $e) {
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
            $auditoria = Auditoria::withTrashed()->findOrFail($id);
            
            if (!$auditoria->archivo_path) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $path = public_path($auditoria->archivo_path);
            
            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no encontrado en el servidor'], 404);
            }

            \App\Helpers\HistorialVersionesHelper::descargar('AUDITORIAS', $auditoria);

            return response()->download($path, $auditoria->archivo_nombre);
        } catch (\Exception $e) {
            Log::error('Error en download: ' . $e->getMessage());
            return response()->json(['error' => 'Error al descargar: ' . $e->getMessage()], 500);
        }
    }

    public function verArchivo($id)
    {
        try {
            $auditoria = Auditoria::withTrashed()->findOrFail($id);
            
            if (!$auditoria->archivo_path) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $path = public_path($auditoria->archivo_path);
            
            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no encontrado en el servidor'], 404);
            }

            \App\Helpers\HistorialVersionesHelper::ver('AUDITORIAS', $auditoria, 'ver_archivo');

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['pdf', 'txt', 'jpg', 'jpeg', 'png', 'gif'])) {
                if ($extension === 'pdf') {
                    return response()->file($path, ['Content-Type' => 'application/pdf']);
                } elseif ($extension === 'txt') {
                    return response()->file($path, ['Content-Type' => 'text/plain']);
                } else {
                    return response()->file($path);
                }
            } else {
                return response()->download($path, $auditoria->archivo_nombre);
            }
        } catch (\Exception $e) {
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
            $auditoria = Auditoria::withTrashed()->findOrFail($id);
            
            if (!$auditoria->trashed()) {
                return response()->json(['success' => false, 'message' => 'La auditoría no está eliminada'], 400);
            }
            
            $existing = Auditoria::where('nombre_auditoria', $auditoria->nombre_auditoria)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe una auditoría activa con el mismo nombre'], 400);
            }
            
            $auditoria->restore();
            
            \App\Helpers\HistorialVersionesHelper::restaurar('AUDITORIAS', $auditoria);
            
            return response()->json(['success' => true, 'message' => 'Auditoría restaurada correctamente']);
        } catch (\Exception $e) {
            Log::error('Error en restaurar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar: ' . $e->getMessage()], 500);
        }
    }

    public function getChartData()
    {
        try {
            $data = Auditoria::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();
                
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error en getChartData: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}