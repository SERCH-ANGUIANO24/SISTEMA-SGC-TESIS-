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
                'informe_id'               => 'nullable|exists:informes_auditoria,id',
                'folio_solicitud'          => 'nullable|string|max:50',
                'fecha_solicitud'          => 'nullable|date',
                'responsable_accion'       => 'nullable|string|max:255',
                'fecha_aplicacion'         => 'nullable|date',
                'actividades_verificacion' => 'nullable|string',
                'fecha_verificacion'       => 'nullable|date',
                'estatus'                  => 'nullable|in:No Atendida,En Proceso,Cerrado',
                'archivo'                  => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt',
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
                'informe_id'               => $validated['informe_id'] ?? null,
                'folio_solicitud'          => $validated['folio_solicitud'] ?? null,
                'fecha_solicitud'          => $validated['fecha_solicitud'] ?? null,
                'responsable_accion'       => $validated['responsable_accion'] ?? null,
                'fecha_aplicacion'         => $validated['fecha_aplicacion'] ?? null,
                'actividades_verificacion' => $validated['actividades_verificacion'] ?? null,
                'fecha_verificacion'       => $validated['fecha_verificacion'] ?? null,
                'estatus'                  => $validated['estatus'] ?? null,
                'fecha_informe'            => $validated['fecha_informe'] ?? null,
                'procesos_auditados'       => $validated['procesos_auditados'] ?? null,
                'tipo_solicitud'           => $validated['tipo_solicitud'] ?? null,
            ];

            // Crear la solicitud PRIMERO
            $solicitud = SolicitudMejora::create($data);

            // Procesar el archivo si existe
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                
                $fileName = time() . '_' . $solicitud->id . '_' . uniqid() . '.' . $extension;
                $path = $file->storeAs('solicitudes_mejora', $fileName, 'public');
                
                $solicitud->archivo_nombre = $originalName;
                $solicitud->archivo_ruta = $path;
                $solicitud->save();
                
                Log::info('Archivo guardado', ['path' => $path, 'id' => $solicitud->id]);
            }

            // ── NOTIFICACIONES ────────────────────────────────────────────
            if (!empty($data['procesos_auditados']) && !empty($data['tipo_solicitud'])) {

                $proceso       = $data['procesos_auditados'];
                $tipoSolicitud = $data['tipo_solicitud'];

                // Mapa estático proceso → departamentos
                $mapaProcesos = [
                    'Planeación'                         => ['Rectoría', 'Dirección Académica', 'Dirección de Administración y Finanzas'],
                    'Preinscripción'                     => ['Servicios Escolares'],
                    'Inscripción'                        => ['Servicios Escolares'],
                    'Reinscripción'                      => ['Servicios Escolares'],
                    'Titulación'                         => ['Servicios Escolares'],
                    'Enseñanza/Aprendizaje'              => ['Dirección Académica'],
                    'Contratación o Control de Personal' => ['Recursos Humanos'],
                    'Vinculación'                        => ['Vinculación'],
                    'TI'                                 => ['Sistemas Computacionales'],
                    'Gestión de Recursos'                => ['Recursos Financieros', 'Almacén'],
                    'Laboratorios y Talleres'            => ['Encargado/a de Laboratorios'],
                    'Centro de Información'              => ['Biblioteca'],
                ];

                // Agregar departamentos de procesos custom registrados por admin/superadmin
                $procesosCustom = DB::table('procesos_custom')
                    ->select('proceso', 'departamento')
                    ->get();

                foreach ($procesosCustom as $pc) {
                    $p = trim($pc->proceso);
                    $d = trim($pc->departamento);
                    if (!$p || !$d) continue;
                    if (!isset($mapaProcesos[$p])) {
                        $mapaProcesos[$p] = [];
                    }
                    if (!in_array($d, $mapaProcesos[$p])) {
                        $mapaProcesos[$p][] = $d;
                    }
                }

                // Obtener departamentos del proceso seleccionado
                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                // Calcular fecha límite en días hábiles (15 días hábiles desde fecha_informe)
                $diasHabiles = 0;
                $fechaActual = isset($data['fecha_informe']) && $data['fecha_informe']
                                ? \Carbon\Carbon::parse($data['fecha_informe'])
                                : now();
                $fechaLimite = $fechaActual->copy();

                while ($diasHabiles < 15) {
                    $fechaLimite->addDay();
                    if (!in_array($fechaLimite->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY])) {
                        $diasHabiles++;
                    }
                }

                $fechaLimiteFormato = $fechaLimite->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
                $fechaInicio        = $fechaActual->copy()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

                // Buscar usuarios que pertenezcan al proceso O a cualquiera de sus departamentos
                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                        $query->where('proceso', $proceso);
                        if (!empty($departamentosDelProceso)) {
                            $query->orWhere(function ($q) use ($proceso, $departamentosDelProceso) {
                                $q->where('proceso', $proceso)
                                ->whereIn('departamento', $departamentosDelProceso);
                            });
                            // También notificar usuarios registrados solo por departamento
                            $query->orWhereIn('departamento', $departamentosDelProceso);
                        }
                    })
                    ->get()
                    ->unique('id'); // Evitar duplicados si un usuario coincide por ambos campos

                if ($usuariosANotificar->isNotEmpty()) {
                    $notif = app(\App\Services\NotificacionService::class);

                    foreach ($usuariosANotificar as $usuario) {
                        $notif->enviar(
                            userId:     $usuario->id,
                            titulo:     'Nueva solicitud de mejora asignada a tu proceso',
                            mensaje:    'Se ha registrado una nueva solicitud de tipo "' . $tipoSolicitud . '" ' .
                                        'para el proceso "' . $proceso . '".' . PHP_EOL . PHP_EOL .
                                        '⏱ Cronómetro de días hábiles (15 días)' . PHP_EOL .
                                        'Fecha de inicio: ' . $fechaInicio . PHP_EOL .
                                        'Fecha límite: ' . $fechaLimiteFormato . PHP_EOL . PHP_EOL .
                                        'Tienes 15 días hábiles (lunes a viernes, excluyendo sábados y domingos) ' .
                                        'para atender esta solicitud.' . PHP_EOL .
                                        'Ingresa al sistema para revisarla.',
                            tipo:       'advertencia',
                            icono:      'bi-clipboard-check',
                            url:        route('auditoria.solicitudes.index'),
                            email:      true,
                            docId:      (string) $solicitud->id,
                            tipoEvento: 'solicitud_mejora'
                        );
                    }
                }
            }
            // ── FIN NOTIFICACIONES ────────────────────────────────────────

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
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);
            
            // Guardar datos anteriores para las notificaciones
            $datosAnteriores = $solicitud->toArray();

            $validated = $request->validate([
                'informe_id'               => 'nullable|exists:informes_auditoria,id',
                'folio_solicitud'          => 'nullable|string|max:50',
                'fecha_solicitud'          => 'nullable|date',
                'responsable_accion'       => 'nullable|string|max:255',
                'fecha_aplicacion'         => 'nullable|date',
                'actividades_verificacion' => 'nullable|string',
                'fecha_verificacion'       => 'nullable|date',
                'estatus'                  => 'nullable|in:No Atendida,En Proceso,Cerrado',
                'archivo'                  => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,txt',
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
                'informe_id'               => $validated['informe_id'] ?? null,
                'folio_solicitud'          => $validated['folio_solicitud'] ?? null,
                'fecha_solicitud'          => $validated['fecha_solicitud'] ?? null,
                'responsable_accion'       => $validated['responsable_accion'] ?? null,
                'fecha_aplicacion'         => $validated['fecha_aplicacion'] ?? null,
                'actividades_verificacion' => $validated['actividades_verificacion'] ?? null,
                'fecha_verificacion'       => $validated['fecha_verificacion'] ?? null,
                'estatus'                  => $validated['estatus'] ?? null,
                'fecha_informe'            => $validated['fecha_informe'] ?? null,
                'procesos_auditados'       => $validated['procesos_auditados'] ?? null,
                'tipo_solicitud'           => $validated['tipo_solicitud'] ?? null,
            ];

            if ($request->hasFile('archivo')) {
                if ($solicitud->archivo_ruta && Storage::disk('public')->exists($solicitud->archivo_ruta)) {
                    Storage::disk('public')->delete($solicitud->archivo_ruta);
                }

                $file = $request->file('archivo');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '_' . $solicitud->id . '_' . uniqid() . '.' . $extension;
                $path = $file->storeAs('solicitudes_mejora', $fileName, 'public');

                $data['archivo_nombre'] = $originalName;
                $data['archivo_ruta'] = $path;
            }

            $solicitud->update($data);

            // ── NOTIFICACIONES AL CAMBIAR ESTATUS ─────────────────────────
            $estatusAnterior = $datosAnteriores['estatus'] ?? null;
            $estatusNuevo    = $data['estatus'] ?? null;

            // NOTIFICACIONES AL CAMBIAR ESTATUS A "En Proceso"
            if ($estatusAnterior !== 'En Proceso' && $estatusNuevo === 'En Proceso') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                $fechaAplicacion = $solicitud->fecha_aplicacion
                    ? \Carbon\Carbon::parse($solicitud->fecha_aplicacion)
                                    ->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                    : 'No establecida';

                $mapaProcesos = [
                    'Planeación'                         => ['Rectoría', 'Dirección Académica', 'Dirección de Administración y Finanzas'],
                    'Preinscripción'                     => ['Servicios Escolares'],
                    'Inscripción'                        => ['Servicios Escolares'],
                    'Reinscripción'                      => ['Servicios Escolares'],
                    'Titulación'                         => ['Servicios Escolares'],
                    'Enseñanza/Aprendizaje'              => ['Dirección Académica'],
                    'Contratación o Control de Personal' => ['Recursos Humanos'],
                    'Vinculación'                        => ['Vinculación'],
                    'TI'                                 => ['Sistemas Computacionales'],
                    'Gestión de Recursos'                => ['Recursos Financieros', 'Almacén'],
                    'Laboratorios y Talleres'            => ['Encargado/a de Laboratorios'],
                    'Centro de Información'              => ['Biblioteca'],
                ];

                $procesosCustom = DB::table('procesos_custom')->select('proceso', 'departamento')->get();
                foreach ($procesosCustom as $pc) {
                    $p = trim($pc->proceso);
                    $d = trim($pc->departamento);
                    if (!$p || !$d) continue;
                    if (!isset($mapaProcesos[$p])) $mapaProcesos[$p] = [];
                    if (!in_array($d, $mapaProcesos[$p])) $mapaProcesos[$p][] = $d;
                }

                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                        $query->where('proceso', $proceso);
                        if (!empty($departamentosDelProceso)) {
                            $query->orWhereIn('departamento', $departamentosDelProceso);
                        }
                    })
                    ->get()
                    ->unique('id');

                if ($usuariosANotificar->isNotEmpty()) {
                    $notif = app(\App\Services\NotificacionService::class);
                    foreach ($usuariosANotificar as $usuario) {
                        $notif->enviar(
                            userId:     $usuario->id,
                            titulo:     'Tu solicitud de mejora está En Proceso ✓',
                            mensaje:    'La solicitud de tipo "' . $tipoSolicitud . '" ' .
                                        'para el proceso "' . $proceso . '" ha sido actualizada.' . PHP_EOL . PHP_EOL .
                                        '📋 Detalle de la solicitud:' . PHP_EOL .
                                        'Estatus: En Proceso' . PHP_EOL .
                                        'Responsable: ' . ($solicitud->responsable_accion ?? 'No establecido') . PHP_EOL .
                                        'Periodo de Aplicación: ' . $fechaAplicacion . PHP_EOL .
                                        'No. Identificación: ' . ($solicitud->folio_solicitud ?? '-') . PHP_EOL . PHP_EOL .
                                        'Ingresa al sistema para revisar el detalle completo.',
                            tipo:       'exito',
                            icono:      'bi-clipboard-check',
                            url:        route('auditoria.solicitudes.index'),
                            email:      true,
                            docId:      (string) $solicitud->id,
                            tipoEvento: 'solicitud_en_proceso'
                        );
                    }
                }
            }

            // NOTIFICACIONES AL CAMBIAR ESTATUS A "Cerrado"
            if ($estatusAnterior === 'En Proceso' && $estatusNuevo === 'Cerrado') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                $mapaProcesos = [
                    'Planeación'                         => ['Rectoría', 'Dirección Académica', 'Dirección de Administración y Finanzas'],
                    'Preinscripción'                     => ['Servicios Escolares'],
                    'Inscripción'                        => ['Servicios Escolares'],
                    'Reinscripción'                      => ['Servicios Escolares'],
                    'Titulación'                         => ['Servicios Escolares'],
                    'Enseñanza/Aprendizaje'              => ['Dirección Académica'],
                    'Contratación o Control de Personal' => ['Recursos Humanos'],
                    'Vinculación'                        => ['Vinculación'],
                    'TI'                                 => ['Sistemas Computacionales'],
                    'Gestión de Recursos'                => ['Recursos Financieros', 'Almacén'],
                    'Laboratorios y Talleres'            => ['Encargado/a de Laboratorios'],
                    'Centro de Información'              => ['Biblioteca'],
                ];

                $procesosCustom = DB::table('procesos_custom')->select('proceso', 'departamento')->get();
                foreach ($procesosCustom as $pc) {
                    $p = trim($pc->proceso);
                    $d = trim($pc->departamento);
                    if (!$p || !$d) continue;
                    if (!isset($mapaProcesos[$p])) $mapaProcesos[$p] = [];
                    if (!in_array($d, $mapaProcesos[$p])) $mapaProcesos[$p][] = $d;
                }

                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                        $query->where('proceso', $proceso);
                        if (!empty($departamentosDelProceso)) {
                            $query->orWhereIn('departamento', $departamentosDelProceso);
                        }
                    })
                    ->get()
                    ->unique('id');

                if ($usuariosANotificar->isNotEmpty()) {
                    $notif = app(\App\Services\NotificacionService::class);
                    foreach ($usuariosANotificar as $usuario) {
                        $notif->enviar(
                            userId:     $usuario->id,
                            titulo:     'Solicitud de mejora cerrada ✓',
                            mensaje:    'La solicitud de tipo "' . $tipoSolicitud . '" ' .
                                        'para el proceso "' . $proceso . '" ha sido cerrada.' . PHP_EOL . PHP_EOL .
                                        '✅ SOLICITUD CERRADA' . PHP_EOL .
                                        'Esta solicitud de mejora ha sido atendida en tiempo y forma.' . PHP_EOL . PHP_EOL .
                                        'No. Identificación: ' . ($solicitud->folio_solicitud ?? '-') . PHP_EOL .
                                        'Responsable: ' . ($solicitud->responsable_accion ?? 'No establecido') . PHP_EOL . PHP_EOL .
                                        'Ingresa al sistema para consultar el detalle.',
                            tipo:       'exito',
                            icono:      'bi-check-circle',
                            url:        route('auditoria.solicitudes.index'),
                            email:      true,
                            docId:      (string) $solicitud->id,
                            tipoEvento: 'solicitud_cerrada'
                        );
                    }
                }
            }

            // NOTIFICACIONES AL CAMBIAR ESTATUS A "No Atendida"
            if ($estatusAnterior === 'En Proceso' && $estatusNuevo === 'No Atendida') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                $mapaProcesos = [
                    'Planeación'                         => ['Rectoría', 'Dirección Académica', 'Dirección de Administración y Finanzas'],
                    'Preinscripción'                     => ['Servicios Escolares'],
                    'Inscripción'                        => ['Servicios Escolares'],
                    'Reinscripción'                      => ['Servicios Escolares'],
                    'Titulación'                         => ['Servicios Escolares'],
                    'Enseñanza/Aprendizaje'              => ['Dirección Académica'],
                    'Contratación o Control de Personal' => ['Recursos Humanos'],
                    'Vinculación'                        => ['Vinculación'],
                    'TI'                                 => ['Sistemas Computacionales'],
                    'Gestión de Recursos'                => ['Recursos Financieros', 'Almacén'],
                    'Laboratorios y Talleres'            => ['Encargado/a de Laboratorios'],
                    'Centro de Información'              => ['Biblioteca'],
                ];

                $procesosCustom = DB::table('procesos_custom')->select('proceso', 'departamento')->get();
                foreach ($procesosCustom as $pc) {
                    $p = trim($pc->proceso);
                    $d = trim($pc->departamento);
                    if (!$p || !$d) continue;
                    if (!isset($mapaProcesos[$p])) $mapaProcesos[$p] = [];
                    if (!in_array($d, $mapaProcesos[$p])) $mapaProcesos[$p][] = $d;
                }

                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                        $query->where('proceso', $proceso);
                        if (!empty($departamentosDelProceso)) {
                            $query->orWhereIn('departamento', $departamentosDelProceso);
                        }
                    })
                    ->get()
                    ->unique('id');

                if ($usuariosANotificar->isNotEmpty()) {
                    $notif = app(\App\Services\NotificacionService::class);
                    foreach ($usuariosANotificar as $usuario) {
                        $notif->enviar(
                            userId:     $usuario->id,
                            titulo:     '⚠️ Solicitud de mejora no atendida',
                            mensaje:    'La solicitud de tipo "' . $tipoSolicitud . '" ' .
                                        'para el proceso "' . $proceso . '" no fue atendida.' . PHP_EOL . PHP_EOL .
                                        '⚠️ PLAZO DE ATENCIÓN VENCIDO' . PHP_EOL .
                                        'En este momento tu solicitud ha vencido el plazo de atención.' . PHP_EOL .
                                        'Por lo que es necesario contactarse con la Coordinación del SGC.' . PHP_EOL . PHP_EOL .
                                        'No. Identificación: ' . ($solicitud->folio_solicitud ?? '-') . PHP_EOL .
                                        'Responsable: ' . ($solicitud->responsable_accion ?? 'No establecido') . PHP_EOL . PHP_EOL .
                                        'Ingresa al sistema para más información.',
                            tipo:       'error',
                            icono:      'bi-exclamation-triangle',
                            url:        route('auditoria.solicitudes.index'),
                            email:      true,
                            docId:      (string) $solicitud->id,
                            tipoEvento: 'solicitud_no_atendida'
                        );
                    }
                }
            }

            // NOTIFICACIÓN "No Atendida" sin estatus previo
            if ($estatusNuevo === 'No Atendida' && $estatusAnterior !== 'No Atendida') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->whereNotIn('role', ['superadmin', 'admin'])
                    ->where('proceso', $proceso)
                    ->get()
                    ->unique('id');

                if ($usuariosANotificar->isNotEmpty()) {
                    $notif = app(\App\Services\NotificacionService::class);
                    foreach ($usuariosANotificar as $usuario) {
                        $notif->enviar(
                            userId:     $usuario->id,
                            titulo:     'Solicitud de mejora no atendida',
                            mensaje:    'La solicitud de tipo "' . $tipoSolicitud . '" ' .
                                        'para el proceso "' . $proceso . '" no fue atendida.' . PHP_EOL . PHP_EOL .
                                        '⚠️ PLAZO DE ATENCIÓN VENCIDO' . PHP_EOL .
                                        'En este momento tu solicitud ha vencido el plazo de atención.' . PHP_EOL .
                                        'Por lo que es necesario contactarse con la Coordinación del SGC.' . PHP_EOL . PHP_EOL .
                                        'No. Identificación: ' . ($solicitud->folio_solicitud ?? '-') . PHP_EOL .
                                        'Responsable: ' . ($solicitud->responsable_accion ?? 'No establecido') . PHP_EOL . PHP_EOL .
                                        'Ingresa al sistema para más información.',
                            tipo:       'error',
                            icono:      'bi-exclamation-triangle',
                            url:        route('auditoria.solicitudes.index'),
                            email:      true,
                            docId:      (string) $solicitud->id,
                            tipoEvento: 'solicitud_no_atendida'
                        );
                    }
                }
            }
            // ── FIN NOTIFICACIONES ────────────────────────────────────────

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora actualizada correctamente',
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

    public function restaurar($id)
    {
        try {
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);
            
            if (!$solicitud->trashed()) {
                return response()->json(['success' => false, 'message' => 'La solicitud no está eliminada'], 400);
            }
            
            if ($solicitud->folio_solicitud) {
                $existing = SolicitudMejora::where('folio_solicitud', $solicitud->folio_solicitud)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existing) {
                    return response()->json(['success' => false, 'message' => 'Ya existe una solicitud activa con el mismo folio'], 400);
                }
            }
            
            $solicitud->restore();
            
            return response()->json(['success' => true, 'message' => 'Solicitud de mejora restaurada correctamente']);
        } catch (\Exception $e) {
            Log::error('Error al restaurar solicitud: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar: ' . $e->getMessage()], 500);
        }
    }

    public function view($id)
    {
        try {
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);

            if (!$solicitud->archivo_ruta) {
                abort(404, 'No hay archivo asociado a esta solicitud');
            }

            $path = storage_path('app/public/' . $solicitud->archivo_ruta);

            if (!file_exists($path)) {
                $basePath = storage_path('app/public/solicitudes_mejora/');
                $files = glob($basePath . '*' . $solicitud->id . '*');
                
                if (empty($files)) {
                    abort(404, 'Archivo no encontrado en el servidor');
                }
                $path = $files[0];
            }

            $extension = strtolower(pathinfo($solicitud->archivo_nombre, PATHINFO_EXTENSION));
            
            if ($extension === 'pdf') {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $solicitud->archivo_nombre . '"'
                ]);
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                return response()->file($path);
            } elseif ($extension === 'txt') {
                return response()->file($path);
            } else {
                return response()->download($path, $solicitud->archivo_nombre);
            }
            
        } catch (\Exception $e) {
            Log::error('Error al ver archivo: ' . $e->getMessage());
            abort(404, 'Error: ' . $e->getMessage());
        }
    }

    public function download($id)
    {
        try {
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);

            if (!$solicitud->archivo_ruta) {
                abort(404, 'No hay archivo asociado a esta solicitud');
            }

            $path = storage_path('app/public/' . $solicitud->archivo_ruta);

            if (!file_exists($path)) {
                $basePath = storage_path('app/public/solicitudes_mejora/');
                $files = glob($basePath . '*' . $solicitud->id . '*');
                
                if (empty($files)) {
                    abort(404, 'Archivo no encontrado en el servidor');
                }
                $path = $files[0];
            }

            return response()->download($path, $solicitud->archivo_nombre);
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo: ' . $e->getMessage());
            abort(404, 'Error al descargar el archivo: ' . $e->getMessage());
        }
    }

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

    public function graficasSolicitudes(Request $request)
    {
        try {
            $anio    = $request->get('anio');
            $proceso = $request->get('proceso');

            $query = SolicitudMejora::query();

            if ($anio) {
                $query->whereYear('fecha_solicitud', $anio);
            }
            if ($proceso) {
                $query->where('procesos_auditados', $proceso);
            }

            $query->whereNotNull('estatus')->where('estatus', '!=', '');
            $solicitudes = $query->get();

            $todasPorEstatus = [
                'No Atendida' => $solicitudes->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $solicitudes->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $solicitudes->where('estatus', 'Cerrado')->count(),
            ];

            $noConformidades = $solicitudes->where('tipo_solicitud', 'No Conformidad');
            $ncPorEstatus = [
                'No Atendida' => $noConformidades->where('estatus', 'No Atendida')->count(),
                'En Proceso' => $noConformidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'    => $noConformidades->where('estatus', 'Cerrado')->count(),
            ];

            $oportunidades = $solicitudes->where('tipo_solicitud', 'Oportunidad de Mejora');
            $omPorEstatus = [
                'No Atendida' => $oportunidades->where('estatus', 'No Atendida')->count(),
                'En Proceso' => $oportunidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'    => $oportunidades->where('estatus', 'Cerrado')->count(),
            ];

            $anios = SolicitudMejora::selectRaw('YEAR(fecha_solicitud) as anio')
                ->distinct()
                ->orderBy('anio', 'desc')
                ->pluck('anio');

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

            $porAnio = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->whereNotNull('fecha_solicitud')
                ->selectRaw('YEAR(fecha_solicitud) as anio, estatus, COUNT(*) as total')
                ->groupBy('anio', 'estatus')
                ->orderBy('anio', 'desc')
                ->get();

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