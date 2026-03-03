<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\InformeAuditoria;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InformeAuditoriaController extends Controller
{
    /** Lista de procesos disponibles para auditar */
    private const PROCESOS = [
        'Planeación',
        'Preinscripción',
        'Inscripción',
        'Reincripción',
        'Enseñanza Aprendizaje',
        'Contratación u control de personal',
        'Vinculación',
        'Tecnologías de la información',
        'Gestión de Recursos',
    ];

    // ------------------------------------------------------------------
    // INDEX
    // ------------------------------------------------------------------
    public function index(Request $request)
    {
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

        // 👇 NUEVO: Aplicar ordenamiento según el filtro "Ordenar por"
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
            $query->orderByDesc('fecha_auditoria'); // Orden por defecto
        }

        $informes = $query->paginate(10)->withQueryString();

        // Años disponibles para el filtro
        $aniosDisponibles = InformeAuditoria::selectRaw('YEAR(fecha_auditoria) as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');

        // Planes de auditoría para el selector del formulario
        $planesAuditoria = Auditoria::orderByDesc('fecha_auditoria')->get(['id', 'nombre_auditoria', 'fecha_auditoria']);

        return view('auditoria.informes.index', compact(
            'informes',
            'aniosDisponibles',
            'planesAuditoria'
        ))->with('procesos', self::PROCESOS);
    }

    // ------------------------------------------------------------------
    // STORE
    // ------------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $this->validarFormulario($request);

        // Subir documento
        if ($request->hasFile('documento')) {
            $file    = $request->file('documento');
            $nombre  = time() . '_' . $file->getClientOriginalName();
            $ruta    = $file->storeAs('informes_auditoria', $nombre, 'public');
            $validated['documento_path']   = $ruta;
            $validated['documento_nombre'] = $file->getClientOriginalName();
        }

        $validated['procesos_auditados'] = $request->procesos_auditados ?? [];

        InformeAuditoria::create($validated);

        return response()->json(['success' => true, 'message' => 'Informe guardado correctamente.']);
    }

    // ------------------------------------------------------------------
    // SHOW (devuelve JSON para el modal de vista)
    // ------------------------------------------------------------------
    public function show(InformeAuditoria $informeAuditoria)
    {
        $informeAuditoria->load('auditoriaRelacionada');

        $data = $informeAuditoria->toArray();
        // Formatear fechas para input type="date"
        $data['fecha_informe']   = $informeAuditoria->fecha_informe->format('Y-m-d');
        $data['fecha_auditoria'] = $informeAuditoria->fecha_auditoria->format('Y-m-d');

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
        $validated = $this->validarFormulario($request, $informeAuditoria->id);
        unset($validated['documento']); //EVITAR SOBREECRIBIR SI NO SE SUBE UN ARCHIVO

        if ($request->hasFile('documento')) {
            // Borrar el anterior
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

        $informeAuditoria->update($validated);

        return response()->json(['success' => true, 'message' => 'Informe actualizado correctamente.']);
    }

    // ------------------------------------------------------------------
    // DESTROY
    // ------------------------------------------------------------------
    public function destroy(InformeAuditoria $informeAuditoria)
    {
        if ($informeAuditoria->documento_path) {
            Storage::disk('public')->delete($informeAuditoria->documento_path);
        }
        $informeAuditoria->delete();

        return response()->json(['success' => true, 'message' => 'Informe eliminado correctamente.']);
    }

    // ------------------------------------------------------------------
    // ESTADÍSTICAS POR AÑO (para el modal de gráficas)
    // ------------------------------------------------------------------
    public function estadisticasPorAnio(Request $request)
    {
        $anio = $request->get('anio', now()->year);
        $stats = InformeAuditoria::estadisticasPorAnio((int) $anio);

        // Datos para la gráfica: no conformidades y oportunidades de mejora por informe en ese año
        $datosGrafica = InformeAuditoria::whereYear('fecha_auditoria', $anio)
            ->get(['nombre_informe', 'no_conformidades', 'oportunidades_mejora', 'procesos_auditados'])
            ->map(fn($i) => [
                'nombre'               => $i->nombre_informe,
                'no_conformidades'     => $i->no_conformidades,
                'oportunidades_mejora' => $i->oportunidades_mejora,
                'procesos'             => $i->procesos_auditados ?? [],
            ]);

        return response()->json(array_merge($stats, [
            'datos_grafica'  => $datosGrafica,
            'anios'          => InformeAuditoria::selectRaw('YEAR(fecha_auditoria) as anio')
                                    ->distinct()->orderByDesc('anio')->pluck('anio'),
        ]));
    }

    // Ver documento (sirve el archivo directamente)
    public function verDocumento(InformeAuditoria $informeAuditoria)
    {
        if (!$informeAuditoria->documento_path) {
            abort(404);
        }

        $ruta = storage_path('app/public/' . $informeAuditoria->documento_path);

        if (!file_exists($ruta)) {
            abort(404, 'Archivo no encontrado.');
        }

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
    //DESCARGAR DOCUMENTO SUBDIDO 
    public function descargar($id)
    {
        $informe = InformeAuditoria::findOrFail($id);
        
        if (!$informe->documento_path) {
            abort(404, 'El documento no existe');
        }
        
        $path = storage_path('app/public/' . $informe->documento_path);
        
        if (!file_exists($path)) {
            abort(404, 'El archivo no se encuentra en el servidor');
        }
        
        return response()->download($path, $informe->documento_nombre);
    }

    // ------------------------------------------------------------------
    // GRÁFICA DE UN SOLO INFORME (acción "Ver Gráfica" en tabla)
    // ------------------------------------------------------------------
    public function graficaInforme(InformeAuditoria $informeAuditoria)
    {
        return response()->json([
            'informe'              => $informeAuditoria->nombre_informe,
            'no_conformidades'     => $informeAuditoria->no_conformidades,
            'oportunidades_mejora' => $informeAuditoria->oportunidades_mejora,
            'procesos_auditados'   => $informeAuditoria->procesos_auditados ?? [],
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
            'fecha_auditoria' => $auditoria->fecha_auditoria->format('Y-m-d'),
        ]);
    }

    // ------------------------------------------------------------------
    // HELPER: validación
    // ------------------------------------------------------------------
    private function validarFormulario(Request $request, $ignoreId = null): array
    {
        $rules = [
            'nombre_informe'          => 'required|string|max:255',
            'tipo_auditoria'          => 'required|in:Interna,Externa',
            'auditor_lider'           => 'required|string|max:255',
            'fecha_informe'           => 'required|date',
            'fecha_auditoria'         => 'required|date',
            'auditoria_relacionada_id'=> 'nullable|exists:auditorias,id',
            'procesos_auditados'      => 'nullable|array',
            'procesos_auditados.*'    => 'string',
            'no_conformidades'        => 'required|integer|min:0',
            'oportunidades_mejora'    => 'required|integer|min:0',
            'documento' => ($ignoreId ? 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,csv|max:10240' : 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv|max:10240'),
        ];

        return $request->validate($rules);
    }
    
}