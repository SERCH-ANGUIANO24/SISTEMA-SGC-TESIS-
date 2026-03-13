<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\SolicitudMejora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SolicitudMejoraController extends Controller
{
    public function index()
    {
        $anios = SolicitudMejora::selectRaw('YEAR(fecha_solicitud) as anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');
        
        return view('auditoria.solicitudes.index', compact('anios'));
    }

    public function data(Request $request)
    {
        try {
            $query = SolicitudMejora::query();

            if ($request->filled('estatus')) {
                $query->where('estatus', $request->estatus);
            }

            if ($request->filled('anio')) {
                $query->whereYear('fecha_solicitud', $request->anio);
            }

            $solicitudes = $query->orderBy('created_at', 'desc')->get();

            return response()->json($solicitudes);
        } catch (\Exception $e) {
            Log::error('Error en data solicitudes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Iniciando store de solicitud', $request->all());
            
            $validated = $request->validate([
                'folio_solicitud'      => 'nullable|string|max:50',
                'fecha_solicitud'      => 'required|date',
                'responsable_accion'   => 'required|string|max:255',
                'fecha_aplicacion'     => 'required|date',
                'actividades_verificacion' => 'nullable|string',
                'fecha_verificacion'   => 'nullable|date',
                'estatus'              => 'required|in:No Atendida,En Proceso,Cerrado',
                'archivo'              => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt'
            ]);

            // Convertir campos month (YYYY-MM) a fecha completa (primer día del mes)
            if ($request->has('fecha_aplicacion') && strlen($request->fecha_aplicacion) == 7) {
                $validated['fecha_aplicacion'] = $request->fecha_aplicacion . '-01';
            }
            if ($request->has('fecha_verificacion') && strlen($request->fecha_verificacion) == 7) {
                $validated['fecha_verificacion'] = $request->fecha_verificacion . '-01';
            }

            $data = [
                'folio_solicitud'           => $validated['folio_solicitud'] ?? null,
                'fecha_solicitud'           => $validated['fecha_solicitud'],
                'responsable_accion'        => $validated['responsable_accion'],
                'fecha_aplicacion'           => $validated['fecha_aplicacion'],
                'actividades_verificacion'   => $validated['actividades_verificacion'] ?? null,
                'fecha_verificacion'         => $validated['fecha_verificacion'] ?? null,
                'estatus'                    => $validated['estatus'],
            ];
            
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
                $path = $file->storeAs('solicitudes_mejora', $fileName, 'public');
                
                $data['archivo_nombre'] = $originalName;
                $data['archivo_ruta'] = $path;
                
                Log::info('Archivo guardado: ' . $path);
            }

            $solicitud = SolicitudMejora::create($data);
            Log::info('Solicitud creada con ID: ' . $solicitud->id);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora guardada correctamente',
                'data' => $solicitud
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error al guardar solicitud:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $solicitud = SolicitudMejora::findOrFail($id);

            $validated = $request->validate([
                'folio_solicitud'      => 'nullable|string|max:50',
                'fecha_solicitud'      => 'required|date',
                'responsable_accion'   => 'required|string|max:255',
                'fecha_aplicacion'     => 'required|date',
                'actividades_verificacion' => 'nullable|string',
                'fecha_verificacion'   => 'nullable|date',
                'estatus'              => 'required|in:No Atendida,En Proceso,Cerrado',
                'archivo'              => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt'
            ]);

            // Convertir campos month
            if ($request->has('fecha_aplicacion') && strlen($request->fecha_aplicacion) == 7) {
                $validated['fecha_aplicacion'] = $request->fecha_aplicacion . '-01';
            }
            if ($request->has('fecha_verificacion') && strlen($request->fecha_verificacion) == 7) {
                $validated['fecha_verificacion'] = $request->fecha_verificacion . '-01';
            }

            $data = [
                'folio_solicitud'           => $validated['folio_solicitud'] ?? null,
                'fecha_solicitud'           => $validated['fecha_solicitud'],
                'responsable_accion'        => $validated['responsable_accion'],
                'fecha_aplicacion'           => $validated['fecha_aplicacion'],
                'actividades_verificacion'   => $validated['actividades_verificacion'] ?? null,
                'fecha_verificacion'         => $validated['fecha_verificacion'] ?? null,
                'estatus'                    => $validated['estatus'],
            ];

            if ($request->hasFile('archivo')) {
                if ($solicitud->archivo_ruta) {
                    Storage::disk('public')->delete($solicitud->archivo_ruta);
                }

                $file = $request->file('archivo');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
                $path = $file->storeAs('solicitudes_mejora', $fileName, 'public');
                
                $data['archivo_nombre'] = $originalName;
                $data['archivo_ruta'] = $path;
            }

            $solicitud->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora actualizada correctamente',
                'data' => $solicitud
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $solicitud = SolicitudMejora::findOrFail($id);
            
            if ($solicitud->archivo_ruta) {
                Storage::disk('public')->delete($solicitud->archivo_ruta);
            }
            
            $solicitud->delete();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function view($id)
    {
        try {
            $solicitud = SolicitudMejora::findOrFail($id);
            
            if (!$solicitud->archivo_ruta) {
                abort(404, 'Archivo no encontrado');
            }

            $path = storage_path('app/public/' . $solicitud->archivo_ruta);
            
            if (!file_exists($path)) {
                abort(404, 'Archivo no encontrado en el servidor');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            Log::error('Error al ver archivo: ' . $e->getMessage());
            abort(404, 'Error al cargar el archivo');
        }
    }

    public function download($id)
    {
        try {
            $solicitud = SolicitudMejora::findOrFail($id);
            
            if (!$solicitud->archivo_ruta) {
                abort(404, 'Archivo no encontrado');
            }

            $path = storage_path('app/public/' . $solicitud->archivo_ruta);
            
            if (!file_exists($path)) {
                abort(404, 'Archivo no encontrado en el servidor');
            }

            return response()->download($path, $solicitud->archivo_nombre);
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo: ' . $e->getMessage());
            abort(404, 'Error al descargar el archivo');
        }
    }
}