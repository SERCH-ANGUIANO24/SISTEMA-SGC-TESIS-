@extends('layouts.app')

@section('title', 'Informes de Auditoría')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
    .stat-green { background: #198754; }

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
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- ── ENCABEZADO - igual que Plan de Auditorías ── --}}
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

    {{-- ── FILTROS - igual que Plan de Auditorías ── --}}
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
                            <th>Fecha Auditoría</th>
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
                        <tr class="align-middle">
                            <td class="fw-bold">{{ $inf->nombre_informe }}</td>
                            <td>
                                <span class="badge-{{ strtolower($inf->tipo_auditoria) }}">{{ $inf->tipo_auditoria }}</span>
                            </td>
                            <td>{{ $inf->auditor_lider }}</td>
                            <td>{{ $inf->fecha_informe->format('d/m/Y') }}</td>
                            <td>{{ $inf->fecha_auditoria->format('d/m/Y') }}</td>
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
                                    {{-- Ver documento (solo PDF) --}}
                                    @if($inf->documento_path && strtolower(pathinfo($inf->documento_nombre, PATHINFO_EXTENSION)) === 'pdf')
                                    <button type="button" class="btn btn-sm btn-outline-info" title="Ver Documento"
                                        onclick="verDocumento({{ $inf->id }}, '{{ addslashes($inf->documento_nombre) }}')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @endif
                                    {{-- Descargar documento --}}
                                    @if($inf->documento_path)
                                    <a href="{{ url('auditorias/informes') }}/{{ $inf->id }}/descargar"
                                       class="btn btn-sm btn-outline-primary" title="Descargar Documento">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif
                                    {{-- Editar --}}
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                        onclick="editarInforme({{ $inf->id }})">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    {{-- Eliminar --}}
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
                  <option value="{{ $plan->id }}" data-fecha="{{ $plan->fecha_auditoria->format('Y-m-d') }}">
                    {{ $plan->nombre_auditoria }}
                  </option>
                @endforeach
              </select>
            </div>
            {{-- Fecha del Informe --}}
            <div class="col-md-6">
              <label class="form-label">Fecha del Informe <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_informe" id="fFechaInforme" required>
              <div class="invalid-feedback" id="fFechaInforme-feedback">La fecha del informe es requerida</div>
            </div>
            {{-- Fecha de Auditoría --}}
            <div class="col-md-6">
              <label class="form-label">Fecha de Auditoría <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_auditoria" id="fFechaAuditoria" required>
              <small class="text-muted">Se llenará automáticamente al seleccionar auditoría relacionada.</small>
              <div class="invalid-feedback" id="fFechaAuditoria-feedback">La fecha de auditoría es requerida</div>
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
                    <div class="input-group mt-2">
                        <input type="text" id="nuevoProceso" class="form-control form-control-sm"
                            placeholder="Agregar nuevo proceso..." maxlength="80"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();agregarNuevoProceso();}">
                        <button type="button" class="btn btn-sm text-white" style="background:#800000;"
                                onclick="agregarNuevoProceso()">
                        <i class="bi bi-plus-lg"></i> Agregar
                        </button>
                    </div>
              </div>
              <div class="invalid-feedback" id="fProcesos-feedback">Debe seleccionar al menos un proceso</div>
            </div>
            {{-- No Conformidades --}}
            <div class="col-md-6">
              <label class="form-label">No Conformidades <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="no_conformidades" id="fNoConf" min="0" placeholder="Ingresa una cantidad" required>
              <div class="invalid-feedback" id="fNoConf-feedback">El número de no conformidades es requerido</div>
            </div>
            {{-- Oportunidades de Mejora --}}
            <div class="col-md-6">
              <label class="form-label">Oportunidades de Mejora <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="oportunidades_mejora" id="fOport" min="0" placeholder="Ingresa una cantidad" required>
              <div class="invalid-feedback" id="fOport-feedback">El número de oportunidades de mejora es requerido</div>
            </div>
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
     MODAL: ESTADÍSTICAS POR AÑO
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEstadisticas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-cyan">
        <h5 class="modal-title"><i class="bi bi-bar-chart-line me-2"></i>Estadísticas por Año</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3 align-items-center">
          <div class="col-auto">
            <label class="form-label fw-bold mb-0">Seleccionar Año</label>
          </div>
          <div class="col-auto">
            <select id="selectAnioEstadisticas" class="form-select" style="width:150px;">
              <option value="">Cargando años...</option>
            </select>
          </div>
        </div>

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

        <div class="grafica-anual-container">
          <h6 class="fw-bold text-muted text-center mb-3">Resumen Anual: Total de No Conformidades y Oportunidades de Mejora</h6>
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <canvas id="chartResumenAnual" height="250"></canvas>
            </div>
          </div>
        </div>

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


{{-- ══════════════════════════════════════════════════════════════
     MODAL: CONFIRMAR ELIMINACIÓN — manejado por SweetAlert2
══════════════════════════════════════════════════════════════ --}}

@endsection


@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

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
    descargar   : (id) => `{{ url('auditorias/informes') }}/${id}/descargar`,
};

const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let chartResumenAnual = null;
let informeIdEliminar = null;
let tipoSeleccionado  = '';
let anioSeleccionado  = '';
let ordenSeleccionado = '';

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
    if (buscar)           params.set('buscar', buscar);
    if (anioSeleccionado) params.set('anio', anioSeleccionado);
    if (tipoSeleccionado) params.set('tipo', tipoSeleccionado);
    if (ordenSeleccionado) params.set('orden', ordenSeleccionado);
    window.location.href = ROUTES.index + (params.toString() ? '?' + params.toString() : '');
}

document.getElementById('fAuditoriaRel').addEventListener('change', function () {
    const fecha = this.options[this.selectedIndex].dataset.fecha;
    if (fecha) {
        document.getElementById('fFechaAuditoria').value = fecha;
        document.getElementById('fFechaAuditoria').classList.remove('is-invalid');
    }
});

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
    const fa = document.getElementById('fFechaAuditoria');
    if (fa) fa.value = '';
    limpiarErroresValidacion();
}

function limpiarErroresValidacion() {
    document.querySelectorAll('#formInforme .form-control, #formInforme .form-select, #fProcesos, #dragArea')
        .forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('#formInforme .invalid-feedback').forEach(el => el.style.display = 'none');
}

function validarCamposRequeridos() {
    limpiarErroresValidacion();
    let invalidos = [], primero = null;

    const check = (id, fbId) => {
        const el = document.getElementById(id);
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
    check('fFechaAuditoria','fFechaAuditoria-feedback');

    if (document.querySelectorAll('.proceso-check:checked').length === 0) {
        document.getElementById('fProcesos').classList.add('is-invalid');
        document.getElementById('fProcesos-feedback').style.display = 'block';
        invalidos.push(document.getElementById('fProcesos'));
        if (!primero) primero = document.getElementById('fProcesos');
    }

    const nc = document.getElementById('fNoConf');
    if (!nc.value && nc.value !== '0') {
        nc.classList.add('is-invalid');
        document.getElementById('fNoConf-feedback').style.display = 'block';
        invalidos.push(nc); if (!primero) primero = nc;
    } else if (nc.value < 0) {
        nc.classList.add('is-invalid');
        document.getElementById('fNoConf-feedback').textContent = 'El valor no puede ser negativo';
        document.getElementById('fNoConf-feedback').style.display = 'block';
        invalidos.push(nc); if (!primero) primero = nc;
    }

    const op = document.getElementById('fOport');
    if (!op.value && op.value !== '0') {
        op.classList.add('is-invalid');
        document.getElementById('fOport-feedback').style.display = 'block';
        invalidos.push(op); if (!primero) primero = op;
    } else if (op.value < 0) {
        op.classList.add('is-invalid');
        document.getElementById('fOport-feedback').textContent = 'El valor no puede ser negativo';
        document.getElementById('fOport-feedback').style.display = 'block';
        invalidos.push(op); if (!primero) primero = op;
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
    const id = document.getElementById('informeId').value;
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
        document.getElementById('fNombre').value          = inf.nombre_informe;
        document.getElementById('fTipo').value            = inf.tipo_auditoria;
        document.getElementById('fAuditor').value         = inf.auditor_lider;
        document.getElementById('fFechaInforme').value    = inf.fecha_informe;
        document.getElementById('fFechaAuditoria').value  = inf.fecha_auditoria;
        document.getElementById('fAuditoriaRel').value    = inf.auditoria_relacionada_id ?? '';
        document.getElementById('fNoConf').value          = inf.no_conformidades;
        document.getElementById('fOport').value           = inf.oportunidades_mejora;
        const procSel = inf.procesos_auditados ?? [];
        document.querySelectorAll('.proceso-check').forEach(cb => { cb.checked = procSel.includes(cb.value); });
        procSel.forEach(proc => {
            const existe = Array.from(document.querySelectorAll('.proceso-check')).some(cb => cb.value === proc);
            if (!existe) {
                const id_cb = 'proc_new_' + Date.now() + '_' + Math.random().toString(36).substr(2,4);
                const div = document.createElement('div');
                div.className = 'form-check d-flex align-items-center gap-1';
                div.innerHTML = `<input class="form-check-input proceso-check" type="checkbox" name="procesos_auditados[]" value="${proc}" id="${id_cb}" checked><label class="form-check-label flex-grow-1" for="${id_cb}">${proc}</label><button type="button" class="btn-eliminar-proceso" onclick="this.closest('.form-check').remove()" title="Eliminar proceso"><i class="bi bi-x"></i></button>`;
                document.getElementById('fProcesos').appendChild(div);
            }
        });
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

function descargarDocumento(id) { window.location.href = ROUTES.descargar(id); }

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

// ── Estadísticas ──────────────────────────────────────────────────────────────
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

function obtenerDatosPorAnio(anio) {
    let total = 0, nc = 0, om = 0;
    const ps = new Set();
    document.querySelectorAll('#tablaInformes tbody tr').forEach(f => {
        if (f.cells[5]?.textContent.trim() === anio) {
            total++;
            nc += parseInt(f.cells[7]?.textContent.trim()) || 0;
            om += parseInt(f.cells[8]?.textContent.trim()) || 0;
            f.cells[6]?.querySelectorAll('.tag-proceso').forEach(s => { if (s.textContent.trim()) ps.add(s.textContent.trim()); });
        }
    });
    return { totalAuditorias: total, totalNC: nc, totalOM: om, procesos: Array.from(ps) };
}

function actualizarTarjetas(t, nc, om) {
    document.getElementById('statTotal').textContent = t;
    document.getElementById('statNC').textContent    = nc;
    document.getElementById('statOM').textContent    = om;
}

function actualizarGraficaResumenAnual(nc, om) {
    const ctx = document.getElementById('chartResumenAnual');
    if (!ctx) return;
    if (chartResumenAnual) chartResumenAnual.destroy();
    chartResumenAnual = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: { labels: ['No Conformidades', 'Oportunidades de Mejora'], datasets: [{ label: 'Totales del Año', data: [nc, om], backgroundColor: ['#dc3545','#fd7e14'], borderColor: ['#b02a37','#ca6510'], borderWidth: 1, borderRadius: 5, barPercentage: 0.6 }] },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.raw + ' en total' } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Cantidad Total' } } } }
    });
}

function cargarEstadisticasAnuales(anio) {
    if (!anio || anio === '0') { actualizarTarjetas(0,0,0); actualizarGraficaResumenAnual(0,0); document.getElementById('listaProcesosEstadisticas').innerHTML = '<span class="text-muted">Seleccione un año válido</span>'; return; }
    const d = obtenerDatosPorAnio(anio);
    actualizarTarjetas(d.totalAuditorias, d.totalNC, d.totalOM);
    actualizarGraficaResumenAnual(d.totalNC, d.totalOM);
    const div = document.getElementById('listaProcesosEstadisticas');
    div.innerHTML = '';
    if (d.procesos.length > 0) { d.procesos.forEach(p => { const s = document.createElement('span'); s.className = 'tag-proceso'; s.textContent = p; div.appendChild(s); }); }
    else { div.innerHTML = '<span class="text-muted">No hay procesos para este año</span>'; }
}

document.getElementById('btnEstadisticas').addEventListener('click', () => {
    const anios = obtenerAniosUnicos();
    new bootstrap.Modal(document.getElementById('modalEstadisticas')).show();
    const sel = document.getElementById('selectAnioEstadisticas');
    sel.innerHTML = '';
    if (anios.length === 0) {
        sel.innerHTML = '<option value="0">Sin años disponibles</option>';
        document.getElementById('listaProcesosEstadisticas').innerHTML = '<span class="text-muted">No hay informes registrados</span>';
        actualizarTarjetas(0,0,0); actualizarGraficaResumenAnual(0,0);
    } else {
        anios.forEach(a => { const o = document.createElement('option'); o.value = a; o.textContent = a; sel.appendChild(o); });
        sel.value = anios[0];
        cargarEstadisticasAnuales(anios[0]);
    }
});

document.getElementById('selectAnioEstadisticas').addEventListener('change', e => {
    if (e.target.value && e.target.value !== '0') cargarEstadisticasAnuales(e.target.value);
});

function agregarNuevoProceso() {
    const input = document.getElementById('nuevoProceso');
    const nombre = input.value.trim();
    if (!nombre) return;
    const existe = Array.from(document.querySelectorAll('.proceso-check')).some(cb => cb.value.toLowerCase() === nombre.toLowerCase());
    if (existe) { input.classList.add('is-invalid'); setTimeout(() => input.classList.remove('is-invalid'), 2000); return; }
    const id_cb = 'proc_new_' + Date.now();
    const div = document.createElement('div');
    div.className = 'form-check d-flex align-items-center gap-1';
    div.innerHTML = `<input class="form-check-input proceso-check" type="checkbox" name="procesos_auditados[]" value="${nombre}" id="${id_cb}" checked><label class="form-check-label flex-grow-1" for="${id_cb}">${nombre}</label><button type="button" class="btn-eliminar-proceso" onclick="this.closest('.form-check').remove()" title="Eliminar proceso"><i class="bi bi-x"></i></button>`;
    document.getElementById('fProcesos').appendChild(div);
    input.value = '';
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    input.focus();
    document.getElementById('fProcesos').classList.remove('is-invalid');
    document.getElementById('fProcesos-feedback').style.display = 'none';
}

// ── Inicialización ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
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
    if (mes) mes.addEventListener('hidden.bs.modal', () => { if (chartResumenAnual) { chartResumenAnual.destroy(); chartResumenAnual = null; } });

    document.querySelectorAll('#formInforme .form-control, #formInforme .form-select').forEach(c => {
        const clear = function() { this.classList.remove('is-invalid'); const el = document.getElementById(this.id + '-feedback'); if (el) el.style.display = 'none'; };
        c.addEventListener('input', clear);
        c.addEventListener('change', clear);
    });

    document.querySelectorAll('.proceso-check').forEach(cb => {
        cb.addEventListener('change', () => {
            if (document.querySelectorAll('.proceso-check:checked').length > 0) {
                document.getElementById('fProcesos').classList.remove('is-invalid');
                document.getElementById('fProcesos-feedback').style.display = 'none';
            }
        });
    });

    document.getElementById('fDocumento').addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            document.getElementById('dragArea').classList.remove('is-invalid');
            document.getElementById('fDocumento-feedback').style.display = 'none';
        }
    });

    // ── Drag & Drop ──────────────────────────────────────────────────────────
    const dragArea = document.getElementById('dragArea');
    if (dragArea) {
        ['dragenter', 'dragover'].forEach(evt => {
            dragArea.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dragArea.style.backgroundColor = '#fff0f0';
                dragArea.style.borderColor     = '#600000';
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            dragArea.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dragArea.style.backgroundColor = '';
                dragArea.style.borderColor     = '';
            });
        });

        dragArea.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (!files || files.length === 0) return;

            const fDocumento = document.getElementById('fDocumento');
            const docLabel   = document.getElementById('docLabel');

            // Transferir el archivo al input real
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            fDocumento.files = dt.files;

            // Actualizar el label con el nombre del archivo
            if (docLabel) docLabel.textContent = files[0].name;

            // Limpiar error si lo había
            dragArea.classList.remove('is-invalid');
            const fb = document.getElementById('fDocumento-feedback');
            if (fb) fb.style.display = 'none';
        });
    }
});
</script>
@endpush