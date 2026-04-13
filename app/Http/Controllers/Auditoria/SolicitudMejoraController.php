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
use Illuminate\Support\Facades\Gate;

// CONTROLADOR QUE GESTIONA TODAS LAS OPERACIONES DEL MÓDULO DE SOLICITUDES DE MEJORA
// PERMITE CREAR, EDITAR, ELIMINAR, RESTAURAR, VER Y DESCARGAR SOLICITUDES
// TAMBIÉN ENVÍA NOTIFICACIONES A LOS USUARIOS SEGÚN EL PROCESO Y EL ESTATUS DE CADA SOLICITUD
// Y GENERA ESTADÍSTICAS E HISTÓRICAS PARA LAS GRÁFICAS DEL MÓDULO
class SolicitudMejoraController extends Controller
{
    public function index()
    {
        // OBTIENE TODOS LOS AÑOS DISTINTOS EN QUE HAY SOLICITUDES REGISTRADAS
        // SE ORDENAN DE MÁS RECIENTE A MÁS ANTIGUO PARA EL FILTRO DE LA VISTA
        $anios = SolicitudMejora::selectRaw('YEAR(fecha_solicitud) as anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        // OBTIENE TODOS LOS INFORMES DE AUDITORÍA PARA EL SELECTOR DEL FORMULARIO
        $informes = InformeAuditoria::orderBy('nombre_informe')->get(['id', 'nombre_informe', 'fecha_informe']);

        // LISTA BASE DE PROCESOS PREDEFINIDOS EN EL SISTEMA
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
            'Sistema de Gestión de la Calidad'
        ];

        // OBTIENE LOS PROCESOS PERSONALIZADOS CREADOS POR LOS USUARIOS DESDE LA BD
        $procesosCustom = DB::table('procesos_custom')
            ->select('proceso')
            ->distinct()
            ->pluck('proceso')
            ->toArray();

        // COMBINA AMBAS LISTAS, ELIMINA DUPLICADOS Y ORDENA ALFABÉTICAMENTE
        $todosLosProcesos = array_unique(array_merge($procesosEstaticos, $procesosCustom));
        sort($todosLosProcesos);

        // RETORNA LA VISTA CON LOS AÑOS, INFORMES Y PROCESOS DISPONIBLES
        return view('auditoria.solicitudes.index', compact('anios', 'informes', 'todosLosProcesos'));
    }

    public function data(Request $request)
    {
        try {
            // INICIA LA CONSULTA BASE SOBRE EL MODELO DE SOLICITUDES DE MEJORA
            $query = SolicitudMejora::query();

            // APLICA FILTRO POR ESTATUS SI SE ENVIÓ DESDE EL FORMULARIO
            if ($request->filled('estatus')) {
                $query->where('estatus', $request->estatus);
            }

            // APLICA FILTRO POR AÑO SI SE ENVIÓ DESDE EL FORMULARIO
            if ($request->filled('anio')) {
                $query->whereYear('fecha_solicitud', $request->anio);
            }

            // OBTIENE LOS RESULTADOS ORDENADOS DE MÁS RECIENTE A MÁS ANTIGUO Y LOS RETORNA EN JSON
            $solicitudes = $query->orderBy('created_at', 'desc')->get();

            return response()->json($solicitudes);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error en data solicitudes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }

    public function store(Request $request)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) { abort(403); }

        try {
            Log::info('Iniciando store de solicitud', $request->all());

            // VALIDA TODOS LOS CAMPOS DEL FORMULARIO DE CREACIÓN DE SOLICITUD
            // EL ESTATUS SOLO PUEDE SER: 'No Atendida', 'En Proceso' O 'Cerrado'
            // EL TIPO SOLO PUEDE SER: 'No Conformidad' O 'Oportunidad de Mejora'
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

            // SI LAS FECHAS VIENEN EN FORMATO AÑO-MES (7 CARACTERES), SE LES AGREGA EL DÍA 01
            // ESTO PERMITE QUE SE GUARDEN CORRECTAMENTE COMO FECHAS COMPLETAS EN LA BD
            if ($request->has('fecha_aplicacion') && strlen($request->fecha_aplicacion) == 7) {
                $validated['fecha_aplicacion'] = $request->fecha_aplicacion . '-01';
            }
            if ($request->has('fecha_verificacion') && strlen($request->fecha_verificacion) == 7) {
                $validated['fecha_verificacion'] = $request->fecha_verificacion . '-01';
            }

            // PREPARA EL ARRAY DE DATOS PARA CREAR LA SOLICITUD EN LA BASE DE DATOS
            // SE USA ?? null PARA EVITAR ERRORES SI ALGÚN CAMPO NO FUE ENVIADO
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

            // CREA LA SOLICITUD PRIMERO PARA OBTENER SU ID ANTES DE PROCESAR EL ARCHIVO
            $solicitud = SolicitudMejora::create($data);

            // SI SE SUBIÓ UN ARCHIVO, LO GUARDA EN EL SERVIDOR CON UN NOMBRE ÚNICO
            // EL NOMBRE INCLUYE EL ID DE LA SOLICITUD PARA FACILITAR SU IDENTIFICACIÓN
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
            // SI SE REGISTRÓ UN PROCESO Y UN TIPO DE SOLICITUD, SE ENVÍAN NOTIFICACIONES
            // A LOS USUARIOS QUE PERTENECEN AL PROCESO O DEPARTAMENTO CORRESPONDIENTE
            if (!empty($data['procesos_auditados']) && !empty($data['tipo_solicitud'])) {

                $proceso       = $data['procesos_auditados'];
                $tipoSolicitud = $data['tipo_solicitud'];

                // MAPA ESTÁTICO QUE RELACIONA CADA PROCESO CON SUS DEPARTAMENTOS RESPONSABLES
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
                    'Sistema de Gestión de la Calidad'   => ['Rectoría', 'Auditoría', 'Coordinador del SGC'],
                ];

                // AGREGA LOS DEPARTAMENTOS DE PROCESOS CUSTOM REGISTRADOS POR ADMIN/SUPERADMIN
                // ESTO PERMITE QUE LOS PROCESOS PERSONALIZADOS TAMBIÉN TENGAN NOTIFICACIONES
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

                // OBTIENE LOS DEPARTAMENTOS CORRESPONDIENTES AL PROCESO DE LA SOLICITUD
                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                // CALCULA LA FECHA LÍMITE DE 15 DÍAS HÁBILES (LUNES A VIERNES) DESDE LA FECHA DEL INFORME
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

                // FORMATEA LAS FECHAS EN ESPAÑOL PARA EL MENSAJE DE LA NOTIFICACIÓN
                $fechaLimiteFormato = $fechaLimite->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
                $fechaInicio        = $fechaActual->copy()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

                // BUSCA LOS USUARIOS ACTIVOS QUE PERTENECEN AL PROCESO O A SUS DEPARTAMENTOS
                // SE ELIMINAN DUPLICADOS EN CASO DE QUE UN USUARIO COINCIDA POR AMBOS CAMPOS
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

                // SI HAY USUARIOS A NOTIFICAR, ENVÍA LA NOTIFICACIÓN A CADA UNO CON EL DETALLE DE LA SOLICITUD
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

            // RETORNA RESPUESTA JSON CON ÉXITO Y LOS DATOS DE LA SOLICITUD RECIÉN CREADA
            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora guardada correctamente',
                'data'    => $solicitud
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // SI HAY ERRORES DE VALIDACIÓN, LOS REGISTRA EN EL LOG Y LOS RETORNA EN JSON CON CÓDIGO 422
            Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // SI OCURRE CUALQUIER OTRO ERROR, LO REGISTRA EN EL LOG CON DETALLES Y RETORNA JSON CON CÓDIGO 500
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
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) { abort(403); }

        try {
            // BUSCA LA SOLICITUD POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);
            
            // GUARDA LOS DATOS ACTUALES ANTES DE MODIFICARLOS (PARA COMPARAR EL ESTATUS Y ENVIAR NOTIFICACIONES)
            $datosAnteriores = $solicitud->toArray();

            // VALIDA TODOS LOS CAMPOS DEL FORMULARIO DE EDICIÓN DE SOLICITUD
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

            // SI LAS FECHAS VIENEN EN FORMATO AÑO-MES (7 CARACTERES), SE LES AGREGA EL DÍA 01
            if ($request->has('fecha_aplicacion') && strlen($request->fecha_aplicacion) == 7) {
                $validated['fecha_aplicacion'] = $request->fecha_aplicacion . '-01';
            }
            if ($request->has('fecha_verificacion') && strlen($request->fecha_verificacion) == 7) {
                $validated['fecha_verificacion'] = $request->fecha_verificacion . '-01';
            }

            // PREPARA EL ARRAY DE DATOS PARA ACTUALIZAR LA SOLICITUD EN LA BASE DE DATOS
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

            // SI SE SUBIÓ UN NUEVO ARCHIVO, ELIMINA EL ANTERIOR Y GUARDA EL NUEVO EN EL SERVIDOR
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

            // ACTUALIZA EL REGISTRO DE LA SOLICITUD EN LA BASE DE DATOS CON LOS NUEVOS DATOS
            $solicitud->update($data);

            // ── NOTIFICACIONES AL CAMBIAR ESTATUS ─────────────────────────
            // SE COMPARA EL ESTATUS ANTERIOR CON EL NUEVO PARA DECIDIR QUÉ NOTIFICACIÓN ENVIAR
            $estatusAnterior = $datosAnteriores['estatus'] ?? null;
            $estatusNuevo    = $data['estatus'] ?? null;

            // NOTIFICACIONES AL CAMBIAR ESTATUS A "En Proceso"
            // SE ENVÍA CUANDO LA SOLICITUD PASA A ESTAR EN PROCESO POR PRIMERA VEZ
            if ($estatusAnterior !== 'En Proceso' && $estatusNuevo === 'En Proceso') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                // FORMATEA LA FECHA DE APLICACIÓN EN ESPAÑOL O MUESTRA 'No establecida' SI ES NULA
                $fechaAplicacion = $solicitud->fecha_aplicacion
                    ? \Carbon\Carbon::parse($solicitud->fecha_aplicacion)
                                    ->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                    : 'No establecida';

                // MAPA ESTÁTICO QUE RELACIONA CADA PROCESO CON SUS DEPARTAMENTOS RESPONSABLES
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
                    'Sistema de Gestión de la Calidad'   => ['Rectoría', 'Auditoría', 'Coordinador del SGC'],
                ];

                // AGREGA LOS DEPARTAMENTOS DE PROCESOS CUSTOM AL MAPA
                $procesosCustom = DB::table('procesos_custom')->select('proceso', 'departamento')->get();
                foreach ($procesosCustom as $pc) {
                    $p = trim($pc->proceso);
                    $d = trim($pc->departamento);
                    if (!$p || !$d) continue;
                    if (!isset($mapaProcesos[$p])) $mapaProcesos[$p] = [];
                    if (!in_array($d, $mapaProcesos[$p])) $mapaProcesos[$p][] = $d;
                }

                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                // BUSCA LOS USUARIOS ACTIVOS DEL PROCESO O SUS DEPARTAMENTOS SIN DUPLICADOS
                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                        $query->where('proceso', $proceso);
                        if (!empty($departamentosDelProceso)) {
                            $query->orWhereIn('departamento', $departamentosDelProceso);
                        }
                    })
                    ->get()
                    ->unique('id');

                // ENVÍA LA NOTIFICACIÓN DE "EN PROCESO" A CADA USUARIO CON EL DETALLE DE LA SOLICITUD
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
            // SE ENVÍA SOLO CUANDO LA SOLICITUD PASA DE "En Proceso" A "Cerrado"
            if ($estatusAnterior === 'En Proceso' && $estatusNuevo === 'Cerrado') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                // MAPA ESTÁTICO QUE RELACIONA CADA PROCESO CON SUS DEPARTAMENTOS RESPONSABLES
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
                    'Sistema de Gestión de la Calidad'   => ['Rectoría', 'Auditoría','Coordinador del SGC'],
                ];

                // AGREGA LOS DEPARTAMENTOS DE PROCESOS CUSTOM AL MAPA
                $procesosCustom = DB::table('procesos_custom')->select('proceso', 'departamento')->get();
                foreach ($procesosCustom as $pc) {
                    $p = trim($pc->proceso);
                    $d = trim($pc->departamento);
                    if (!$p || !$d) continue;
                    if (!isset($mapaProcesos[$p])) $mapaProcesos[$p] = [];
                    if (!in_array($d, $mapaProcesos[$p])) $mapaProcesos[$p][] = $d;
                }

                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                // BUSCA LOS USUARIOS ACTIVOS DEL PROCESO O SUS DEPARTAMENTOS SIN DUPLICADOS
                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                        $query->where('proceso', $proceso);
                        if (!empty($departamentosDelProceso)) {
                            $query->orWhereIn('departamento', $departamentosDelProceso);
                        }
                    })
                    ->get()
                    ->unique('id');

                // ENVÍA LA NOTIFICACIÓN DE CIERRE A CADA USUARIO INDICANDO QUE LA SOLICITUD FUE ATENDIDA
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
            // SE ENVÍA CUANDO LA SOLICITUD REGRESA A "No Atendida" DESDE "En Proceso"
            if ($estatusAnterior === 'En Proceso' && $estatusNuevo === 'No Atendida') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                // MAPA ESTÁTICO QUE RELACIONA CADA PROCESO CON SUS DEPARTAMENTOS RESPONSABLES
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
                    'Sistema de Gestión de la Calidad'   => ['Rectoría', 'Auditoría','Coordinador del SGC'],
                ];

                // AGREGA LOS DEPARTAMENTOS DE PROCESOS CUSTOM AL MAPA
                $procesosCustom = DB::table('procesos_custom')->select('proceso', 'departamento')->get();
                foreach ($procesosCustom as $pc) {
                    $p = trim($pc->proceso);
                    $d = trim($pc->departamento);
                    if (!$p || !$d) continue;
                    if (!isset($mapaProcesos[$p])) $mapaProcesos[$p] = [];
                    if (!in_array($d, $mapaProcesos[$p])) $mapaProcesos[$p][] = $d;
                }

                $departamentosDelProceso = $mapaProcesos[$proceso] ?? [];

                // BUSCA LOS USUARIOS ACTIVOS DEL PROCESO O SUS DEPARTAMENTOS SIN DUPLICADOS
                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->where(function ($query) use ($proceso, $departamentosDelProceso) {
                        $query->where('proceso', $proceso);
                        if (!empty($departamentosDelProceso)) {
                            $query->orWhereIn('departamento', $departamentosDelProceso);
                        }
                    })
                    ->get()
                    ->unique('id');

                // ENVÍA LA NOTIFICACIÓN DE PLAZO VENCIDO A CADA USUARIO INDICANDO QUE NO FUE ATENDIDA
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

            // NOTIFICACIÓN "No Atendida" SIN ESTATUS PREVIO
            // SE ENVÍA CUANDO EL ESTATUS CAMBIA A "No Atendida" DESDE CUALQUIER OTRO ESTADO DISTINTO
            // SOLO SE NOTIFICA A USUARIOS SIN ROL DE ADMIN O SUPERADMIN
            if ($estatusNuevo === 'No Atendida' && $estatusAnterior !== 'No Atendida') {

                $proceso       = $solicitud->procesos_auditados;
                $tipoSolicitud = $solicitud->tipo_solicitud;

                // BUSCA LOS USUARIOS ACTIVOS DEL PROCESO EXCLUYENDO ADMINS Y SUPERADMINS
                $usuariosANotificar = \App\Models\User::where('is_active', true)
                    ->whereNotIn('role', ['superadmin', 'admin'])
                    ->where('proceso', $proceso)
                    ->get()
                    ->unique('id');

                // ENVÍA LA NOTIFICACIÓN DE PLAZO VENCIDO A CADA USUARIO DEL PROCESO
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

            // RETORNA RESPUESTA JSON CON ÉXITO Y LOS DATOS ACTUALIZADOS DE LA SOLICITUD
            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora actualizada correctamente',
                'data'    => $solicitud
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // SI HAY ERRORES DE VALIDACIÓN, LOS REGISTRA EN EL LOG Y LOS RETORNA EN JSON CON CÓDIGO 422
            Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // SI OCURRE CUALQUIER OTRO ERROR, LO REGISTRA EN EL LOG Y RETORNA JSON CON CÓDIGO 500
            Log::error('Error al actualizar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) { abort(403); }
        
        try {
            // BUSCA LA SOLICITUD POR ID. SI NO EXISTE, LANZA UN ERROR 404
            $solicitud = SolicitudMejora::findOrFail($id);
            
            // SOFT DELETE DE LA SOLICITUD (NO ELIMINA EL ARCHIVO FÍSICO DEL SERVIDOR)
            $solicitud->delete();

            // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE LA SOLICITUD FUE ELIMINADA
            return response()->json([
                'success' => true,
                'message' => 'Solicitud de mejora eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
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
            // BUSCA LA SOLICITUD POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);
            
            // VERIFICA QUE LA SOLICITUD ESTÉ REALMENTE ELIMINADA ANTES DE INTENTAR RESTAURARLA
            if (!$solicitud->trashed()) {
                return response()->json(['success' => false, 'message' => 'La solicitud no está eliminada'], 400);
            }
            
            // SI LA SOLICITUD TIENE FOLIO, VERIFICA QUE NO EXISTA YA UNA ACTIVA CON EL MISMO FOLIO
            if ($solicitud->folio_solicitud) {
                $existing = SolicitudMejora::where('folio_solicitud', $solicitud->folio_solicitud)
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($existing) {
                    return response()->json(['success' => false, 'message' => 'Ya existe una solicitud activa con el mismo folio'], 400);
                }
            }
            
            // RESTAURA LA SOLICITUD ELIMINANDO SU MARCA DE SOFT DELETE (deleted_at = NULL)
            $solicitud->restore();
            
            // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE LA SOLICITUD FUE RESTAURADA
            return response()->json(['success' => true, 'message' => 'Solicitud de mejora restaurada correctamente']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error al restaurar solicitud: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar: ' . $e->getMessage()], 500);
        }
    }

    public function view($id)
    {
        try {
            // BUSCA LA SOLICITUD POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);

            // VERIFICA QUE LA SOLICITUD TENGA UN ARCHIVO ASOCIADO ANTES DE INTENTAR VISUALIZARLO
            if (!$solicitud->archivo_ruta) {
                abort(404, 'No hay archivo asociado a esta solicitud');
            }

            // CONSTRUYE LA RUTA FÍSICA COMPLETA DEL ARCHIVO EN EL SERVIDOR
            $path = storage_path('app/public/' . $solicitud->archivo_ruta);

            // SI EL ARCHIVO NO EXISTE EN LA RUTA ESPERADA, INTENTA BUSCARLO POR EL ID DE LA SOLICITUD
            if (!file_exists($path)) {
                $basePath = storage_path('app/public/solicitudes_mejora/');
                $files = glob($basePath . '*' . $solicitud->id . '*');
                
                if (empty($files)) {
                    abort(404, 'Archivo no encontrado en el servidor');
                }
                $path = $files[0];
            }

            // OBTIENE LA EXTENSIÓN DEL ARCHIVO PARA DETERMINAR CÓMO MOSTRARLO EN EL NAVEGADOR
            $extension = strtolower(pathinfo($solicitud->archivo_nombre, PATHINFO_EXTENSION));
            
            // LOS PDF SE MUESTRAN INLINE EN EL NAVEGADOR CON SU TIPO MIME ESPECÍFICO
            if ($extension === 'pdf') {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $solicitud->archivo_nombre . '"'
                ]);
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                // LAS IMÁGENES SE MUESTRAN DIRECTAMENTE EN EL NAVEGADOR
                return response()->file($path);
            } elseif ($extension === 'txt') {
                // LOS ARCHIVOS DE TEXTO SE MUESTRAN DIRECTAMENTE EN EL NAVEGADOR
                return response()->file($path);
            } else {
                // CUALQUIER OTRO FORMATO SE FUERZA A DESCARGAR
                return response()->download($path, $solicitud->archivo_nombre);
            }
            
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA ERROR 404
            Log::error('Error al ver archivo: ' . $e->getMessage());
            abort(404, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Descargar archivo de la solicitud de mejora
     */
    public function download($id)
    {
        try {
            // BUSCA LA SOLICITUD POR ID INCLUYENDO LAS ELIMINADAS (withTrashed)
            $solicitud = SolicitudMejora::withTrashed()->findOrFail($id);

            // Registrar descarga en el historial de versiones
            \App\Helpers\HistorialVersionesHelper::descargar('SOLICITUDES_MEJORA', $solicitud);

            // VERIFICA QUE LA SOLICITUD TENGA UN ARCHIVO ASOCIADO ANTES DE INTENTAR DESCARGARLO
            if (!$solicitud->archivo_ruta) {
                abort(404, 'No hay archivo asociado a esta solicitud');
            }

            // CONSTRUYE LA RUTA FÍSICA COMPLETA DEL ARCHIVO EN EL SERVIDOR
            $path = storage_path('app/public/' . $solicitud->archivo_ruta);

            // SI EL ARCHIVO NO EXISTE EN LA RUTA ESPERADA, INTENTA BUSCARLO POR EL ID DE LA SOLICITUD
            if (!file_exists($path)) {
                $basePath = storage_path('app/public/solicitudes_mejora/');
                $files = glob($basePath . '*' . $solicitud->id . '*');
                
                if (empty($files)) {
                    abort(404, 'Archivo no encontrado en el servidor');
                }
                $path = $files[0];
            }

            // FUERZA LA DESCARGA DEL ARCHIVO CON SU NOMBRE ORIGINAL
            return response()->download($path, $solicitud->archivo_nombre);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA ERROR 404
            Log::error('Error al descargar archivo: ' . $e->getMessage());
            abort(404, 'Error al descargar el archivo: ' . $e->getMessage());
        }
    }

    public function ncOmPorProceso(Request $request)
    {
        try {
            // OBTIENE EL ID DEL INFORME Y EL PROCESO DEL REQUEST PARA BUSCAR SUS NC Y OM
            $informeId = $request->get('informe_id');
            $proceso   = $request->get('proceso');

            // SI NO SE PROPORCIONARON AMBOS VALORES, RETORNA NC Y OM EN CERO
            if (!$informeId || !$proceso) {
                return response()->json(['nc' => 0, 'om' => 0]);
            }

            // BUSCA EL INFORME Y VERIFICA QUE TENGA DATOS DE NC/OM POR PROCESO
            $informe = InformeAuditoria::find($informeId);
            if (!$informe || !$informe->nc_om_por_proceso) {
                return response()->json(['nc' => 0, 'om' => 0]);
            }

            // BUSCA EL PROCESO EN EL ARRAY DE NC/OM USANDO COMPARACIÓN FLEXIBLE
            // PRIMERO INTENTA COINCIDENCIA EXACTA, LUEGO PARCIAL Y FINALMENTE POR SIMILITUD (70%)
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

            // RETORNA EL NC Y OM ENCONTRADOS O CERO SI NO SE ENCONTRÓ EL PROCESO
            return response()->json([
                'nc' => $encontrado['nc'] ?? 0,
                'om' => $encontrado['om'] ?? 0,
            ]);

        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA NC Y OM EN CERO
            Log::error('Error en ncOmPorProceso: ' . $e->getMessage());
            return response()->json(['nc' => 0, 'om' => 0]);
        }
    }

    public function graficasSolicitudes(Request $request)
    {
        try {
            // OBTIENE LOS FILTROS DE AÑO Y PROCESO DEL REQUEST
            $anio    = $request->get('anio');
            $proceso = $request->get('proceso');

            // INICIA LA CONSULTA BASE SOBRE LAS SOLICITUDES DE MEJORA
            $query = SolicitudMejora::query();

            // APLICA EL FILTRO DE AÑO SI SE PROPORCIONÓ
            if ($anio) {
                $query->whereYear('fecha_solicitud', $anio);
            }

            // APLICA EL FILTRO DE PROCESO SI SE PROPORCIONÓ
            if ($proceso) {
                $query->where('procesos_auditados', $proceso);
            }

            // OBTIENE SOLO LAS SOLICITUDES QUE TIENEN ESTATUS ASIGNADO
            $query->whereNotNull('estatus')->where('estatus', '!=', '');
            $solicitudes = $query->get();

            // CUENTA LAS SOLICITUDES TOTALES AGRUPADAS POR ESTATUS
            $todasPorEstatus = [
                'No Atendida' => $solicitudes->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $solicitudes->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $solicitudes->where('estatus', 'Cerrado')->count(),
            ];

            // CUENTA LAS NO CONFORMIDADES AGRUPADAS POR ESTATUS
            $noConformidades = $solicitudes->where('tipo_solicitud', 'No Conformidad');
            $ncPorEstatus = [
                'No Atendida' => $noConformidades->where('estatus', 'No Atendida')->count(),
                'En Proceso' => $noConformidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'    => $noConformidades->where('estatus', 'Cerrado')->count(),
            ];

            // CUENTA LAS OPORTUNIDADES DE MEJORA AGRUPADAS POR ESTATUS
            $oportunidades = $solicitudes->where('tipo_solicitud', 'Oportunidad de Mejora');
            $omPorEstatus = [
                'No Atendida' => $oportunidades->where('estatus', 'No Atendida')->count(),
                'En Proceso' => $oportunidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'    => $oportunidades->where('estatus', 'Cerrado')->count(),
            ];

            // OBTIENE LOS AÑOS Y PROCESOS DISPONIBLES PARA LOS FILTROS DE LAS GRÁFICAS
            $anios = SolicitudMejora::selectRaw('YEAR(fecha_solicitud) as anio')
                ->distinct()
                ->orderBy('anio', 'desc')
                ->pluck('anio');

            $procesosDisponibles = SolicitudMejora::whereNotNull('procesos_auditados')
                ->whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->distinct()
                ->pluck('procesos_auditados');

            // RETORNA TODOS LOS DATOS EN JSON PARA SER CONSUMIDOS POR LAS GRÁFICAS DEL FRONTEND
            return response()->json([
                'todas_por_estatus' => $todasPorEstatus,
                'nc_por_estatus'    => $ncPorEstatus,
                'om_por_estatus'    => $omPorEstatus,
                'total'             => $solicitudes->count(),
                'anios'             => $anios,
                'procesos'          => $procesosDisponibles,
            ]);

        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error en graficasSolicitudes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }

    public function ncOmPorProcesoAnio(Request $request)
    {
        try {
            // OBTIENE EL PROCESO Y AÑO DEL REQUEST PARA CALCULAR EL TOTAL DE NC Y OM
            $proceso = $request->get('proceso');
            $anio    = $request->get('anio');

            // SI NO SE PROPORCIONÓ EL PROCESO, RETORNA NC Y OM EN CERO
            if (!$proceso) {
                return response()->json(['nc' => 0, 'om' => 0]);
            }

            // BUSCA TODOS LOS INFORMES QUE TIENEN NC/OM POR PROCESO REGISTRADOS
            // APLICA EL FILTRO DE AÑO SI SE PROPORCIONÓ
            $query = InformeAuditoria::whereNotNull('nc_om_por_proceso');
            if ($anio) {
                $query->whereYear('fecha_informe', $anio);
            }

            // ACUMULA EL TOTAL DE NC Y OM DEL PROCESO EN TODOS LOS INFORMES ENCONTRADOS
            $informes = $query->get();
            $totalNc  = 0;
            $totalOm  = 0;

            foreach ($informes as $informe) {
                // BUSCA EL PROCESO EN EL ARRAY DE NC/OM USANDO COMPARACIÓN FLEXIBLE
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

                // SUMA LOS NC Y OM DEL PROCESO EN ESTE INFORME AL TOTAL ACUMULADO
                if ($encontrado) {
                    $totalNc += $encontrado['nc'] ?? 0;
                    $totalOm += $encontrado['om'] ?? 0;
                }
            }

            // RETORNA EL TOTAL ACUMULADO DE NC Y OM PARA EL PROCESO Y AÑO INDICADOS
            return response()->json(['nc' => $totalNc, 'om' => $totalOm]);

        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA NC Y OM EN CERO
            Log::error('Error en ncOmPorProcesoAnio: ' . $e->getMessage());
            return response()->json(['nc' => 0, 'om' => 0]);
        }
    }

    public function historico()
    {
        try {
            // OBTIENE TODAS LAS SOLICITUDES QUE TIENEN ESTATUS ASIGNADO (SIN FILTRO DE AÑO)
            $solicitudes = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->get();

            // CUENTA EL TOTAL HISTÓRICO DE SOLICITUDES AGRUPADAS POR ESTATUS
            $totales = [
                'No Atendida' => $solicitudes->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $solicitudes->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $solicitudes->where('estatus', 'Cerrado')->count(),
            ];

            $total = array_sum($totales);

            // OBTIENE SOLO LAS NO CONFORMIDADES PARA LOS TOTALES HISTÓRICOS POR TIPO
            $noConformidades = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->where('tipo_solicitud', 'No Conformidad')
                ->get();

            // OBTIENE SOLO LAS OPORTUNIDADES DE MEJORA PARA LOS TOTALES HISTÓRICOS POR TIPO
            $oportunidades = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->where('tipo_solicitud', 'Oportunidad de Mejora')
                ->get();

            // CUENTA EL TOTAL HISTÓRICO DE NO CONFORMIDADES AGRUPADAS POR ESTATUS
            $totalesNC = [
                'No Atendida' => $noConformidades->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $noConformidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $noConformidades->where('estatus', 'Cerrado')->count(),
            ];

            // CUENTA EL TOTAL HISTÓRICO DE OPORTUNIDADES DE MEJORA AGRUPADAS POR ESTATUS
            $totalesOM = [
                'No Atendida' => $oportunidades->where('estatus', 'No Atendida')->count(),
                'En Proceso'  => $oportunidades->where('estatus', 'En Proceso')->count(),
                'Cerrado'     => $oportunidades->where('estatus', 'Cerrado')->count(),
            ];

            // OBTIENE EL CONTEO DE SOLICITUDES AGRUPADAS POR AÑO Y ESTATUS PARA LA GRÁFICA HISTÓRICA
            $porAnio = SolicitudMejora::whereNotNull('estatus')
                ->where('estatus', '!=', '')
                ->whereNotNull('fecha_solicitud')
                ->selectRaw('YEAR(fecha_solicitud) as anio, estatus, COUNT(*) as total')
                ->groupBy('anio', 'estatus')
                ->orderBy('anio', 'desc')
                ->get();

            // CONSTRUYE EL ARRAY DE DATOS POR AÑO INICIALIZANDO TODOS LOS ESTATUS EN CERO
            // Y LUEGO ASIGNA LOS CONTEOS REALES DE CADA COMBINACIÓN AÑO-ESTATUS
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

            // RETORNA TODOS LOS DATOS HISTÓRICOS EN JSON PARA SER CONSUMIDOS POR LAS GRÁFICAS
            return response()->json([
                'total'    => $total,
                'totales'  => $totales,
                'por_anio' => array_values($aniosData),
                'totales_nc' => $totalesNC,
                'totales_om' => $totalesOM,
            ]);

        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            Log::error('Error en historico: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar datos'], 500);
        }
    }
}