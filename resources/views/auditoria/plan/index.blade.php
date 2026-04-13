{{-- VISTA PRINCIPAL DEL MÓDULO PLAN DE AUDITORÍAS --}}
{{-- MUESTRA LA TABLA DE AUDITORÍAS CON FILTROS, BUSCADOR Y OPCIONES PARA REGISTRAR, EDITAR Y ELIMINAR --}}
@extends('layouts.app')

@section('title', 'Plan de Auditorías - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    {{-- ENCABEZADO: TÍTULO Y BOTÓN DE REGISTRAR (SOLO PARA USUARIOS CON PERMISO auditoria-access) --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">

                {{-- TÍTULO CON ENLACE AL DASHBOARD --}}
                <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none">
                    <h1 class="h3 mb-0" style="color: #4f46e5;">
                        <i class="bi-calendar-check me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                        Plan de Auditorías
                    </h1>
                </a>

                {{-- Solo admin y superadmin pueden registrar auditorías --}}
                {{-- BOTÓN PARA ABRIR EL MODAL DE REGISTRO — SOLO VISIBLE PARA admin, superadmin Y auditor_lider --}}
                @can('auditoria-access')
                    <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaAuditoria" style="background-color: #737373; color: white; border: none;">
                        <i class="bi bi-plus-circle"></i> Registrar Auditoría
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    {{-- BARRA DE FILTROS: BUSCADOR, ORDENAR, FILTRO POR AÑO Y TIPO DE AUDITORÍA --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 flex-wrap">

                <!-- Buscar archivos con X visible -->
                {{-- BUSCADOR DE AUDITORÍAS CON BOTÓN X PARA LIMPIAR --}}
                <div class="d-flex align-items-center position-relative" style="width: 700px;">
                    <div class="position-relative flex-grow-1">
                        {{-- ÍCONO DE LUPA DENTRO DEL INPUT --}}
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                        {{-- INPUT DE BÚSQUEDA — SE FILTRA CON JavaScript AL ESCRIBIR --}}
                        <input type="text" class="form-control ps-5" style="width: 100%; height: 42px; border-radius: 4px 0 0 4px; border-right: none;" placeholder="Buscar archivos" id="buscadorArchivos">
                    </div>
                    {{-- BOTÓN X PARA LIMPIAR EL BUSCADOR — LLAMA A limpiarBuscador() --}}
                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center btn-clear-search" 
                            style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border: 1px solid #ced4da; border-left: none; transition: all 0.2s;"
                            id="limpiarBusqueda"
                            onclick="limpiarBuscador()"
                            title="Limpiar búsqueda">
                        <i class="bi bi-x-lg" style="font-size: 1.4rem; color: #6c757d;"></i>
                    </button>
                </div>

                <!-- Ordenar por -->
                {{-- DROPDOWN PARA ORDENAR LOS RESULTADOS POR NOMBRE O FECHA --}}
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnOrdenar" style="height: 42px; background-color: white;">
                        <i class="bi bi-arrow-up-short"></i> <span id="ordenarTexto">Ordenar por</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('nombre-asc', 'Nombre (A-Z)')">Nombre (A-Z)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('nombre-desc', 'Nombre (Z-A)')">Nombre (Z-A)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-asc', 'Fecha (más antiguo)')">Fecha (más antiguo)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-desc', 'Fecha (más reciente)')">Fecha (más reciente)</a></li>
                    </ul>
                </div>

                <!-- Filtrar por Año -->
                {{-- DROPDOWN PARA FILTRAR POR AÑO — LAS OPCIONES SE GENERAN DINÁMICAMENTE DESDE $anios --}}
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnAnio" style="height: 42px; background-color: white;">
                        <i class="bi bi-calendar"></i> <span id="anioTexto">Filtrar por Año</span>
                    </button>
                    <ul class="dropdown-menu" id="menuAnios">
                        {{-- OPCIÓN PARA VER TODOS LOS AÑOS SIN FILTRO --}}
                        <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('', 'Filtrar por Año')">Todos los años</a></li>
                        {{-- SI $anios NO ESTÁ DEFINIDA USA ARREGLO VACÍO PARA EVITAR ERROR --}}
                        @foreach($anios ?? [] as $anio)
                            <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('{{ $anio }}', 'Año {{ $anio }}')">{{ $anio }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Tipo de Auditoría -->
                {{-- DROPDOWN PARA FILTRAR POR TIPO: INTERNA, EXTERNA O TODOS --}}
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnTipo" style="height: 42px; background-color: white;">
                        <i class="bi bi-building"></i> <span id="tipoTexto">Tipo de Auditoría</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('', 'Todos los tipos')">Todos los tipos</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Interna', 'Interna')" id="opcionInterna">Interna</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Externa', 'Externa')" id="opcionExterna">Externa</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Auditorías -->
    {{-- TABLA PRINCIPAL — EL CUERPO (tablaBody) SE LLENA DINÁMICAMENTE CON JAVASCRIPT --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre de Auditoría</th>   {{-- NOMBRE DE LA AUDITORÍA --}}
                            <th>Tipo de Auditoría</th>      {{-- INTERNA O EXTERNA --}}
                            <th>Auditor Líder</th>          {{-- RESPONSABLE PRINCIPAL --}}
                            <th>Fecha De Auditoría</th>     {{-- RANGO DE FECHAS --}}
                            <th>Año</th>                    {{-- AÑO DE LA AUDITORÍA --}}
                            <th>Plan de Auditoría</th>      {{-- ARCHIVO DEL PLAN --}}
                            <th>Auditores</th>              {{-- AUDITORES PARTICIPANTES --}}
                            <th class="text-end">Acciones</th> {{-- BOTONES DE ACCIÓN --}}
                        </tr>
                    </thead>
                    {{-- LAS FILAS SE INSERTAN AQUÍ CON renderizarTabla() EN JAVASCRIPT --}}
                    <tbody id="tablaBody">
                        <tr>
                            <td colspan="8" class="text-center">Cargando auditorías...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA REGISTRAR/EDITAR AUDITORÍA -->
{{-- SOLO SE RENDERIZA ESTE MODAL SI EL USUARIO TIENE PERMISO auditoria-access --}}
@can('auditoria-access')
<div class="modal fade" id="modalNuevaAuditoria" tabindex="-1" aria-labelledby="modalNuevaAuditoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                {{-- TÍTULO SE CAMBIA DINÁMICAMENTE: "REGISTRAR" AL CREAR, "EDITAR" AL MODIFICAR --}}
                <h5 class="modal-title" id="modalNuevaAuditoriaLabel">
                    <i class="bi bi-plus-circle me-2" style="color: #000000;"></i>
                    Registrar Nueva Auditoría
                </h5>

            </div>
            {{-- FORMULARIO CON enctype PARA PERMITIR SUBIR ARCHIVOS --}}
            <form id="formAuditoria" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- CAMPO OCULTO: GUARDA EL ID AL EDITAR, VACÍO AL CREAR --}}
                    <input type="hidden" id="auditoria_id" name="auditoria_id">

                    <!-- DATOS DE LA AUDITORÍA -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #000000;">DATOS DE LA AUDITORÍA</h6>
                        </div>

                        {{-- NOMBRE DE LA AUDITORÍA --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre de Auditoría *</label>
                            <input type="text" class="form-control" id="nombre_auditoria" name="nombre_auditoria" placeholder="Ej: Auditoría Anual 2026">
                            {{-- MENSAJE DE ERROR DE VALIDACIÓN --}}
                            <div class="msg-error" id="err-nombre_auditoria">El nombre de la auditoría es requerido</div>
                        </div>

                        {{-- TIPO DE AUDITORÍA: INTERNA O EXTERNA --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Auditoría *</label>
                            <select class="form-control" id="tipo_auditoria" name="tipo_auditoria">
                                <option value="">-- Seleccionar --</option>
                                <option value="Interna">Interna</option>
                                <option value="Externa">Externa</option>
                            </select>
                            <div class="msg-error" id="err-tipo_auditoria">El tipo de auditoría es requerido</div>
                        </div>

                        {{-- NOMBRE DEL AUDITOR LÍDER --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditor Líder *</label>
                            <input type="text" class="form-control" id="auditor_lider" name="auditor_lider" placeholder="Nombre del auditor líder">
                            <div class="msg-error" id="err-auditor_lider">El nombre del auditor líder es requerido</div>
                        </div>

                        <!-- CAMPO ÚNICO PARA RANGO DE FECHAS -->
                        {{-- SELECTOR DE RANGO DE FECHAS CON DATERANGEPICKER --}}
                        {{-- LOS CAMPOS OCULTOS fecha_inicio Y fecha_fin SE LLENAN AL SELECCIONAR --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Auditoría*</label>
                            <input type="text" class="form-control" id="rango_fechas" name="rango_fechas" placeholder="Seleccionar Fecha de Auditoría" readonly>
                            <input type="hidden" id="fecha_inicio" name="fecha_inicio">
                            <input type="hidden" id="fecha_fin" name="fecha_fin">
                            <div class="msg-error" id="err-rango_fechas">Debe seleccionar Fecha de Auditoría</div>
                        </div>

                        {{-- AÑO DE LA AUDITORÍA (POR DEFECTO EL AÑO ACTUAL) --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Año *</label>
                            <input type="number" class="form-control" id="anio" name="anio" min="2000" max="2100" value="{{ date('Y') }}" placeholder="Ej: 2026">
                            <div class="msg-error" id="err-anio">El año es requerido</div>
                        </div>

                        {{-- LISTA DE AUDITORES PARTICIPANTES --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditores *</label>
                            <input type="text" class="form-control" id="auditores" name="auditores" placeholder="Nombre de Auditores">
                            <div class="msg-error" id="err-auditores">Los auditores son requeridos</div>
                        </div>
                    </div>

                    <!-- PLAN DE AUDITORÍA (ARCHIVO) -->
                    {{-- SECCIÓN PARA SUBIR EL ARCHIVO DEL PLAN DE AUDITORÍA --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #000000;">PLAN DE AUDITORÍA</h6>
                            <div class="border rounded p-4 bg-light">
                                {{-- ÁREA VISUAL PARA ARRASTRAR O SELECCIONAR EL ARCHIVO --}}
                                <div class="text-center mb-3">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #000000;"></i>
                                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    {{-- FORMATOS ACEPTADOS Y TAMAÑO MÁXIMO --}}
                                    <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                </div>
                                <div class="d-flex justify-content-center">
                                    {{-- INPUT DE ARCHIVO — ACEPTA LOS FORMATOS LISTADOS --}}
                                    <input type="file" class="form-control" id="archivo_plan" name="archivo_plan" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.txt">
                                </div>
                                <div class="msg-error mt-2" id="err-archivo_plan">El archivo del plan es requerido</div>
                                {{-- NOMBRE DEL ARCHIVO ACTUAL (VISIBLE AL EDITAR UNA AUDITORÍA EXISTENTE) --}}
                                <div id="nombreArchivoActual" class="text-center mt-2 text-muted" style="display: none;">
                                    Archivo actual: <span id="nombreArchivo"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BOTONES DEL MODAL --}}
                <div class="modal-footer">
                    {{-- BOTÓN CANCELAR — CIERRA EL MODAL Y LIMPIA EL FORMULARIO --}}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    {{-- BOTÓN GUARDAR — LLAMA A guardarAuditoria() CON VALIDACIÓN PREVIA --}}
                    <button type="button" class="btn text-white" style="background-color: #800000;" id="btnGuardarAuditoria">
                        <i class="bi bi-check-circle me-1"></i> Guardar Auditoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- CONTENEDOR DONDE SE INYECTAN DINÁMICAMENTE LOS MODALES DE VISUALIZACIÓN DE ARCHIVOS --}}
<div id="modalesContainer"></div>
@endsection

@push('styles')
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
{{-- ESTILOS DEL DATERANGEPICKER --}}
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    /* ESTILOS DE LA TABLA */
    .table th {
        background-color: #f8f9fa;
        color: black;
        text-align: center;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
    }

    /* MENSAJES DE ERROR DE VALIDACIÓN DEL FORMULARIO */
    .msg-error {
        display: none;
        color: #800000;
        font-size: 0.82rem;
        margin-top: 4px;
    }

    /* BORDE ROJO EN CAMPO CON ERROR */
    .campo-invalido {
        border-color: #800000 !important;
    }

    /* ESTILOS DE LOS BOTONES DE FILTRO */
    .btn-light {
        background-color: white !important;
        color: #6c757d;
        border: 1px solid #ced4da;
    }
    .btn-light:hover {
        border-color: #800000;
    }

    /* HOVER EN OPCIONES DEL DROPDOWN */
    .dropdown-item:hover {
        background-color: #737373 !important;
        color: #ffffff !important;
    }

    /* BADGE VERDE PARA AUDITORÍA INTERNA */
    .badge-interna {
        background-color: #28a745;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
    }

    /* BADGE ROJO PARA AUDITORÍA EXTERNA */
    .badge-externa {
        background-color: #dc3545;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
    }

    /* ÁREA DE SUBIDA DE ARCHIVO CON BORDE PUNTEADO */
    .border.rounded.p-4.bg-light {
        border: 2px dashed #000000 !important;
        transition: all 0.3s ease;
    }
    .border.rounded.p-4.bg-light:hover {
        background-color: #fff !important;
        border-color: #000000 !important;
    }

    /* ESTILO PARA MENSAJE DE ÉXITO */
    .alert-success {
        background-color: #48b161;
        color: #ffffff;
        border-color: #c3e6cb;
        border-radius: 8px;
        padding: 12px 20px;
        margin: 0 auto 20px auto;
        font-weight: 500;
        display: flex;
        align-items: center;
        position: relative;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        width: 95%;
        max-width: 1400px;
        min-width: 300px;
    }
    
    .alert-success i {
        font-size: 1.5rem;
        margin-right: 15px;
    }
    
    .alert-success .btn-close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem;
    }

    /* ESTILOS DE BOTONES DE ACCIÓN EN LA TABLA */
    .btn-outline-info {
        color: #0dcaf0;
        border-color: #0dcaf0;
    }
    .btn-outline-info:hover {
        color: #fff;
        background-color: #0dcaf0;
        border-color: #0dcaf0;
    }
    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .btn-outline-primary {
        color: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-outline-primary:hover {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }
    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    /* ESTILOS PARA LAS ALERTAS DE SWEETALERT2 */
    .swal2-popup {
        font-size: 1.2rem !important;
    }
    .swal2-title {
        color: #000000 !important;
    }
    .swal2-confirm {
        background-color: #dc3545 !important;
    }
    .swal2-cancel {
        background-color: #6c757d !important;
    }

    /* EVITA QUE EL NOMBRE DEL ARCHIVO SE DESBORDE EN LA CELDA */
    .nombre-archivo {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        vertical-align: middle;
    }

    /* HOVER PARA BOTÓN DE LIMPIAR BÚSQUEDA */
    .btn-clear-search:hover {
        background-color: #737373 !important;
        border-color: #737373 !important;
    }
    .btn-clear-search:hover i {
        color: white !important;
    }

    /* =====================================================
       ESTILOS RESPONSIVOS - AGREGADOS AL FINAL
    ===================================================== */

    /* Tablets (769px a 992px) */
    @media (min-width: 769px) and (max-width: 992px) {
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .table {
            min-width: 950px !important;
            width: max-content !important;
        }
        .table th, .table td {
            white-space: nowrap !important;
        }
        .table th {
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
        }
        .table td {
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
        }
        .btn-sm {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.65rem !important;
        }
        .badge-interna, .badge-externa {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        .nombre-archivo {
            max-width: 120px !important;
        }
        .border.rounded.p-4.bg-light {
            padding: 15px 10px !important;
        }
        .border.rounded.p-4.bg-light i {
            font-size: 2rem !important;
        }
        .border.rounded.p-4.bg-light p.mt-2 strong {
            font-size: 0.75rem !important;
        }
        .border.rounded.p-4.bg-light .small {
            font-size: 0.65rem !important;
        }
        .modal-dialog {
            max-width: 95% !important;
            margin: 1rem auto !important;
        }
        .d-flex.align-items-center.gap-3.flex-wrap {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        .dropdown .btn, #btnOrdenar, #btnAnio, #btnTipo {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.75rem !important;
        }
    }

    /* Móviles (768px y menos) */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        .h3 {
            font-size: 1.5rem !important;
        }
        .h3 i {
            font-size: 2rem !important;
        }
        .d-flex.align-items-center.justify-content-between {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem !important;
        }
        .d-flex.align-items-center.justify-content-between .btn {
            width: 100% !important;
        }
        .d-flex.align-items-center.gap-3.flex-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.75rem !important;
        }
        .d-flex.align-items-center.position-relative[style*="width: 700px"] {
            width: 100% !important;
        }
        .dropdown {
            width: 100% !important;
        }
        .dropdown .btn, #btnOrdenar, #btnAnio, #btnTipo {
            width: 100% !important;
            height: 38px !important;
            font-size: 0.8rem !important;
        }
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .table {
            min-width: 800px !important;
            width: max-content !important;
        }
        .table th, .table td {
            white-space: nowrap !important;
            font-size: 0.75rem !important;
            padding: 6px 4px !important;
        }
        .btn-sm {
            padding: 0.15rem 0.25rem !important;
            font-size: 0.6rem !important;
        }
        .badge-interna, .badge-externa {
            font-size: 0.6rem !important;
            padding: 0.15rem 0.3rem !important;
        }
        .nombre-archivo {
            max-width: 100px !important;
        }
        .border.rounded.p-4.bg-light {
            padding: 12px 8px !important;
        }
        .border.rounded.p-4.bg-light i {
            font-size: 1.8rem !important;
        }
        .border.rounded.p-4.bg-light p.mt-2 strong {
            font-size: 0.7rem !important;
        }
        .border.rounded.p-4.bg-light .small {
            font-size: 0.6rem !important;
        }
        .modal-dialog {
            margin: 0.5rem !important;
        }
        .modal-body {
            padding: 0.75rem !important;
        }
        .modal-footer {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        .modal-footer .btn {
            flex: 1 !important;
        }
        .d-flex.justify-content-end.gap-1 {
            flex-wrap: wrap !important;
            justify-content: center !important;
        }
        .row.mb-4 .col-md-6 {
            margin-bottom: 0.75rem !important;
        }
    }

    /* Móviles muy pequeños (480px y menos) */
    @media (max-width: 480px) {
        .table th, .table td {
            font-size: 0.65rem !important;
            padding: 4px 3px !important;
        }
        .btn-sm {
            padding: 0.1rem 0.2rem !important;
            font-size: 0.55rem !important;
        }
        .btn-sm i {
            font-size: 0.6rem !important;
        }
        .nombre-archivo {
            max-width: 70px !important;
        }
        .badge-interna, .badge-externa {
            font-size: 0.55rem !important;
        }
        .border.rounded.p-4.bg-light i {
            font-size: 1.5rem !important;
        }
        .border.rounded.p-4.bg-light p.mt-2 strong {
            font-size: 0.65rem !important;
        }
    }
</style>
@endpush

@push('scripts')
{{-- LIBRERÍAS EXTERNAS: JQUERY, MOMENT.JS, DATERANGEPICKER Y SWEETALERT2 --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // VARIABLES GLOBALES DE ESTADO DE FILTROS Y DATOS
    let auditoriasData = [];       // ARREGLO CON TODOS LOS DATOS DE AUDITORÍAS CARGADOS
    let tipoSeleccionado = '';     // FILTRO ACTIVO POR TIPO (INTERNA / EXTERNA)
    let anioSeleccionado = '';     // FILTRO ACTIVO POR AÑO
    let ordenSeleccionado = '';    // CRITERIO DE ORDENAMIENTO ACTIVO
    const userRole = '{{ Auth::user()->role }}'; // ROL DEL USUARIO AUTENTICADO

    // Lista de extensiones sin vista previa (no mostrarán botón "Ver")
    // EXTENSIONES QUE NO TIENEN PREVISUALIZACIÓN EN EL NAVEGADOR
    const extensionesSinVista = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];

    $(document).ready(function() {
        // AL CARGAR LA PÁGINA: CARGA LOS DATOS, CONFIGURA EVENTOS E INICIALIZA EL SELECTOR DE FECHAS
        cargarAuditorias();
        configurarEventos();
        inicializarDateRangePicker();

        // AL CERRAR EL MODAL, RESETEA EL FORMULARIO Y LIMPIA LOS ERRORES
        $('#modalNuevaAuditoria').on('hidden.bs.modal', function () {
            resetForm();
            limpiarErrores();
        });
        
        // Actualizar el título del modal cuando se abre para edición
        // CAMBIA EL TÍTULO SEGÚN SI ES CREACIÓN O EDICIÓN
        $('#modalNuevaAuditoria').on('show.bs.modal', function () {
            if ($('#auditoria_id').val()) {
                $('#modalNuevaAuditoriaLabel').html('<i class="bi bi-pencil-square me-2" style="color: #000000;"></i> Editar Auditoría');
            } else {
                $('#modalNuevaAuditoriaLabel').html('<i class="bi bi-plus-circle me-2" style="color: #000000;"></i> Registrar Nueva Auditoría');
            }
        });
    });

    // INICIALIZA EL PLUGIN DATERANGEPICKER EN EL CAMPO DE RANGO DE FECHAS
    // AL APLICAR, GUARDA LAS FECHAS EN LOS CAMPOS OCULTOS fecha_inicio Y fecha_fin
    function inicializarDateRangePicker() {
        $('#rango_fechas').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            autoUpdateInput: false,
            startDate: moment(),
            endDate: moment()
        });

        // AL CONFIRMAR EL RANGO: MUESTRA LAS FECHAS EN EL INPUT Y LAS GUARDA EN LOS CAMPOS OCULTOS
        $('#rango_fechas').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $('#fecha_inicio').val(picker.startDate.format('YYYY-MM-DD'));
            $('#fecha_fin').val(picker.endDate.format('YYYY-MM-DD'));
            $('#err-rango_fechas').hide();
        });

        // AL CANCELAR: LIMPIA EL CAMPO Y LOS VALORES OCULTOS
        $('#rango_fechas').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
        });
    }

    // CONFIGURA LOS EVENTOS DEL FORMULARIO, BUSCADOR Y VALIDACIÓN EN TIEMPO REAL
    function configurarEventos() {
        // AL HACER CLIC EN GUARDAR, LLAMA A guardarAuditoria()
        $('#btnGuardarAuditoria').on('click', guardarAuditoria);

        // FILTRA LA TABLA AL ESCRIBIR EN EL BUSCADOR
        $('#buscadorArchivos').on('keyup', function() {
            filtrarPorBusqueda($(this).val());
        });

        // LIMPIA EL BUSCADOR Y MUESTRA TODOS LOS RESULTADOS
        $('#limpiarBusqueda').on('click', function() {
            $('#buscadorArchivos').val('');
            filtrarPorBusqueda('');
        });

        // Validación en tiempo real
        // OCULTA EL ERROR DEL CAMPO EN CUANTO EL USUARIO ESCRIBE UN VALOR VÁLIDO
        $('#nombre_auditoria, #tipo_auditoria, #auditor_lider, #anio, #auditores').on('input change', function() {
            const id = $(this).attr('id');
            if ($(this).val().trim()) {
                $(`#err-${id}`).hide();
                $(this).removeClass('campo-invalido');
            }
        });

        // OCULTA EL ERROR DEL ARCHIVO EN CUANTO SE SELECCIONA UNO
        $('#archivo_plan').on('change', function() {
            if (this.files.length) $('#err-archivo_plan').hide();
        });
    }

    // CARGA LAS AUDITORÍAS DESDE LA API APLICANDO LOS FILTROS ACTIVOS DE TIPO Y AÑO
    function cargarAuditorias() {
        let url = '{{ route("auditoria.plan.data") }}';
        let params = new URLSearchParams();
        if (tipoSeleccionado) params.append('tipo', tipoSeleccionado);
        if (anioSeleccionado) params.append('anio', anioSeleccionado);
        if (params.toString()) url += '?' + params.toString();

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            auditoriasData = data;
            poblarFiltroAnios(data);   // ACTUALIZA EL DROPDOWN DE AÑOS CON LOS DATOS RECIBIDOS
            renderizarTabla(data);     // DIBUJA LAS FILAS EN LA TABLA
        })
        .catch(error => {
            console.error(error);
            $('#tablaBody').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos</td></tr>');
        });
    }

    // GENERA LAS OPCIONES DEL DROPDOWN DE AÑOS CON LOS AÑOS ÚNICOS DE LAS AUDITORÍAS CARGADAS
    function poblarFiltroAnios(data) {
        const anios = [...new Set(data.map(a => a.anio).filter(Boolean))].sort((a,b) => b-a);
        let html = '<li><a class="dropdown-item" href="#" onclick="seleccionarAnio(\'\', \'Filtrar por Año\')">Todos los años</a></li>';
        anios.forEach(anio => {
            html += `<li><a class="dropdown-item" href="#" onclick="seleccionarAnio('${anio}', 'Año ${anio}')">${anio}</a></li>`;
        });
        $('#menuAnios').html(html);
    }

    // DIBUJA LAS FILAS DE LA TABLA CON LOS DATOS RECIBIDOS
    // TAMBIÉN GENERA LOS MODALES DE VISUALIZACIÓN DE ARCHIVOS
    function renderizarTabla(data) {
        const tbody = $('#tablaBody');
        tbody.empty();

        // SI NO HAY DATOS, MUESTRA MENSAJE INFORMATIVO
        if (data.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center py-4">No hay auditorías registradas</td></tr>');
            return;
        }

        $('#modalesContainer').empty();

        data.forEach(auditoria => {
            // GENERA Y AGREGA EL MODAL DE VISUALIZACIÓN PARA CADA AUDITORÍA CON ARCHIVO
            if (auditoria.archivo_nombre) {
                const modal = generarModalVisualizador(auditoria);
                if (modal) $('#modalesContainer').append(modal);
            }

            // ASIGNA EL BADGE VERDE (INTERNA) O ROJO (EXTERNA)
            const badgeClass = auditoria.tipo_auditoria === 'Interna' ? 'badge-interna' : 'badge-externa';

            // Calcular rango y días
            // CALCULA LOS DÍAS TOTALES ENTRE FECHA INICIO Y FIN Y LOS MUESTRA EN LA CELDA
            let fechas = '-';
            if (auditoria.fecha_inicio && auditoria.fecha_fin) {
                const inicio = moment(auditoria.fecha_inicio);
                const fin = moment(auditoria.fecha_fin);
                const dias = fin.diff(inicio, 'days') + 1;
                fechas = inicio.format('DD/MM/YYYY') + ' - ' + fin.format('DD/MM/YYYY') + ` (${dias} días)`;
            }

            // Determinar si el archivo tiene vista previa (botón "Ver")
            // DETECTA LA EXTENSIÓN Y ASIGNA EL ÍCONO CORRESPONDIENTE
            let tieneVista = false;
            let iconoArchivo = 'bi-file-earmark';
            if (auditoria.archivo_nombre) {
                const ext = auditoria.archivo_nombre.split('.').pop().toLowerCase();
                tieneVista = !extensionesSinVista.includes(ext);
                // Asignar ícono según extensión
                if (ext === 'pdf') iconoArchivo = 'bi-file-pdf';
                else if (['doc', 'docx'].includes(ext)) iconoArchivo = 'bi-file-word';
                else if (['xls', 'xlsx'].includes(ext)) iconoArchivo = 'bi-file-excel';
                else if (ext === 'csv') iconoArchivo = 'bi-file-spreadsheet';
                else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'].includes(ext)) iconoArchivo = 'bi-file-image';
                else if (ext === 'txt') iconoArchivo = 'bi-file-text';
            }

            {{-- Acciones según el rol del usuario --}}
            let acciones = '';
            
            @can('auditoria-access')
                {{-- Admin y superadmin tienen todas las acciones --}}
                // BOTONES: VER (SI TIENE VISTA), EDITAR, DESCARGAR Y ELIMINAR
                acciones = `
                    <div class="d-flex justify-content-end gap-1"> 
                        ${tieneVista ? '<button class="btn btn-sm btn-outline-info" onclick="verArchivo('+auditoria.id+')" title="Ver"><i class="bi bi-eye"></i></button>' : ''}
                        <button class="btn btn-sm btn-outline-secondary" onclick="editarAuditoria(${auditoria.id})" title="Editar"><i class="bi bi-pencil-square"></i></button>
                        <a href="{{ url('auditoria/plan/download') }}/${auditoria.id}" class="btn btn-sm btn-outline-primary" title="Descargar"><i class="bi bi-download"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarAuditoria(${auditoria.id}, '${auditoria.nombre_auditoria.replace(/'/g, "\\'")}')" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </div>
                `;
            @else
                {{-- Usuario normal solo puede ver y descargar --}}
                // SOLO BOTONES: VER (SI TIENE VISTA) Y DESCARGAR
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${tieneVista ? '<button class="btn btn-sm btn-outline-info" onclick="verArchivo('+auditoria.id+')"  title="Ver"><i class="bi bi-eye"></i></button>' : ''}
                        <a href="{{ url('auditoria/plan/download') }}/${auditoria.id}" class="btn btn-sm btn-outline-primary" title="Descargar"><i class="bi bi-download"></i></a>
                    </div>
                `;
            @endcan

            // Mostrar nombre completo del archivo con ícono
            // MUESTRA EL NOMBRE DEL ARCHIVO CON SU ÍCONO CORRESPONDIENTE O UN GUIÓN SI NO HAY ARCHIVO
            let archivoMostrar = '-';
            if (auditoria.archivo_nombre) {
                archivoMostrar = `
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <i class="bi ${iconoArchivo}" style="color: #000000; font-size: 1.2rem;"></i>     
                        <span class="nombre-archivo" title="${auditoria.archivo_nombre}">${auditoria.archivo_nombre}</span>
                    </div>
                `;
            }

            // CONSTRUYE Y AGREGA LA FILA DE LA AUDITORÍA A LA TABLA
            const row = `
                <tr>
                    <td class="fw-bold">${auditoria.nombre_auditoria || ''}</td>
                    <td><span class="${badgeClass}">${auditoria.tipo_auditoria || ''}</span></td>
                    <td>${auditoria.auditor_lider || ''}</td>
                    <td>${fechas}</td>
                    <td>${auditoria.anio || ''}</td>
                    <td>${archivoMostrar}</td>
                    <td>${auditoria.auditores || '-'}</td>
                    <td class="text-end">${acciones}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // GENERA EL HTML DEL MODAL DE VISUALIZACIÓN PARA UN ARCHIVO
    // MUESTRA IMAGEN, PDF/TXT EN IFRAME, O MENSAJE DE "SIN VISTA PREVIA" SEGÚN LA EXTENSIÓN
    function generarModalVisualizador(auditoria) {
        if (!auditoria.archivo_nombre) return '';
        const extension = auditoria.archivo_nombre.split('.').pop().toLowerCase();
        const url = `{{ url('auditoria/plan/ver') }}/${auditoria.id}`;
        const downloadUrl = `{{ url('auditoria/plan/download') }}/${auditoria.id}`;
        const modalId = `viewDocumentModal${auditoria.id}`;

        let contenido = '';
        // Imágenes
        // MUESTRA LA IMAGEN DIRECTAMENTE
        if (['jpg','jpeg','png','gif'].includes(extension)) {
            contenido = `<img src="${url}" class="img-fluid" style="max-height: 100%;">`;
        } 
        // PDF y TXT se muestran en iframe
        // MUESTRA PDF O TXT EN UN IFRAME
        else if (extension === 'pdf' || extension === 'txt') {
            contenido = `<iframe src="${url}" style="width:100%;height:100%;border:none;"></iframe>`;
        } 
        // Resto: sin vista previa
        // OTROS FORMATOS NO SOPORTADOS MUESTRAN MENSAJE INFORMATIVO
        else {
            contenido = `
                <div class="d-flex flex-column justify-content-center align-items-center h-100">
                    <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                    <p class="mt-3">Vista previa no disponible para este tipo de archivo</p>
                </div>
            `;
        }

        // RETORNA EL HTML COMPLETO DEL MODAL DE VISUALIZACIÓN
        return `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-file-earmark-text me-2" style="color: #000000;"></i>
                                ${auditoria.archivo_nombre}
                            </h5>

                        </div>
                        <div class="modal-body p-0" style="height:70vh;">${contenido}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="${downloadUrl}" class="btn text-white" style="background-color:#800000;" download> <i class="bi bi-download me-1"></i> Descargar</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // ABRE EL MODAL DE VISUALIZACIÓN DEL ARCHIVO DE LA AUDITORÍA INDICADA
    function verArchivo(id) {
        const modal = $(`#viewDocumentModal${id}`);
        if (modal.length) new bootstrap.Modal(modal[0]).show();
    }

    // VALIDA TODOS LOS CAMPOS DEL FORMULARIO ANTES DE GUARDAR
    // MUESTRA LOS MENSAJES DE ERROR Y RETORNA false SI ALGUNO ES INVÁLIDO
    function validarFormulario() {
        let valido = true;
        const campos = ['nombre_auditoria', 'tipo_auditoria', 'auditor_lider', 'anio', 'auditores'];
        campos.forEach(id => {
            const valor = $('#'+id).val()?.trim();
            if (!valor) {
                $(`#err-${id}`).show();
                $('#'+id).addClass('campo-invalido');
                valido = false;
            } else {
                $(`#err-${id}`).hide();
                $('#'+id).removeClass('campo-invalido');
            }
        });

        // VALIDA QUE SE HAYA SELECCIONADO UN RANGO DE FECHAS
        if (!$('#fecha_inicio').val() || !$('#fecha_fin').val()) {
            $('#err-rango_fechas').show();
            $('#rango_fechas').addClass('campo-invalido');
            valido = false;
        } else {
            $('#err-rango_fechas').hide();
            $('#rango_fechas').removeClass('campo-invalido');
        }

        // EL ARCHIVO ES OBLIGATORIO SOLO AL CREAR (NO AL EDITAR)
        const esEdicion = !!$('#auditoria_id').val();
        const archivo = $('#archivo_plan')[0].files[0];
        if (!esEdicion && !archivo) {
            $('#err-archivo_plan').show();
            valido = false;
        } else {
            $('#err-archivo_plan').hide();
        }

        return valido;
    }

    // OCULTA TODOS LOS MENSAJES DE ERROR Y QUITA LAS CLASES DE CAMPO INVÁLIDO
    function limpiarErrores() {
        $('.msg-error').hide();
        $('.campo-invalido').removeClass('campo-invalido');
    }

    // RESETEA TODOS LOS CAMPOS DEL FORMULARIO A SU ESTADO INICIAL
    function resetForm() {
        $('#formAuditoria')[0].reset();
        $('#auditoria_id').val('');
        $('#rango_fechas').val('');
        $('#fecha_inicio').val('');
        $('#fecha_fin').val('');
        $('#nombreArchivoActual').hide();
        limpiarErrores();
    }

    // ENVÍA EL FORMULARIO VÍA FETCH (POST PARA CREAR, PUT PARA EDITAR)
    // MUESTRA SPINNER EN EL BOTÓN MIENTRAS SE PROCESA Y RECARGA LA TABLA AL TERMINAR
    function guardarAuditoria() {
        if (!validarFormulario()) return;

        const id = $('#auditoria_id').val();
        const url = id ? `{{ url('auditoria/plan') }}/${id}` : '{{ route('auditoria.plan.store') }}';
        const formData = new FormData($('#formAuditoria')[0]);
        if (id) formData.append('_method', 'PUT'); // LARAVEL NECESITA _method PARA SIMULAR PUT

        // DESHABILITA EL BOTÓN Y MUESTRA SPINNER MIENTRAS SE GUARDA
        $('#btnGuardarAuditoria').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Guardando...');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        })
        .then(data => {
            if (data.success) {
                // ÉXITO: CIERRA EL MODAL, RECARGA LA TABLA Y MUESTRA MENSAJE
                $('#modalNuevaAuditoria').modal('hide');
                cargarAuditorias();
                resetForm();
                mostrarMensajeExito(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error(error);
            let mensaje = 'Error al guardar.';
            if (error.errors) {
                mensaje = Object.values(error.errors).flat().join('\n');
            } else if (error.message) {
                mensaje = error.message;
            }
            alert(mensaje);
        })
        .finally(() => {
            // RESTAURA EL BOTÓN A SU ESTADO ORIGINAL SIN IMPORTAR EL RESULTADO
            $('#btnGuardarAuditoria').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Guardar Auditoría');
        });
    }

    // LLENA EL FORMULARIO CON LOS DATOS DE LA AUDITORÍA Y ABRE EL MODAL EN MODO EDICIÓN
    function editarAuditoria(id) {
        const auditoria = auditoriasData.find(a => a.id === id);
        if (!auditoria) return;

        $('#auditoria_id').val(auditoria.id);
        $('#nombre_auditoria').val(auditoria.nombre_auditoria);
        $('#tipo_auditoria').val(auditoria.tipo_auditoria);
        $('#auditor_lider').val(auditoria.auditor_lider);
        $('#anio').val(auditoria.anio);
        $('#auditores').val(auditoria.auditores || '');

        // LLENA EL CAMPO DE RANGO DE FECHAS Y LOS CAMPOS OCULTOS
        if (auditoria.fecha_inicio && auditoria.fecha_fin) {
            const inicio = moment(auditoria.fecha_inicio);
            const fin = moment(auditoria.fecha_fin);
            $('#rango_fechas').val(inicio.format('DD/MM/YYYY') + ' - ' + fin.format('DD/MM/YYYY'));
            $('#fecha_inicio').val(auditoria.fecha_inicio);
            $('#fecha_fin').val(auditoria.fecha_fin);
        } else {
            $('#rango_fechas').val('');
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
        }

        // MUESTRA EL NOMBRE DEL ARCHIVO ACTUAL SI EXISTE
        if (auditoria.archivo_nombre) {
            $('#nombreArchivo').text(auditoria.archivo_nombre);
            $('#nombreArchivoActual').show();
        } else {
            $('#nombreArchivoActual').hide();
        }

        $('#modalNuevaAuditoria').modal('show');
    }

    // MUESTRA ALERTA DE CONFIRMACIÓN (SWEETALERT2) Y ELIMINA LA AUDITORÍA VÍA FETCH SI SE CONFIRMA
    function eliminarAuditoria(id, nombre) {
        Swal.fire({
            title: '¿Eliminar auditoría?',
            text: `¿Estás seguro de eliminar "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // MUESTRA SPINNER DE CARGA MIENTRAS SE PROCESA LA ELIMINACIÓN
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    showConfirmButton: false,
                    timer: null
                });

                fetch(`{{ url('auditoria/plan') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // ELIMINACIÓN EXITOSA: MUESTRA MENSAJE Y RECARGA LA PÁGINA
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // ERROR DEL SERVIDOR: MUESTRA EL MENSAJE
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al eliminar',
                            confirmButtonColor: '#800000',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                })
                .catch(error => {
                    // ERROR DE CONEXIÓN
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión',
                        confirmButtonColor: '#800000',
                        confirmButtonText: 'Cerrar'
                    });
                });
            }
        });
    }

    // MUESTRA UN MENSAJE DE ÉXITO VERDE EN LA PARTE SUPERIOR DE LA PÁGINA
    // SE OCULTA AUTOMÁTICAMENTE DESPUÉS DE 5 SEGUNDOS
    function mostrarMensajeExito(mensaje) {
        const alerta = `
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle me-2"></i> ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.container-fluid .row:first .col-12').prepend(alerta);
        setTimeout(() => $('.alert-success').alert('close'), 5000);
    }

    // FUNCIONES DE FILTROS

    // APLICA EL ORDENAMIENTO SELECCIONADO Y ACTUALIZA EL TEXTO DEL BOTÓN
    function seleccionarOrden(criterio, texto) {
        ordenSeleccionado = criterio;
        $('#ordenarTexto').text(texto);
        $('#btnOrdenar').addClass('seleccionado');
        filtrarYRenderizar();
    }

    // APLICA EL FILTRO DE TIPO Y ACTUALIZA EL TEXTO DEL BOTÓN
    function seleccionarTipo(tipo, texto) {
        tipoSeleccionado = tipo;
        $('#tipoTexto').text(texto);
        if (tipo) $('#btnTipo').addClass('seleccionado'); else $('#btnTipo').removeClass('seleccionado');
        filtrarYRenderizar();
    }

    // APLICA EL FILTRO DE AÑO Y ACTUALIZA EL TEXTO DEL BOTÓN
    function seleccionarAnio(anio, texto) {
        anioSeleccionado = anio;
        $('#anioTexto').text(texto);
        if (anio) $('#btnAnio').addClass('seleccionado'); else $('#btnAnio').removeClass('seleccionado');
        filtrarYRenderizar();
    }

    // APLICA TODOS LOS FILTROS ACTIVOS (TIPO, AÑO, BÚSQUEDA Y ORDEN) Y RENDERIZA LA TABLA
    function filtrarYRenderizar() {
        let datos = auditoriasData.filter(a => {
            if (tipoSeleccionado && a.tipo_auditoria !== tipoSeleccionado) return false;
            if (anioSeleccionado && String(a.anio) !== String(anioSeleccionado)) return false;
            return true;
        });

        // FILTRA POR TEXTO: BUSCA EN NOMBRE Y AUDITOR LÍDER
        const texto = $('#buscadorArchivos').val().toLowerCase().trim();
        if (texto) {
            datos = datos.filter(a => a.nombre_auditoria.toLowerCase().includes(texto) || (a.auditor_lider && a.auditor_lider.toLowerCase().includes(texto)));
        }

        // APLICA EL CRITERIO DE ORDENAMIENTO SELECCIONADO
        if (ordenSeleccionado) {
            switch(ordenSeleccionado) {
                case 'nombre-asc':
                    datos.sort((a,b) => a.nombre_auditoria.localeCompare(b.nombre_auditoria));
                    break;
                case 'nombre-desc':
                    datos.sort((a,b) => b.nombre_auditoria.localeCompare(a.nombre_auditoria));
                    break;
                case 'fecha-asc':
                    datos.sort((a,b) => new Date(a.fecha_inicio) - new Date(b.fecha_inicio));
                    break;
                case 'fecha-desc':
                    datos.sort((a,b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio));
                    break;
            }
        }

        renderizarTabla(datos);
    }

    // LLAMA A filtrarYRenderizar() AL BUSCAR (FUNCIÓN PUENTE)
    function filtrarPorBusqueda() {
        filtrarYRenderizar();
    }
    
    // LIMPIA EL CAMPO DE BÚSQUEDA Y MUESTRA TODOS LOS RESULTADOS
    function limpiarBuscador() {
        $('#buscadorArchivos').val('');
        filtrarPorBusqueda('');
    }
</script>
@endpush