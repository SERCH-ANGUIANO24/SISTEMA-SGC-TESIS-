@extends('layouts.app')

@section('title', 'Informes de Auditoría')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    /* ── Tabla - igual que Plan de Auditorías ── */
    .table th {
        background-color: #f8f9fa;
        color: black;
        text-align: center;
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 12px;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
        font-size: 0.9rem;
        padding: 10px 12px;
    }

    .table tbody tr:hover {
        background-color: #fdf0f1;
    }

    /* ── Badges igual que Plan de Auditorías ── */
    .badge-interna {
        background-color: #28a745;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .badge-externa {
        background-color: #dc3545;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* ── Botones de acción - igual que Plan de Auditorías ── */
    .btn-accion {
        margin: 0 2px;
    }

    .btn-outline-info    { color: #0dcaf0; border-color: #0dcaf0; }
    .btn-outline-info:hover    { background-color: 0dcaf0; color: #fff; }

    .btn-outline-secondary { color: #6c757d; border-color: #6c757d; }
    .btn-outline-secondary:hover { background-color: #6c757d; color: #fff; }

    .btn-outline-primary { color: #0d6efd; border-color: #0d6efd; }
    .btn-outline-primary:hover { background-color: #0d6efd; color: #fff; }

    .btn-outline-danger  { color: #dc3545; border-color: #dc3545; }
    .btn-outline-danger:hover  { background-color: #dc3545; color: #fff; }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    /* ── Botones dropdown - igual que Plan de Auditorías ── */
    .btn-light {
        background-color: white !important;
        color: #6c757d;
        border: 1px solid #ced4da;
    }

    .btn-light:hover {
        background-color: #f8f9fa !important;
        border-color: #800000;
    }

    .btn-light i { color: #6c757d; }

    .btn-light.seleccionado {
        background-color: #e9ecef !important;
        border-color: #737373;
        color: #495057;
    }

    .btn-light.seleccionado i { color: #495057; }

    .dropdown-item:hover {
        background-color: #737373 !important;
        color: #ffffff !important;
    }

    .dropdown-item.active {
        background-color: #800000 !important;
        color: white !important;
    }

    /* ── Botón limpiar búsqueda ── */
    #limpiarBusqueda {
        transition: all 0.2s ease;
        border-color: #ced4da;
        background-color: white;
    }

    #limpiarBusqueda:hover {
        background-color: #f8f9fa;
        border-color: #800000;
    }

    #limpiarBusqueda:hover i { color: #800000; }

    /* ── Hover botón registrar ── */
    .btn[style*="background-color: #737373"]:hover {
        background-color: #5a5a5a !important;
        color: white !important;
    }

    /* ── Tabla responsive ── */
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 15px;
    }

    /* ── Paginación ── */
    .pagination-info {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .pagination {
        display: flex;
        justify-content: flex-end;
        gap: 5px;
    }

    /* ── Procesos ── */
    .procesos-container {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
    }

    .tag-proceso {
        background-color: #e9ecef;
        color: #495057;
        border-radius: 20px;
        padding: 2px 9px;
        font-size: 0.72rem;
        display: inline-block;
        margin: 1px;
    }

    .num-red    { color: #dc3545; font-weight: 700; text-align: center; }
    .num-orange { color: #fd7e14; font-weight: 700; text-align: center; }

    /* ── Modales ── */
    .modal-header-rojo {
        background-color: #800000;
        color: #fff;
    }

    .modal-header-cyan {
        background-color: #0dcaf0;
        color: #fff;
    }

    .modal-header-cyan .btn-close,
    .modal-header-rojo .btn-close {
        filter: invert(1);
    }

    /* ── Tarjetas estadísticas ── */
    .stat-card {
        border-radius: 10px;
        color: #fff;
        text-align: center;
        padding: 18px 10px;
    }

    .stat-card .num   { font-size: 2rem; font-weight: 700; }
    .stat-card .label { font-size: 0.85rem; }

    .stat-blue  { background: #0d6efd; }
    .stat-red   { background: #dc3545; }
    .stat-green { background: rgba(253, 126, 20, 0.85); }

    /* ── Visor de documento ── */
    #iframeDoc { width: 100%; height: 100%; border: none; }

    #modalDocumento .modal-dialog {
        max-width: 90%;
    }

    #modalDocumento .modal-body {
        height: 80vh;
        overflow: auto;
        padding: 0;
    }

    #modalDocumento .modal-body iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* ── Formulario labels ── */
    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
    }

    #selectAnioEstadisticas { border: 2px solid #0dcaf0; }

    .grafica-anual-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }

    /* ── Drag & Drop - igual que Plan de Auditorías ── */
    .border.rounded.p-4.bg-light,
    .drag-area {
        border: 2px dashed #800000 !important;
        border-radius: 5px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .drag-area:hover,
    .border.rounded.p-4.bg-light:hover {
        background-color: #fff0f0 !important;
        border-color: #600000 !important;
    }

    .drag-area i { font-size: 3rem; color: #800000; }
    .drag-area p { margin: 5px 0 0; color: #6c757d; }

    /* ── Form controls focus ── */
    .form-control:focus,
    .form-select:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
        z-index: 1;
    }

    /* ── Lista checkboxes procesos ── */
    .procesos-checklist {
        background-color: #f8f9fa;
        max-height: 200px;
        overflow-y: auto;
        column-count: 2;
        column-gap: 10px;
    }

    .procesos-checklist .form-check {
        break-inside: avoid;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.15s;
    }

    .procesos-checklist .form-check:hover { background-color: #f0e0e2; }

    .procesos-checklist .form-check-input:checked ~ .form-check-label {
        color: #800000;
        font-weight: 600;
    }

    .procesos-checklist .form-check-input:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
    }

    .procesos-checklist .form-check-input:checked {
        background-color: #800000;
        border-color: #800000;
    }

    /* ── Mensaje de éxito - igual que Plan de Auditorías ── */
    .alert-exito {
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

    .alert-exito i { font-size: 1.5rem; margin-right: 15px; }

    .alert-exito .btn-close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        filter: invert(1);
    }

    /* ── Botón eliminar proceso ── */
    .btn-eliminar-proceso {
        background: none;
        border: none;
        color: #dc3545;
        font-size: 1.1rem;
        padding: 0 2px;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
        opacity: 0.6;
        transition: opacity 0.15s;
    }

    .btn-eliminar-proceso:hover { opacity: 1; }

    /* ── Validación ── */
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .was-validated .form-control:invalid,
    .form-control.is-invalid,
    .was-validated .form-select:invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .was-validated .form-control:invalid:focus,
    .form-control.is-invalid:focus,
    .was-validated .form-select:invalid:focus,
    .form-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    .drag-area.is-invalid {
        border-color: #dc3545 !important;
        background-color: rgba(220, 53, 69, 0.05);
    }

    .procesos-checklist.is-invalid {
        border-color: #dc3545 !important;
        background-color: rgba(220, 53, 69, 0.02);
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
        20%, 40%, 60%, 80% { transform: translateX(2px); }
    }

    .campo-invalido-shake { animation: shake 0.5s ease-in-out; }

    /* ── SweetAlert2 personalizado ── */
    .swal2-popup  { font-size: 1.2rem !important; }
    .swal2-title  { color: #800000 !important; }
    .swal2-confirm { background-color: #dc3545 !important; }
    .swal2-cancel  { background-color: #6c757d !important; }

    /* Estilo para mostrar el rango de fechas en la tabla */
    .rango-fechas {
        background-color: #f8f9fa;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        display: inline-block;
        border-left: 3px solid #800000;
    }
    .rango-fechas i {
        color: #800000;
        margin-right: 5px;
    }
    .fecha-detalle {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 2px;
    }

    /* ── Autocomplete procesos custom ── */
    #autocomplete-procesos {
        display: none; position: absolute; z-index: 9999;
        background: #fff; border: 1px solid #dee2e6; border-top: none;
        border-radius: 0 0 6px 6px; width: 100%; max-height: 200px;
        overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        left: 0; top: 100%;
    }
    #autocomplete-procesos .ac-item {
        padding: 8px 12px; cursor: pointer; font-size: 0.88rem;
        border-bottom: 1px solid #f3f3f3; display: flex; align-items: center; gap: 8px;
    }
    #autocomplete-procesos .ac-item:hover,
    #autocomplete-procesos .ac-item.ac-active { background-color: #fdf0f1; color: #800000; }
    #autocomplete-procesos .ac-item i { color: #800000; font-size: 0.8rem; }

    /* ── NUEVO: tabla NC/OM por proceso ── */
    #tablaNcOmPorProceso {
        display: none;
        margin-top: 16px;
    }

    #tablaNcOmPorProceso table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    #tablaNcOmPorProceso thead th {
        background-color: #800000;
        color: #fff;
        padding: 8px 10px;
        text-align: center;
        font-weight: 600;
        border: none;
    }

    #tablaNcOmPorProceso thead th:first-child {
        text-align: left;
        border-radius: 6px 0 0 0;
    }

    #tablaNcOmPorProceso thead th:last-child {
        border-radius: 0 6px 0 0;
    }

    #tablaNcOmPorProceso tbody tr:nth-child(even) {
        background-color: #fdf0f1;
    }

    #tablaNcOmPorProceso tbody td {
        padding: 6px 10px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }

    #tablaNcOmPorProceso tbody td input[type="number"] {
        width: 80px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 3px 6px;
        font-size: 0.88rem;
    }

    #tablaNcOmPorProceso tbody td input[type="number"]:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.15rem rgba(128,0,0,0.2);
        outline: none;
    }

    .totales-nc-om {
        margin-top: 8px;
        font-size: 0.88rem;
        display: flex;
        gap: 20px;
        justify-content: flex-end;
        padding-right: 4px;
    }

    .totales-nc-om .badge-total-nc {
        background-color: #dc3545;
        color: #fff;
        border-radius: 20px;
        padding: 3px 12px;
        font-weight: 600;
    }

    .totales-nc-om .badge-total-om {
        background-color: #fd7e14;
        color: #fff;
        border-radius: 20px;
        padding: 3px 12px;
        font-weight: 600;
    }

    /* ── NUEVO: filtro tipo en estadísticas ── */
    .filtro-tipo-estadisticas .btn-tipo {
        border: 2px solid #dee2e6;
        border-radius: 6px;
        padding: 5px 18px;
        font-size: 0.88rem;
        font-weight: 500;
        cursor: pointer;
        background: #fff;
        color: #495057;
        transition: all 0.15s;
    }

    .filtro-tipo-estadisticas .btn-tipo:hover {
        border-color: #0dcaf0;
        color: #0dcaf0;
    }

    .filtro-tipo-estadisticas .btn-tipo.activo-todos {
        background-color: #0dcaf0;
        border-color: #0dcaf0;
        color: #fff;
    }

    .filtro-tipo-estadisticas .btn-tipo.activo-interna {
        background-color: #28a745;
        border-color: #28a745;
        color: #fff;
    }

    .filtro-tipo-estadisticas .btn-tipo.activo-externa {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- ── ENCABEZADO ── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                    <h1 class="h3 mb-0" style="color: #800000; cursor: pointer;">
                        <i class="bi bi-file-earmark-text me-2" style="font-size: 2.5rem; vertical-align: middle;"></i>
                        Informes
                    </h1>
                </a>
                <button class="btn" type="button" id="btnNuevoInforme" style="background-color: #737373; color: white; border: none;">
                    <i class="bi bi-plus-circle"></i> Registrar Informe
                </button>
            </div>
        </div>
    </div>

    {{-- ── FILTROS ── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 flex-wrap">

                <!-- Buscar archivos -->
                <div class="d-flex align-items-center position-relative" style="width: 700px;">
                    <div class="position-relative flex-grow-1">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                        <input type="text" class="form-control ps-5" id="inputBuscar"
                               style="width: 100%; height: 42px; font-size: 1rem; border-radius: 4px 0 0 4px; border-right: none;"
                               placeholder="Buscar archivos">
                    </div>
                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                            style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border-left: none;"
                            id="limpiarBusqueda"
                            onclick="limpiarBuscador()"
                            title="Limpiar búsqueda">
                        <i class="bi bi-x-lg" style="font-size: 1.4rem; font-weight: bold;"></i>
                    </button>
                </div>

                <!-- Ordenar por -->
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
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnAnio" style="height: 42px; background-color: white;">
                        <i class="bi bi-calendar"></i> <span id="anioTexto">Filtrar por Año</span>
                    </button>
                    <ul class="dropdown-menu" id="menuAnios">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('', 'Filtrar por Año')">Todos los años</a></li>
                    </ul>
                </div>

                <!-- Tipo de Auditoría -->
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnTipo" style="height: 42px; background-color: white;">
                        <i class="bi bi-building"></i> <span id="tipoTexto">Tipo de Auditoría</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('', 'Tipo de Auditoría')">Todos los tipos</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Interna', 'Interna')" id="opcionInterna">Interna</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Externa', 'Externa')" id="opcionExterna">Externa</a></li>
                    </ul>
                </div>

                <!-- Estadísticas -->
                <button class="btn" id="btnEstadisticas" style="background-color: #0dcaf0; color: #fff; border: none; height: 42px; padding: 8px 15px; font-weight: 500;">
                    <i class="bi bi-bar-chart-line me-1"></i>Estadísticas
                </button>

            </div>
        </div>
    </div>

    {{-- ── TABLA ── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-responsive">
                <table id="tablaInformes" class="table table-bordered" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre de Informe</th>
                            <th>Tipo</th>
                            <th>Auditor Líder</th>
                            <th>Fecha Informe</th>
                            <th>Periodo Auditoría</th>
                            <th>Año</th>
                            <th>Procesos Auditados</th>
                            <th>No Conformidades</th>
                            <th>Oport. Mejora</th>
                            <th>Documento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($informes as $inf)
                        <tr class="align-middle"
                            data-tipo="{{ $inf->tipo_auditoria }}"
                            data-nc-om-por-proceso="{{ json_encode($inf->nc_om_por_proceso ?? []) }}">
                            <td class="fw-bold">{{ $inf->nombre_informe }}</td>
                            <td>
                                <span class="badge-{{ strtolower($inf->tipo_auditoria) }}">{{ $inf->tipo_auditoria }}</span>
                            </td>
                            <td>{{ $inf->auditor_lider }}</td>
                            <td>{{ $inf->fecha_informe->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $fi = $inf->fecha_inicio ?? ($inf->auditoriaRelacionada?->fecha_inicio ?? null);
                                    $ff = $inf->fecha_fin    ?? ($inf->auditoriaRelacionada?->fecha_fin    ?? null);
                                @endphp
                                @if($fi && $ff)
                                    {{ \Carbon\Carbon::parse($fi)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($ff)->format('d/m/Y') }}
                                    <div class="fecha-detalle">({{ \Carbon\Carbon::parse($fi)->diffInDays(\Carbon\Carbon::parse($ff)) + 1 }} días)</div>
                                @elseif($inf->fecha_auditoria)
                                    {{ $inf->fecha_auditoria->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $inf->anio }}</td>
                            <td>
                                <div class="procesos-container">
                                    @if($inf->procesos_auditados)
                                        @foreach($inf->procesos_auditados as $p)
                                            <span class="tag-proceso">{{ $p }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="num-red">{{ $inf->no_conformidades }}</td>
                            <td class="num-orange">{{ $inf->oportunidades_mejora }}</td>
                            <td>
                                @if($inf->documento_path)
                                    <span style="color: #212529;"><i class="bi bi-file-earmark-pdf me-1" style="color: #dc3545;"></i>{{ Str::limit($inf->documento_nombre, 20) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    @if($inf->documento_path && strtolower(pathinfo($inf->documento_nombre, PATHINFO_EXTENSION)) === 'pdf')
                                    <button type="button" class="btn btn-sm btn-outline-info" title="Ver Documento"
                                        onclick="verDocumento({{ $inf->id }}, '{{ addslashes($inf->documento_nombre) }}')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @endif
                                    @if($inf->documento_path)
                                    <a href="{{ url('auditorias/informes') }}/{{ $inf->id }}/descargar"
                                       class="btn btn-sm btn-outline-primary" title="Descargar Documento">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                        onclick="editarInforme({{ $inf->id }})">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                        onclick="eliminarInforme({{ $inf->id }}, '{{ addslashes($inf->nombre_informe) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="text-center text-muted py-3">No hay informes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="pagination-info">
        Mostrando registros del {{ $informes->firstItem() ?? 0 }} al {{ $informes->lastItem() ?? 0 }}
        de un total de {{ $informes->total() }} registros
    </div>

    <div class="pagination">
        {{ $informes->links('pagination::bootstrap-5') }}
    </div>

</div><!-- /container -->


{{-- ══════════════════════════════════════════════════════════════
     MODAL: SUBIR / EDITAR INFORME
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalInforme" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-rojo">
        <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i><span id="tituloModalInforme">Subir Informe</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formInforme" enctype="multipart/form-data" novalidate>
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <input type="hidden" name="informe_id" id="informeId">

          <div class="row g-3">
            {{-- Nombre del Informe --}}
            <div class="col-md-6">
              <label class="form-label">Nombre del Informe <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre_informe" id="fNombre" placeholder="Ej. Informe_2024_Q1" required>
              <div class="invalid-feedback" id="fNombre-feedback">El nombre del informe es requerido</div>
            </div>
            {{-- Tipo de Auditoría --}}
            <div class="col-md-6">
              <label class="form-label">Tipo de Auditoría <span class="text-danger">*</span></label>
              <select class="form-select" name="tipo_auditoria" id="fTipo" required>
                <option value="">-- Seleccionar --</option>
                <option value="Interna">Interna</option>
                <option value="Externa">Externa</option>
              </select>
              <div class="invalid-feedback" id="fTipo-feedback">El tipo de auditoría es requerido</div>
            </div>
            {{-- Auditor Líder --}}
            <div class="col-md-6">
              <label class="form-label">Auditor Líder <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="auditor_lider" id="fAuditor" placeholder="Nombre del auditor" required>
              <div class="invalid-feedback" id="fAuditor-feedback">El nombre del auditor líder es requerido</div>
            </div>
            {{-- Auditoría Relacionada --}}
            <div class="col-md-6">
              <label class="form-label">Auditoría Relacionada</label>
              <select class="form-select" name="auditoria_relacionada_id" id="fAuditoriaRel">
                <option value="">-- Seleccionar --</option>
                @foreach($planesAuditoria as $plan)
                  <option value="{{ $plan->id }}"
                          data-fecha-inicio="{{ $plan->fecha_inicio ? \Carbon\Carbon::parse($plan->fecha_inicio)->format('Y-m-d') : '' }}"
                          data-fecha-fin="{{ $plan->fecha_fin ? \Carbon\Carbon::parse($plan->fecha_fin)->format('Y-m-d') : '' }}">
                    {{ $plan->nombre_auditoria }}
                  </option>
                @endforeach
              </select>
              <small class="text-muted">Al seleccionar, se copiará el rango de fechas al campo "Periodo de Auditoría"</small>
            </div>
            {{-- Fecha del Informe --}}
            <div class="col-md-6">
              <label class="form-label">Fecha del Informe <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_informe" id="fFechaInforme" required>
              <div class="invalid-feedback" id="fFechaInforme-feedback">La fecha del informe es requerida</div>
            </div>
            {{-- Periodo de Auditoría --}}
            <div class="col-md-6">
              <label class="form-label">
                Periodo de Auditoría <span class="text-danger">*</span>
                <small class="text-muted fw-normal ms-1"><i class="bi bi-lock-fill"></i> Se llena automáticamente</small>
              </label>
              <input type="text" class="form-control" id="rango_fechas_auditoria" name="rango_fechas_auditoria"
                     placeholder="Selecciona una Auditoría Relacionada para ver el periodo"
                     readonly style="background-color:#f8f9fa; cursor:not-allowed; border-color:#ced4da;">
              <input type="hidden" id="fecha_inicio" name="fecha_inicio">
              <input type="hidden" id="fecha_fin" name="fecha_fin">
              <div class="invalid-feedback" id="rango-fechas-feedback">Debe seleccionar el periodo de auditoría</div>
            </div>
            {{-- Procesos Auditados --}}
            <div class="col-12">
              <label class="form-label">Procesos Auditados <span class="text-danger">*</span> <small class="text-muted">(puede elegir varios)</small></label>
              <div id="fProcesos" class="procesos-checklist border rounded p-3">
                @foreach($procesos as $proc)
                  <div class="form-check">
                    <input class="form-check-input proceso-check" type="checkbox"
                           name="procesos_auditados[]"
                           value="{{ $proc }}"
                           id="proc_{{ Str::slug($proc) }}">
                    <label class="form-check-label" for="proc_{{ Str::slug($proc) }}">{{ $proc }}</label>
                  </div>
                @endforeach
              </div>
              <div class="invalid-feedback" id="fProcesos-feedback">Debe seleccionar al menos un proceso</div>
            </div>

            {{-- ══ NUEVO: Tabla dinámica NC y OM por proceso ══ --}}
            <div class="col-12" id="tablaNcOmPorProceso">
              <label class="form-label">
                <i class="bi bi-table me-1" style="color:#800000;"></i>
                No Conformidades y Oportunidades de Mejora por Proceso
              </label>
              <div class="border rounded overflow-hidden">
                <table style="width:100%; border-collapse:collapse; font-size:0.88rem;">
                  <thead>
                    <tr>
                      <th style="background:#800000;color:#fff;padding:8px 12px;text-align:left;font-weight:600;">Proceso</th>
                      <th style="background:#800000;color:#fff;padding:8px 12px;text-align:center;font-weight:600;width:130px;">No Conformidades</th>
                      <th style="background:#800000;color:#fff;padding:8px 12px;text-align:center;font-weight:600;width:150px;">Oport. de Mejora</th>
                    </tr>
                  </thead>
                  <tbody id="cuerpoTablaNcOm">
                    {{-- Filas generadas dinámicamente por JS --}}
                  </tbody>
                </table>
              </div>
              <div class="totales-nc-om mt-2">
                <span>Totales:</span>
                <span class="badge-total-nc">NC: <span id="totalNcDisplay">0</span></span>
                <span class="badge-total-om">OM: <span id="totalOmDisplay">0</span></span>
              </div>
              <small class="text-muted mt-1 d-block">
                <i class="bi bi-info-circle me-1"></i>
                Los totales se calculan automáticamente sumando los valores de cada proceso.
              </small>
            </div>
            {{-- ══ FIN tabla dinámica ══ --}}

            {{-- Campos ocultos para totales (compatibilidad con validación del servidor) --}}
            <input type="hidden" name="no_conformidades" id="fNoConf" value="0">
            <input type="hidden" name="oportunidades_mejora" id="fOport" value="0">

            {{-- Documento --}}
            <div class="col-12">
              <label class="form-label">Documento <span id="docRequerido" class="text-danger">*</span></label>
              <div class="drag-area border rounded p-4 bg-light" onclick="document.getElementById('fDocumento').click()" id="dragArea">
                <div class="text-center mb-3">
                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #800000;"></i>
                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                    <p class="text-muted small mb-0" id="docLabel">PDF, DOC, DOCX, XLS, XLSX, CSV — máx. 10 MB</p>
                </div>
              </div>
              <input type="file" id="fDocumento" name="documento" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv" class="d-none"
                onchange="document.getElementById('docLabel').textContent = this.files[0]?.name ?? 'PDF, DOC, DOCX, XLS, XLSX — máx. 10 MB'">
              <div class="invalid-feedback" id="fDocumento-feedback">El documento es requerido</div>
            </div>
          </div>
          {{-- Errores --}}
          <div id="erroresForm" class="alert alert-danger mt-3 d-none"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="cerrarModalInforme()">Cancelar</button>
        <button type="button" class="btn text-white" style="background-color: #800000; border: none;" onclick="guardarInforme()">
          <i class="bi bi-check-circle me-1"></i> Guardar Informe
        </button>
      </div>
    </div>
  </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL: ESTADÍSTICAS POR AÑO  (CON FILTRO TIPO + GRÁFICA POR PROCESO)
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEstadisticas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-cyan">
        <h5 class="modal-title"><i class="bi bi-bar-chart-line me-2"></i>Estadísticas por Año</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        {{-- ── Fila selectores: Año + Tipo ── --}}
        <div class="row mb-4 align-items-center g-2">
          <div class="col-auto">
            <label class="form-label fw-bold mb-0">Año</label>
          </div>
          <div class="col-auto">
            <select id="selectAnioEstadisticas" class="form-select" style="width:150px; border:2px solid #0dcaf0;">
              <option value="">Cargando años...</option>
            </select>
          </div>
          <div class="col-auto ms-3">
            <label class="form-label fw-bold mb-0">Tipo de Auditoría</label>
          </div>
          <div class="col-auto filtro-tipo-estadisticas d-flex gap-2">
            <button type="button" class="btn-tipo activo-todos" id="btnTipoTodos" onclick="seleccionarTipoEstadisticas('todos')">
              <i class="bi bi-grid me-1"></i>Todos
            </button>
            <button type="button" class="btn-tipo" id="btnTipoInterna" onclick="seleccionarTipoEstadisticas('Interna')">
              <i class="bi bi-check-circle me-1"></i>Interna
            </button>
            <button type="button" class="btn-tipo" id="btnTipoExterna" onclick="seleccionarTipoEstadisticas('Externa')">
              <i class="bi bi-x-circle me-1"></i>Externa
            </button>
          </div>
        </div>

        {{-- ── Tarjetas resumen ── --}}
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="stat-card stat-blue">
              <div class="label">Total Auditorías</div>
              <div class="num" id="statTotal">0</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card stat-red">
              <div class="label">No Conformidades</div>
              <div class="num" id="statNC">0</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card stat-green">
              <div class="label">Oportunidades de Mejora</div>
              <div class="num" id="statOM">0</div>
            </div>
          </div>
        </div>

        {{-- ── Gráfica: NC y OM por proceso ── --}}
        <div class="grafica-anual-container mt-3">
          <h6 class="fw-bold text-muted text-center mb-3">
            <i class="bi bi-diagram-3 me-1"></i>
            No Conformidades y Oportunidades de Mejora por Proceso
          </h6>
          <div id="sinDatosProcesoMsg" class="text-center text-muted py-3" style="display:none;">
            <i class="bi bi-info-circle me-1"></i>
            No hay datos de desglose por proceso para los filtros seleccionados.
          </div>
          <div id="wrapperChartPorProceso">
            <canvas id="chartPorProceso" height="300"></canvas>
          </div>
        </div>

        {{-- ── Lista procesos ── --}}
        <div class="row mt-4">
          <div class="col-12">
            <h6 class="fw-bold text-muted">Procesos Auditados en el año:</h6>
            <div id="listaProcesosEstadisticas" class="d-flex flex-wrap gap-1 mt-2 p-3 border rounded bg-light"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL: VER DOCUMENTO
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-rojo">
        <h5 class="modal-title"><i class="bi bi-file-earmark me-2"></i><span id="tituloDocumento"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="iframeDoc" src="about:blank"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cerrar
        </button>
        <a id="btnDescargarDocumento" href="#" class="btn text-white" style="background-color: #800000; border: none;">
          <i class="bi bi-download me-1"></i> Descargar
        </a>
      </div>
    </div>
  </div>
</div>
@endsection


@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// ── Configuración de rutas ────────────────────────────────────────────────────
const ROUTES = {
    index       : "{{ route('informes-auditoria.index') }}",
    store       : "{{ route('informes-auditoria.store') }}",
    show        : (id) => `{{ url('auditorias/informes') }}/${id}`,
    update      : (id) => `{{ url('auditorias/informes') }}/${id}`,
    destroy     : (id) => `{{ url('auditorias/informes') }}/${id}`,
    estadisticas: "{{ route('informes-auditoria.estadisticas') }}",
    documento   : (id) => `{{ url('auditorias/informes') }}/${id}/documento`,
    descargar      : (id) => `{{ url('auditorias/informes') }}/${id}/descargar`,
    procesosCustom : "{{ route('informes-auditoria.procesos-custom') }}",
};

const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let chartPorProceso    = null;   // ← NUEVA gráfica
let tipoSeleccionado   = '';
let anioSeleccionado   = '';
let ordenSeleccionado  = '';
let tipoEstadisticas   = 'todos'; // ← filtro dentro del modal estadísticas

// ── Campo Periodo de Auditoría: solo lectura, se llena desde Auditoría Relacionada ──
function inicializarDateRangePicker() {
    // Campo solo lectura — no usa daterangepicker.
    // El valor se asigna automáticamente al seleccionar una Auditoría Relacionada.
}

// Al seleccionar auditoría relacionada → copiar fechas al campo (solo lectura)
document.getElementById('fAuditoriaRel').addEventListener('change', function () {
    const selected    = this.options[this.selectedIndex];
    const fechaInicio = selected.dataset.fechaInicio;
    const fechaFin    = selected.dataset.fechaFin;

    if (fechaInicio && fechaFin) {
        // Formatear como DD/MM/YYYY - DD/MM/YYYY para mostrar
        const fmtInicio = fechaInicio.split('-').reverse().join('/');
        const fmtFin    = fechaFin.split('-').reverse().join('/');
        document.getElementById('rango_fechas_auditoria').value = fmtInicio + ' - ' + fmtFin;
        document.getElementById('fecha_inicio').value = fechaInicio;
        document.getElementById('fecha_fin').value    = fechaFin;
        document.getElementById('rango_fechas_auditoria').classList.remove('is-invalid');
        const fb = document.getElementById('rango-fechas-feedback');
        if (fb) fb.style.display = 'none';
    } else {
        // Si se deselecciona, limpiar el campo
        document.getElementById('rango_fechas_auditoria').value = '';
        document.getElementById('fecha_inicio').value = '';
        document.getElementById('fecha_fin').value    = '';
    }
});

// ── Helpers modales ───────────────────────────────────────────────────────────
function getModalInstance(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return null;
    let inst = bootstrap.Modal.getInstance(el);
    if (!inst) inst = new bootstrap.Modal(el, { backdrop: true, keyboard: true });
    return inst;
}

function limpiarBuscador() {
    const b = document.getElementById('inputBuscar');
    if (b) { b.value = ''; aplicarFiltros(); b.focus(); }
}

function seleccionarAnio(anio, texto) {
    anioSeleccionado = anio;
    document.getElementById('anioTexto').innerText = texto;
    document.getElementById('btnAnio').classList.toggle('seleccionado', anio !== '');
    aplicarFiltros();
}

function seleccionarTipo(tipo, texto) {
    tipoSeleccionado = tipo;
    document.getElementById('tipoTexto').innerText = texto;
    document.getElementById('btnTipo').classList.toggle('seleccionado', tipo !== '');
    const opI = document.getElementById('opcionInterna');
    const opE = document.getElementById('opcionExterna');
    if (opI) opI.classList.remove('active');
    if (opE) opE.classList.remove('active');
    if (tipo === 'Interna' && opI) opI.classList.add('active');
    else if (tipo === 'Externa' && opE) opE.classList.add('active');
    aplicarFiltros();
}

function seleccionarOrden(criterio, texto) {
    ordenSeleccionado = criterio;
    document.getElementById('ordenarTexto').innerText = texto;
    if (criterio) document.getElementById('btnOrdenar').classList.add('seleccionado');
    aplicarFiltros();
}

function aplicarFiltros() {
    const params = new URLSearchParams();
    const buscar = document.getElementById('inputBuscar').value;
    if (buscar)            params.set('buscar', buscar);
    if (anioSeleccionado)  params.set('anio', anioSeleccionado);
    if (tipoSeleccionado)  params.set('tipo', tipoSeleccionado);
    if (ordenSeleccionado) params.set('orden', ordenSeleccionado);
    window.location.href = ROUTES.index + (params.toString() ? '?' + params.toString() : '');
}

// ── Reset formulario ──────────────────────────────────────────────────────────
function resetForm() {
    const form = document.getElementById('formInforme');
    if (form) form.reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('informeId').value  = '';
    const err = document.getElementById('erroresForm');
    if (err) { err.classList.add('d-none'); err.innerHTML = ''; }
    const docLabel = document.getElementById('docLabel');
    if (docLabel) docLabel.textContent = 'PDF, DOC, DOCX, XLS, XLSX — máx. 10 MB';
    document.querySelectorAll('.proceso-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('#fProcesos .form-check').forEach(div => {
        const cb = div.querySelector('input');
        if (cb && cb.id.startsWith('proc_new_')) div.remove();
    });
    const np = document.getElementById('nuevoProceso');
    if (np) np.value = '';
    const fi = document.getElementById('fDocumento');
    if (fi) { fi.value = ''; fi.required = true; }
    const dr = document.getElementById('docRequerido');
    if (dr) dr.textContent = '*';

    // Limpiar fechas
    document.getElementById('rango_fechas_auditoria').value = '';
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value    = '';

    // ── Limpiar tabla NC/OM ──
    document.getElementById('cuerpoTablaNcOm').innerHTML = '';
    document.getElementById('tablaNcOmPorProceso').style.display = 'none';
    document.getElementById('totalNcDisplay').textContent = '0';
    document.getElementById('totalOmDisplay').textContent = '0';
    document.getElementById('fNoConf').value = '0';
    document.getElementById('fOport').value  = '0';

    limpiarErroresValidacion();
}

function limpiarErroresValidacion() {
    document.querySelectorAll('#formInforme .form-control, #formInforme .form-select, #fProcesos, #dragArea, #rango_fechas_auditoria')
        .forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('#formInforme .invalid-feedback').forEach(el => el.style.display = 'none');
}

function validarCamposRequeridos() {
    limpiarErroresValidacion();
    let invalidos = [], primero = null;

    const check = (id, fbId) => {
        const el  = document.getElementById(id);
        const val = el.tagName === 'SELECT' ? el.value : el.value.trim();
        if (!val) {
            el.classList.add('is-invalid');
            document.getElementById(fbId).style.display = 'block';
            invalidos.push(el);
            if (!primero) primero = el;
        }
    };

    check('fNombre',       'fNombre-feedback');
    check('fTipo',         'fTipo-feedback');
    check('fAuditor',      'fAuditor-feedback');
    check('fFechaInforme', 'fFechaInforme-feedback');

    if (!$('#fecha_inicio').val() || !$('#fecha_fin').val()) {
        $('#rango_fechas_auditoria').addClass('is-invalid');
        $('#rango-fechas-feedback').show();
        invalidos.push(document.getElementById('rango_fechas_auditoria'));
        if (!primero) primero = document.getElementById('rango_fechas_auditoria');
    }

    if (document.querySelectorAll('.proceso-check:checked').length === 0) {
        document.getElementById('fProcesos').classList.add('is-invalid');
        document.getElementById('fProcesos-feedback').style.display = 'block';
        invalidos.push(document.getElementById('fProcesos'));
        if (!primero) primero = document.getElementById('fProcesos');
    }

    const esCreacion = document.getElementById('formMethod').value === 'POST';
    const fd = document.getElementById('fDocumento');
    if (esCreacion && (!fd.files || fd.files.length === 0)) {
        document.getElementById('dragArea').classList.add('is-invalid');
        document.getElementById('fDocumento-feedback').style.display = 'block';
        invalidos.push(document.getElementById('dragArea'));
        if (!primero) primero = document.getElementById('dragArea');
    }

    return { valido: invalidos.length === 0, primerCampoInvalido: primero };
}

// ── Tabla dinámica NC/OM por proceso ─────────────────────────────────────────

/**
 * Reconstruye la tabla de NC/OM cada vez que cambia la selección de procesos.
 * Preserva los valores ya ingresados en filas existentes.
 */
function reconstruirTablaNcOm() {
    const procesosSeleccionados = Array.from(document.querySelectorAll('.proceso-check:checked'))
        .map(cb => cb.value);

    const tabla = document.getElementById('tablaNcOmPorProceso');
    const tbody = document.getElementById('cuerpoTablaNcOm');

    if (procesosSeleccionados.length === 0) {
        tabla.style.display = 'none';
        tbody.innerHTML = '';
        actualizarTotalesNcOm();
        return;
    }

    tabla.style.display = 'block';

    // Guardar valores actuales antes de reconstruir
    const valoresActuales = {};
    tbody.querySelectorAll('tr').forEach(tr => {
        const proc = tr.dataset.proceso;
        if (proc) {
            valoresActuales[proc] = {
                nc: tr.querySelector('.input-nc')?.value ?? '0',
                om: tr.querySelector('.input-om')?.value ?? '0',
            };
        }
    });

    tbody.innerHTML = '';
    procesosSeleccionados.forEach((proc, idx) => {
        const nc = valoresActuales[proc]?.nc ?? '0';
        const om = valoresActuales[proc]?.om ?? '0';
        const bg = idx % 2 === 1 ? 'background:#fdf0f1;' : '';
        const tr = document.createElement('tr');
        tr.dataset.proceso = proc;
        tr.style.cssText = bg;
        tr.innerHTML = `
            <td style="padding:6px 12px; border-bottom:1px solid #dee2e6; vertical-align:middle;">
                <i class="bi bi-diagram-3 me-1" style="color:#800000; font-size:0.8rem;"></i>
                ${_escHtml(proc)}
            </td>
            <td style="padding:6px 12px; border-bottom:1px solid #dee2e6; text-align:center;">
                <input type="number"
                       class="form-control form-control-sm input-nc"
                       name="nc_por_proceso[${_escAttr(proc)}]"
                       min="0" value="${_escAttr(nc)}"
                       style="width:80px; margin:0 auto; text-align:center;"
                       oninput="actualizarTotalesNcOm()">
            </td>
            <td style="padding:6px 12px; border-bottom:1px solid #dee2e6; text-align:center;">
                <input type="number"
                       class="form-control form-control-sm input-om"
                       name="om_por_proceso[${_escAttr(proc)}]"
                       min="0" value="${_escAttr(om)}"
                       style="width:80px; margin:0 auto; text-align:center;"
                       oninput="actualizarTotalesNcOm()">
            </td>`;
        tbody.appendChild(tr);
    });

    actualizarTotalesNcOm();
}

function actualizarTotalesNcOm() {
    let totalNc = 0, totalOm = 0;
    document.querySelectorAll('#cuerpoTablaNcOm .input-nc').forEach(i => { totalNc += Math.max(0, parseInt(i.value) || 0); });
    document.querySelectorAll('#cuerpoTablaNcOm .input-om').forEach(i => { totalOm += Math.max(0, parseInt(i.value) || 0); });
    document.getElementById('totalNcDisplay').textContent = totalNc;
    document.getElementById('totalOmDisplay').textContent = totalOm;
    // Sincronizar campos ocultos (para compatibilidad con servidor)
    document.getElementById('fNoConf').value = totalNc;
    document.getElementById('fOport').value  = totalOm;
}

function _escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function _escAttr(str) {
    return String(str).replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── Guardar informe ───────────────────────────────────────────────────────────
document.getElementById('btnNuevoInforme').addEventListener('click', () => {
    resetForm();
    document.getElementById('tituloModalInforme').textContent = 'Subir Informe';
    document.getElementById('docRequerido').textContent = '*';
    document.getElementById('fDocumento').required = true;
    const el = document.getElementById('modalInforme');
    const ex = bootstrap.Modal.getInstance(el);
    if (ex) ex.dispose();
    const mi = new bootstrap.Modal(el, { backdrop: true, keyboard: true });
    mi.show();
    el.addEventListener('shown.bs.modal', function h() {
        const f = document.getElementById('fNombre');
        if (f) f.focus();
        el.removeEventListener('shown.bs.modal', h);
    });
});

function cerrarModalInforme() {
    const el = document.getElementById('modalInforme');
    const mi = bootstrap.Modal.getInstance(el);
    if (mi) {
        mi.hide();
        el.addEventListener('hidden.bs.modal', function h() {
            resetForm();
            const i = bootstrap.Modal.getInstance(el);
            if (i) i.dispose();
            el.removeEventListener('hidden.bs.modal', h);
        });
    }
}

async function guardarInforme() {
    const v = validarCamposRequeridos();
    if (!v.valido) {
        if (v.primerCampoInvalido) {
            v.primerCampoInvalido.classList.add('campo-invalido-shake');
            setTimeout(() => v.primerCampoInvalido.classList.remove('campo-invalido-shake'), 500);
            v.primerCampoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    const formData = new FormData(document.getElementById('formInforme'));
    const id  = document.getElementById('informeId').value;
    const url = id ? ROUTES.update(id) : ROUTES.store;
    if (id) formData.set('_method', 'PUT');
    try {
        const res  = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: formData });
        const data = await res.json();
        if (data.success) {
            const el = document.getElementById('modalInforme');
            const mi = bootstrap.Modal.getInstance(el);
            if (mi) {
                mi.hide();
                el.addEventListener('hidden.bs.modal', function h() {
                    const i = bootstrap.Modal.getInstance(el);
                    if (i) i.dispose();
                    resetForm();
                    el.removeEventListener('hidden.bs.modal', h);
                    mostrarMensajeExito(data.message || "Informe guardado correctamente");
                    setTimeout(() => location.reload(), 1500);
                });
            }
        } else {
            mostrarErrores(data.errors ?? { message: data.message || 'Error al guardar el informe' });
        }
    } catch (e) {
        console.error('Error:', e);
        mostrarErrores({ error: ['Error inesperado. Inténtalo de nuevo.'] });
    }
}

function mostrarErrores(errors) {
    const div = document.getElementById('erroresForm');
    if (!div) { let m = []; for (const k in errors) { const v = Array.isArray(errors[k]) ? errors[k] : [errors[k]]; m = m.concat(v); } alert('Errores:\n' + m.join('\n')); return; }
    let html = '<ul class="mb-0">';
    for (const k in errors) { const m = Array.isArray(errors[k]) ? errors[k] : [errors[k]]; m.forEach(msg => { html += `<li>${msg}</li>`; }); }
    html += '</ul>';
    div.innerHTML = html;
    div.classList.remove('d-none');
    div.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function mostrarMensajeExito(mensaje) {
    document.querySelectorAll(".alert-exito").forEach(a => a.remove());
    const a = document.createElement("div");
    a.className = "alert-exito alert-dismissible fade show";
    a.setAttribute("role", "alert");
    a.innerHTML = `<i class="bi bi-check-circle"></i> ${mensaje}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
    const c = document.querySelector(".container-fluid");
    if (c) c.insertBefore(a, c.firstChild);
    setTimeout(() => { if (a && a.parentNode) a.remove(); }, 5000);
}

// ── Editar informe ────────────────────────────────────────────────────────────
async function editarInforme(id) {
    try {
        resetForm();
        document.getElementById('tituloModalInforme').textContent = 'Editar Informe';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('informeId').value  = id;
        document.getElementById('docRequerido').textContent = '';
        document.getElementById('fDocumento').required = false;

        const res  = await fetch(ROUTES.show(id), { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
        const data = await res.json();
        const inf  = data.informe;

        document.getElementById('fNombre').value       = inf.nombre_informe;
        document.getElementById('fTipo').value         = inf.tipo_auditoria;
        document.getElementById('fAuditor').value      = inf.auditor_lider;
        document.getElementById('fFechaInforme').value = inf.fecha_informe;

        if (inf.fecha_inicio && inf.fecha_fin) {
            // Tiene rango guardado → mostrarlo
            const fmtI = inf.fecha_inicio.split('-').reverse().join('/');
            const fmtF = inf.fecha_fin.split('-').reverse().join('/');
            document.getElementById('rango_fechas_auditoria').value = fmtI + ' - ' + fmtF;
            document.getElementById('fecha_inicio').value = inf.fecha_inicio;
            document.getElementById('fecha_fin').value    = inf.fecha_fin;
        } else if (inf.fecha_auditoria) {
            // Registro antiguo sin rango → mostrar fecha_auditoria como referencia
            const fmtA = inf.fecha_auditoria.split('-').reverse().join('/');
            document.getElementById('rango_fechas_auditoria').value = fmtA;
            document.getElementById('fecha_inicio').value = inf.fecha_auditoria;
            document.getElementById('fecha_fin').value    = inf.fecha_auditoria;
        }

        document.getElementById('fAuditoriaRel').value = inf.auditoria_relacionada_id ?? '';
        // Los totales se muestran en la tabla, los campos ocultos se actualizan después
        document.getElementById('fNoConf').value = inf.no_conformidades;
        document.getElementById('fOport').value  = inf.oportunidades_mejora;

        const procSel = inf.procesos_auditados ?? [];
        document.querySelectorAll('.proceso-check').forEach(cb => { cb.checked = procSel.includes(cb.value); });
        procSel.forEach(proc => {
            const existe = Array.from(document.querySelectorAll('.proceso-check')).some(cb => cb.value === proc);
            if (!existe) {
                const id_cb = 'proc_new_' + Date.now() + '_' + Math.random().toString(36).substr(2,4);
                const div = document.createElement('div');
                div.className = 'form-check d-flex align-items-center gap-1';
                div.innerHTML = `<input class="form-check-input proceso-check" type="checkbox" name="procesos_auditados[]" value="${proc}" id="${id_cb}" checked><label class="form-check-label flex-grow-1" for="${id_cb}">${proc}</label><button type="button" class="btn-eliminar-proceso" onclick="this.closest('.form-check').remove(); reconstruirTablaNcOm();" title="Eliminar proceso"><i class="bi bi-x"></i></button>`;
                document.getElementById('fProcesos').appendChild(div);
                // Registrar listener en el nuevo checkbox
                div.querySelector('input').addEventListener('change', () => { reconstruirTablaNcOm(); });
            }
        });

        // ── Construir tabla NC/OM con valores guardados ──
        reconstruirTablaNcOm();

        // Cargar valores guardados en la tabla
        const ncOmGuardado = inf.nc_om_por_proceso ?? [];
        ncOmGuardado.forEach(item => {
            const tr = document.querySelector(`#cuerpoTablaNcOm tr[data-proceso="${CSS.escape(item.proceso)}"]`);
            if (tr) {
                const inputNc = tr.querySelector('.input-nc');
                const inputOm = tr.querySelector('.input-om');
                if (inputNc) inputNc.value = item.nc ?? 0;
                if (inputOm) inputOm.value = item.om ?? 0;
            }
        });
        actualizarTotalesNcOm();

        if (inf.documento_nombre) document.getElementById('docLabel').textContent = inf.documento_nombre + ' (archivo actual)';

        const el = document.getElementById('modalInforme');
        const ex = bootstrap.Modal.getInstance(el);
        if (ex) ex.dispose();
        new bootstrap.Modal(el, { backdrop: true, keyboard: true }).show();
    } catch (e) {
        console.error('Error al cargar el informe:', e);
        mostrarErrores({ error: ['Error al cargar los datos del informe'] });
    }
}

function eliminarInforme(id, nombre) {
    Swal.fire({
        title: '¿Eliminar archivo?',
        text: `¿Estás seguro de eliminar "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res  = await fetch(ROUTES.destroy(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.success) {
                    mostrarMensajeExito("Informe eliminado correctamente");
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (e) { console.error('Error al eliminar:', e); alert('Error al eliminar el informe'); }
        }
    });
}

async function verDocumento(id, nombre) {
    document.getElementById('tituloDocumento').textContent = nombre;
    document.getElementById('iframeDoc').src = 'about:blank';
    document.getElementById('btnDescargarDocumento').href = ROUTES.descargar(id);
    new bootstrap.Modal(document.getElementById('modalDocumento')).show();
    const ext = nombre.split('.').pop().toLowerCase();
    if (ext === 'pdf') {
        document.getElementById('iframeDoc').src = ROUTES.documento(id) + '?t=' + Date.now();
    } else {
        const tipos = { docx:'Word', doc:'Word', xlsx:'Excel', xls:'Excel', pptx:'PowerPoint', ppt:'PowerPoint' };
        const tipo  = tipos[ext] ?? ext.toUpperCase();
        document.getElementById('iframeDoc').srcdoc = `<html><head><style>body{margin:0;display:flex;align-items:center;justify-content:center;height:100vh;background:#2b2b2b;font-family:Arial,sans-serif;color:#fff;text-align:center;}.c{padding:40px;}.i{font-size:4rem;margin-bottom:15px;}.t{font-size:1.3rem;margin-bottom:8px;font-weight:600;}.s{font-size:.9rem;color:#aaa;margin-bottom:25px;}.b{background:#800000;color:#fff;border:none;padding:12px 28px;border-radius:6px;font-size:1rem;cursor:pointer;text-decoration:none;display:inline-block;}.b:hover{background:#600000;}</style></head><body><div class="c"><div class="i">📄</div><div class="t">Vista previa no disponible para archivos ${tipo}</div><div class="s">Descarga el archivo para abrirlo con ${tipo}</div><a class="b" href="${ROUTES.descargar(id)}" download="${nombre}">⬇ Descargar ${nombre}</a></div></body></html>`;
    }
}

document.getElementById('modalDocumento').addEventListener('hidden.bs.modal', () => {
    document.getElementById('iframeDoc').src = 'about:blank';
});

// ── Filtros de la tabla ───────────────────────────────────────────────────────
function obtenerAniosUnicos() {
    const s = new Set();
    document.querySelectorAll('#tablaInformes tbody tr').forEach(f => {
        const a = f.cells[5]?.textContent.trim();
        if (a) s.add(a);
    });
    return Array.from(s).sort();
}

function cargarAniosEnFiltro() {
    const anios = obtenerAniosUnicos();
    const menu  = document.getElementById('menuAnios');
    if (!menu) return;
    while (menu.children.length > 1) menu.removeChild(menu.lastChild);
    anios.forEach(a => {
        const li = document.createElement('li');
        li.innerHTML = `<a class="dropdown-item" href="#" onclick="seleccionarAnio('${a}', 'Año ${a}')">${a}</a>`;
        menu.appendChild(li);
    });
}

function getUrlParameter(name) { return new URLSearchParams(window.location.search).get(name); }

function inicializarFiltrosDesdeURL() {
    const ap = getUrlParameter('anio');
    const tp = getUrlParameter('tipo');
    const bp = getUrlParameter('buscar');
    const op = getUrlParameter('orden');
    if (ap) { anioSeleccionado = ap; document.getElementById('anioTexto').innerText = `Año ${ap}`; document.getElementById('btnAnio').classList.add('seleccionado'); }
    if (tp) {
        tipoSeleccionado = tp;
        document.getElementById('tipoTexto').innerText = tp;
        document.getElementById('btnTipo').classList.add('seleccionado');
        const oi = document.getElementById('opcionInterna');
        const oe = document.getElementById('opcionExterna');
        if (tp === 'Interna' && oi) oi.classList.add('active');
        else if (tp === 'Externa' && oe) oe.classList.add('active');
    }
    if (bp) document.getElementById('inputBuscar').value = bp;
    if (op) {
        ordenSeleccionado = op;
        const t = { 'nombre-asc':'Nombre (A-Z)', 'nombre-desc':'Nombre (Z-A)', 'fecha-asc':'Fecha (más antiguo)', 'fecha-desc':'Fecha (más reciente)' };
        document.getElementById('ordenarTexto').innerText = t[op] || 'Ordenar por';
        document.getElementById('btnOrdenar').classList.add('seleccionado');
    }
}

// ── Estadísticas ──────────────────────────────────────────────────────────────

/**
 * Recopila datos de la tabla HTML filtrando por año Y tipo.
 * Lee el atributo data-nc-om-por-proceso de cada fila para construir
 * la gráfica por proceso.
 */
function obtenerDatosFiltrados(anio, tipo) {
    let total = 0, nc = 0, om = 0;
    const procesosSet = new Set();
    // mapa: proceso → {nc, om}
    const procesosMap = {};

    document.querySelectorAll('#tablaInformes tbody tr').forEach(fila => {
        const anioFila = fila.cells[5]?.textContent.trim();
        const tipoFila = fila.dataset.tipo ?? '';

        const coincideAnio = anioFila === anio;
        const coincideTipo = (tipo === 'todos') || (tipoFila === tipo);

        if (!coincideAnio || !coincideTipo) return;

        total++;
        nc += parseInt(fila.cells[7]?.textContent.trim()) || 0;
        om += parseInt(fila.cells[8]?.textContent.trim()) || 0;

        // procesos nombres
        fila.cells[6]?.querySelectorAll('.tag-proceso').forEach(s => {
            if (s.textContent.trim()) procesosSet.add(s.textContent.trim());
        });

        // desglose nc/om por proceso
        let ncOmData = [];
        try { ncOmData = JSON.parse(fila.dataset.ncOmPorProceso || '[]'); } catch(e) {}
        ncOmData.forEach(item => {
            if (!item.proceso) return;
            if (!procesosMap[item.proceso]) procesosMap[item.proceso] = { nc: 0, om: 0 };
            procesosMap[item.proceso].nc += parseInt(item.nc) || 0;
            procesosMap[item.proceso].om += parseInt(item.om) || 0;
        });
    });

    return {
        totalAuditorias : total,
        totalNC         : nc,
        totalOM         : om,
        procesos        : Array.from(procesosSet),
        procesosMap     : procesosMap,
    };
}

function actualizarTarjetas(t, nc, om) {
    document.getElementById('statTotal').textContent = t;
    document.getElementById('statNC').textContent    = nc;
    document.getElementById('statOM').textContent    = om;
}


/**
 * Gráfica de barras agrupadas NC y OM por proceso.
 */
function actualizarGraficaPorProceso(procesosMap) {
    const ctx    = document.getElementById('chartPorProceso');
    const msgEl  = document.getElementById('sinDatosProcesoMsg');
    const wrpEl  = document.getElementById('wrapperChartPorProceso');

    if (!ctx) return;
    if (chartPorProceso) { chartPorProceso.destroy(); chartPorProceso = null; }

    const procesos = Object.keys(procesosMap);

    if (procesos.length === 0) {
        msgEl.style.display = 'block';
        wrpEl.style.display = 'none';
        return;
    }

    msgEl.style.display = 'none';
    wrpEl.style.display = 'block';

    const dataNc = procesos.map(p => procesosMap[p].nc);
    const dataOm = procesos.map(p => procesosMap[p].om);

    // Altura dinámica: mínimo 300, +25px por proceso adicional
    ctx.height = Math.max(300, 200 + procesos.length * 25);

    chartPorProceso = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: procesos,
            datasets: [
                {
                    label: 'No Conformidades',
                    data : dataNc,
                    backgroundColor: 'rgba(220, 53, 69, 0.85)',
                    borderColor    : '#b02a37',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Oport. de Mejora',
                    data : dataOm,
                    backgroundColor: 'rgba(253, 126, 20, 0.85)',
                    borderColor    : '#ca6510',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: { size: 13 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.raw}`
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 35,
                        minRotation: 0,
                        font: { size: 11 }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    title: { display: true, text: 'Cantidad' }
                }
            }
        }
    });
}

function actualizarListaProcesos(procesos) {
    const div = document.getElementById('listaProcesosEstadisticas');
    div.innerHTML = '';
    if (procesos.length > 0) {
        procesos.forEach(p => {
            const s = document.createElement('span');
            s.className   = 'tag-proceso';
            s.textContent = p;
            div.appendChild(s);
        });
    } else {
        div.innerHTML = '<span class="text-muted">No hay procesos para los filtros seleccionados</span>';
    }
}

function refrescarEstadisticas() {
    const anio = document.getElementById('selectAnioEstadisticas').value;
    if (!anio || anio === '0') {
        actualizarTarjetas(0, 0, 0);
        actualizarGraficaPorProceso({});
        actualizarListaProcesos([]);
        return;
    }
    const d = obtenerDatosFiltrados(anio, tipoEstadisticas);
    actualizarTarjetas(d.totalAuditorias, d.totalNC, d.totalOM);
    actualizarGraficaPorProceso(d.procesosMap);
    actualizarListaProcesos(d.procesos);
}

// ── Filtro tipo dentro del modal de estadísticas ──────────────────────────────
function seleccionarTipoEstadisticas(tipo) {
    tipoEstadisticas = tipo;
    // Actualizar botones
    document.getElementById('btnTipoTodos').className    = 'btn-tipo' + (tipo === 'todos'    ? ' activo-todos'    : '');
    document.getElementById('btnTipoInterna').className  = 'btn-tipo' + (tipo === 'Interna'  ? ' activo-interna'  : '');
    document.getElementById('btnTipoExterna').className  = 'btn-tipo' + (tipo === 'Externa'  ? ' activo-externa'  : '');
    refrescarEstadisticas();
}

// ── Abrir modal estadísticas ──────────────────────────────────────────────────
document.getElementById('btnEstadisticas').addEventListener('click', () => {
    const anios = obtenerAniosUnicos();
    tipoEstadisticas = 'todos';
    seleccionarTipoEstadisticas('todos'); // resetear botones

    new bootstrap.Modal(document.getElementById('modalEstadisticas')).show();

    const sel = document.getElementById('selectAnioEstadisticas');
    sel.innerHTML = '';
    if (anios.length === 0) {
        sel.innerHTML = '<option value="0">Sin años disponibles</option>';
        actualizarTarjetas(0, 0, 0);
        actualizarGraficaPorProceso({});
        actualizarListaProcesos([]);
    } else {
        anios.forEach(a => { const o = document.createElement('option'); o.value = a; o.textContent = a; sel.appendChild(o); });
        sel.value = anios[0];
        refrescarEstadisticas();
    }
});

document.getElementById('selectAnioEstadisticas').addEventListener('change', () => {
    refrescarEstadisticas();
});

// Destruir gráficas al cerrar modal
document.getElementById('modalEstadisticas').addEventListener('hidden.bs.modal', () => {
    if (chartPorProceso)   { chartPorProceso.destroy();  chartPorProceso   = null; }
});

// ── Agregar nuevo proceso ─────────────────────────────────────────────────────
function agregarNuevoProceso() {
    const input  = document.getElementById('nuevoProceso');
    const nombre = input.value.trim();
    if (!nombre) return;
    const existe = Array.from(document.querySelectorAll('.proceso-check')).some(cb => cb.value.toLowerCase() === nombre.toLowerCase());
    if (existe) { input.classList.add('is-invalid'); setTimeout(() => input.classList.remove('is-invalid'), 2000); return; }
    const id_cb = 'proc_new_' + Date.now();
    const div = document.createElement('div');
    div.className = 'form-check d-flex align-items-center gap-1';
    div.innerHTML = `<input class="form-check-input proceso-check" type="checkbox" name="procesos_auditados[]" value="${nombre}" id="${id_cb}" checked>
                     <label class="form-check-label flex-grow-1" for="${id_cb}">${nombre}</label>
                     <button type="button" class="btn-eliminar-proceso" onclick="this.closest('.form-check').remove(); reconstruirTablaNcOm();" title="Eliminar proceso"><i class="bi bi-x"></i></button>`;
    document.getElementById('fProcesos').appendChild(div);
    // Registrar listener en el nuevo checkbox
    div.querySelector('input').addEventListener('change', () => { reconstruirTablaNcOm(); });
    input.value = '';
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    input.focus();
    document.getElementById('fProcesos').classList.remove('is-invalid');
    document.getElementById('fProcesos-feedback').style.display = 'none';
    reconstruirTablaNcOm();
}

// ── Inicialización ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    inicializarDateRangePicker();
    cargarAniosEnFiltro();
    inicializarFiltrosDesdeURL();

    const b = document.getElementById('inputBuscar');
    if (b) b.addEventListener('keyup', () => aplicarFiltros());

    const mi = document.getElementById('modalInforme');
    if (mi) {
        mi.addEventListener('hidden.bs.modal', () => resetForm());
        mi.addEventListener('hide.bs.modal', () => { const f = document.getElementById('fDocumento'); if (f) f.value = ''; });
    }

    const mes = document.getElementById('modalEstadisticas');
    if (mes) mes.addEventListener('hidden.bs.modal', () => {
            if (chartPorProceso)   { chartPorProceso.destroy();  chartPorProceso   = null; }
    });

    document.querySelectorAll('#formInforme .form-control, #formInforme .form-select').forEach(c => {
        const clear = function() { this.classList.remove('is-invalid'); const el = document.getElementById(this.id + '-feedback'); if (el) el.style.display = 'none'; };
        c.addEventListener('input', clear);
        c.addEventListener('change', clear);
    });

    // ── Listener checkboxes de procesos → reconstruir tabla NC/OM ──
    document.querySelectorAll('.proceso-check').forEach(cb => {
        cb.addEventListener('change', () => {
            if (document.querySelectorAll('.proceso-check:checked').length > 0) {
                document.getElementById('fProcesos').classList.remove('is-invalid');
                document.getElementById('fProcesos-feedback').style.display = 'none';
            }
            reconstruirTablaNcOm();
        });
    });

    document.getElementById('fDocumento').addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            document.getElementById('dragArea').classList.remove('is-invalid');
            document.getElementById('fDocumento-feedback').style.display = 'none';
        }
    });

    // ── Autocomplete nuevoProceso ──────────────────────────────────
    const _npInput = document.getElementById('nuevoProceso');
    if (_npInput) {
        _npInput.addEventListener('input', function() { _mostrarDropdown(this.value.trim()); });
        _npInput.addEventListener('blur',  function() { setTimeout(() => { document.getElementById('autocomplete-procesos').style.display = 'none'; _procIdx = -1; }, 150); });
        _npInput.addEventListener('keydown', function(e) {
            const dd    = document.getElementById('autocomplete-procesos');
            const items = dd.querySelectorAll('.ac-item');
            if (e.key === 'ArrowDown') { e.preventDefault(); _procIdx = Math.min(_procIdx + 1, items.length - 1); items.forEach((i, x) => i.classList.toggle('ac-active', x === _procIdx)); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); _procIdx = Math.max(_procIdx - 1, 0); items.forEach((i, x) => i.classList.toggle('ac-active', x === _procIdx)); }
            else if (e.key === 'Enter' && _procIdx >= 0 && items[_procIdx]) { e.preventDefault(); _seleccionarProceso(items[_procIdx].dataset.val || this.value.trim()); }
            else if (e.key === 'Escape') { dd.style.display = 'none'; _procIdx = -1; }
        });
    }

    // ── Drag & Drop ──────────────────────────────────────────────
    const dragArea = document.getElementById('dragArea');
    if (dragArea) {
        ['dragenter', 'dragover'].forEach(evt => {
            dragArea.addEventListener(evt, function(e) {
                e.preventDefault(); e.stopPropagation();
                dragArea.style.backgroundColor = '#fff0f0';
                dragArea.style.borderColor     = '#600000';
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dragArea.addEventListener(evt, function(e) {
                e.preventDefault(); e.stopPropagation();
                dragArea.style.backgroundColor = '';
                dragArea.style.borderColor     = '';
            });
        });
        dragArea.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (!files || files.length === 0) return;
            const fDocumento = document.getElementById('fDocumento');
            const docLabel   = document.getElementById('docLabel');
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            fDocumento.files = dt.files;
            if (docLabel) docLabel.textContent = files[0].name;
            dragArea.classList.remove('is-invalid');
            const fb = document.getElementById('fDocumento-feedback');
            if (fb) fb.style.display = 'none';
        });
    }
});

// ── Autocomplete procesos ─────────────────────────────────────────────────────
let _procCache = null;
let _procIdx   = -1;

async function _fetchProcesos() {
    if (_procCache !== null) return _procCache;
    try {
        const r = await fetch(ROUTES.procesosCustom, { headers: { 'Accept': 'application/json' } });
        _procCache = await r.json();
    } catch (e) { _procCache = []; }
    return _procCache;
}

async function _mostrarDropdown(query) {
    const dd = document.getElementById('autocomplete-procesos');
    if (!dd || !query) { if (dd) dd.style.display = 'none'; return; }
    const todos      = await _fetchProcesos();
    const yaEnLista  = Array.from(document.querySelectorAll('.proceso-check')).map(c => c.value.toLowerCase());
    const filtrados  = todos.filter(p => p.toLowerCase().includes(query.toLowerCase()) && !yaEnLista.includes(p.toLowerCase()));
    dd.innerHTML = ''; _procIdx = -1;
    filtrados.forEach(p => {
        const d = document.createElement('div'); d.className = 'ac-item';
        d.innerHTML = `<i class="bi bi-diagram-3"></i> ${p}`; d.dataset.val = p;
        d.addEventListener('mousedown', e => { e.preventDefault(); _seleccionarProceso(p); });
        dd.appendChild(d);
    });
    const exactoEnLista    = yaEnLista.includes(query.toLowerCase());
    const exactoEnFiltrados = filtrados.some(p => p.toLowerCase() === query.toLowerCase());
    if (!exactoEnLista && !exactoEnFiltrados) {
        const d = document.createElement('div'); d.className = 'ac-item'; d.style.color = '#800000';
        d.innerHTML = `<i class="bi bi-plus-circle"></i> Agregar "<strong>${query}</strong>"`;
        d.addEventListener('mousedown', e => { e.preventDefault(); _seleccionarProceso(query); });
        dd.appendChild(d);
    }
    dd.style.display = dd.children.length ? 'block' : 'none';
}

function _seleccionarProceso(nombre) {
    document.getElementById('nuevoProceso').value = nombre;
    document.getElementById('autocomplete-procesos').style.display = 'none';
    agregarNuevoProceso();
}
</script>
@endpush