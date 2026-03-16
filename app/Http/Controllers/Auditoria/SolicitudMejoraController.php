<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\SolicitudMejora;
use App\Models\InformeAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SolicitudMejoraController extends Controller
{
    public function index()
    {
        $anios = SolicitudMejora::selectRaw('YEAR(fecha_solicitud) as anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        $informes = InformeAuditoria::orderBy('nombre_informe')->get(['id', 'nombre_informe', 'fecha_informe']);

        $procesosEstaticos = [
            'Planeación',
            'Preinscripción',
            'Inscripción',
            'Reinscripción',
            'Titulación',
            'Enseñanza/Aprendizaje',
            'Contratación o Control de Personal',
            'Vinculación',
            'TI',
            'Gestión de Recursos',
            'Laboratorios y Talleres',
            'Centro de Información',
        ];

        $procesosCustom = DB::table('procesos_custom')
            ->select('proceso')
            ->distinct()
            ->pluck('proceso')
            ->toArray();

        $todosLosProcesos = array_unique(array_merge($procesosEstaticos, $procesosCustom));
        sort($todosLosProcesos);

        return view('auditoria.solicitudes.index', compact('anios', 'informes', 'todosLosProcesos'));
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
                'folio_solicitud'          => 'nullable|string|max:50',
                'fecha_solicitud'          => 'nullable|date',
                'responsable_accion'       => 'nullable|string|max:255',
                'fecha_aplicacion'         => 'nullable|date',
                'actividades_verificacion' => 'nullable|string',
                'fecha_verificacion'       => 'nullable|date',
                'estatus'                  => 'nullable|in:No Atendida,En Proceso,Cerrado',
                'archivo'                  => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt',
                'informe_id'               => 'nullable|exists:informes_auditoria,id',
                'fecha_informe'            => 'nullable|date',
                'procesos_auditados'       => 'nullable|string',
                'tipo_solicitud'           => 'nullable|in:No Conformidad,Oportunidad de Mejora',
            ]);

            if ($request->has('fecha_aplicacion') && strlen($request->fecha_aplicacion) == 7) {
                $validated['fecha_aplicacion'] = $request->fecha_aplicacion . '-01';
            }
            if ($request->has('fecha_verificacion') && strlen($request->fecha_verificacion) == 7) {
                $validated['fecha_verificacion'] = $request->fecha_verificacion . '-01';
            }

            $data = [
                'folio_solicitud'          => $validated['folio_solicitud'] ?? null,
                'fecha_solicitud'          => $validated['fecha_solicitud'] ?? null,
                'responsable_accion'       => $validated['responsable_accion'] ?? null,
                'fecha_aplicacion'         => $validated['fecha_aplicacion'] ?? null,
                'actividades_verificacion' => $validated['actividades_verificacion'] ?? null,
                'fecha_verificacion'       => $validated['fecha_verificacion'] ?? null,
                'estatus'                  => $validated['estatus'] ?? null,
                'informe_id'               => $validated['informe_id'] ?? null,
                'fecha_informe'            => $validated['fecha_informe'] ?? null,
                'procesos_auditados'       => $validated['procesos_auditados'] ?? null,
                'tipo_solicitud'           => $validated['tipo_solicitud'] ?? null,
            ];

            if ($request->hasFile('archivo')) {
                $file         = $request->file('archivo');
                $originalName = $file->getClientOriginalName();
                $extension    = $file->getClientOriginalExtension();
                $fileName     = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                $path         = $file->storeAs('solicitudes_mejora', $fileName, 'public');

                $data['archivo_nombre'] = $originalName;
                $data['archivo_ruta']   = $path;

                Log::info('Archivo guardado: ' . $path);
            }

            $solicitud = SolicitudMejora::create($data);
            Log::info('Solicitud creada con ID: ' . $solicitud->id);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora guardada correctamente',
                'data'    => $solicitud
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al guardar solicitud:', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
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
                'folio_solicitud'          => 'nullable|string|max:50',
                'fecha_solicitud'          => 'nullable|date',
                'responsable_accion'       => 'nullable|string|max:255',
                'fecha_aplicacion'         => 'nullable|date',
                'actividades_verificacion' => 'nullable|string',
                'fecha_verificacion'       => 'nullable|date',
                'estatus'                  => 'nullable|in:No Atendida,En Proceso,Cerrado',
                'archivo'                  => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt',
                'informe_id'               => 'nullable|exists:informes_auditoria,id',
                'fecha_informe'            => 'nullable|date',
                'procesos_auditados'       => 'nullable|string',
                'tipo_solicitud'           => 'nullable|in:No Conformidad,Oportunidad de Mejora',
            ]);

            if ($request->has('fecha_aplicacion') && strlen($request->fecha_aplicacion) == 7) {
                $validated['fecha_aplicacion'] = $request->fecha_aplicacion . '-01';
            }
            if ($request->has('fecha_verificacion') && strlen($request->fecha_verificacion) == 7) {
                $validated['fecha_verificacion'] = $request->fecha_verificacion . '-01';
            }

            $data = [
                'folio_solicitud'          => $validated['folio_solicitud'] ?? null,
                'fecha_solicitud'          => $validated['fecha_solicitud'] ?? null,
                'responsable_accion'       => $validated['responsable_accion'] ?? null,
                'fecha_aplicacion'         => $validated['fecha_aplicacion'] ?? null,
                'actividades_verificacion' => $validated['actividades_verificacion'] ?? null,
                'fecha_verificacion'       => $validated['fecha_verificacion'] ?? null,
                'estatus'                  => $validated['estatus'] ?? null,
                'informe_id'               => $validated['informe_id'] ?? null,
                'fecha_informe'            => $validated['fecha_informe'] ?? null,
                'procesos_auditados'       => $validated['procesos_auditados'] ?? null,
                'tipo_solicitud'           => $validated['tipo_solicitud'] ?? null,
            ];

            if ($request->hasFile('archivo')) {
                if ($solicitud->archivo_ruta) {
                    Storage::disk('public')->delete($solicitud->archivo_ruta);
                }

                $file         = $request->file('archivo');
                $originalName = $file->getClientOriginalName();
                $extension    = $file->getClientOriginalExtension();
                $fileName     = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                $path         = $file->storeAs('solicitudes_mejora', $fileName, 'public');

                $data['archivo_nombre'] = $originalName;
                $data['archivo_ruta']   = $path;
            }

            $solicitud->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora actualizada correctamente',
                'data'    => $solicitud
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

    // ===== MÉTODO: N.C y O.M por proceso según informe =====
    public function ncOmPorProceso(Request $request)
    {
        try {
            $informeId = $request->get('informe_id');
            $proceso   = $request->get('proceso');

            if (!$informeId || !$proceso) {
                return response()->json(['nc' => 0, 'om' => 0]);
            }

            $informe = InformeAuditoria::find($informeId);
            if (!$informe || !$informe->nc_om_por_proceso) {
                return response()->json(['nc' => 0, 'om' => 0]);
            }

            $encontrado = collect($informe->nc_om_por_proceso)
                ->first(function($item) use ($proceso) {
                    if (!isset($item['proceso'])) return false;

                    $itemNorm    = strtolower(trim(preg_replace('/\s+/', ' ', $item['proceso'])));
                    $procesoNorm = strtolower(trim(preg_replace('/\s+/', ' ', $proceso)));

                    if ($itemNorm === $procesoNorm) return true;
                    if (str_contains($itemNorm, $procesoNorm)) return true;
                    if (str_contains($procesoNorm, $itemNorm)) return true;

                    similar_text($itemNorm, $procesoNorm, $percent);
                    return $percent >= 70;
                });

            return response()->json([
                'nc' => $encontrado['nc'] ?? 0,
                'om' => $encontrado['om'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ncOmPorProceso: ' . $e->getMessage());
            return response()->json(['nc' => 0, 'om' => 0]);
        }
    }

    // ===== MÉTODO: Datos para gráficas de solicitudes =====
    public function graficasSolicitudes(Request $request)
    {
        try {
            $anio    = $request->get('anio');
            $proceso = $request->get('proceso');

            $query = SolicitudMejora::query();

            if ($anio) {
                $query->whereYear('created_at', $anio);
            }
            if ($proceso) {
                $query->where('procesos_auditados', $proceso);
            }

            $query->whereNotNull('estatus')->where('estatus', '!=', '');
            $solicitudes = $query->get();

            // Gráfica 1: Todas las solicitudes por estatus
            $todasPorEstatus = [
                'No Atendida' => $solicitudes->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $solicitudes->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $solicitudes->where('estatus', 'Cerrado')->count(),
            ];

            // Gráfica 2: Solo No Conformidades por estatus (En Proceso y Cerrado)
            $noConformidades = $solicitudes->where('tipo_solicitud', 'No Conformidad');
            $ncPorEstatus = [
                'No Atendida' => $noConformidades->where('estatus', 'No Atendida')->count(),
                'En Proceso' => $noConformidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'    => $noConformidades->where('estatus', 'Cerrado')->count(),
            ];

            // Gráfica 3: Solo Oportunidades de Mejora por estatus (En Proceso y Cerrado)
            $oportunidades = $solicitudes->where('tipo_solicitud', 'Oportunidad de Mejora');
            $omPorEstatus = [
                'No Atendida' => $oportunidades->where('estatus', 'No Atendida')->count(),
                'En Proceso' => $oportunidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'    => $oportunidades->where('estatus', 'Cerrado')->count(),
            ];

            // Años disponibles para el filtro
            $anios = SolicitudMejora::selectRaw('YEAR(created_at) as anio')
                ->distinct()
                ->orderBy('anio', 'desc')
                ->pluck('anio');

            // Procesos disponibles para el filtro
            $procesosDisponibles = SolicitudMejora::whereNotNull('procesos_auditados')
                ->whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->distinct()
                ->pluck('procesos_auditados');

            return response()->json([
                'todas_por_estatus' => $todasPorEstatus,
                'nc_por_estatus'    => $ncPorEstatus,
                'om_por_estatus'    => $omPorEstatus,
                'total'             => $solicitudes->count(),
                'anios'             => $anios,
                'procesos'          => $procesosDisponibles,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en graficasSolicitudes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }

    public function ncOmPorProcesoAnio(Request $request)
    {
        try {
            $proceso = $request->get('proceso');
            $anio    = $request->get('anio');

            if (!$proceso) {
                return response()->json(['nc' => 0, 'om' => 0]);
            }

            $query = InformeAuditoria::whereNotNull('nc_om_por_proceso');
            if ($anio) {
                $query->whereYear('fecha_informe', $anio);
            }

            $informes = $query->get();
            $totalNc  = 0;
            $totalOm  = 0;

            foreach ($informes as $informe) {
                $encontrado = collect($informe->nc_om_por_proceso)
                    ->first(function($item) use ($proceso) {
                        if (!isset($item['proceso'])) return false;
                        $itemNorm    = strtolower(trim(preg_replace('/\s+/', ' ', $item['proceso'])));
                        $procesoNorm = strtolower(trim(preg_replace('/\s+/', ' ', $proceso)));
                        if ($itemNorm === $procesoNorm) return true;
                        if (str_contains($itemNorm, $procesoNorm)) return true;
                        if (str_contains($procesoNorm, $itemNorm)) return true;
                        similar_text($itemNorm, $procesoNorm, $percent);
                        return $percent >= 70;
                    });

                if ($encontrado) {
                    $totalNc += $encontrado['nc'] ?? 0;
                    $totalOm += $encontrado['om'] ?? 0;
                }
            }

            return response()->json(['nc' => $totalNc, 'om' => $totalOm]);

        } catch (\Exception $e) {
            Log::error('Error en ncOmPorProcesoAnio: ' . $e->getMessage());
            return response()->json(['nc' => 0, 'om' => 0]);
        }
    }

     public function historico()
    {
        try {
            // Totales generales históricos
            $solicitudes = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->get();
 
            $totales = [
                'No Atendida' => $solicitudes->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $solicitudes->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $solicitudes->where('estatus', 'Cerrado')->count(),
            ];
 
            $total = array_sum($totales);

            $noConformidades = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->where('tipo_solicitud', 'No Conformidad')
                ->get();

            $oportunidades = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->where('tipo_solicitud', 'Oportunidad de Mejora')
                ->get();

            $totalesNC = [
                'No Atendida' => $noConformidades->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $noConformidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $noConformidades->where('estatus', 'Cerrado')->count(),
            ];

            $totalesOM = [
                'No Atendida' => $oportunidades->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $oportunidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $oportunidades->where('estatus', 'Cerrado')->count(),
            ];
 
            // Detalle por año
            $porAnio = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->whereNotNull('fecha_solicitud')
                ->selectRaw('YEAR(fecha_solicitud) as anio, estatus, COUNT(*) as total')
                ->groupBy('anio', 'estatus')
                ->orderBy('anio', 'desc')
                ->get();
 
            // Transformar en formato por año
            $aniosData = [];
            foreach ($porAnio as $row) {
                if (!isset($aniosData[$row->anio])) {
                    $aniosData[$row->anio] = [
                        'anio'         => $row->anio,
                        'No Atendida'  => 0,
                        'En Proceso'   => 0,
                        'Cerrado'      => 0,
                    ];
                }
                $aniosData[$row->anio][$row->estatus] = $row->total;
            }
 
            return response()->json([
                'total'    => $total,
                'totales'  => $totales,
                'por_anio' => array_values($aniosData),
                'totales_nc' => $totalesNC,
                'totales_om' => $totalesOM,
            ]);
 
        } catch (\Exception $e) {
            Log::error('Error en historico: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }
}