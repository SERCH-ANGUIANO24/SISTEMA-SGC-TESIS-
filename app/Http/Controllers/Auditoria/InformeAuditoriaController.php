<?php

namespace App\Http\Controllers\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\InformeAuditoria;
use App\Models\Auditoria;
use App\Models\ProcesoCustom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

// CONTROLADOR QUE GESTIONA TODAS LAS OPERACIONES DEL MÓDULO DE INFORMES DE AUDITORÍA
// PERMITE CREAR, EDITAR, ELIMINAR, RESTAURAR, DESCARGAR Y VISUALIZAR INFORMES
// TAMBIÉN GENERA ESTADÍSTICAS Y DATOS PARA GRÁFICAS DEL MÓDULO
class InformeAuditoriaController extends Controller
{
    /**
     * Combina la lista estática base + procesos de procesos_custom,
     * elimina duplicados y ordena alfabéticamente.
     */
    private function getProcesos(): array
    {
        // LISTA BASE DE PROCESOS PREDEFINIDOS EN EL SISTEMA
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
            'Sistema de Gestión de la Calidad',
        ];

        // OBTIENE LOS PROCESOS PERSONALIZADOS CREADOS POR LOS USUARIOS DESDE LA BD
        $custom = ProcesoCustom::whereNotNull('proceso')
            ->where('proceso', '!=', '')
            ->distinct()
            ->orderBy('proceso')
            ->pluck('proceso')
            ->toArray();

        // COMBINA AMBAS LISTAS, ELIMINA DUPLICADOS Y ORDENA ALFABÉTICAMENTE
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
        // OBTIENE SOLO LOS PROCESOS PERSONALIZADOS PARA EL AUTOCOMPLETADO EN EL FORMULARIO
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
        // REGISTRA EN EL HISTORIAL QUE ALGUIEN VISITÓ LA PÁGINA PRINCIPAL DE INFORMES
        \App\Helpers\HistorialVersionesHelper::ver('INFORMES_AUDITORIA', null, 'index');

        // INICIA LA CONSULTA BASE CON LA RELACIÓN DE AUDITORÍA CARGADA
        $query = InformeAuditoria::with('auditoriaRelacionada');

        // APLICA FILTRO POR AÑO SI SE ENVIÓ DESDE EL FORMULARIO DE BÚSQUEDA
        if ($request->filled('anio')) {
            $query->whereYear('fecha_auditoria', $request->anio);
        }

        // APLICA FILTRO POR TIPO DE AUDITORÍA (INTERNA/EXTERNA) SI SE ENVIÓ
        if ($request->filled('tipo')) {
            $query->where('tipo_auditoria', $request->tipo);
        }

        // APLICA BÚSQUEDA POR NOMBRE DEL INFORME SI SE ENVIÓ UN TÉRMINO DE BÚSQUEDA
        if ($request->filled('buscar')) {
            $query->where('nombre_informe', 'like', '%' . $request->buscar . '%');
        }

        // APLICA EL ORDEN SELECCIONADO POR EL USUARIO (NOMBRE O FECHA, ASC O DESC)
        // SI NO SE SELECCIONA NINGUNO, ORDENA POR FECHA DE AUDITORÍA DE MÁS RECIENTE A MÁS ANTIGUO
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

        // PAGINA LOS RESULTADOS DE 10 EN 10 Y MANTIENE LOS PARÁMETROS DE BÚSQUEDA EN LA URL
        $informes = $query->paginate(10)->withQueryString();

        // OBTIENE LOS AÑOS DISPONIBLES PARA EL FILTRO DEL LISTADO
        $aniosDisponibles = InformeAuditoria::selectRaw('YEAR(fecha_auditoria) as anio')
            ->distinct()
            ->orderByDesc('anio')
            ->pluck('anio');

        // OBTIENE TODOS LOS PLANES DE AUDITORÍA PARA EL SELECTOR DEL FORMULARIO
        $planesAuditoria = Auditoria::orderByDesc('fecha_inicio')->get(['id', 'nombre_auditoria', 'fecha_inicio', 'fecha_fin']);

        // OBTIENE LA LISTA COMBINADA DE PROCESOS (BASE + PERSONALIZADOS) PARA EL FORMULARIO
        $procesos = $this->getProcesos();

        // RETORNA LA VISTA DEL LISTADO DE INFORMES CON TODOS LOS DATOS NECESARIOS
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
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) { abort(403); }

        // VALIDA Y OBTIENE TODOS LOS DATOS DEL FORMULARIO DE CREACIÓN
        $validated = $this->validarFormulario($request);

        // SI NO SE PROPORCIONÓ FECHA DE AUDITORÍA, USA LA FECHA DE INICIO O LA FECHA DEL INFORME COMO RESPALDO
        if (empty($validated['fecha_auditoria'])) {
            $validated['fecha_auditoria'] = $validated['fecha_inicio']
                ?? $validated['fecha_informe'];
        }

        // SI SE SUBIÓ UN DOCUMENTO, LO GUARDA EN EL SERVIDOR Y REGISTRA SU RUTA Y NOMBRE ORIGINAL
        if ($request->hasFile('documento')) {
            $file    = $request->file('documento');
            $nombre  = time() . '_' . $file->getClientOriginalName();
            $ruta    = $file->storeAs('informes_auditoria', $nombre, 'public');
            $validated['documento_path']   = $ruta;
            $validated['documento_nombre'] = $file->getClientOriginalName();
        }

        // GUARDA LOS PROCESOS AUDITADOS Y CONSTRUYE EL ARRAY DE NC/OM POR PROCESO
        $validated['procesos_auditados'] = $request->procesos_auditados ?? [];
        $validated['nc_om_por_proceso'] = $this->buildNcOmPorProceso($request);

        // CALCULA EL TOTAL DE NO CONFORMIDADES Y OPORTUNIDADES DE MEJORA SUMANDO TODOS LOS PROCESOS
        $validated['no_conformidades']   = collect($validated['nc_om_por_proceso'])->sum('nc');
        $validated['oportunidades_mejora'] = collect($validated['nc_om_por_proceso'])->sum('om');

        // CREA EL REGISTRO DEL INFORME EN LA BASE DE DATOS
        $informe = InformeAuditoria::create($validated);

        // Registrar SUBIR (documento)
        \App\Helpers\HistorialVersionesHelper::subir('INFORMES_AUDITORIA', $informe);

        // ── NOTIFICACIÓN: nuevo informe subido ─────────────────────────
        // ENVÍA UNA NOTIFICACIÓN A TODOS LOS USUARIOS INFORMANDO QUE HAY UN NUEVO INFORME DISPONIBLE
        // INCLUYE EL TIPO DE AUDITORÍA, AUDITOR LÍDER Y FECHA DE REGISTRO EN EL MENSAJE
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

        // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE EL INFORME FUE GUARDADO
        return response()->json(['success' => true, 'message' => 'Informe guardado correctamente.']);
    }

    // ------------------------------------------------------------------
    // SHOW (devuelve JSON para el modal de edición)
    // ------------------------------------------------------------------
    public function show(InformeAuditoria $informeAuditoria)
    {
        // CARGA LA AUDITORÍA RELACIONADA CON SUS DATOS PARA MOSTRARLOS EN EL MODAL
        $informeAuditoria->load('auditoriaRelacionada');

        // Registrar visualización del detalle
        \App\Helpers\HistorialVersionesHelper::ver('INFORMES_AUDITORIA', $informeAuditoria, 'detalle');

        // CONVIERTE EL MODELO A ARRAY Y FORMATEA LAS FECHAS AL FORMATO Y-m-d PARA LOS INPUTS DEL FORMULARIO
        $data = $informeAuditoria->toArray();
        $data['fecha_informe']   = $informeAuditoria->fecha_informe->format('Y-m-d');
        $data['fecha_auditoria'] = $informeAuditoria->fecha_auditoria->format('Y-m-d');

        // OBTIENE LAS FECHAS DE INICIO Y FIN DEL INFORME O DE LA AUDITORÍA RELACIONADA COMO RESPALDO
        $fechaInicio = $informeAuditoria->fecha_inicio
            ?? ($informeAuditoria->auditoriaRelacionada?->fecha_inicio ?? null);
        $fechaFin = $informeAuditoria->fecha_fin
            ?? ($informeAuditoria->auditoriaRelacionada?->fecha_fin ?? null);
        $data['fecha_inicio'] = $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('Y-m-d') : null;
        $data['fecha_fin']    = $fechaFin    ? \Carbon\Carbon::parse($fechaFin)->format('Y-m-d')    : null;

        // INCLUYE EL ARRAY DE NC/OM POR PROCESO PARA PRE-LLENAR EL FORMULARIO DE EDICIÓN
        $data['nc_om_por_proceso'] = $informeAuditoria->nc_om_por_proceso ?? [];

        // RETORNA LOS DATOS DEL INFORME Y LA URL DEL DOCUMENTO (SI EXISTE) EN FORMATO JSON
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
        //DA EL ACCESO A TODOS LOS DE ROL DE ADMIN SUPERADMIN Y AUDITOR_LIDER COMO ADMINISTRADORES DE ESTE MODULO DE INFORMES
        if (!Gate::allows('auditoria-access')) { abort(403); }

        // GUARDA LOS DATOS ACTUALES DEL INFORME ANTES DE MODIFICARLOS (PARA EL HISTORIAL)
        $datosAnteriores = $informeAuditoria->toArray();

        // VALIDA Y OBTIENE LOS DATOS DEL FORMULARIO DE EDICIÓN
        // SE PASA EL ID DEL INFORME ACTUAL PARA IGNORARLO EN VALIDACIONES ÚNICAS
        $validated = $this->validarFormulario($request, $informeAuditoria->id);

        // SE ELIMINA EL CAMPO DOCUMENTO DE LOS DATOS VALIDADOS PORQUE SE MANEJA POR SEPARADO
        unset($validated['documento']);

        // SI NO SE PROPORCIONÓ FECHA DE AUDITORÍA, USA LA FECHA DE INICIO O LA FECHA ACTUAL DEL INFORME
        if (empty($validated['fecha_auditoria'])) {
            $validated['fecha_auditoria'] = $validated['fecha_inicio']
                ?? $informeAuditoria->fecha_auditoria->format('Y-m-d');
        }

        // SI SE SUBIÓ UN NUEVO DOCUMENTO, ELIMINA EL ANTERIOR Y GUARDA EL NUEVO EN EL SERVIDOR
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

        // ACTUALIZA LOS PROCESOS AUDITADOS Y RECALCULA EL ARRAY DE NC/OM POR PROCESO
        $validated['procesos_auditados'] = $request->procesos_auditados ?? [];
        $validated['nc_om_por_proceso'] = $this->buildNcOmPorProceso($request);

        // RECALCULA EL TOTAL DE NO CONFORMIDADES Y OPORTUNIDADES DE MEJORA
        $validated['no_conformidades']    = collect($validated['nc_om_por_proceso'])->sum('nc');
        $validated['oportunidades_mejora'] = collect($validated['nc_om_por_proceso'])->sum('om');

        // ACTUALIZA EL REGISTRO DEL INFORME EN LA BASE DE DATOS CON LOS NUEVOS DATOS
        $informeAuditoria->update($validated);

        // Registrar EDICIÓN
        \App\Helpers\HistorialVersionesHelper::editar('INFORMES_AUDITORIA', $informeAuditoria, $datosAnteriores, $informeAuditoria->toArray());

        // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE EL INFORME FUE ACTUALIZADO
        return response()->json(['success' => true, 'message' => 'Informe actualizado correctamente.']);
    }

    // ------------------------------------------------------------------
    // DESTROY (Soft Delete - NO elimina el archivo físico)
    // ------------------------------------------------------------------
    public function destroy(InformeAuditoria $informeAuditoria)
    {
        // VERIFICA QUE EL USUARIO TENGA PERMISO PARA ACCEDER AL MÓDULO DE AUDITORÍAS
        if (!Gate::allows('auditoria-access')) { abort(403); }

        // GUARDA LOS DATOS DEL INFORME ANTES DE ELIMINARLO (PARA EL HISTORIAL)
        $datosInforme = $informeAuditoria->toArray();

        // Soft delete - NO elimina el archivo físico
        $informeAuditoria->delete();

        // Registrar ELIMINAR
        \App\Helpers\HistorialVersionesHelper::eliminar('INFORMES_AUDITORIA', $informeAuditoria, $datosInforme);

        // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE EL INFORME FUE ELIMINADO
        return response()->json(['success' => true, 'message' => 'Informe eliminado correctamente.']);
    }

    // ------------------------------------------------------------------
    // RESTAURAR INFORME ELIMINADO
    // ------------------------------------------------------------------
    public function restaurar($id)
    {
        try {
            // BUSCA EL INFORME POR ID INCLUYENDO LOS ELIMINADOS (withTrashed)
            $informe = InformeAuditoria::withTrashed()->findOrFail($id);
            
            // VERIFICA QUE EL INFORME ESTÉ REALMENTE ELIMINADO ANTES DE INTENTAR RESTAURARLO
            if (!$informe->trashed()) {
                return response()->json(['success' => false, 'message' => 'El informe no está eliminado'], 400);
            }
            
            // VERIFICA QUE NO EXISTA OTRO INFORME ACTIVO CON EL MISMO NOMBRE (EVITA DUPLICADOS)
            $existing = InformeAuditoria::where('nombre_informe', $informe->nombre_informe)
                ->whereNull('deleted_at')
                ->first();
                
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Ya existe un informe activo con el mismo nombre'], 400);
            }
            
            // VERIFICA QUE EL ARCHIVO FÍSICO TODAVÍA EXISTA EN EL SERVIDOR ANTES DE RESTAURAR
            if ($informe->documento_path && !Storage::disk('public')->exists($informe->documento_path)) {
                return response()->json(['success' => false, 'message' => 'El archivo físico no existe en el servidor. No se puede restaurar.'], 400);
            }
            
            // RESTAURA EL INFORME ELIMINANDO SU MARCA DE SOFT DELETE (deleted_at = NULL)
            $informe->restore();
            
            // REGISTRA EN EL HISTORIAL QUE EL INFORME FUE RESTAURADO
            \App\Helpers\HistorialVersionesHelper::restaurar('INFORMES_AUDITORIA', $informe);
            
            // RETORNA RESPUESTA JSON CON ÉXITO INDICANDO QUE EL INFORME FUE RESTAURADO
            return response()->json(['success' => true, 'message' => 'Informe restaurado correctamente']);
        } catch (\Exception $e) {
            // SI OCURRE UN ERROR, LO REGISTRA EN EL LOG Y RETORNA EL MENSAJE EN JSON CON CÓDIGO 500
            \Log::error('Error al restaurar informe: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar: ' . $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------------------------
    // ESTADÍSTICAS POR AÑO
    // ------------------------------------------------------------------
    public function estadisticasPorAnio(Request $request)
    {
        // OBTIENE EL AÑO DEL FILTRO O USA EL AÑO ACTUAL POR DEFECTO
        $anio = $request->get('anio', now()->year);

        // OBTIENE LAS ESTADÍSTICAS GENERALES DEL AÑO (TOTALES, PROMEDIOS, ETC.)
        $stats = InformeAuditoria::estadisticasPorAnio((int) $anio);

        // OBTIENE LOS DATOS DETALLADOS DE CADA INFORME DEL AÑO PARA GENERAR LA GRÁFICA
        $datosGrafica = InformeAuditoria::whereYear('fecha_auditoria', $anio)
            ->get(['nombre_informe', 'no_conformidades', 'oportunidades_mejora', 'procesos_auditados', 'nc_om_por_proceso'])
            ->map(fn($i) => [
                'nombre'               => $i->nombre_informe,
                'no_conformidades'     => $i->no_conformidades,
                'oportunidades_mejora' => $i->oportunidades_mejora,
                'procesos'             => $i->procesos_auditados ?? [],
                'nc_om_por_proceso'    => $i->nc_om_por_proceso ?? [],
            ]);

        // RETORNA LAS ESTADÍSTICAS + DATOS DE GRÁFICA + LISTA DE AÑOS DISPONIBLES EN JSON
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
        // SI EL INFORME NO TIENE DOCUMENTO ASOCIADO, RETORNA ERROR 404
        if (!$informeAuditoria->documento_path) abort(404);

        // Registrar visualización del documento
        \App\Helpers\HistorialVersionesHelper::ver('INFORMES_AUDITORIA', $informeAuditoria, 'ver_archivo');

        // CONSTRUYE LA RUTA FÍSICA COMPLETA DEL ARCHIVO EN EL SERVIDOR
        $ruta = storage_path('app/public/' . $informeAuditoria->documento_path);
        if (!file_exists($ruta)) abort(404, 'Archivo no encontrado.');

        // MAPEO DE EXTENSIONES A TIPOS MIME PARA INDICARLE AL NAVEGADOR CÓMO MOSTRAR EL ARCHIVO
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

        // RETORNA EL ARCHIVO COMO RESPUESTA INLINE (SE ABRE EN EL NAVEGADOR, NO SE DESCARGA)
        // X-Frame-Options SAMEORIGIN PERMITE QUE SE MUESTRE EN UN IFRAME DEL MISMO DOMINIO
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
        // BUSCA EL INFORME POR ID INCLUYENDO LOS ELIMINADOS (withTrashed)
        $informe = InformeAuditoria::withTrashed()->findOrFail($id);

        // SI EL INFORME NO TIENE DOCUMENTO ASOCIADO, RETORNA ERROR 404
        if (!$informe->documento_path) abort(404, 'El documento no existe');

        // Registrar descarga
        \App\Helpers\HistorialVersionesHelper::descargar('INFORMES_AUDITORIA', $informe);

        // VERIFICA QUE EL ARCHIVO FÍSICO EXISTA EN EL SERVIDOR ANTES DE DESCARGARLO
        $path = storage_path('app/public/' . $informe->documento_path);
        if (!file_exists($path)) abort(404, 'El archivo no se encuentra en el servidor');

        // FUERZA LA DESCARGA DEL ARCHIVO CON SU NOMBRE ORIGINAL
        return response()->download($path, $informe->documento_nombre);
    }

    // ------------------------------------------------------------------
    // GRÁFICA DE UN SOLO INFORME
    // ------------------------------------------------------------------
    public function graficaInforme(InformeAuditoria $informeAuditoria)
    {
        // RETORNA EN JSON TODOS LOS DATOS NECESARIOS PARA GENERAR LA GRÁFICA DE UN INFORME ESPECÍFICO
        // INCLUYE NOMBRE, NC, OM, PROCESOS AUDITADOS Y NC/OM DESGLOSADOS POR PROCESO
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
        // RETORNA LA FECHA DE INICIO DE LA AUDITORÍA RELACIONADA EN FORMATO Y-m-d
        // SE USA VÍA AJAX PARA AUTOCOMPLETAR LA FECHA EN EL FORMULARIO AL SELECCIONAR UN PLAN
        return response()->json([
            'fecha_auditoria' => \Carbon\Carbon::parse($auditoria->fecha_inicio)->format('Y-m-d'),
        ]);
    }

    // ------------------------------------------------------------------
    // HELPER: construir array nc_om_por_proceso desde el request
    // ------------------------------------------------------------------
    private function buildNcOmPorProceso(Request $request): array
    {
        // OBTIENE LOS PROCESOS AUDITADOS Y LOS MAPAS DE NC, OM Y CRITERIOS DEL REQUEST
        $procesos = $request->input('procesos_auditados', []);
        $ncMap    = $request->input('nc_por_proceso', []);
        $omMap    = $request->input('om_por_proceso', []);
        $criterioMap = $request->input('criterio_por_proceso', []);

        // CONSTRUYE UN ARRAY CON LOS NC, OM Y CRITERIO DE CADA PROCESO AUDITADO
        // SI NO SE PROPORCIONÓ NC U OM PARA UN PROCESO, SE USA 0 COMO VALOR POR DEFECTO
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
        // DEFINE LAS REGLAS DE VALIDACIÓN PARA EL FORMULARIO DE CREACIÓN/EDICIÓN DE INFORMES
        // EL DOCUMENTO ES OBLIGATORIO SOLO EN LA CREACIÓN, EN EDICIÓN ES OPCIONAL
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

        // EJECUTA LA VALIDACIÓN Y RETORNA LOS DATOS VALIDADOS
        return $request->validate($rules);
    }
}