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
            $auditoria = Auditoria::findOrFail($id);

            $validated = $request->validate([
                'nombre_auditoria' => 'required|string|max:255',
                'tipo_auditoria' => 'required|in:Interna,Externa',
                'auditor_lider' => 'required|string|max:255',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'anio' => 'required|integer|min:2000|max:2100',
                'auditores' => 'nullable|string'
            ]);

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
            
            if ($auditoria->archivo_path) {
                $rutaArchivo = public_path($auditoria->archivo_path);
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
            }
            
            $auditoria->delete();

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
            $auditoria = Auditoria::findOrFail($id);
            
            if (!$auditoria->archivo_path) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $path = public_path($auditoria->archivo_path);
            
            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no encontrado en el servidor'], 404);
            }

            return response()->download($path, $auditoria->archivo_nombre);
        } catch (\Exception $e) {
            Log::error('Error en download: ' . $e->getMessage());
            return response()->json(['error' => 'Error al descargar: ' . $e->getMessage()], 500);
        }
    }

    public function verArchivo($id)
    {
        try {
            $auditoria = Auditoria::findOrFail($id);
            
            if (!$auditoria->archivo_path) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $path = public_path($auditoria->archivo_path);
            
            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no encontrado en el servidor'], 404);
            }

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
}