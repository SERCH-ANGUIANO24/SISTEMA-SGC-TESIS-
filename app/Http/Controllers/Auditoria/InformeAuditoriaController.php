<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\InformeAuditoria;
use App\Models\Auditoria;
use App\Models\ProcesoCustom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InformeAuditoriaController extends Controller
{
    /**
     * Combina la lista estática base + procesos de procesos_custom,
     * elimina duplicados y ordena alfabéticamente.
     */
    private function getProcesos(): array
    {
        $base = [
            'Planeación',
            'Preinscripción',
            'Inscripción',
            'Reinscripción',
            'Titulación',
            'Enseñanza Aprendizaje',
            'Contratación u control de personal',
            'Vinculación',
            'Tecnologías de la información',
            'Gestión de Recursos',
            'Laboratorios y Talleres',
            'Centro de Información',
        ];

        $custom = ProcesoCustom::whereNotNull('proceso')
            ->where('proceso', '!=', '')
            ->distinct()
            ->orderBy('proceso')
            ->pluck('proceso')
            ->toArray();

        return collect(array_merge($base, $custom))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    // ------------------------------------------------------------------
    // PROCESOS CUSTOM — devuelve JSON para autocomplete en el blade
    // ------------------------------------------------------------------
    public function getProcesosCustom()
    {
        $procesos = ProcesoCustom::whereNotNull('proceso')
            ->where('proceso', '!=', '')
            ->distinct()
            ->orderBy('proceso')
            ->pluck('proceso');

        return response()->json($procesos);
    }

    // ------------------------------------------------------------------
    // INDEX
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
        // Registrar visualización del módulo
        \App\Helpers\HistorialVersionesHelper::ver('INFORMES_AUDITORIA', null, 'index');

        $query = InformeAuditoria::with('auditoriaRelacionada');

        if ($request->filled('anio')) {
            $query->whereYear('fecha_auditoria', $request->anio);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_auditoria', $request->tipo);
        }

        if ($request->filled('buscar')) {
            $query->where('nombre_informe', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('orden')) {
            switch ($request->orden) {
                case 'nombre-asc':
                    $query->orderBy('nombre_informe', 'asc');
                    break;
                case 'nombre-desc':
                    $query->orderBy('nombre_informe', 'desc');
                    break;
                case 'fecha-asc':
                    $query->orderBy('fecha_auditoria', 'asc');
                    break;
                case 'fecha-desc':
                    $query->orderBy('fecha_auditoria', 'desc');
                    break;
                default:
                    $query->orderByDesc('fecha_auditoria');
                    break;
            }
        } else {
            $query->orderByDesc('fecha_auditoria');
        }

        $informes = $query->paginate(10)->withQueryString();

        $aniosDisponibles = InformeAuditoria::selectRaw('YEAR(fecha_auditoria) as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');

        $planesAuditoria = Auditoria::orderByDesc('fecha_inicio')->get(['id', 'nombre_auditoria', 'fecha_inicio', 'fecha_fin']);

        $procesos = $this->getProcesos();

        return view('auditoria.informes.index', compact(
            'informes',
            'aniosDisponibles',
            'planesAuditoria',
            'procesos'
        ));
    }

    // ------------------------------------------------------------------
    // STORE
    // ------------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $this->validarFormulario($request);

        if (empty($validated['fecha_auditoria'])) {
            $validated['fecha_auditoria'] = $validated['fecha_inicio']
                ?? $validated['fecha_informe'];
        }

        if ($request->hasFile('documento')) {
            $file    = $request->file('documento');
            $nombre  = time() . '_' . $file->getClientOriginalName();
            $ruta    = $file->storeAs('informes_auditoria', $nombre, 'public');
            $validated['documento_path']   = $ruta;
            $validated['documento_nombre'] = $file->getClientOriginalName();
        }

        $validated['procesos_auditados'] = $request->procesos_auditados ?? [];
        $validated['nc_om_por_proceso'] = $this->buildNcOmPorProceso($request);
        $validated['no_conformidades']   = collect($validated['nc_om_por_proceso'])->sum('nc');
        $validated['oportunidades_mejora'] = collect($validated['nc_om_por_proceso'])->sum('om');

        $informe = InformeAuditoria::create($validated);

        // Registrar SUBIR (documento)
        \App\Helpers\HistorialVersionesHelper::subir('INFORMES_AUDITORIA', $informe);

        // ── NOTIFICACIÓN: nuevo informe subido ─────────────────────────
        $notif = app(\App\Services\NotificacionService::class);
        $notif->enviarATodos(
            titulo:     'Nuevo informe disponible: ' . $informe->nombre_informe,
            mensaje:    'Se ha publicado un nuevo informe de auditoría.' . PHP_EOL .
                        'Tipo de auditoría: ' . $informe->tipo_auditoria . PHP_EOL .
                        'Auditor Líder: ' . $informe->auditor_lider . PHP_EOL .
                        'Fecha de registro del informe: ' . \Carbon\Carbon::parse($informe->created_at)->format('d/m/Y'),
            tipo:       'info',
            icono:      'bi-file-earmark-text',
            url:        ('/auditoria/informes'),
            email:      true,
            docId:      (string) $informe->id,
            tipoEvento: 'nuevo_informe'
        );
        // ── FIN NOTIFICACIÓN ───────────────────────────────────────────

        return response()->json(['success' => true, 'message' => 'Informe guardado correctamente.']);
    }

    // ------------------------------------------------------------------
    // SHOW (devuelve JSON para el modal de edición)
    // ------------------------------------------------------------------
    public function show(InformeAuditoria $informeAuditoria)
    {
        $informeAuditoria->load('auditoriaRelacionada');

        // Registrar visualización del detalle
        \App\Helpers\HistorialVersionesHelper::ver('INFORMES_AUDITORIA', $informeAuditoria, 'detalle');

        $data = $informeAuditoria->toArray();
        $data['fecha_informe']   = $informeAuditoria->fecha_informe->format('Y-m-d');
        $data['fecha_auditoria'] = $informeAuditoria->fecha_auditoria->format('Y-m-d');
        $fechaInicio = $informeAuditoria->fecha_inicio
            ?? ($informeAuditoria->auditoriaRelacionada?->fecha_inicio ?? null);
        $fechaFin = $informeAuditoria->fecha_fin
            ?? ($informeAuditoria->auditoriaRelacionada?->fecha_fin ?? null);
        $data['fecha_inicio'] = $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('Y-m-d') : null;
        $data['fecha_fin']    = $fechaFin    ? \Carbon\Carbon::parse($fechaFin)->format('Y-m-d')    : null;

        $data['nc_om_por_proceso'] = $informeAuditoria->nc_om_por_proceso ?? [];

        return response()->json([
            'informe'       => $data,
            'documento_url' => $informeAuditoria->documento_path
                ? Storage::url($informeAuditoria->documento_path)
                : null,
        ]);
    }

    // ------------------------------------------------------------------
    // UPDATE
    // ------------------------------------------------------------------
    public function update(Request $request, InformeAuditoria $informeAuditoria)
    {
        // Guardar datos anteriores para el historial
        $datosAnteriores = $informeAuditoria->toArray();

        $validated = $this->validarFormulario($request, $informeAuditoria->id);
        unset($validated['documento']);

        if (empty($validated['fecha_auditoria'])) {
            $validated['fecha_auditoria'] = $validated['fecha_inicio']
                ?? $informeAuditoria->fecha_auditoria->format('Y-m-d');
        }

        if ($request->hasFile('documento')) {
            if ($informeAuditoria->documento_path) {
                Storage::disk('public')->delete($informeAuditoria->documento_path);
            }
            $file    = $request->file('documento');
            $nombre  = time() . '_' . $file->getClientOriginalName();
            $ruta    = $file->storeAs('informes_auditoria', $nombre, 'public');
            $validated['documento_path']   = $ruta;
            $validated['documento_nombre'] = $file->getClientOriginalName();
        }

        $validated['procesos_auditados'] = $request->procesos_auditados ?? [];
        $validated['nc_om_por_proceso'] = $this->buildNcOmPorProceso($request);
        $validated['no_conformidades']    = collect($validated['nc_om_por_proceso'])->sum('nc');
        $validated['oportunidades_mejora'] = collect($validated['nc_om_por_proceso'])->sum('om');

        $informeAuditoria->update($validated);

        // Registrar EDICIÓN
        \App\Helpers\HistorialVersionesHelper::editar('INFORMES_AUDITORIA', $informeAuditoria, $datosAnteriores, $informeAuditoria->toArray());

        return response()->json(['success' => true, 'message' => 'Informe actualizado correctamente.']);
    }

    // ------------------------------------------------------------------
    // DESTROY (Soft Delete - NO elimina el archivo físico)
    // ------------------------------------------------------------------
    public function destroy(InformeAuditoria $informeAuditoria)
    {
        // Guardar datos antes de eliminar
        $datosInforme = $informeAuditoria->toArray();

        // Soft delete - NO elimina el archivo físico
        $informeAuditoria->delete();

        // Registrar ELIMINAR
        \App\Helpers\HistorialVersionesHelper::eliminar('INFORMES_AUDITORIA', $informeAuditoria, $datosInforme);

        return response()->json(['success' => true, 'message' => 'Informe eliminado correctamente.']);
    }

    // ------------------------------------------------------------------
    // RESTAURAR INFORME ELIMINADO
    // ------------------------------------------------------------------
    public function restaurar($id)
    {
        try {
            $informe = InformeAuditoria::withTrashed()->findOrFail($id);
            
            if (!$informe->trashed()) {
                return response()->json(['success' => false, 'message' => 'El informe no está eliminado'], 400);
            }
            
            // Verificar que no exista otro informe con el mismo nombre activo
            $existing = InformeAuditoria::where('nombre_informe', $informe->nombre_informe)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe un informe activo con el mismo nombre'], 400);
            }
            
            // Verificar que el archivo físico aún existe
            if ($informe->documento_path && !Storage::disk('public')->exists($informe->documento_path)) {
                return response()->json(['success' => false, 'message' => 'El archivo físico no existe en el servidor. No se puede restaurar.'], 400);
            }
            
            $informe->restore();
            
            \App\Helpers\HistorialVersionesHelper::restaurar('INFORMES_AUDITORIA', $informe);
            
            return response()->json(['success' => true, 'message' => 'Informe restaurado correctamente']);
        } catch (\Exception $e) {
            \Log::error('Error al restaurar informe: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar: ' . $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------------------------
    // ESTADÍSTICAS POR AÑO
    // ------------------------------------------------------------------
    public function estadisticasPorAnio(Request $request)
    {
        $anio = $request->get('anio', now()->year);
        $stats = InformeAuditoria::estadisticasPorAnio((int) $anio);

        $datosGrafica = InformeAuditoria::whereYear('fecha_auditoria', $anio)
            ->get(['nombre_informe', 'no_conformidades', 'oportunidades_mejora', 'procesos_auditados', 'nc_om_por_proceso'])
            ->map(fn($i) => [
                'nombre'               => $i->nombre_informe,
                'no_conformidades'     => $i->no_conformidades,
                'oportunidades_mejora' => $i->oportunidades_mejora,
                'procesos'             => $i->procesos_auditados ?? [],
                'nc_om_por_proceso'    => $i->nc_om_por_proceso ?? [],
            ]);

        return response()->json(array_merge($stats, [
            'datos_grafica' => $datosGrafica,
            'anios'         => InformeAuditoria::selectRaw('YEAR(fecha_auditoria) as anio')
                                   ->distinct()->orderByDesc('anio')->pluck('anio'),
        ]));
    }

    // ------------------------------------------------------------------
    // VER DOCUMENTO
    // ------------------------------------------------------------------
    public function verDocumento(InformeAuditoria $informeAuditoria)
    {
        if (!$informeAuditoria->documento_path) abort(404);

        // Registrar visualización del documento
        \App\Helpers\HistorialVersionesHelper::ver('INFORMES_AUDITORIA', $informeAuditoria, 'ver_archivo');

        $ruta = storage_path('app/public/' . $informeAuditoria->documento_path);
        if (!file_exists($ruta)) abort(404, 'Archivo no encontrado.');

        $extension = pathinfo($ruta, PATHINFO_EXTENSION);
        $mimes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv'  => 'text/csv',
        ];
        $mime = $mimes[strtolower($extension)] ?? 'application/octet-stream';

        return response()->file($ruta, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $informeAuditoria->documento_nombre . '"',
            'X-Frame-Options'     => 'SAMEORIGIN',
        ]);
    }

    // ------------------------------------------------------------------
    // DESCARGAR DOCUMENTO
    // ------------------------------------------------------------------
    public function descargar($id)
    {
        $informe = InformeAuditoria::withTrashed()->findOrFail($id);
        if (!$informe->documento_path) abort(404, 'El documento no existe');

        // Registrar descarga
        \App\Helpers\HistorialVersionesHelper::descargar('INFORMES_AUDITORIA', $informe);

        $path = storage_path('app/public/' . $informe->documento_path);
        if (!file_exists($path)) abort(404, 'El archivo no se encuentra en el servidor');
        return response()->download($path, $informe->documento_nombre);
    }

    // ------------------------------------------------------------------
    // GRÁFICA DE UN SOLO INFORME
    // ------------------------------------------------------------------
    public function graficaInforme(InformeAuditoria $informeAuditoria)
    {
        return response()->json([
            'informe'              => $informeAuditoria->nombre_informe,
            'no_conformidades'     => $informeAuditoria->no_conformidades,
            'oportunidades_mejora' => $informeAuditoria->oportunidades_mejora,
            'procesos_auditados'   => $informeAuditoria->procesos_auditados ?? [],
            'nc_om_por_proceso'    => $informeAuditoria->nc_om_por_proceso ?? [],
            'fecha_auditoria'      => $informeAuditoria->fecha_auditoria->format('d/m/Y'),
            'tipo'                 => $informeAuditoria->tipo_auditoria,
        ]);
    }

    // ------------------------------------------------------------------
    // OBTENER FECHA DE AUDITORÍA RELACIONADA (AJAX)
    // ------------------------------------------------------------------
    public function fechaAuditoriaRelacionada(Auditoria $auditoria)
    {
        return response()->json([
            'fecha_auditoria' => \Carbon\Carbon::parse($auditoria->fecha_inicio)->format('Y-m-d'),
        ]);
    }

    // ------------------------------------------------------------------
    // HELPER: construir array nc_om_por_proceso desde el request
    // ------------------------------------------------------------------
    private function buildNcOmPorProceso(Request $request): array
    {
        $procesos = $request->input('procesos_auditados', []);
        $ncMap    = $request->input('nc_por_proceso', []);
        $omMap    = $request->input('om_por_proceso', []);
        $criterioMap = $request->input('criterio_por_proceso', []);

        $resultado = [];
        foreach ($procesos as $proceso) {
            $resultado[] = [
                'proceso' => $proceso,
                'criterio' => $criterioMap[$proceso] ?? '',
                'nc'      => isset($ncMap[$proceso]) ? max(0, (int) $ncMap[$proceso]) : 0,
                'om'      => isset($omMap[$proceso]) ? max(0, (int) $omMap[$proceso]) : 0,
            ];
        }
        return $resultado;
    }

    // ------------------------------------------------------------------
    // HELPER: validación
    // ------------------------------------------------------------------
    private function validarFormulario(Request $request, $ignoreId = null): array
    {
        $rules = [
            'nombre_informe'           => 'required|string|max:255',
            'tipo_auditoria'           => 'required|in:Interna,Externa',
            'auditor_lider'            => 'required|string|max:255',
            'fecha_informe'            => 'required|date',
            'fecha_auditoria'          => 'nullable|date',
            'fecha_inicio'             => 'required|date',
            'fecha_fin'                => 'required|date|after_or_equal:fecha_inicio',
            'auditoria_relacionada_id' => 'nullable|exists:auditorias,id',
            'procesos_auditados'       => 'nullable|array',
            'procesos_auditados.*'     => 'string',
            'no_conformidades'         => 'nullable|integer|min:0',
            'oportunidades_mejora'     => 'nullable|integer|min:0',
            'nc_por_proceso'           => 'nullable|array',
            'om_por_proceso'           => 'nullable|array',
            'criterio_por_proceso'     => 'nullable|array', 
            'documento'                => ($ignoreId
                ? 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,csv|max:10240'
                : 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv|max:10240'),
        ];

        return $request->validate($rules);
    }
}