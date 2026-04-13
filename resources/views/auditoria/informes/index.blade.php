@extends('layouts.app')

@section('title', 'Informes de Auditoría')

{{-- ══════════════════════════════════════════════════════════════
     ESTILOS CSS DE LA PÁGINA DE INFORMES DE AUDITORÍA
     INCLUYE: TABLA, BADGES, BOTONES, MODALES, GRÁFICAS Y RESPONSIVE
══════════════════════════════════════════════════════════════ --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .table th { background-color: #f8f9fa; color: black; text-align: center; vertical-align: middle; border-left: none !important; border-right: none !important; font-size: 0.9rem; font-weight: 600; padding: 12px; white-space: nowrap; }
    .table td { vertical-align: middle; border-left: none !important; border-right: none !important; font-size: 0.9rem; padding: 10px 12px; }
    .table tbody tr:hover { background-color: #f0fdf1; }
    .badge-interna { background-color: #28a745; color: white; padding: 0.3rem 0.6rem; border-radius: 5px; font-size: 0.8rem; font-weight: 500; }
    .badge-externa { background-color: #dc3545; color: white; padding: 0.3rem 0.6rem; border-radius: 5px; font-size: 0.8rem; font-weight: 500; }
    .btn-accion { margin: 0 2px; }
    .btn-outline-info    { color: #0dcaf0; border-color: #0dcaf0; }
    .btn-outline-info:hover    { background-color: #0dcaf0; color: #fff; }
    .btn-outline-secondary { color: #6c757d; border-color: #6c757d; }
    .btn-outline-secondary:hover { background-color: #6c757d; color: #fff; }
    .btn-outline-primary { color: #0d6efd; border-color: #0d6efd; }
    .btn-outline-primary:hover { background-color: #0d6efd; color: #fff; }
    .btn-outline-danger  { color: #dc3545; border-color: #dc3545; }
    .btn-outline-danger:hover  { background-color: #dc3545; color: #fff; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; border-radius: 0.2rem; }
    .btn-light { background-color: white !important; color: #6c757d; border: 1px solid #ced4da; }
    .btn-light:hover { background-color: #f8f9fa !important; border-color: #800000; }
    .btn-light i { color: #6c757d; }
    .btn-light.seleccionado { background-color: #e9ecef !important; border-color: #737373; color: #495057; }
    .btn-light.seleccionado i { color: #495057; }
    .dropdown-item:hover { background-color: #737373 !important; color: #ffffff !important; }
    .dropdown-item.active { background-color: #737373 !important; color: white !important; }
    #limpiarBusqueda { transition: all 0.2s ease; border-color: #ced4da; background-color: white; width: 42px; height: 42px; border-radius: 0 4px 4px 0; border-left: none; }
    #limpiarBusqueda:hover { background-color: #737373 !important; border-color: #737373 !important; }
    #limpiarBusqueda:hover i { color: white !important; }
    .btn[style*="background-color: #737373"]:hover { background-color: #5a5a5a !important; color: white !important; }
    .table-responsive { border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto; margin-bottom: 15px; }
    .pagination-info { color: #6c757d; font-size: 0.9rem; margin-bottom: 10px; }
    .pagination { display: flex; justify-content: flex-end; gap: 5px; }
    .procesos-container { display: flex; flex-wrap: wrap; gap: 3px; }
    .tag-proceso { background-color: #e9ecef; color: #495057; border-radius: 20px; padding: 2px 9px; font-size: 0.72rem; display: inline-block; margin: 1px; }
    .num-red    { color: #dc3545; font-weight: 700; text-align: center; }
    .num-orange { color: #28a745; font-weight: 700; text-align: center; }
    .modal-header-rojo {
        background-color: #ffffff;
        color: #000000;
    }
    .modal-header-rojo .btn-close {
        filter: none;
        opacity: 0.8;
    }
    .modal-header-cyan { background-color: #0dcaf0; color: #fff; }
    .modal-header-cyan .btn-close, .modal-header-rojo .btn-close { filter: invert(1); }
    .stat-card { border-radius: 10px; color: #fff; text-align: center; padding: 18px 10px; }
    .stat-card .num   { font-size: 2rem; font-weight: 700; }
    .stat-card .label { font-size: 0.85rem; }
    .stat-blue  { background: #0dcaf0; }
    .stat-red   { background: #dc3545; }
    .stat-green { background: #28a745; }
    #iframeDoc { width: 100%; height: 100%; border: none; }
    #modalDocumento .modal-dialog { max-width: 90%; }
    #modalDocumento .modal-body { height: 80vh; overflow: auto; padding: 0; }
    #modalDocumento .modal-body iframe { width: 100%; height: 100%; border: none; }
    .form-label { font-size: 0.85rem; font-weight: 600; color: #495057; }
    #selectAnioEstadisticas { border: 2px solid #0dcaf0; }
    .grafica-anual-container { background-color: #f8f9fa; border-radius: 8px; padding: 15px; margin-top: 20px; }
    .border.rounded.p-4.bg-light, .drag-area { border: 2px dashed #000000 !important; border-radius: 5px; padding: 20px; text-align: center; background: #f8f9fa; cursor: pointer; transition: all 0.3s ease; }
    .drag-area:hover, .border.rounded.p-4.bg-light:hover { background-color: #fff !important; border-color: #000000 !important; }
    .drag-area i { font-size: 3rem; color: #000000; }
    .drag-area p { margin: 5px 0 0; color: #6c757d; }
    .form-control:focus, .form-select:focus { border-color: #737373; box-shadow: 0 0 0 0.3rem #d2e2f9; z-index: 1; }
    .procesos-checklist { background-color: #fff; max-height: 200px; overflow-y: auto; column-count: 2; column-gap: 10px; }
    .procesos-checklist .form-check { break-inside: avoid; padding: 4px 8px; border-radius: 4px; transition: background-color 0.15s; }
    .procesos-checklist .form-check:hover { background-color: #ffffff; }
    .procesos-checklist .form-check-input:checked ~ .form-check-label { color: #000000; font-weight: 600; }
    .procesos-checklist .form-check-input:focus { border-color: #737373; box-shadow: 0 0 0 0.2rem #ffffff; }
    .procesos-checklist .form-check-input:checked { background-color: #737373; border-color: #737373; }
    .alert-exito { background-color: #48b161; color: #ffffff; border-color: #c3e6cb; border-radius: 8px; padding: 12px 20px; margin: 0 auto 20px auto; font-weight: 500; display: flex; align-items: center; position: relative; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); width: 95%; max-width: 1400px; min-width: 300px; }
    .alert-exito i { font-size: 1.5rem; margin-right: 15px; }
    .alert-exito .btn-close { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); filter: invert(1); }
    .btn-eliminar-proceso { background: none; border: none; color: #dc3545; font-size: 1.1rem; padding: 0 2px; line-height: 1; cursor: pointer; flex-shrink: 0; opacity: 0.6; transition: opacity 0.15s; }
    .btn-eliminar-proceso:hover { opacity: 1; }
    .is-invalid { border-color: #dc3545 !important; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right calc(0.375em + 0.1875rem) center; background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem); }
    .invalid-feedback { display: none; width: 100%; margin-top: 0.25rem; font-size: 0.875em; color: #dc3545; }
    .was-validated .form-control:invalid, .form-control.is-invalid, .was-validated .form-select:invalid, .form-select.is-invalid { border-color: #dc3545; padding-right: calc(1.5em + 0.75rem); background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right calc(0.375em + 0.1875rem) center; background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem); }
    .was-validated .form-control:invalid:focus, .form-control.is-invalid:focus, .was-validated .form-select:invalid:focus, .form-select.is-invalid:focus { border-color: #dc3545; box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25); }
    .invalid-feedback { display: block; color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; }
    .drag-area.is-invalid { border-color: #dc3545 !important; background-color: rgba(220, 53, 69, 0.05); }
    .procesos-checklist.is-invalid { border-color: #dc3545 !important; background-color: rgba(220, 53, 69, 0.02); }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); } 20%, 40%, 60%, 80% { transform: translateX(2px); } }
    .campo-invalido-shake { animation: shake 0.5s ease-in-out; }
    .swal2-popup  { font-size: 1.2rem !important; }
    .swal2-title  { color: #000000 !important; }
    .swal2-confirm { background-color: #dc3545 !important; }
    .swal2-cancel  { background-color: #6c757d !important; }
    .rango-fechas { background-color: #f8f9fa; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; display: inline-block; border-left: 3px solid #800000; }
    .rango-fechas i { color: #800000; margin-right: 5px; }
    .fecha-detalle { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }
    #autocomplete-procesos { display: none; position: absolute; z-index: 9999; background: #fff; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 6px 6px; width: 100%; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.12); left: 0; top: 100%; }
    #autocomplete-procesos .ac-item { padding: 8px 12px; cursor: pointer; font-size: 0.88rem; border-bottom: 1px solid #f3f3f3; display: flex; align-items: center; gap: 8px; }
    #autocomplete-procesos .ac-item:hover, #autocomplete-procesos .ac-item.ac-active { background-color: #fdf0f1; color: #000000; }
    #autocomplete-procesos .ac-item i { color: #800000; font-size: 0.8rem; }
    #tablaNcOmPorProceso { display: none; margin-top: 16px; }
    #tablaNcOmPorProceso table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    #tablaNcOmPorProceso thead th { background-color: #000000; color: #fff; padding: 8px 10px; text-align: center; font-weight: 600; border: none; }
    #tablaNcOmPorProceso thead th:first-child { text-align: left; border-radius: 6px 0 0 0; }
    #tablaNcOmPorProceso thead th:last-child { border-radius: 0 6px 0 0; }
    #tablaNcOmPorProceso tbody tr:nth-child(even) { background-color: #ffffff; }
    #tablaNcOmPorProceso tbody td { padding: 6px 10px; border-bottom: 1px solid #dee2e6; vertical-align: middle; }
    #tablaNcOmPorProceso tbody td input[type="number"] { width: 80px; text-align: center; border: 1px solid #ced4da; border-radius: 4px; padding: 3px 6px; font-size: 0.88rem; }
    #tablaNcOmPorProceso tbody td input[type="number"]:focus { border-color: #000000; box-shadow: 0 0 0 0.3rem #d2e2f9; outline: none; }
    /* Nuevo estilo para el campo de texto de criterio */
    #tablaNcOmPorProceso tbody td input[type="text"] { width: 180px; border: 1px solid #ced4da; border-radius: 4px; padding: 3px 6px; font-size: 0.88rem; }
    #tablaNcOmPorProceso tbody td input[type="text"]:focus { border-color: #000000; box-shadow: 0 0 0 0.3rem #d2e2f9; outline: none; }
    .totales-nc-om { margin-top: 8px; font-size: 0.88rem; display: flex; gap: 20px; justify-content: flex-end; padding-right: 4px; }
    .totales-nc-om .badge-total-nc { background-color: #dc3545; color: #fff; border-radius: 20px; padding: 3px 12px; font-weight: 600; }
    .totales-nc-om .badge-total-om { background-color: #28a745; color: #fff; border-radius: 20px; padding: 3px 12px; font-weight: 600; }
    .filtro-tipo-estadisticas .btn-tipo { border: 2px solid #dee2e6; border-radius: 6px; padding: 5px 18px; font-size: 0.88rem; font-weight: 500; cursor: pointer; background: #fff; color: #495057; transition: all 0.15s; }
    .filtro-tipo-estadisticas .btn-tipo:hover { border-color: #737373; color: #737373; }
    .filtro-tipo-estadisticas .btn-tipo.activo-todos { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .filtro-tipo-estadisticas .btn-tipo.activo-interna { background-color: #28a745; border-color: #28a745; color: #fff; }
    .filtro-tipo-estadisticas .btn-tipo.activo-externa { background-color: #dc3545; border-color: #dc3545; color: #fff; }
    [title] { position: relative; cursor: help; }
    .tooltip { --bs-tooltip-bg: #737373; --bs-tooltip-color: #ffffff; font-size: 0.875rem; }
    .tooltip .tooltip-inner { background-color: #737373; color: white; padding: 0.5rem 1rem; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
    .tooltip.bs-tooltip-top .tooltip-arrow::before { border-top-color: #737373; }
    .tooltip.bs-tooltip-bottom .tooltip-arrow::before { border-bottom-color: #737373; }
    .tooltip.bs-tooltip-start .tooltip-arrow::before { border-left-color: #737373; }
    .tooltip.bs-tooltip-end .tooltip-arrow::before { border-right-color: #737373; }

    /* Botón "Todos" del modal de estadísticas con color #0dcaf0 cuando está activo */
    #btnTipoTodos.activo-todos {
        background-color: #0dcaf0 !important;
        border-color: #0dcaf0 !important;
        color: #fff !important;
    }

    /* Estilo para la columna de criterios: solo el texto, sin proceso */
    .criterio-item {
        margin-bottom: 2px;
    }
    .criterio-text {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
    }

    /* =====================================================
       ESTILOS RESPONSIVOS
    ===================================================== */

    /* Tablets específicamente (769px a 992px) */
    @media (min-width: 769px) and (max-width: 992px) {
        /* Forzar scroll horizontal en tablet */
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        
        #tablaInformes {
            min-width: 950px !important;
            width: max-content !important;
        }
        
        .table th, .table td {
            white-space: nowrap !important;
        }
        
        /* Reducir padding en tablet para que quepa más */
        .table th {
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
        }
        .table td {
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
        }
        
        /* Botones más compactos en tablet */
        .btn-sm {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.65rem !important;
        }
        
        .tag-proceso {
            font-size: 0.6rem !important;
            padding: 1px 4px !important;
        }
        
        /* Filtros en tablet */
        .row.mb-4 .col-12 .d-flex {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        
        .d-flex.align-items-center.position-relative[style*="width: 700px"] {
            width: 100% !important;
            min-width: auto !important;
        }
        
        .dropdown .btn, #btnEstadisticas, #btnHistorico {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.75rem !important;
        }
    }

    /* Tablets y pantallas medianas (992px y menos) */
    @media (max-width: 992px) {
        /* Encabezado */
        .container-fluid > .row:first-child .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem !important;
        }
        .container-fluid > .row:first-child .d-flex .btn {
            width: 100% !important;
        }
        
        /* Filtros - se envuelven */
        .row.mb-4 .col-12 .d-flex {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.75rem !important;
        }
        .row.mb-4 .col-12 .d-flex > div.d-flex.align-items-center.position-relative {
            width: 100% !important;
        }
        .row.mb-4 .col-12 .d-flex .dropdown,
        .row.mb-4 .col-12 .d-flex .btn {
            width: 100% !important;
        }
        .row.mb-4 .col-12 .d-flex .dropdown .btn {
            width: 100% !important;
        }
    }

    /* Móviles (768px y menos) */
    @media (max-width: 768px) {
        /* Contenedor principal */
        .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        
        /* Título */
        .h3 {
            font-size: 1.5rem !important;
        }
        .h3 i {
            font-size: 2rem !important;
        }
        
        /* Buscador - sin espacio entre input y botón */
        .d-flex.align-items-center.position-relative[style*="width: 700px"] {
            width: 100% !important;
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            gap: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .d-flex.align-items-center.position-relative .position-relative {
            flex: 1 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        #inputBuscar {
            font-size: 0.9rem !important;
            height: 38px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-right: none !important;
            margin: 0 !important;
        }
        
        #limpiarBusqueda {
            width: 42px !important;
            height: 38px !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-left: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        #limpiarBusqueda i {
            font-size: 1.2rem !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Botones de filtros */
        .dropdown .btn, #btnEstadisticas, #btnHistorico {
            height: 38px !important;
            font-size: 0.8rem !important;
        }
        
        /* Tabla - scroll horizontal obligatorio */
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        #tablaInformes {
            min-width: 800px !important;
            width: max-content !important;
        }
        .table th, .table td {
            white-space: nowrap !important;
        }
        /* Permitir wrap solo en columnas de texto largo */
        .table td:nth-child(1), /* Nombre */
        .table td:nth-child(7) { /* Procesos */
            white-space: normal !important;
            min-width: 150px !important;
            max-width: 200px !important;
        }
        
        /* Columna de acciones */
        .table td:last-child .d-flex {
            flex-wrap: nowrap !important;
        }
        
        /* Paginación */
        .pagination-info {
            font-size: 0.75rem !important;
            text-align: center !important;
            margin-bottom: 8px !important;
        }
        .pagination {
            justify-content: center !important;
        }
        .pagination .page-link {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        
        /* Documento nombre */
        .table td:nth-child(11) span {
            max-width: 100px !important;
            display: inline-block !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        
        /* Criterios */
        .criterio-text {
            max-width: 150px !important;
        }
    }

    /* Móviles muy pequeños (480px y menos) */
    @media (max-width: 480px) {
        /* Stats cards */
        .stat-card .num {
            font-size: 1.3rem !important;
        }
        .stat-card .label {
            font-size: 0.7rem !important;
        }
        
        /* Modales */
        .modal-dialog {
            margin: 0.5rem !important;
        }
        .modal-body {
            padding: 0.75rem !important;
        }
        
        /* Checklist procesos en móvil - una columna */
        .procesos-checklist {
            column-count: 1 !important;
        }
        
        /* Tabla NC/OM en móvil */
        #tablaNcOmPorProceso table {
            font-size: 0.7rem !important;
        }
        #tablaNcOmPorProceso tbody td input[type="text"] {
            width: 100px !important;
            font-size: 0.7rem !important;
        }
        #tablaNcOmPorProceso tbody td input[type="number"] {
            width: 55px !important;
            font-size: 0.7rem !important;
        }
        
        /* Drag area */
        .drag-area i {
            font-size: 2rem !important;
        }
        .drag-area p {
            font-size: 0.7rem !important;
        }
    }

    /* Mejoras para gráficas responsivas */
    @media (max-width: 768px) {
        #wrapperChartPorProceso,
        #wrapperChartHistorico {
            overflow-x: auto !important;
        }
        
        #chartPorProceso,
        #chartHistorico {
            min-width: 500px !important;
        }
        
        .grafica-anual-container {
            padding: 10px !important;
        }
        
        /* Tarjetas de estadísticas en móvil */
        .stat-card .num {
            font-size: 1.3rem !important;
        }
        .stat-card .label {
            font-size: 0.7rem !important;
        }
        .stat-card {
            padding: 12px 5px !important;
        }
    }
    
    @media (max-width: 480px) {
        .grafica-anual-container h6 {
            font-size: 0.8rem !important;
        }
        
        /* Filtros de tipo en gráficas */
        .filtro-tipo-estadisticas .btn-tipo {
            padding: 3px 10px !important;
            font-size: 0.7rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- ── ENCABEZADO: TÍTULO DE LA PÁGINA Y BOTÓN PARA REGISTRAR NUEVO INFORME ── --}}
    {{-- EL TÍTULO ES UN ENLACE AL DASHBOARD DE AUDITORÍA --}}
    {{-- EL BOTÓN DE REGISTRO SOLO ES VISIBLE PARA SUPERADMIN, ADMIN Y AUDITOR_LIDER --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                    <h1 class="h3 mb-0" style="color: #059669; cursor: pointer;">
                        <i class="bi bi-file-earmark-text me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                        Informes
                    </h1>
                </a>
                @can('auditoria-access')
                <button class="btn" type="button" id="btnNuevoInforme" style="background-color: #737373; color: white; border: none;">
                    <i class="bi bi-plus-circle"></i> Registrar Informe
                </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- ── FILTROS: BUSCADOR, ORDENAR, AÑO, TIPO, ESTADÍSTICAS E HISTÓRICO ── --}}
    {{-- TODOS LOS FILTROS RECONSTRUYEN LA URL CON LOS PARÁMETROS Y RECARGAN LA PÁGINA --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 flex-wrap">

                {{-- BUSCADOR DE INFORMES POR NOMBRE CON BOTÓN PARA LIMPIAR LA BÚSQUEDA --}}
                <div class="d-flex align-items-center position-relative" style="width: 700px;">
                    <div class="position-relative flex-grow-1">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                        <input type="text" class="form-control ps-5" id="inputBuscar"
                               style="width: 100%; height: 42px; font-size: 1rem; border-radius: 4px 0 0 4px; border-right: none;"
                               placeholder="Buscar archivos">
                    </div>
                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center btn-clear-search"
                            id="limpiarBusqueda"
                            onclick="limpiarBuscador()"
                            title="Limpiar búsqueda">
                        <i class="bi bi-x-lg" style="font-size: 1.4rem; font-weight: bold;"></i>
                    </button>
                </div>

                {{-- DROPDOWN PARA ORDENAR LOS INFORMES POR NOMBRE O FECHA (ASC O DESC) --}}
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

                {{-- DROPDOWN PARA FILTRAR LOS INFORMES POR AÑO --}}
                {{-- LOS AÑOS SE CARGAN DINÁMICAMENTE DESDE JAVASCRIPT --}}
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnAnio" style="height: 42px; background-color: white;">
                        <i class="bi bi-calendar"></i> <span id="anioTexto">Filtrar por Año</span>
                    </button>
                    <ul class="dropdown-menu" id="menuAnios">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('', 'Filtrar por Año')">Todos los años</a></li>
                    </ul>
                </div>

                {{-- DROPDOWN PARA FILTRAR LOS INFORMES POR TIPO (INTERNA O EXTERNA) --}}
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

                {{-- BOTÓN QUE ABRE EL MODAL DE ESTADÍSTICAS POR AÑO CON GRÁFICAS --}}
                <button class="btn" id="btnEstadisticas" style="background-color: #0dcaf0; color: #fff; border: none; height: 42px; padding: 8px 15px; font-weight: 500;">
                    <i class="bi bi-bar-chart-line me-1"></i>Estadísticas
                </button>

                {{-- BOTÓN QUE ABRE EL MODAL DEL HISTÓRICO GLOBAL DE NC Y OM POR AÑO --}}
                <button class="btn" id="btnHistorico" style="background-color: #0d6efd; color: #fff; border: none; height: 42px; padding: 8px 15px; font-weight: 500;">
                    <i class="bi bi-graph-up me-1"></i>Histórico
                </button>

            </div>
        </div>
    </div>

    {{-- ── TABLA PRINCIPAL DE INFORMES DE AUDITORÍA ── --}}
    {{-- MUESTRA TODOS LOS INFORMES CON SUS DATOS Y ACCIONES DISPONIBLES --}}
    {{-- LOS ATRIBUTOS data-tipo Y data-nc-om-por-proceso SE USAN PARA LAS ESTADÍSTICAS EN JS --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-responsive">
                <table id="tablaInformes" class="table table-bordered" style="width:100%;">
                    <thead>
                            <th>Nombre de Informe</th>
                            <th>Tipo</th>
                            <th>Auditor Líder</th>
                            <th>Fecha Informe</th>
                            <th>Periodo Auditoría</th>
                            <th>Año</th>
                            <th>Procesos Auditados</th>
                            <th>Criterios</th>
                            <th>No Conformidades</th>
                            <th>Oport. Mejora</th>
                            <th>Documento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- RECORRE CADA INFORME Y LO MUESTRA EN UNA FILA DE LA TABLA --}}
                        {{-- SI NO HAY INFORMES, MUESTRA UN MENSAJE EN EL CENTRO --}}
                        @forelse($informes as $inf)
                        <tr class="align-middle"
                            data-tipo="{{ $inf->tipo_auditoria }}"
                            data-nc-om-por-proceso="{{ json_encode($inf->nc_om_por_proceso ?? []) }}">

                            {{-- NOMBRE DEL INFORME EN NEGRITAS --}}
                            <td class="fw-bold">{{ $inf->nombre_informe }}</td>

                            {{-- TIPO DE AUDITORÍA CON BADGE DE COLOR (VERDE=INTERNA, ROJO=EXTERNA) --}}
                            <td><span class="badge-{{ strtolower($inf->tipo_auditoria) }}">{{ $inf->tipo_auditoria }}</span></td>

                            {{-- NOMBRE DEL AUDITOR LÍDER --}}
                            <td>{{ $inf->auditor_lider }}</td>

                            {{-- FECHA EN QUE SE EMITIÓ EL INFORME --}}
                            <td>{{ $inf->fecha_informe->format('d/m/Y') }}</td>

                            {{-- PERIODO DE AUDITORÍA: MUESTRA EL RANGO DE FECHAS Y LOS DÍAS DE DURACIÓN --}}
                            {{-- OBTIENE LAS FECHAS DEL INFORME O DE LA AUDITORÍA RELACIONADA COMO RESPALDO --}}
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
                                @else -
                                @endif
                            </td>

                            {{-- AÑO AL QUE PERTENECE EL INFORME --}}
                            <td>{{ $inf->anio }}</td>

                            {{-- LISTA DE PROCESOS AUDITADOS MOSTRADOS COMO ETIQUETAS (TAGS) --}}
                            <td>
                                <div class="procesos-container">
                                    @if($inf->procesos_auditados)
                                        @foreach($inf->procesos_auditados as $p)
                                            <span class="tag-proceso">{{ $p }}</span>
                                        @endforeach
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- CRITERIOS DE AUDITORÍA POR PROCESO (SE TRUNCAN SI SON MUY LARGOS) --}}
                            {{-- EL TITLE DEL DIV MUESTRA EL NOMBRE DEL PROCESO AL HACER HOVER --}}
                            <td>
                                @if($inf->nc_om_por_proceso)
                                    @foreach($inf->nc_om_por_proceso as $item)
                                        <div class="criterio-item" title="Proceso: {{ $item['proceso'] }}">
                                            <span class="criterio-text">{{ $item['criterio'] ?? '—' }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- TOTAL DE NO CONFORMIDADES EN ROJO Y OPORTUNIDADES DE MEJORA EN VERDE --}}
                            <td class="num-red">{{ $inf->no_conformidades }}</td>
                            <td class="num-orange">{{ $inf->oportunidades_mejora }}</td>

                            {{-- NOMBRE DEL DOCUMENTO ADJUNTO (TRUNCADO A 20 CARACTERES) O GUIÓN --}}
                            <td>
                                @if($inf->documento_path)
                                    <span style="color: #212529;"><i class="bi bi-file-earmark-pdf me-1" style="color: #000000;"></i>{{ Str::limit($inf->documento_nombre, 20) }}</span>
                                @else <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- BOTONES DE ACCIÓN: VER, DESCARGAR, EDITAR Y ELIMINAR --}}
                            {{-- VER Y DESCARGAR SOLO SI HAY DOCUMENTO, EDITAR Y ELIMINAR REQUIEREN PERMISO --}}
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- BOTÓN VER: SOLO DISPONIBLE SI EL DOCUMENTO ES PDF --}}
                                    @if($inf->documento_path && strtolower(pathinfo($inf->documento_nombre, PATHINFO_EXTENSION)) === 'pdf')
                                    <button type="button" class="btn btn-sm btn-outline-info" title="Ver documento"
                                        onclick="verDocumento({{ $inf->id }}, '{{ addslashes($inf->documento_nombre) }}')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @endif

                                    {{-- BOTÓN DESCARGAR: SOLO DISPONIBLE SI HAY DOCUMENTO ADJUNTO --}}
                                    @if($inf->documento_path)
                                    <a href="{{ url('auditorias/informes') }}/{{ $inf->id }}/descargar"
                                       class="btn btn-sm btn-outline-primary" title="Descargar documento">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @endif

                                    {{-- BOTÓN EDITAR: SOLO VISIBLE PARA SUPERADMIN, ADMIN Y AUDITOR_LIDER --}}
                                    @can('auditoria-access')
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar informe"
                                        onclick="editarInforme({{ $inf->id }})">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @endcan

                                    {{-- BOTÓN ELIMINAR: SOLO VISIBLE PARA SUPERADMIN, ADMIN Y AUDITOR_LIDER --}}
                                    @can('auditoria-access')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar informe"
                                        onclick="eliminarInforme({{ $inf->id }}, '{{ addslashes($inf->nombre_informe) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        {{-- MENSAJE CUANDO NO HAY INFORMES REGISTRADOS --}}
                        <tr><td colspan="12" class="text-center text-muted py-3">No hay informes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── PAGINACIÓN: MUESTRA EL RANGO DE REGISTROS VISIBLES Y LOS BOTONES DE PÁGINA ── --}}
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
     SE USA TANTO PARA CREAR UN NUEVO INFORME COMO PARA EDITAR UNO EXISTENTE
     EL TÍTULO Y EL MÉTODO (POST/PUT) CAMBIAN DINÁMICAMENTE DESDE JAVASCRIPT
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalInforme" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-rojo">
        <h5 class="modal-title"><i class="bi bi-cloud-upload me-2" style="color: #000000"></i><span id="tituloModalInforme">Subir Informe</span></h5>
      </div>
      <div class="modal-body">
        <form id="formInforme" enctype="multipart/form-data" novalidate>
          @csrf
          {{-- CAMPO OCULTO PARA INDICAR SI ES CREACIÓN (POST) O EDICIÓN (PUT) --}}
          <input type="hidden" name="_method" id="formMethod" value="POST">
          {{-- CAMPO OCULTO PARA GUARDAR EL ID DEL INFORME AL EDITAR --}}
          <input type="hidden" name="informe_id" id="informeId">
          <div class="row g-3">

            {{-- NOMBRE DEL INFORME --}}
            <div class="col-md-6">
              <label class="form-label">Nombre del Informe <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombre_informe" id="fNombre" placeholder="Ej. Informe_2024_Q1" required>
              <div class="invalid-feedback" id="fNombre-feedback">El nombre del informe es requerido</div>
            </div>

            {{-- TIPO DE AUDITORÍA (INTERNA O EXTERNA) --}}
            <div class="col-md-6">
              <label class="form-label">Tipo de Auditoría <span class="text-danger">*</span></label>
              <select class="form-select" name="tipo_auditoria" id="fTipo" required>
                <option value="">-- Seleccionar --</option>
                <option value="Interna">Interna</option>
                <option value="Externa">Externa</option>
              </select>
              <div class="invalid-feedback" id="fTipo-feedback">El tipo de auditoría es requerido</div>
            </div>

            {{-- NOMBRE DEL AUDITOR LÍDER --}}
            <div class="col-md-6">
              <label class="form-label">Auditor Líder <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="auditor_lider" id="fAuditor" placeholder="Nombre del auditor" required>
              <div class="invalid-feedback" id="fAuditor-feedback">El nombre del auditor líder es requerido</div>
            </div>

            {{-- SELECTOR DE AUDITORÍA RELACIONADA --}}
            {{-- AL SELECCIONAR UNO, SE COPIAN AUTOMÁTICAMENTE LAS FECHAS DE INICIO Y FIN --}}
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

            {{-- FECHA EN QUE SE EMITE EL INFORME --}}
            <div class="col-md-6">
              <label class="form-label">Fecha del Informe <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fecha_informe" id="fFechaInforme" required>
              <div class="invalid-feedback" id="fFechaInforme-feedback">La fecha del informe es requerida</div>
            </div>

            {{-- PERIODO DE AUDITORÍA (SOLO LECTURA, SE LLENA AUTOMÁTICAMENTE AL ELEGIR AUDITORÍA RELACIONADA) --}}
            {{-- LOS CAMPOS OCULTOS fecha_inicio Y fecha_fin SON LOS QUE SE ENVÍAN AL SERVIDOR --}}
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

            {{-- CHECKLIST DE PROCESOS AUDITADOS --}}
            {{-- SE PUEDE SELECCIONAR MÚLTIPLES PROCESOS Y AGREGAR NUEVOS DESDE EL CAMPO DE ABAJO --}}
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

            {{-- TABLA DE NO CONFORMIDADES Y OPORTUNIDADES DE MEJORA POR PROCESO --}}
            {{-- APARECE AUTOMÁTICAMENTE CUANDO SE SELECCIONA AL MENOS UN PROCESO --}}
            {{-- LOS TOTALES SE CALCULAN AUTOMÁTICAMENTE AL INGRESAR VALORES --}}
            <div class="col-12" id="tablaNcOmPorProceso">
              <label class="form-label">
                <i class="bi bi-table me-1" style="color:#000000;"></i>
                No Conformidades y Oportunidades de Mejora por Proceso
              </label>
              <div class="border rounded overflow-hidden">
                <table style="width:100%; border-collapse:collapse; font-size:0.88rem;">
                  <thead>
                     <tr>
                      <th style="background:#ffffff;color:#212529;padding:8px 12px;text-align:left;font-weight:600;border-bottom:1px solid #dee2e6;">Proceso</th>
                      <th style="background:#ffffff;color:#212529;padding:8px 12px;text-align:left;font-weight:600;border-bottom:1px solid #dee2e6;">Criterio de Auditoría</th>
                      <th style="background:#ffffff;color:#212529;padding:8px 12px;text-align:center;font-weight:600;width:130px;border-bottom:1px solid #dee2e6;">No Conformidades</th>
                      <th style="background:#ffffff;color:#212529;padding:8px 12px;text-align:center;font-weight:600;width:150px;border-bottom:1px solid #dee2e6;">Oport. de Mejora</th>
                     </tr>
                  </thead>
                  {{-- LAS FILAS SE GENERAN DINÁMICAMENTE DESDE JAVASCRIPT AL SELECCIONAR PROCESOS --}}
                  <tbody id="cuerpoTablaNcOm"></tbody>
                </table>
              </div>
              {{-- TOTALES DE NC Y OM CALCULADOS AUTOMÁTICAMENTE --}}
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

            {{-- CAMPOS OCULTOS QUE GUARDAN LOS TOTALES CALCULADOS PARA ENVIARLOS AL SERVIDOR --}}
            <input type="hidden" name="no_conformidades" id="fNoConf" value="0">
            <input type="hidden" name="oportunidades_mejora" id="fOport" value="0">

            {{-- ÁREA DE CARGA DE DOCUMENTO (DRAG & DROP O CLIC PARA SELECCIONAR) --}}
            {{-- EL DOCUMENTO ES OBLIGATORIO EN CREACIÓN Y OPCIONAL EN EDICIÓN --}}
            <div class="col-12">
              <label class="form-label">Documento <span id="docRequerido" class="text-danger">*</span></label>
              <div class="drag-area border rounded p-4 bg-light" onclick="document.getElementById('fDocumento').click()" id="dragArea">
                <div class="text-center mb-3">
                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #000000;"></i>
                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                    <p class="text-muted small mb-0" id="docLabel">PDF, DOC, DOCX, XLS, XLSX, CSV — máx. 10 MB</p>
                </div>
              </div>
              {{-- INPUT DE ARCHIVO OCULTO QUE SE ACTIVA AL HACER CLIC EN EL ÁREA DE DRAG & DROP --}}
              <input type="file" id="fDocumento" name="documento" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv" class="d-none"
                onchange="document.getElementById('docLabel').textContent = this.files[0]?.name ?? 'PDF, DOC, DOCX, XLS, XLSX — máx. 10 MB'">
              <div class="invalid-feedback" id="fDocumento-feedback">El documento es requerido</div>
            </div>
          </div>

          {{-- CONTENEDOR PARA MOSTRAR ERRORES DEL SERVIDOR (SE OCULTA INICIALMENTE) --}}
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
     MUESTRA TARJETAS CON TOTALES Y UNA GRÁFICA DE BARRAS DE NC/OM POR PROCESO
     TIENE FILTROS DE AÑO Y TIPO DE AUDITORÍA (TODOS/INTERNA/EXTERNA)
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEstadisticas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-cyan">
        <h5 class="modal-title"><i class="bi bi-bar-chart-line me-2"></i>Estadísticas por Año</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        {{-- FILTROS DE AÑO Y TIPO DE AUDITORÍA DEL MODAL DE ESTADÍSTICAS --}}
        <div class="row mb-4 align-items-center g-2">
          <div class="col-auto">
            <label class="form-label fw-bold mb-0">Año</label>
          </div>
          <div class="col-auto">
            {{-- SELECT DE AÑO QUE SE LLENA DINÁMICAMENTE DESDE JAVASCRIPT --}}
            <select id="selectAnioEstadisticas" class="form-select" style="width:150px; border:2px solid #0dcaf0;">
              <option value="">Cargando años...</option>
            </select>
          </div>
          <div class="col-auto ms-3">
            <label class="form-label fw-bold mb-0">Tipo de Auditoría</label>
          </div>
          {{-- BOTONES DE FILTRO POR TIPO: TODOS (AZUL), INTERNA (VERDE), EXTERNA (ROJO) --}}
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

        {{-- TARJETAS DE ESTADÍSTICAS: TOTAL AUDITORÍAS, NC Y OM --}}
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

        {{-- GRÁFICA DE BARRAS DE NC Y OM DESGLOSADOS POR PROCESO --}}
        {{-- SE OCULTA Y MUESTRA MENSAJE SI NO HAY DATOS PARA LOS FILTROS SELECCIONADOS --}}
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

        {{-- LISTA DE PROCESOS AUDITADOS EN EL AÑO SELECCIONADO --}}
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
     MODAL: HISTÓRICO GLOBAL
     MUESTRA UNA GRÁFICA DE BARRAS CON NC Y OM TOTALES POR AÑO
     Y UNA TABLA RESUMEN CON LOS DATOS DE CADA AÑO
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalHistorico" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#0d6efd; color:#fff;">
        <h5 class="modal-title"><i class="bi bi-graph-up me-2"></i>Histórico Global — NC y Oport. de Mejora por Año</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter:invert(1);"></button>
      </div>
      <div class="modal-body">
        <div class="grafica-anual-container">

          {{-- BOTONES DE FILTRO POR TIPO EN EL MODAL DE HISTÓRICO --}}
          <div class="d-flex justify-content-center mb-3">
            <div class="filtro-tipo-estadisticas d-flex gap-2">
              <button type="button" class="btn-tipo activo-todos" id="btnHistTodos" onclick="seleccionarTipoHistorico('todos')">
                <i class="bi bi-grid me-1"></i>Todos
              </button>
              <button type="button" class="btn-tipo" id="btnHistInterna" onclick="seleccionarTipoHistorico('Interna')">
                <i class="bi bi-check-circle me-1"></i>Interna
              </button>
              <button type="button" class="btn-tipo" id="btnHistExterna" onclick="seleccionarTipoHistorico('Externa')">
                <i class="bi bi-x-circle me-1"></i>Externa
              </button>
            </div>
          </div>

          {{-- GRÁFICA DE BARRAS HISTÓRICA POR AÑO --}}
          {{-- SE OCULTA Y MUESTRA MENSAJE SI NO HAY DATOS DISPONIBLES --}}
          <h6 class="fw-bold text-muted text-center mb-3">
            <i class="bi bi-calendar3 me-1"></i>
            Totales de No Conformidades y Oportunidades de Mejora por Año
          </h6>
          <div id="sinDatosHistoricoMsg" class="text-center text-muted py-4" style="display:none;">
            <i class="bi bi-info-circle me-1"></i> No hay datos históricos disponibles.
          </div>
          <div id="wrapperChartHistorico">
            <canvas id="chartHistorico" height="260"></canvas>
          </div>
        </div>

        {{-- TABLA RESUMEN CON LOS TOTALES POR AÑO (SE LLENA DINÁMICAMENTE DESDE JAVASCRIPT) --}}
        <div class="mt-4">
          <h6 class="fw-bold text-muted mb-2"><i class="bi bi-table me-1"></i>Resumen por Año</h6>
          <div class="table-responsive">
            <table class="table table-bordered" style="font-size:0.9rem;">
              <thead>
                 <tr>
                  <th style="background:#0d6efd;color:#fff;text-align:center;border:none;">Año</th>
                  <th style="background:#0d6efd;color:#fff;text-align:center;border:none;">Total Auditorías</th>
                  <th style="background:#0d6efd;color:#fff;text-align:center;border:none;">No Conformidades</th>
                  <th style="background:#0d6efd;color:#fff;text-align:center;border:none;">Oport. de Mejora</th>
                 </tr>
              </thead>
              {{-- LAS FILAS SE GENERAN DINÁMICAMENTE DESDE JAVASCRIPT --}}
              <tbody id="cuerpoTablaHistorico"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     MODAL: VER DOCUMENTO
     MUESTRA EL DOCUMENTO EN UN IFRAME DENTRO DEL MODAL
     SI ES PDF LO MUESTRA DIRECTAMENTE, OTROS FORMATOS MUESTRAN OPCIÓN DE DESCARGA
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-rojo">
        <h5 class="modal-title"><i class="bi bi-file-earmark me-2"></i><span id="tituloDocumento"></span></h5>
      </div>
      {{-- IFRAME QUE CARGA EL DOCUMENTO AL ABRIR EL MODAL Y SE LIMPIA AL CERRARLO --}}
      <div class="modal-body p-0">
        <iframe id="iframeDoc" src="about:blank"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class=""></i> Cerrar
        </button>
        {{-- BOTÓN PARA DESCARGAR EL DOCUMENTO DESDE EL MODAL DE VISUALIZACIÓN --}}
        <a id="btnDescargarDocumento" href="#" class="btn text-white" style="background-color: #800000; border: none;">
          <i class="bi bi-download me-1"></i> Descargar
        </a>
      </div>
    </div>
  </div>
</div>
@endsection


{{-- ══════════════════════════════════════════════════════════════
     SCRIPTS DE LA PÁGINA DE INFORMES DE AUDITORÍA
     INCLUYE: SWEETALERT2, CHART.JS, JQUERY, MOMENT Y DATERANGEPICKER
══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// ============================================================
// 1.  DATOS COMPLETOS DE TODOS LOS INFORMES (para estadísticas)
//     SE GENERAN DESDE PHP Y SE PASAN A JAVASCRIPT COMO JSON
//     SE USAN PARA CALCULAR ESTADÍSTICAS SIN LLAMADAS AJAX ADICIONALES
// ============================================================
@php
    $informesData = $informes->map(function($inf) {
        return [
            'id' => $inf->id,
            'nombre_informe' => $inf->nombre_informe,
            'tipo_auditoria' => $inf->tipo_auditoria,
            'auditor_lider' => $inf->auditor_lider,
            'fecha_informe' => $inf->fecha_informe->format('Y-m-d'),
            'fecha_inicio' => $inf->fecha_inicio ?? ($inf->auditoriaRelacionada?->fecha_inicio ?? null),
            'fecha_fin' => $inf->fecha_fin ?? ($inf->auditoriaRelacionada?->fecha_fin ?? null),
            'anio' => $inf->anio,
            'procesos_auditados' => $inf->procesos_auditados,
            'nc_om_por_proceso' => $inf->nc_om_por_proceso,
            'no_conformidades' => $inf->no_conformidades,
            'oportunidades_mejora' => $inf->oportunidades_mejora,
        ];
    });
@endphp
const informesCompletos = @json($informesData);

// ============================================================
// 2.  RUTAS Y VARIABLES GLOBALES
//     RUTAS: URLS DEL SERVIDOR PARA LAS OPERACIONES CRUD
//     VARIABLES: ESTADO ACTUAL DE LOS FILTROS Y LAS GRÁFICAS
// ============================================================
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

// INSTANCIAS DE LAS GRÁFICAS (SE DESTRUYEN Y RECREAN AL CAMBIAR FILTROS)
let chartPorProceso    = null;
let chartHistorico     = null;

// ESTADO ACTUAL DE LOS FILTROS DE LA TABLA PRINCIPAL
let tipoSeleccionado   = '';
let anioSeleccionado   = '';
let ordenSeleccionado  = '';

// ESTADO ACTUAL DE LOS FILTROS DE LOS MODALES DE ESTADÍSTICAS E HISTÓRICO
let tipoEstadisticas   = 'todos';
let tipoHistorico      = 'todos';

// COLORES PARA LAS BARRAS DE NC Y OM EN LAS GRÁFICAS
const coloresNCPorTipo = {
    todos: 'rgba(220, 53, 69, 0.85)',
    Interna: 'rgba(220, 53, 69, 0.85)',
    Externa: 'rgba(220, 53, 69, 0.85)'
};
const coloresOMPorTipo = {
    todos: 'rgba(40, 167, 69, 0.85)',
    Interna: 'rgba(40, 167, 69, 0.85)',
    Externa: 'rgba(40, 167, 69, 0.85)'
};

// ============================================================
// 3.  FUNCIONES DE FILTRADO Y ESTADÍSTICAS (USANDO informesCompletos)
//     CALCULAN LOS DATOS LOCALMENTE SIN LLAMADAS AL SERVIDOR
// ============================================================

// FILTRA LOS INFORMES POR AÑO Y TIPO, Y CALCULA TOTALES Y DESGLOSE POR PROCESO
function obtenerDatosFiltrados(anio, tipo) {
    let total = 0, nc = 0, om = 0;
    const procesosSet = new Set();
    const procesosMap = {};

    informesCompletos.forEach(inf => {
        const coincideAnio = anio === '' || inf.anio == anio;
        const coincideTipo = (tipo === 'todos') || (inf.tipo_auditoria === tipo);
        if (!coincideAnio || !coincideTipo) return;

        total++;
        nc += inf.no_conformidades || 0;
        om += inf.oportunidades_mejora || 0;

        // ACUMULA LOS PROCESOS AUDITADOS EN UN SET PARA ELIMINAR DUPLICADOS
        if (inf.procesos_auditados && Array.isArray(inf.procesos_auditados)) {
            inf.procesos_auditados.forEach(p => procesosSet.add(p));
        }

        // ACUMULA NC Y OM POR PROCESO EN UN MAPA PARA LA GRÁFICA DE BARRAS
        if (inf.nc_om_por_proceso && Array.isArray(inf.nc_om_por_proceso)) {
            inf.nc_om_por_proceso.forEach(item => {
                if (!item.proceso) return;
                if (!procesosMap[item.proceso]) {
                    procesosMap[item.proceso] = { nc: 0, om: 0 };
                }
                procesosMap[item.proceso].nc += parseInt(item.nc) || 0;
                procesosMap[item.proceso].om += parseInt(item.om) || 0;
            });
        }
    });

    return { totalAuditorias: total, totalNC: nc, totalOM: om, procesos: Array.from(procesosSet), procesosMap };
}

// OBTIENE LOS AÑOS ÚNICOS DE TODOS LOS INFORMES ORDENADOS ALFABÉTICAMENTE
function obtenerAniosUnicosCompletos() {
    const años = new Set();
    informesCompletos.forEach(inf => { if (inf.anio) años.add(inf.anio.toString()); });
    return Array.from(años).sort();
}

// ACTUALIZA LOS VALORES DE LAS TARJETAS DE ESTADÍSTICAS EN EL MODAL
function actualizarTarjetas(t, nc, om) {
    document.getElementById('statTotal').textContent = t;
    document.getElementById('statNC').textContent    = nc;
    document.getElementById('statOM').textContent    = om;
}

// CREA O ACTUALIZA LA GRÁFICA DE BARRAS DE NC Y OM POR PROCESO
function actualizarGraficaPorProceso(procesosMap) {
    const ctx    = document.getElementById('chartPorProceso');
    const msgEl  = document.getElementById('sinDatosProcesoMsg');
    const wrpEl  = document.getElementById('wrapperChartPorProceso');
    if (!ctx) return;
    if (chartPorProceso) { chartPorProceso.destroy(); chartPorProceso = null; }
    const procesos = Object.keys(procesosMap);
    if (procesos.length === 0) { msgEl.style.display = 'block'; wrpEl.style.display = 'none'; return; }
    msgEl.style.display = 'none'; wrpEl.style.display = 'block';
    ctx.height = Math.max(300, 200 + procesos.length * 25);

    const colorNC = coloresNCPorTipo[tipoEstadisticas] || coloresNCPorTipo.todos;
    const colorOM = coloresOMPorTipo[tipoEstadisticas] || coloresOMPorTipo.todos;
    const borderColorNC = colorNC.replace('0.85', '1');
    const borderColorOM = colorOM.replace('0.85', '1');

    chartPorProceso = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: procesos,
            datasets: [
                {
                    label: 'No Conformidades',
                    data: procesos.map(p => procesosMap[p].nc),
                    backgroundColor: colorNC,
                    borderColor: borderColorNC,
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Oport. de Mejora',
                    data: procesos.map(p => procesosMap[p].om),
                    backgroundColor: colorOM,
                    borderColor: borderColorOM,
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { size: 13 } } },
                tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.raw}` } }
            },
            scales: {
                x: { ticks: { maxRotation: 35, minRotation: 0, font: { size: 11 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Cantidad' } }
            }
        }
    });
}

// ACTUALIZA LA LISTA DE ETIQUETAS DE PROCESOS EN EL MODAL DE ESTADÍSTICAS
function actualizarListaProcesos(procesos) {
    const div = document.getElementById('listaProcesosEstadisticas');
    div.innerHTML = '';
    if (procesos.length > 0) {
        procesos.forEach(p => { const s = document.createElement('span'); s.className = 'tag-proceso'; s.textContent = p; div.appendChild(s); });
    } else {
        div.innerHTML = '<span class="text-muted">No hay procesos para los filtros seleccionados</span>';
    }
}

// REFRESCA TODAS LAS ESTADÍSTICAS DEL MODAL AL CAMBIAR AÑO O TIPO
function refrescarEstadisticas() {
    const anio = document.getElementById('selectAnioEstadisticas').value;
    if (!anio || anio === '0') { actualizarTarjetas(0, 0, 0); actualizarGraficaPorProceso({}); actualizarListaProcesos([]); return; }
    const d = obtenerDatosFiltrados(anio, tipoEstadisticas);
    actualizarTarjetas(d.totalAuditorias, d.totalNC, d.totalOM);
    actualizarGraficaPorProceso(d.procesosMap);
    actualizarListaProcesos(d.procesos);
}

// CAMBIA EL TIPO ACTIVO EN EL MODAL DE ESTADÍSTICAS Y REFRESCA LOS DATOS
function seleccionarTipoEstadisticas(tipo) {
    tipoEstadisticas = tipo;
    document.getElementById('btnTipoTodos').className    = 'btn-tipo' + (tipo === 'todos'    ? ' activo-todos'    : '');
    document.getElementById('btnTipoInterna').className  = 'btn-tipo' + (tipo === 'Interna'  ? ' activo-interna'  : '');
    document.getElementById('btnTipoExterna').className  = 'btn-tipo' + (tipo === 'Externa'  ? ' activo-externa'  : '');
    refrescarEstadisticas();
}

// GENERA LA GRÁFICA HISTÓRICA Y LA TABLA DE RESUMEN POR AÑO
function renderHistorico() {
    const anios = obtenerAniosUnicosCompletos();
    const msgEl = document.getElementById('sinDatosHistoricoMsg');
    const wrpEl = document.getElementById('wrapperChartHistorico');
    const tbody = document.getElementById('cuerpoTablaHistorico');
    if (chartHistorico) { chartHistorico.destroy(); chartHistorico = null; }
    if (anios.length === 0) {
        if (msgEl) msgEl.style.display = 'block';
        if (wrpEl) wrpEl.style.display = 'none';
        if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>';
        return;
    }
    if (msgEl) msgEl.style.display = 'none';
    if (wrpEl) wrpEl.style.display = 'block';
    if (tbody) tbody.innerHTML = '';
    const dataNc = [], dataOm = [], dataTotal = [];
    anios.forEach((a, idx) => {
        const d = obtenerDatosFiltrados(a, tipoHistorico);
        dataNc.push(d.totalNC);
        dataOm.push(d.totalOM);
        dataTotal.push(d.totalAuditorias);
        if (tbody) {
            const tr = document.createElement('tr');
            tr.style.background = idx % 2 === 1 ? '#f8f9fa' : '#fff';
            tr.innerHTML = `<td style="text-align:center;font-weight:600;">${a}</td>
                            <td style="text-align:center;">${d.totalAuditorias}</td>
                            <td style="text-align:center;color:#dc3545;font-weight:700;">${d.totalNC}</td>
                            <td style="text-align:center;color:#fd7e14;font-weight:700;">${d.totalOM}</td>`;
            tbody.appendChild(tr);
        }
    });
    const ctx = document.getElementById('chartHistorico');
    if (!ctx) return;
    const colorNC = coloresNCPorTipo[tipoHistorico] || coloresNCPorTipo.todos;
    const colorOM = coloresOMPorTipo[tipoHistorico] || coloresOMPorTipo.todos;
    const borderColorNC = colorNC.replace('0.85', '1');
    const borderColorOM = colorOM.replace('0.85', '1');
    chartHistorico = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: anios,
            datasets: [
                {
                    label: 'No Conformidades',
                    data: dataNc,
                    backgroundColor: colorNC,
                    borderColor: borderColorNC,
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6
                },
                {
                    label: 'Oport. de Mejora',
                    data: dataOm,
                    backgroundColor: colorOM,
                    borderColor: borderColorOM,
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { size: 13 } } },
                tooltip: { callbacks: { label: c => `${c.dataset.label}: ${c.raw}` } }
            },
            scales: {
                x: { ticks: { font: { size: 12 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Total' } }
            }
        }
    });
}

// CAMBIA EL TIPO ACTIVO EN EL MODAL DE HISTÓRICO Y REGENERA LA GRÁFICA
function seleccionarTipoHistorico(tipo) {
    tipoHistorico = tipo;
    document.getElementById('btnHistTodos').className   = 'btn-tipo' + (tipo === 'todos'   ? ' activo-todos'   : '');
    document.getElementById('btnHistInterna').className = 'btn-tipo' + (tipo === 'Interna' ? ' activo-interna' : '');
    document.getElementById('btnHistExterna').className = 'btn-tipo' + (tipo === 'Externa' ? ' activo-externa' : '');
    renderHistorico();
}

// ============================================================
// 4.  EVENTOS PARA ABRIR MODALES DE ESTADÍSTICAS E HISTÓRICO
// ============================================================

// AL HACER CLIC EN "ESTADÍSTICAS", CARGA LOS AÑOS Y MUESTRA EL MODAL
document.getElementById('btnEstadisticas').addEventListener('click', () => {
    const anios = obtenerAniosUnicosCompletos();
    tipoEstadisticas = 'todos';
    seleccionarTipoEstadisticas('todos');
    new bootstrap.Modal(document.getElementById('modalEstadisticas')).show();
    const sel = document.getElementById('selectAnioEstadisticas');
    sel.innerHTML = '';
    if (anios.length === 0) {
        sel.innerHTML = '<option value="0">Sin años disponibles</option>';
        actualizarTarjetas(0, 0, 0); actualizarGraficaPorProceso({}); actualizarListaProcesos([]);
    } else {
        anios.forEach(a => { const o = document.createElement('option'); o.value = a; o.textContent = a; sel.appendChild(o); });
        sel.value = anios[0];
        refrescarEstadisticas();
    }
});

// AL HACER CLIC EN "HISTÓRICO", MUESTRA EL MODAL Y GENERA LA GRÁFICA
document.getElementById('btnHistorico').addEventListener('click', () => {
    tipoHistorico = 'todos';
    seleccionarTipoHistorico('todos');
    new bootstrap.Modal(document.getElementById('modalHistorico')).show();
    renderHistorico();
});

// AL CERRAR EL MODAL DE ESTADÍSTICAS, DESTRUYE LA GRÁFICA PARA LIBERAR MEMORIA
document.getElementById('modalEstadisticas').addEventListener('hidden.bs.modal', () => {
    if (chartPorProceso) { chartPorProceso.destroy(); chartPorProceso = null; }
});

// AL CERRAR EL MODAL DE HISTÓRICO, DESTRUYE LA GRÁFICA PARA LIBERAR MEMORIA
document.getElementById('modalHistorico').addEventListener('hidden.bs.modal', () => {
    if (chartHistorico) { chartHistorico.destroy(); chartHistorico = null; }
});

// ============================================================
// 5.  FUNCIONES ORIGINALES PARA EL CRUD Y MANEJO DE FORMULARIOS
// ============================================================

// FUNCIÓN VACÍA POR COMPATIBILIDAD (EL DATERANGEPICKER NO SE USA ACTUALMENTE)
function inicializarDateRangePicker() {}

// AL SELECCIONAR UNA AUDITORÍA RELACIONADA, COPIA SUS FECHAS AL CAMPO DE PERIODO
document.getElementById('fAuditoriaRel').addEventListener('change', function () {
    const selected    = this.options[this.selectedIndex];
    const fechaInicio = selected.dataset.fechaInicio;
    const fechaFin    = selected.dataset.fechaFin;
    if (fechaInicio && fechaFin) {
        const fmtInicio = fechaInicio.split('-').reverse().join('/');
        const fmtFin    = fechaFin.split('-').reverse().join('/');
        document.getElementById('rango_fechas_auditoria').value = fmtInicio + ' - ' + fmtFin;
        document.getElementById('fecha_inicio').value = fechaInicio;
        document.getElementById('fecha_fin').value    = fechaFin;
        document.getElementById('rango_fechas_auditoria').classList.remove('is-invalid');
        const fb = document.getElementById('rango-fechas-feedback');
        if (fb) fb.style.display = 'none';
    } else {
        document.getElementById('rango_fechas_auditoria').value = '';
        document.getElementById('fecha_inicio').value = '';
        document.getElementById('fecha_fin').value    = '';
    }
});

// OBTIENE LA INSTANCIA DE UN MODAL DE BOOTSTRAP O CREA UNA NUEVA SI NO EXISTE
function getModalInstance(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return null;
    let inst = bootstrap.Modal.getInstance(el);
    if (!inst) inst = new bootstrap.Modal(el, { backdrop: true, keyboard: true });
    return inst;
}

// LIMPIA EL BUSCADOR Y APLICA LOS FILTROS PARA VOLVER A CARGAR LA TABLA
function limpiarBuscador() {
    const b = document.getElementById('inputBuscar');
    if (b) { b.value = ''; aplicarFiltros(); b.focus(); }
}

// CAMBIA EL AÑO SELECCIONADO EN EL FILTRO Y RECARGA LA TABLA
function seleccionarAnio(anio, texto) {
    anioSeleccionado = anio;
    document.getElementById('anioTexto').innerText = texto;
    document.getElementById('btnAnio').classList.toggle('seleccionado', anio !== '');
    aplicarFiltros();
}

// CAMBIA EL TIPO SELECCIONADO EN EL FILTRO Y RECARGA LA TABLA
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

// CAMBIA EL CRITERIO DE ORDEN SELECCIONADO Y RECARGA LA TABLA
function seleccionarOrden(criterio, texto) {
    ordenSeleccionado = criterio;
    document.getElementById('ordenarTexto').innerText = texto;
    if (criterio) document.getElementById('btnOrdenar').classList.add('seleccionado');
    aplicarFiltros();
}

// CONSTRUYE LA URL CON LOS PARÁMETROS DE FILTRO Y RECARGA LA PÁGINA
function aplicarFiltros() {
    const params = new URLSearchParams();
    const buscar = document.getElementById('inputBuscar').value;
    if (buscar)            params.set('buscar', buscar);
    if (anioSeleccionado)  params.set('anio', anioSeleccionado);
    if (tipoSeleccionado)  params.set('tipo', tipoSeleccionado);
    if (ordenSeleccionado) params.set('orden', ordenSeleccionado);
    window.location.href = ROUTES.index + (params.toString() ? '?' + params.toString() : '');
}

// RESETEA EL FORMULARIO DEL MODAL A SU ESTADO INICIAL (SIN DATOS)
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
    document.getElementById('rango_fechas_auditoria').value = '';
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value    = '';
    document.getElementById('cuerpoTablaNcOm').innerHTML = '';
    document.getElementById('tablaNcOmPorProceso').style.display = 'none';
    document.getElementById('totalNcDisplay').textContent = '0';
    document.getElementById('totalOmDisplay').textContent = '0';
    document.getElementById('fNoConf').value = '0';
    document.getElementById('fOport').value  = '0';
    limpiarErroresValidacion();
}

// QUITA LAS CLASES DE ERROR DE TODOS LOS CAMPOS DEL FORMULARIO
function limpiarErroresValidacion() {
    document.querySelectorAll('#formInforme .form-control, #formInforme .form-select, #fProcesos, #dragArea, #rango_fechas_auditoria')
        .forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('#formInforme .invalid-feedback').forEach(el => el.style.display = 'none');
}

// VALIDA TODOS LOS CAMPOS REQUERIDOS DEL FORMULARIO Y MARCA LOS QUE ESTÁN VACÍOS
// RETORNA UN OBJETO CON: valido (bool) Y primerCampoInvalido (elemento DOM)
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

// RECONSTRUYE LA TABLA DE NC/OM POR PROCESO BASÁNDOSE EN LOS PROCESOS SELECCIONADOS
// CONSERVA LOS VALORES INGRESADOS AL CAMBIAR LA SELECCIÓN DE PROCESOS
function reconstruirTablaNcOm() {
    const procesosSeleccionados = Array.from(document.querySelectorAll('.proceso-check:checked')).map(cb => cb.value);
    const tabla = document.getElementById('tablaNcOmPorProceso');
    const tbody = document.getElementById('cuerpoTablaNcOm');
    if (procesosSeleccionados.length === 0) { tabla.style.display = 'none'; tbody.innerHTML = ''; actualizarTotalesNcOm(); return; }
    tabla.style.display = 'block';
    const valoresActuales = {};
    tbody.querySelectorAll('tr').forEach(tr => {
        const proc = tr.dataset.proceso;
        if (proc) { 
            valoresActuales[proc] = { 
                nc: tr.querySelector('.input-nc')?.value ?? '0', 
                om: tr.querySelector('.input-om')?.value ?? '0',
                criterio: tr.querySelector('.input-criterio')?.value ?? ''
            }; 
        }
    });
    tbody.innerHTML = '';
    procesosSeleccionados.forEach((proc, idx) => {
        const nc = valoresActuales[proc]?.nc ?? '';
        const om = valoresActuales[proc]?.om ?? '';
        const criterio = valoresActuales[proc]?.criterio ?? '';
        const bg = '';
        const tr = document.createElement('tr');
        tr.dataset.proceso = proc;
        tr.style.cssText = bg;
        tr.innerHTML = `<td style="padding:6px 12px; border-bottom:1px solid #dee2e6; vertical-align:middle;"><i class="bi bi-diagram-3 me-1" style="color:#000000; font-size:0.8rem;"></i>${_escHtml(proc)}</td>
                        <td style="padding:6px 12px; border-bottom:1px solid #dee2e6; vertical-align:middle;">
                            <input type="text" class="form-control form-control-sm input-criterio" name="criterio_por_proceso[${_escAttr(proc)}]" value="${_escAttr(criterio)}" placeholder="Criterio" style="width:100%;">
                        </td>
                        <td style="padding:6px 12px; border-bottom:1px solid #dee2e6; text-align:center;">
                            <input type="number" class="form-control form-control-sm input-nc" name="nc_por_proceso[${_escAttr(proc)}]" min="0" value="${_escAttr(nc)}" placeholder="Ej:2" style="width:80px; margin:0 auto; text-align:center;" oninput="actualizarTotalesNcOm()">
                        </td>
                        <td style="padding:6px 12px; border-bottom:1px solid #dee2e6; text-align:center;">
                            <input type="number" class="form-control form-control-sm input-om" name="om_por_proceso[${_escAttr(proc)}]" min="0" value="${_escAttr(om)}" placeholder="Ej:2" style="width:80px; margin:0 auto; text-align:center;" oninput="actualizarTotalesNcOm()">
                        </td>`;
        tbody.appendChild(tr);
    });
    actualizarTotalesNcOm();
}

// SUMA LOS VALORES DE NC Y OM DE TODOS LOS PROCESOS Y ACTUALIZA LOS TOTALES VISIBLES
function actualizarTotalesNcOm() {
    let totalNc = 0, totalOm = 0;
    document.querySelectorAll('#cuerpoTablaNcOm .input-nc').forEach(i => { totalNc += Math.max(0, parseInt(i.value) || 0); });
    document.querySelectorAll('#cuerpoTablaNcOm .input-om').forEach(i => { totalOm += Math.max(0, parseInt(i.value) || 0); });
    document.getElementById('totalNcDisplay').textContent = totalNc;
    document.getElementById('totalOmDisplay').textContent = totalOm;
    document.getElementById('fNoConf').value = totalNc;
    document.getElementById('fOport').value  = totalOm;
}

// FUNCIONES DE ESCAPE PARA PREVENIR INYECCIÓN DE HTML Y ATRIBUTOS EN EL DOM
function _escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function _escAttr(str) { return String(str).replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

// AL HACER CLIC EN "REGISTRAR INFORME", RESETEA Y ABRE EL MODAL EN MODO CREACIÓN
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

// CIERRA EL MODAL Y RESETEA EL FORMULARIO AL TERMINAR LA ANIMACIÓN DE CIERRE
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

// VALIDA EL FORMULARIO Y ENVÍA LOS DATOS AL SERVIDOR VÍA FETCH
// MUESTRA ÉXITO O ERRORES SEGÚN LA RESPUESTA
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

// MUESTRA LOS ERRORES DEL SERVIDOR EN EL CONTENEDOR DE ERRORES DEL FORMULARIO
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

// MUESTRA UN MENSAJE DE ÉXITO VERDE EN LA PARTE SUPERIOR DE LA PÁGINA
// SE ELIMINA AUTOMÁTICAMENTE DESPUÉS DE 5 SEGUNDOS
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

// CARGA LOS DATOS DEL INFORME DESDE EL SERVIDOR Y ABRE EL MODAL EN MODO EDICIÓN
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
            const fmtI = inf.fecha_inicio.split('-').reverse().join('/');
            const fmtF = inf.fecha_fin.split('-').reverse().join('/');
            document.getElementById('rango_fechas_auditoria').value = fmtI + ' - ' + fmtF;
            document.getElementById('fecha_inicio').value = inf.fecha_inicio;
            document.getElementById('fecha_fin').value    = inf.fecha_fin;
        } else if (inf.fecha_auditoria) {
            const fmtA = inf.fecha_auditoria.split('-').reverse().join('/');
            document.getElementById('rango_fechas_auditoria').value = fmtA;
            document.getElementById('fecha_inicio').value = inf.fecha_auditoria;
            document.getElementById('fecha_fin').value    = inf.fecha_auditoria;
        }
        document.getElementById('fAuditoriaRel').value = inf.auditoria_relacionada_id ?? '';
        document.getElementById('fNoConf').value = inf.no_conformidades;
        document.getElementById('fOport').value  = inf.oportunidades_mejora;
        const procSel = inf.procesos_auditados ?? [];
        document.querySelectorAll('.proceso-check').forEach(cb => { cb.checked = procSel.includes(cb.value); });
        // SI HAY PROCESOS QUE NO ESTÁN EN LA LISTA BASE, LOS AGREGA DINÁMICAMENTE
        procSel.forEach(proc => {
            const existe = Array.from(document.querySelectorAll('.proceso-check')).some(cb => cb.value === proc);
            if (!existe) {
                const id_cb = 'proc_new_' + Date.now() + '_' + Math.random().toString(36).substr(2,4);
                const div = document.createElement('div');
                div.className = 'form-check d-flex align-items-center gap-1';
                div.innerHTML = `<input class="form-check-input proceso-check" type="checkbox" name="procesos_auditados[]" value="${proc}" id="${id_cb}" checked><label class="form-check-label flex-grow-1" for="${id_cb}">${proc}</label><button type="button" class="btn-eliminar-proceso" onclick="this.closest('.form-check').remove(); reconstruirTablaNcOm();" title="Eliminar proceso"><i class="bi bi-x"></i></button>`;
                document.getElementById('fProcesos').appendChild(div);
                div.querySelector('input').addEventListener('change', () => { reconstruirTablaNcOm(); });
            }
        });
        reconstruirTablaNcOm();
        // CARGA LOS VALORES DE NC, OM Y CRITERIO GUARDADOS EN EL INFORME
        const ncOmGuardado = inf.nc_om_por_proceso ?? [];
        ncOmGuardado.forEach(item => {
            const tr = document.querySelector(`#cuerpoTablaNcOm tr[data-proceso="${CSS.escape(item.proceso)}"]`);
            if (tr) {
                const inputNc = tr.querySelector('.input-nc');
                const inputOm = tr.querySelector('.input-om');
                const inputCriterio = tr.querySelector('.input-criterio');
                if (inputNc) inputNc.value = item.nc ?? 0;
                if (inputOm) inputOm.value = item.om ?? 0;
                if (inputCriterio && item.criterio !== undefined) inputCriterio.value = item.criterio;
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

// MUESTRA UNA ALERTA DE CONFIRMACIÓN ANTES DE ELIMINAR EL INFORME
// SI EL USUARIO CONFIRMA, ENVÍA LA PETICIÓN DELETE AL SERVIDOR
function eliminarInforme(id, nombre) {
    Swal.fire({
        title: '¿Eliminar informe?',
        text: `¿Estás seguro de eliminar "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Eliminando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            fetch(ROUTES.destroy(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' } })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: '¡Eliminado!', text: data.message, confirmButtonColor: '#000000', timer: 2000, showConfirmButton: false  }).then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al eliminar', confirmButtonColor: '#000000' });
                }
            })
            .catch(error => { console.error('Error:', error); Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', confirmButtonColor: '#000000',showConfirmButton: false  }); });
        }
    });
}

// ABRE EL MODAL DE VISUALIZACIÓN CON EL DOCUMENTO DEL INFORME
// LOS PDF SE CARGAN EN EL IFRAME, OTROS FORMATOS MUESTRAN UN MENSAJE DE DESCARGA
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

// AL CERRAR EL MODAL DE DOCUMENTO, LIMPIA EL IFRAME PARA LIBERAR RECURSOS
document.getElementById('modalDocumento').addEventListener('hidden.bs.modal', () => {
    document.getElementById('iframeDoc').src = 'about:blank';
});

// ── FILTROS DE LA TABLA (BASADOS EN LA TABLA VISIBLE) ──────────────────────────────────────

// OBTIENE LOS AÑOS ÚNICOS DE LAS FILAS VISIBLES EN LA TABLA PARA EL MENÚ DE FILTROS
function obtenerAniosUnicos() {
    const s = new Set();
    document.querySelectorAll('#tablaInformes tbody tr').forEach(f => {
        const a = f.cells[5]?.textContent.trim(); // Año está en índice 5
        if (a) s.add(a);
    });
    return Array.from(s).sort();
}

// LLENA EL MENÚ DESPLEGABLE DE AÑOS CON LOS AÑOS ENCONTRADOS EN LA TABLA
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

// OBTIENE EL VALOR DE UN PARÁMETRO DE LA URL ACTUAL
function getUrlParameter(name) { return new URLSearchParams(window.location.search).get(name); }

// RESTAURA EL ESTADO DE LOS FILTROS DESDE LOS PARÁMETROS DE LA URL AL CARGAR LA PÁGINA
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

// ── AGREGAR NUEVO PROCESO ─────────────────────────────────────────────────────

// AGREGA UN NUEVO PROCESO A LA LISTA DEL FORMULARIO Y ACTUALIZA LA TABLA DE NC/OM
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
    div.querySelector('input').addEventListener('change', () => { reconstruirTablaNcOm(); });
    input.value = '';
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    input.focus();
    document.getElementById('fProcesos').classList.remove('is-invalid');
    document.getElementById('fProcesos-feedback').style.display = 'none';
    reconstruirTablaNcOm();
}

// ── AUTOCOMPLETE DE PROCESOS ─────────────────────────────────────────────────
// PERMITE BUSCAR Y SELECCIONAR PROCESOS EXISTENTES O AGREGAR NUEVOS

let _procCache = null; // CACHÉ DE PROCESOS PARA EVITAR MÚLTIPLES LLAMADAS AL SERVIDOR
let _procIdx   = -1;   // ÍNDICE DEL ELEMENTO SELECCIONADO CON LAS FLECHAS DEL TECLADO

// OBTIENE LOS PROCESOS CUSTOM DEL SERVIDOR (CON CACHÉ PARA EVITAR PETICIONES REPETIDAS)
async function _fetchProcesos() {
    if (_procCache !== null) return _procCache;
    try {
        const r = await fetch(ROUTES.procesosCustom, { headers: { 'Accept': 'application/json' } });
        _procCache = await r.json();
    } catch (e) { _procCache = []; }
    return _procCache;
}

// MUESTRA EL DROPDOWN DE AUTOCOMPLETADO CON LOS PROCESOS QUE COINCIDEN CON LA BÚSQUEDA
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
    // SI EL TEXTO INGRESADO NO EXISTE EN LA LISTA, MUESTRA OPCIÓN PARA AGREGARLO NUEVO
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

// SELECCIONA UN PROCESO DEL DROPDOWN Y LO AGREGA A LA LISTA
function _seleccionarProceso(nombre) {
    document.getElementById('nuevoProceso').value = nombre;
    document.getElementById('autocomplete-procesos').style.display = 'none';
    agregarNuevoProceso();
}

// ── INICIALIZACIÓN AL CARGAR LA PÁGINA ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    inicializarDateRangePicker();
    cargarAniosEnFiltro();
    inicializarFiltrosDesdeURL();

    // APLICA FILTROS EN TIEMPO REAL AL ESCRIBIR EN EL BUSCADOR
    const b = document.getElementById('inputBuscar');
    if (b) b.addEventListener('keyup', () => aplicarFiltros());

    // AL CERRAR EL MODAL DE INFORME, RESETEA EL FORMULARIO
    const mi = document.getElementById('modalInforme');
    if (mi) {
        mi.addEventListener('hidden.bs.modal', () => resetForm());
        mi.addEventListener('hide.bs.modal', () => { const f = document.getElementById('fDocumento'); if (f) f.value = ''; });
    }

    // AL CERRAR EL MODAL DE ESTADÍSTICAS, DESTRUYE LA GRÁFICA
    const mes = document.getElementById('modalEstadisticas');
    if (mes) mes.addEventListener('hidden.bs.modal', () => { if (chartPorProceso) { chartPorProceso.destroy(); chartPorProceso = null; } });

    // LIMPIA LOS ERRORES DE VALIDACIÓN AL ESCRIBIR EN LOS CAMPOS DEL FORMULARIO
    document.querySelectorAll('#formInforme .form-control, #formInforme .form-select').forEach(c => {
        const clear = function() { this.classList.remove('is-invalid'); const el = document.getElementById(this.id + '-feedback'); if (el) el.style.display = 'none'; };
        c.addEventListener('input', clear);
        c.addEventListener('change', clear);
    });

    // AL SELECCIONAR UN PROCESO, RECONSTRUYE LA TABLA DE NC/OM
    document.querySelectorAll('.proceso-check').forEach(cb => {
        cb.addEventListener('change', () => {
            if (document.querySelectorAll('.proceso-check:checked').length > 0) {
                document.getElementById('fProcesos').classList.remove('is-invalid');
                document.getElementById('fProcesos-feedback').style.display = 'none';
            }
            reconstruirTablaNcOm();
        });
    });

    // AL SELECCIONAR UN ARCHIVO, QUITA EL ERROR DE VALIDACIÓN DEL ÁREA DE DRAG & DROP
    document.getElementById('fDocumento').addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            document.getElementById('dragArea').classList.remove('is-invalid');
            document.getElementById('fDocumento-feedback').style.display = 'none';
        }
    });

    // CONFIGURACIÓN DEL CAMPO DE NUEVO PROCESO CON AUTOCOMPLETADO Y NAVEGACIÓN POR TECLADO
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

    // CONFIGURACIÓN DEL ÁREA DE DRAG & DROP PARA SUBIR ARCHIVOS
    const dragArea = document.getElementById('dragArea');
    if (dragArea) {
        // AL ARRASTRAR UN ARCHIVO SOBRE EL ÁREA, CAMBIA EL ESTILO VISUAL
        ['dragenter', 'dragover'].forEach(evt => {
            dragArea.addEventListener(evt, function(e) { e.preventDefault(); e.stopPropagation(); dragArea.style.backgroundColor = '#fff0f0'; dragArea.style.borderColor = '#00000'; });
        });
        // AL SALIR O SOLTAR, RESTAURA EL ESTILO ORIGINAL
        ['dragleave', 'drop'].forEach(evt => {
            dragArea.addEventListener(evt, function(e) { e.preventDefault(); e.stopPropagation(); dragArea.style.backgroundColor = ''; dragArea.style.borderColor = ''; });
        });
        // AL SOLTAR EL ARCHIVO, LO ASIGNA AL INPUT DE ARCHIVO Y ACTUALIZA EL LABEL
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
    
    // ============================================================
    // 6.  MEJORAS DE RESPONSIVIDAD PARA GRÁFICAS
    //     AJUSTA EL TAMAÑO DE FUENTE Y LA ROTACIÓN DE ETIQUETAS SEGÚN EL ANCHO DE PANTALLA
    // ============================================================

    // AJUSTA EL TAMAÑO DE FUENTE Y ROTACIÓN DE LAS GRÁFICAS SEGÚN EL ANCHO DE LA PANTALLA
    function ajustarTamanioGraficas() {
        const anchoPantalla = window.innerWidth;
        
        if (chartPorProceso) {
            const options = chartPorProceso.options;
            if (anchoPantalla < 768) {
                options.plugins.legend.labels.font.size = 10;
                options.scales.x.ticks.font.size = 8;
                options.scales.x.ticks.maxRotation = 45;
                options.scales.x.ticks.minRotation = 45;
            } else {
                options.plugins.legend.labels.font.size = 13;
                options.scales.x.ticks.font.size = 11;
                options.scales.x.ticks.maxRotation = 35;
                options.scales.x.ticks.minRotation = 0;
            }
            chartPorProceso.update();
        }
        
        if (chartHistorico) {
            const options = chartHistorico.options;
            if (anchoPantalla < 768) {
                options.plugins.legend.labels.font.size = 10;
                options.scales.x.ticks.font.size = 9;
            } else {
                options.plugins.legend.labels.font.size = 13;
                options.scales.x.ticks.font.size = 12;
            }
            chartHistorico.update();
        }
    }

    // AL ABRIR EL MODAL DE ESTADÍSTICAS, REDIMENSIONA LA GRÁFICA PARA QUE SE VEA BIEN
    document.getElementById('modalEstadisticas').addEventListener('shown.bs.modal', function() {
        setTimeout(() => {
            if (chartPorProceso) {
                chartPorProceso.resize();
                ajustarTamanioGraficas();
            }
        }, 100);
    });

    // AL ABRIR EL MODAL DE HISTÓRICO, REDIMENSIONA LA GRÁFICA PARA QUE SE VEA BIEN
    document.getElementById('modalHistorico').addEventListener('shown.bs.modal', function() {
        setTimeout(() => {
            if (chartHistorico) {
                chartHistorico.resize();
                ajustarTamanioGraficas();
            }
        }, 100);
    });

    // AL CAMBIAR EL TAMAÑO DE LA VENTANA, REDIMENSIONA AMBAS GRÁFICAS AUTOMÁTICAMENTE
    window.addEventListener('resize', function() {
        setTimeout(() => {
            if (chartPorProceso) chartPorProceso.resize();
            if (chartHistorico) chartHistorico.resize();
            ajustarTamanioGraficas();
        }, 200);
    });
});
</script>
@endpush