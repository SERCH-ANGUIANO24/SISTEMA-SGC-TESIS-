@extends('layouts.app')

@section('title', 'Solicitudes de Mejora - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <!-- Header con ícono de carpeta -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- MENSAJE DE ÉXITO -->
            <div id="mensajeExitoContainer"></div>
            
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                    <h1 class="h3 mb-0" style="color: #dc2626; cursor: pointer;">
                        <i class="bi-arrow-up-circle me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                        Solicitud de Mejora
                    </h1>
                </a>
                
                {{-- Solo admin y superadmin pueden registrar solicitudes --}}
                @can('auditoria-access')
                <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud" style="background-color: #737373; color: white; border: none;">
                    <i class="bi bi-plus-circle"></i> Registrar Solicitud
                </button>
                @endcan
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    @include('auditoria.solicitudes.partials.filtros')

    <!-- TABLA DE SOLICITUDES -->
    @include('auditoria.solicitudes.partials.tabla')
</div>

<!-- MODAL PARA REGISTRAR/EDITAR SOLICITUD (solo admin y superadmin) -->
@can('auditoria-access')
@include('auditoria.solicitudes.modal.modal_solicitud')
@endcan

<!-- MODAL PARA VER ARCHIVOS (visible para todos) -->
@include('auditoria.solicitudes.modal.modal_ver_archivo')

<!-- MODAL PARA VER CALENDARIO (visible para todos) -->
@include('auditoria.solicitudes.modal.modal_calendario')

<!-- CONTENEDOR PARA MODALES DINÁMICOS -->
<div id="modalesContainer"></div>

<!-- MODAL GRÁFICAS (visible para todos) -->
@include('auditoria.solicitudes.modal.modal_graficas')

<!-- MODAL HISTÓRICO (visible para todos) -->
@include('auditoria.solicitudes.modal.modal_historico')

@endsection

@push('styles')
<style>
    /* ===== ESTILOS DE LA CARD (TABLA) ===== */
    .table {
        font-size: 0.9rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        color: #212529;
        text-align: center;
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
        border-top: 2px solid #dee2e6 !important;
        font-weight: 600;
        padding: 12px 8px;
    }

    .table td {
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
        padding: 12px 8px;
    }
    
    /* Badges */
    .badge-no-atendida {
        background-color: #fd7e14;
        color: white;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .badge-proceso {
        background-color: #ffc107;
        color: white;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .badge-cerrado {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .btn-light {
        background-color: white !important;
        color: #6c757d;
        border: 1px solid #ced4da;
        height: 42px;
        padding: 0 15px;
    }
    
    .btn-light:hover {
        background-color: #f8f9fa !important;
        border-color: #800000;
    }
    
    .btn-light.seleccionado {
        background-color: #e9ecef !important;
        border-color: #737373;
        color: #495057;
    }
    
    .dropdown-item:hover {
        background-color: #737373 !important;
        color: #ffffff !important;
    }
    
    .dropdown-item.active {
        background-color: #800000 !important;
        color: white !important;
    }
    
    #limpiarBusqueda {
        transition: all 0.2s ease;
        border-color: #ced4da;
        background-color: white;
        width: 42px;
        height: 42px;
    }
    
    #limpiarBusqueda:hover {
        background-color: #f8f9fa;
        border-color: #800000;
    }
    
    #limpiarBusqueda:hover i {
        color: #800000;
    }
    
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
    }
    
    .border.rounded.p-4.bg-light {
        border: 2px dashed #000000 !important;
        transition: all 0.3s ease;
    }
    
    .border.rounded.p-4.bg-light:hover {
        background-color: #fff !important;
        border-color: #000000 !important;
    }

    .modal-xl {
        max-width: 90%;
    }
    
    .modal-body {
        background-color: #ffffff;
        height: 80vh;
        overflow: auto;
    }
    
    .modal-body iframe,
    .modal-body embed {
        width: 100%;
        height: 100%;
        border: none;
    }

    .documento-nombre {
        color: #495057;
        font-size: 0.9rem;
    }
    
    /* ===== ESTILOS DE BOTONES ===== */
    .btn-outline-info {
        color: #0dcaf0;
        border-color: #0dcaf0;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-info:hover {
        color: #fff;
        background-color: #0dcaf0;
        border-color: #0dcaf0;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-warning {
        color: #6f42c1;
        border-color: #6f42c1;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-warning:hover {
        color: #fff;
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
    .btn-outline-warning i {
        color: #6f42c1;
    }
    .btn-outline-warning:hover i {
        color: #fff;
    }

    .btn-outline-primary {
        color: #0d6efd;
        border-color: #0d6efd;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-primary:hover {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    .btn-outline-warning {
        --bs-btn-color: #6f42c1;
        --bs-btn-border-color: #6f42c1;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #6f42c1;
        --bs-btn-hover-border-color: #6f42c1;
        --bs-btn-focus-shadow-rgb: 111, 66, 193;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #6f42c1;
        --bs-btn-active-border-color: #6f42c1;
    }

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

    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* ===== ESTILOS MEJORADOS PARA EL CRONÓMETRO ===== */
    .cronometro-info {
        background-color: #e9ecef;
        border-left: 4px solid #6c757d;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .cronometro-activo {
        background-color: #f8f9fa;
        border-left: 4px solid #6c757d;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .cronometro-completado {
        background-color: #f1f3f5;
        border-left: 4px solid #495057;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .cronometro-completado strong {
        color: #495057;
        font-size: 1.2rem;
        display: block;
        margin-bottom: 8px;
    }
    
    .cronometro-completado i {
        color: #6c757d;
        font-size: 2rem;
        margin-bottom: 10px;
    }
    
    /* Estilo para alertas en gris */
    .alert-info-custom {
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        color: #495057;
        border-radius: 6px;
        padding: 12px 15px;
    }
    
    .alert-warning-custom {
        background-color: #f8f9fa;
        border: 1px solid #adb5bd;
        color: #495057;
        border-radius: 6px;
        padding: 12px 15px;
    }
    
    .alert-secondary-custom {
        background-color: #f1f3f5;
        border: 1px solid #6c757d;
        color: #495057;
        border-radius: 6px;
        padding: 12px 15px;
    }

    /* Estilo para solicitud cerrada */
    .solicitud-cerrada {
        background-color: #f8f9fa;
        border: 2px solid #6c757d;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        margin: 20px 0;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    
    .solicitud-cerrada i {
        font-size: 4rem;
        color: #6c757d;
        margin-bottom: 15px;
    }
    
    .solicitud-cerrada h4 {
        color: #495057;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .solicitud-cerrada p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    /* ===== FORZAR TAMAÑO CORRECTO DEL MODAL DE TEMA EN SOLICITUDES ===== */
    #modalTema .modal-body {
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    #modalTema .modal-dialog {
        max-width: 380px !important;
        width: 380px !important;
    }

    #modalTema .modal-content {
        max-width: 380px !important;
    }

    #modalTema {
        --bs-modal-width: 380px !important;
    }

    /* =====================================================
       ESTILOS RESPONSIVOS - SOLICITUDES DE MEJORA
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
            font-size: 0.75rem !important;
            padding: 8px 6px !important;
        }
        .btn-sm {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.65rem !important;
        }
        .badge-no-atendida, .badge-proceso, .badge-cerrado {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        .documento-nombre {
            max-width: 120px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            display: inline-block !important;
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
        .modal-dialog {
            max-width: 95% !important;
            margin: 1rem auto !important;
        }
        .d-flex.align-items-center.gap-3.flex-wrap {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        .dropdown .btn, #btnOrdenar, #btnAnio, #btnEstatus {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.75rem !important;
            height: 38px !important;
        }
        .cronometro-info, .cronometro-activo, .cronometro-completado {
            padding: 12px !important;
        }
        .cronometro-info i, .cronometro-activo i, .cronometro-completado i {
            font-size: 1.5rem !important;
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
        .filtros-container {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.75rem !important;
        }
        .search-wrapper {
            width: 100% !important;
        }
        .d-flex.align-items-center.position-relative[style*="width: 700px"] {
            width: 100% !important;
        }
        .dropdown {
            width: 100% !important;
        }
        .dropdown .btn, #btnOrdenar, #btnAnio, #btnEstatus {
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
            font-size: 0.7rem !important;
            padding: 6px 4px !important;
        }
        .btn-sm {
            padding: 0.15rem 0.25rem !important;
            font-size: 0.6rem !important;
        }
        .btn-sm i {
            font-size: 0.65rem !important;
        }
        .badge-no-atendida, .badge-proceso, .badge-cerrado {
            font-size: 0.6rem !important;
            padding: 0.15rem 0.3rem !important;
        }
        .documento-nombre {
            max-width: 80px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            display: inline-block !important;
            font-size: 0.7rem !important;
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
            height: 70vh !important;
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
        .cronometro-info, .cronometro-activo, .cronometro-completado {
            padding: 10px !important;
        }
        .cronometro-info i, .cronometro-activo i, .cronometro-completado i {
            font-size: 1.3rem !important;
        }
        .cronometro-info h5, .cronometro-activo h5, .cronometro-completado h5 {
            font-size: 0.9rem !important;
        }
        .cronometro-info p, .cronometro-activo p, .cronometro-completado p {
            font-size: 0.75rem !important;
        }
        .solicitud-cerrada {
            padding: 15px !important;
        }
        .solicitud-cerrada i {
            font-size: 2.5rem !important;
        }
        .solicitud-cerrada h4 {
            font-size: 1.1rem !important;
        }
        
        /* ===== CORRECCIÓN PARA INPUT FILE EN MÓVIL ===== */
        #archivo_plan {
            width: 100% !important;
            padding: 8px 10px !important;
            font-size: 0.7rem !important;
            margin-top: 8px !important;
            margin-bottom: 8px !important;
            box-sizing: border-box !important;
        }
        
        #archivo_plan::file-selector-button {
            padding: 4px 8px !important;
            font-size: 0.65rem !important;
            margin-right: 6px !important;
        }
        
        #nombreArchivoActual {
            font-size: 0.7rem !important;
            padding: 5px !important;
        }
        
        .msg-error {
            font-size: 0.65rem !important;
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
            font-size: 0.55rem !important;
        }
        .documento-nombre {
            max-width: 60px !important;
        }
        .badge-no-atendida, .badge-proceso, .badge-cerrado {
            font-size: 0.55rem !important;
        }
        .border.rounded.p-4.bg-light i {
            font-size: 1.5rem !important;
        }
        .border.rounded.p-4.bg-light p.mt-2 strong {
            font-size: 0.65rem !important;
        }
        .cronometro-info, .cronometro-activo, .cronometro-completado {
            padding: 8px !important;
        }
        .cronometro-info h6, .cronometro-activo h6, .cronometro-completado h6 {
            font-size: 0.8rem !important;
        }
        
        /* Input file en móviles muy pequeños */
        #archivo_plan {
            padding: 6px 8px !important;
            font-size: 0.65rem !important;
        }
        
        #archivo_plan::file-selector-button {
            padding: 3px 6px !important;
            font-size: 0.6rem !important;
            margin-right: 5px !important;
        }
    }
        /* =====================================================
       CORRECCIÓN FORZADA PARA INPUT FILE EN MÓVIL
    ===================================================== */
    
    /* Eliminar el flex del contenedor problemático */
    .border.rounded.p-4.bg-light .d-flex.justify-content-center {
        display: block !important;
        width: 100% !important;
        text-align: left !important;
    }
    
    /* Forzar que el input file ocupe todo el ancho y esté alineado */
    .border.rounded.p-4.bg-light .d-flex.justify-content-center .form-control,
    .border.rounded.p-4.bg-light .d-flex.justify-content-center #archivo_plan {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 8px 10px !important;
        box-sizing: border-box !important;
    }
    
    /* Móviles (768px y menos) - corrección específica */
    @media (max-width: 768px) {
        .border.rounded.p-4.bg-light {
            padding: 12px !important;
        }
        
        /* Forzar input file en móvil */
        .border.rounded.p-4.bg-light .d-flex.justify-content-center {
            display: block !important;
            width: 100% !important;
        }
        
        #archivo_plan,
        .border.rounded.p-4.bg-light .d-flex.justify-content-center #archivo_plan {
            width: 100% !important;
            padding: 8px 10px !important;
            font-size: 0.7rem !important;
            margin-top: 8px !important;
            margin-bottom: 8px !important;
            box-sizing: border-box !important;
            display: block !important;
        }
        
        #archivo_plan::file-selector-button,
        .border.rounded.p-4.bg-light .d-flex.justify-content-center #archivo_plan::file-selector-button {
            padding: 4px 8px !important;
            font-size: 0.65rem !important;
            margin-right: 6px !important;
        }
        
        #nombreArchivoActual {
            font-size: 0.7rem !important;
            padding: 5px !important;
            text-align: center !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let solicitudesData = [];
    let estatusSeleccionado = '';
    let anioSeleccionado = '';
    let ordenSeleccionado = '';
    const userRole = '{{ Auth::user()->role }}';

    document.addEventListener('DOMContentLoaded', function() {
        cargarSolicitudes();
        configurarEventos();
        
        @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
        const modal = document.getElementById('modalNuevaSolicitud');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                resetForm();
            });
        }
        @endif
    });

    function configurarEventos() {
        @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
        const form = document.getElementById('formSolicitud');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                guardarSolicitud();
            });
        }
        @endif
        
        const buscador = document.getElementById('buscadorArchivos');
        if (buscador) {
            buscador.addEventListener('keyup', function() {
                filtrarPorBusqueda(this.value);
            });
        }
    }

    function limpiarBuscador() {
        const buscador = document.getElementById('buscadorArchivos');
        if (buscador) {
            buscador.value = '';
            filtrarPorBusqueda('');
            buscador.focus();
        }
    }

    function seleccionarOrden(criterio, texto) {
        ordenSeleccionado = criterio;
        document.getElementById('ordenarTexto').innerText = texto;
        document.getElementById('btnOrdenar').classList.add('seleccionado');
        ordenarPor(criterio);
    }

    function seleccionarEstatus(estatus, texto) {
        estatusSeleccionado = estatus;
        document.getElementById('estatusTexto').innerText = texto;
        document.getElementById('btnEstatus').classList.add('seleccionado');
        cargarSolicitudes();
    }

    function seleccionarAnio(anio, texto) {
        anioSeleccionado = anio;
        document.getElementById('anioTexto').innerText = texto;
        
        if (anio !== '') {
            document.getElementById('btnAnio').classList.add('seleccionado');
        } else {
            document.getElementById('btnAnio').classList.remove('seleccionado');
        }
        
        cargarSolicitudes();
    }

    function ordenarPor(criterio) {
        if (!solicitudesData || solicitudesData.length === 0) return;
        
        let datosOrdenados = [...solicitudesData];
        
        switch(criterio) {
            case 'nombre-asc':
                datosOrdenados.sort((a, b) => (a.folio_solicitud || '').localeCompare(b.folio_solicitud || ''));
                break;
            case 'nombre-desc':
                datosOrdenados.sort((a, b) => (b.folio_solicitud || '').localeCompare(a.folio_solicitud || ''));
                break;
            case 'fecha-asc':
                datosOrdenados.sort((a, b) => new Date(a.fecha_solicitud) - new Date(b.fecha_solicitud));
                break;
            case 'fecha-desc':
                datosOrdenados.sort((a, b) => new Date(b.fecha_solicitud) - new Date(a.fecha_solicitud));
                break;
        }
        
        renderizarTabla(datosOrdenados);
    }

    function filtrarPorBusqueda(texto) {
        if (!solicitudesData || solicitudesData.length === 0) return;
        
        texto = texto.toLowerCase().trim();
        
        if (texto === '') {
            renderizarTabla(solicitudesData);
            return;
        }
        
        const datosFiltrados = solicitudesData.filter(solicitud => 
            (solicitud.responsable_accion && solicitud.responsable_accion.toLowerCase().includes(texto)) ||
            (solicitud.folio_solicitud && solicitud.folio_solicitud.toLowerCase().includes(texto))
        );
        
        renderizarTabla(datosFiltrados);
    }

    function cargarSolicitudes() {
        let url = '{{ route("auditoria.solicitudes.data") }}';
        let params = new URLSearchParams();
        
        if (estatusSeleccionado) params.append('estatus', estatusSeleccionado);
        if (anioSeleccionado) params.append('anio', anioSeleccionado);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            solicitudesData = data;
            
            if (ordenSeleccionado) {
                ordenarPor(ordenSeleccionado);
            } else {
                renderizarTabla(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tablaBody').innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error al cargar las solicitudes</td></tr>';
        });
    }

    /**
     * Determina si un archivo se puede visualizar en el navegador.
     * @param {string} filename Nombre del archivo con extensión.
     * @returns {boolean}
     */
    function esArchivoVisualizable(filename) {
        if (!filename) return false;
        const ext = filename.split('.').pop().toLowerCase();
        const visualizables = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'txt'];
        return visualizables.includes(ext);
    }

    function renderizarTabla(data) {
        const tbody = document.getElementById('tablaBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4">No hay solicitudes de mejora registradas</td></tr>';
            return;
        }

        data.forEach(solicitud => {
            const tr = document.createElement('tr');
            
            const fechaSolicitud = solicitud.fecha_solicitud ? new Date(solicitud.fecha_solicitud).toLocaleDateString('es-ES') : '';
            
            // Periodos: extraer mes y año (MM/YYYY)
            const periodoAplicacion = solicitud.fecha_aplicacion 
                ? new Date(solicitud.fecha_aplicacion).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit' }).replace(/\//g, '/')
                : '';
            const periodoVerificacion = solicitud.fecha_verificacion 
                ? new Date(solicitud.fecha_verificacion).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit' }).replace(/\//g, '/')
                : '-';
            
            // Badge según estatus
            let badgeClass = '';
            if (solicitud.estatus === 'No Atendida') badgeClass = 'badge-no-atendida';
            else if (solicitud.estatus === 'En Proceso') badgeClass = 'badge-proceso';
            else if (solicitud.estatus === 'Cerrado') badgeClass = 'badge-cerrado';
            
            // Construir celda de documento con ícono y nombre
            let documentoHtml = '';
            if (solicitud.archivo_nombre) {
                const ext = solicitud.archivo_nombre.split('.').pop().toLowerCase();
                let icono = 'bi-file-earmark';
                let color = '#000000';
                
                if (['pdf'].includes(ext)) {
                    icono = 'bi-file-pdf';
                    color = '#000000';
                } else if (['doc', 'docx'].includes(ext)) {
                    icono = 'bi-file-word';
                    color = '#000000';
                } else if (['xls', 'xlsx'].includes(ext)) {
                    icono = 'bi-file-excel';
                    color = '#000000';
                } else if (['ppt', 'pptx'].includes(ext)) {
                    icono = 'bi-file-ppt';
                    color = '#000000';
                } else if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) {
                    icono = 'bi-file-image';
                    color = '#000000';
                } else if (ext === 'csv') {
                    icono = 'bi-file-spreadsheet';
                    color = '#000000';
                } else if (['txt'].includes(ext)) {
                    icono = 'bi-file-text';
                    color = '#000000';
                } else {
                    icono = 'bi-file-earmark';
                    color = '#000000';
                }
                
                documentoHtml = `
                    <i class="bi ${icono}" style="color: ${color}; margin-right: 4px;"></i>
                    <span class="documento-nombre" title="${solicitud.archivo_nombre}">${solicitud.archivo_nombre}</span>
                `;
            } else {
                documentoHtml = '<span class="text-muted">—</span>';
            }
            
            // Determinar si se muestra el botón "Ver"
            const visualizable = esArchivoVisualizable(solicitud.archivo_nombre);
            
            // Acciones según el rol del usuario
            let acciones = '';
            
            if (userRole === 'admin' || userRole === 'superadmin') {
                // Admin y superadmin tienen todas las acciones
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${solicitud.archivo_nombre && visualizable ? 
                            `<button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="verArchivo(${solicitud.id})" 
                                    title="Ver archivo">
                                <i class="bi bi-eye"></i>
                            </button>` : ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="editarSolicitud(${solicitud.id})"
                                title="Editar solicitud">
                            <i class="bi bi-pencil-square"></i>
                            </button>
                        
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="verCalendario(${solicitud.id})"
                                title="Ver fechas">
                            <i class="bi bi-calendar"></i>
                        </button>
                        
                        ${solicitud.archivo_nombre ? 
                            `<a href="{{ url('auditoria/solicitudes/download') }}/${solicitud.id}" 
                               class="btn btn-sm btn-outline-primary"
                               title="Descargar archivo">
                                <i class="bi bi-download"></i>
                            </a>` : ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="eliminarSolicitud(${solicitud.id}, '${(solicitud.folio_solicitud || '').replace(/'/g, "\\'")}')"
                                title="Eliminar solicitud">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
            } else {
                // Usuario normal solo puede ver calendario, ver archivo y descargar
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${solicitud.archivo_nombre && visualizable ? 
                            `<button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="verArchivo(${solicitud.id})" 
                                    title="Ver archivo">
                                <i class="bi bi-eye"></i>
                            </button>` : ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="verCalendario(${solicitud.id})"
                                title="Ver fechas">
                            <i class="bi bi-calendar"></i>
                        </button>
                        
                        ${solicitud.archivo_nombre ? 
                            `<a href="{{ url('auditoria.solicitudes/download') }}/${solicitud.id}" 
                               class="btn btn-sm btn-outline-primary"
                               title="Descargar archivo">
                                <i class="bi bi-download"></i>
                            </a>` : ''}
                    </div>
                `;
            }
            
            tr.innerHTML = `
                <td class="text-center">${fechaSolicitud}</td>
                <td class="text-center">${solicitud.folio_solicitud || '-'}</td>
                <td>${solicitud.responsable_accion || ''}</td>
                <td class="text-center">${solicitud.procesos_auditados || '<span class="text-muted">—</span>'}</td>
                <td class="text-center">${solicitud.tipo_solicitud ? 
                    `<span style="background-color:${solicitud.tipo_solicitud === 'No Conformidad' ? '#fdeaea' : '#edfde8'}; color:${solicitud.tipo_solicitud === 'No Conformidad' ? '#dc3545' : '#28a745'}; padding: 3px 8px; border-radius: 5px; font-size: 0.8rem; font-weight: 500;">${solicitud.tipo_solicitud}</span>` 
                    : '<span class="text-muted">—</span>'}</td>
                <td class="text-center">${periodoAplicacion}</td>
                <td>${solicitud.actividades_verificacion ? 
                    (solicitud.actividades_verificacion.length > 30 ? 
                        solicitud.actividades_verificacion.substring(0, 30) + '...' : 
                        solicitud.actividades_verificacion) : 
                    '-'}</td>
                <td>${documentoHtml}</td>
                <td class="text-center">${periodoVerificacion}</td>
                <td class="text-center"><span class="${badgeClass}">${solicitud.estatus || ''}</span></td>
                <td class="text-end">${acciones}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ===== FUNCIÓN VER ARCHIVO CORREGIDA =====
    function verArchivo(id) {
        const solicitud = solicitudesData.find(s => s.id === id);
        if (!solicitud) return;

        const url = `/auditoria/solicitudes/ver/${id}`;
        const downloadUrl = `/auditoria/solicitudes/download/${id}`;
        const ext = solicitud.archivo_nombre.split('.').pop().toLowerCase();
        
        const visualizable = esArchivoVisualizable(solicitud.archivo_nombre);
        
        let contenidoVisor = '';
        if (visualizable) {
            if (ext === 'pdf') {
                contenidoVisor = `<iframe src="${url}" style="width:100%; height:80vh;" frameborder="0"></iframe>`;
            } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext)) {
                contenidoVisor = `<img src="${url}" style="max-width:100%; max-height:80vh; display:block; margin:auto;" />`;
            } else if (ext === 'txt') {
                contenidoVisor = `<iframe src="${url}" style="width:100%; height:80vh;" frameborder="0"></iframe>`;
            } else {
                contenidoVisor = `<iframe src="${url}" style="width:100%; height:80vh;" frameborder="0"></iframe>`;
            }
        } else {
            contenidoVisor = `
                <div class="text-center p-5">
                    <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                    <p class="mt-3">Este tipo de archivo no se puede visualizar en el navegador.</p>
                    <a href="${downloadUrl}" class="btn text-white" style="background-color:#800000;">Descargar archivo</a>
                </div>
            `;
        }

        const modalHtml = `
            <div class="modal fade" id="viewDocumentModal${id}" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"> <i class="bi bi-file-earmark-text me-2" style="color: #000000;"></i> ${solicitud.archivo_nombre}</h5>
                            
                        </div>
                        <div class="modal-body p-0">
                            ${contenidoVisor}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="${downloadUrl}" class="btn text-white" style="background-color:#800000;"><i class="bi bi-download me-1"></i> Descargar</a>  
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const container = document.getElementById('modalesContainer');
        container.innerHTML = modalHtml;
        
        const modal = new bootstrap.Modal(document.getElementById(`viewDocumentModal${id}`));
        modal.show();
    }

    /**
     * Calcula los días hábiles entre dos fechas (excluye sábados y domingos)
     */
    function businessDaysBetween(start, end) {
        let count = 0;
        const cur = new Date(start);
        cur.setHours(0, 0, 0, 0);
        const endDate = new Date(end);
        endDate.setHours(0, 0, 0, 0);
        
        while (cur <= endDate) {
            const day = cur.getDay();
            // 0 = domingo, 6 = sábado
            if (day !== 0 && day !== 6) {
                count++;
            }
            cur.setDate(cur.getDate() + 1);
        }
        return count;
    }

    /**
     * Calcula la fecha después de sumar días hábiles
     */
    function addBusinessDays(startDate, days) {
        let result = new Date(startDate);
        result.setHours(0, 0, 0, 0);
        let addedDays = 0;
        
        while (addedDays < days) {
            result.setDate(result.getDate() + 1);
            const day = result.getDay();
            if (day !== 0 && day !== 6) {
                addedDays++;
            }
        }
        return result;
    }

    function verCalendario(id) {
        const solicitud = solicitudesData.find(s => s.id === id);
        if (!solicitud) return;

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        const fechaSolicitud = solicitud.fecha_solicitud ? new Date(solicitud.fecha_solicitud) : null;
        if (fechaSolicitud) fechaSolicitud.setHours(0, 0, 0, 0);

        // ===== SOLICITUD CERRADA =====
        if (solicitud.estatus === 'Cerrado') {
            const contenidoCerrado = `
                <div class="p-4 text-center">
                    <div class="solicitud-cerrada">
                        <i class="bi bi-check-circle-fill"></i>
                        <h4>SOLICITUD CERRADA</h4>
                        <p>Esta solicitud de mejora ha sido cerrada</p>
                    </div>
                </div>
            `;
            document.getElementById('calendarioContent').innerHTML = contenidoCerrado;
            const modal = new bootstrap.Modal(document.getElementById('calendarioModal'));
            modal.show();
            return;
        }

                // ===== SOLICITUD NO ATENDIDA - VERIFICAR SI VENCIÓ EL PLAZO =====
        if (solicitud.estatus === 'No Atendida') {
            const fechaInforme = solicitud.fecha_informe 
                ? (() => { const d = new Date(solicitud.fecha_informe); d.setHours(0,0,0,0); return d; })()
                : null;

            let vencidoPor15Dias = false;
            let vencidoPor27 = false;

            // Verificar si pasaron 15 días hábiles desde el informe
            if (fechaInforme) {
                const diasHabiles = businessDaysBetween(fechaInforme, hoy);
                if (diasHabiles >= 15) vencidoPor15Dias = true;
            }

            // Verificar si ya pasó el día 27 del mes de aplicación
            if (solicitud.fecha_aplicacion) {
                const fechaApli = new Date(solicitud.fecha_aplicacion);
                const dia27MesApli = new Date(fechaApli.getFullYear(), fechaApli.getMonth(), 27);
                dia27MesApli.setHours(0, 0, 0, 0);
                if (hoy > dia27MesApli) vencidoPor27 = true;
            }

            if (solicitud.estatus === 'No Atendida') {
                const contenidoVencido = `
                    <div class="p-4 text-center">
                        <div style="background-color:#f8f9fa;border:2px solid #dc3545;border-radius:10px;padding:30px;text-align:center;margin:20px 0;box-shadow:0 4px 8px rgba(0,0,0,0.05);">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size:4rem;color:#dc3545;margin-bottom:15px;display:block;"></i>
                            <h4 style="color:#dc3545;font-weight:700;margin-bottom:10px;">PLAZO DE ATENCIÓN VENCIDO</h4>
                            <p style="color:#495057;font-size:1.1rem;margin-bottom:5px;">En este momento tu solicitud ha vencido el plazo de atención.</p>
                            <p style="color:#495057;font-size:1.1rem;margin-bottom:15px;">Por lo que es necesario contactarse con la</p>
                            <p style="background-color:#dc3545;color:white;padding:10px 20px;border-radius:8px;font-weight:600;font-size:1.1rem;display:inline-block;">
                                <i class="bi bi-telephone-fill me-2"></i>Coordinación del SGC
                            </p>
                            <div class="mt-3" style="font-size:0.85rem;color:#6c757d;">
                                ${vencidoPor15Dias ? '<p><i class="bi bi-clock me-1"></i> Han transcurrido más de 15 días hábiles desde la fecha del informe.</p>' : ''}
                                ${vencidoPor27 ? '<p><i class="bi bi-calendar-x me-1"></i> Ya pasó el día 27 del mes de aplicación.</p>' : ''}
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('calendarioContent').innerHTML = contenidoVencido;
                const modal = new bootstrap.Modal(document.getElementById('calendarioModal'));
                modal.show();
                return;
            }
        }

        // ===== NOTIFICACIÓN: 3 DÍAS ANTES DEL 27 DE CADA MES =====
        let alertaDia27 = '';
        const dia27Actual = new Date(hoy.getFullYear(), hoy.getMonth(), 27);
        dia27Actual.setHours(0, 0, 0, 0);
        const diffHacia27 = Math.ceil((dia27Actual - hoy) / (1000 * 60 * 60 * 24));

        if (diffHacia27 >= 0 && diffHacia27 <= 3) {
            const diasTexto = diffHacia27 === 0 ? 'hoy' : `en ${diffHacia27} día${diffHacia27 > 1 ? 's' : ''}`;
            alertaDia27 = `
                <div class="alert-warning-custom mt-2">
                    <i class="bi bi-bell-fill me-2"></i>
                    <strong>¡Atención!</strong> El día 27 del mes vence ${diasTexto}.
                    Recuerda verificar el estado de esta solicitud.
                </div>
            `;
        }

        // ===== BLOQUE CRONÓMETRO / ENTREGADA =====
        // Si ya tiene fecha_solicitud → "Tu solicitud fue entregada"
        // Si no → mostrar cronómetro de 15 días hábiles
        let cronometroHTML = '';
        let cronometroClass = 'cronometro-info';

        if (solicitud.fecha_solicitud) {
            // Ya tiene fecha de solicitud → entregada
            const fechaEntrega = new Date(solicitud.fecha_solicitud);
            cronometroHTML = `
                <div class="text-center">
                    <i class="bi bi-check2-circle" style="font-size: 2.5rem; color: #495057;"></i>
                    <h5 class="mt-2 fw-bold" style="color: #495057;">TU SOLICITUD FUE ENTREGADA</h5>
                    <p class="mb-1">Fecha de entrega: <strong>${fechaEntrega.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</strong></p>
                </div>
            `;
            cronometroClass = 'cronometro-completado';
        } else {
            // Sin fecha de solicitud → mostrar cronómetro 15 días hábiles
            const fechaInicioCronometro = solicitud.fecha_informe
                ? (() => { const d = new Date(solicitud.fecha_informe); d.setHours(0,0,0,0); return d; })()
                : null;

            if (fechaInicioCronometro) {
                if (hoy < fechaInicioCronometro) {
                    cronometroHTML = `
                        <div class="text-center">
                            <i class="bi bi-info-circle" style="font-size: 2rem; color: #6c757d;"></i>
                            <p class="mt-2 mb-1">El cronómetro de 15 días hábiles iniciará el</p>
                            <strong>${fechaInicioCronometro.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>
                            <p class="text-muted small mt-2">(Fecha del informe relacionado)</p>
                        </div>
                    `;
                    cronometroClass = 'cronometro-info';
                } else {
                    const diasHabiles = businessDaysBetween(fechaInicioCronometro, hoy);
                    if (diasHabiles >= 15) {
                        cronometroHTML = `
                            <div class="text-center">
                                <i class="bi bi-calendar-check" style="font-size: 2.5rem; color: #495057;"></i>
                                <h5 class="mt-2 fw-bold" style="color: #495057;">YA PASARON LOS 15 DÍAS HÁBILES</h5>
                                <p class="mb-1">Han transcurrido <strong>${diasHabiles}</strong> días hábiles</p>
                                <p class="text-muted small">desde la fecha del informe relacionado</p>
                            </div>
                        `;
                        cronometroClass = 'cronometro-completado';
                    } else {
                        const diasRestantes = 15 - diasHabiles;
                        const fechaLimite   = addBusinessDays(fechaInicioCronometro, 15);
                        cronometroHTML = `
                            <div class="text-center">
                                <i class="bi bi-hourglass-split" style="font-size: 2rem; color: #6c757d;"></i>
                                <h6 class="mt-2 fw-bold">Cronómetro de días hábiles (15 días)</h6>
                                <p class="mb-1">Han transcurrido <strong>${diasHabiles}</strong> días hábiles</p>
                                <p class="mb-2">Faltan <strong>${diasRestantes}</strong> días hábiles para completar 15</p>
                                <p class="text-muted small">Fecha límite: ${fechaLimite.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                            </div>
                        `;
                        cronometroClass = 'cronometro-activo';
                    }
                }
            }
        }

        // ===== SIN PERIODO DE APLICACIÓN =====
        if (!solicitud.fecha_aplicacion) {
            const contenidoSinFecha = `
                <div class="p-3">
                    <h6 class="fw-bold">Detalle de fechas</h6>
                    <ul class="list-unstyled">
                        <li><strong>Fecha de Solicitud:</strong> ${fechaSolicitud ? fechaSolicitud.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : 'No establecida'}</li>
                        ${solicitud.fecha_informe ? `<li><strong>Fecha del Informe:</strong> ${new Date(solicitud.fecha_informe).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })} <small class="text-muted">(inicio del cronómetro)</small></li>` : ''}
                        <li><strong>Periodo de Aplicación:</strong> <span class="text-muted fst-italic">No se ha establecido el periodo de aplicación</span></li>
                        ${solicitud.fecha_verificacion ? `<li><strong>Periodo de Verificación:</strong> ${new Date(solicitud.fecha_verificacion).toLocaleDateString('es-ES', { year: 'numeric', month: 'long' })}</li>` : ''}
                        <li><strong>Responsable:</strong> ${solicitud.responsable_accion || 'No establecido'}</li>
                        <li><strong>No. Identificación:</strong> ${solicitud.folio_solicitud || '-'}</li>
                    </ul>
                    <div class="alert-info-custom mt-2">
                        <i class="bi bi-info-circle me-2"></i> Esta solicitud aún no tiene un periodo de aplicación definido.
                    </div>
                    ${alertaDia27}
                    ${cronometroHTML ? `
                    <div class="mt-3 p-3 rounded ${cronometroClass}">
                        ${cronometroHTML}
                        <small class="text-muted d-block mt-2 text-center">
                            <i class="bi bi-info-circle" style="font-size: 1rem; vertical-align: middle;"></i>
                            Días hábiles: lunes a viernes, excluyendo sábados y domingos.
                        </small>
                    </div>` : ''}
                </div>
            `;
            document.getElementById('calendarioContent').innerHTML = contenidoSinFecha;
            const modal = new bootstrap.Modal(document.getElementById('calendarioModal'));
            modal.show();
            return;
        }

        // ===== CON PERIODO DE APLICACIÓN =====
        const fechaAplicacion = new Date(solicitud.fecha_aplicacion);
        fechaAplicacion.setDate(1);
        fechaAplicacion.setHours(0, 0, 0, 0);

        // Alerta de inicio del periodo de aplicación
        const diffInicioApli = Math.ceil((fechaAplicacion - hoy) / (1000 * 60 * 60 * 24));
        let alertaAplicacion = '';
        if (diffInicioApli > 0) {
            alertaAplicacion = `
                <div class="alert-info-custom mt-2">
                    <i class="bi bi-calendar-event me-2"></i>
                    El periodo de aplicación inicia el <strong>${fechaAplicacion.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>
                    — faltan <strong>${diffInicioApli}</strong> día${diffInicioApli > 1 ? 's' : ''}.
                </div>
            `;
        } else if (diffInicioApli === 0) {
            alertaAplicacion = `
                <div class="alert-warning-custom mt-2">
                    <i class="bi bi-calendar-check me-2"></i>
                    <strong>¡Hoy inicia</strong> el periodo de aplicación!
                </div>
            `;
        } else {
            alertaAplicacion = `
                <div class="alert-secondary-custom mt-2">
                    <i class="bi bi-calendar-x me-2"></i> El periodo de aplicación ya inició
                    (${fechaAplicacion.toLocaleDateString('es-ES', { year: 'numeric', month: 'long' })}).
                </div>
            `;
        }

        const contenido = `
            <div class="p-3">
                <h6 class="fw-bold">Detalle de fechas</h6>
                <ul class="list-unstyled">
                    <li><strong>Fecha de Solicitud:</strong> ${fechaSolicitud ? fechaSolicitud.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : 'No establecida'}</li>
                    ${solicitud.fecha_informe ? `<li><strong>Fecha del Informe:</strong> ${new Date(solicitud.fecha_informe).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })} <small class="text-muted">(inicio del cronómetro)</small></li>` : ''}
                    <li><strong>Periodo de Aplicación:</strong> ${fechaAplicacion.toLocaleDateString('es-ES', { year: 'numeric', month: 'long' })}
                        <small class="text-muted">(inicia el ${fechaAplicacion.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })})</small>
                    </li>
                    ${solicitud.fecha_verificacion ? `<li><strong>Periodo de Verificación:</strong> ${new Date(solicitud.fecha_verificacion).toLocaleDateString('es-ES', { year: 'numeric', month: 'long' })}</li>` : ''}
                    <li><strong>Responsable:</strong> ${solicitud.responsable_accion || 'No establecido'}</li>
                    <li><strong>No. Identificación:</strong> ${solicitud.folio_solicitud || '-'}</li>
                </ul>

                ${alertaAplicacion}
                ${alertaDia27}

                <div class="mt-3 p-3 rounded ${cronometroClass}">
                    ${cronometroHTML}
                    <small class="text-muted d-block mt-2 text-center">
                        <i class="bi bi-info-circle" style="font-size: 1rem; vertical-align: middle;"></i>
                        Días hábiles: lunes a viernes, excluyendo sábados y domingos.
                    </small>
                </div>
            </div>
        `;

        document.getElementById('calendarioContent').innerHTML = contenido;
        const modal = new bootstrap.Modal(document.getElementById('calendarioModal'));
        modal.show();
    }
    // ===== FUNCIÓN GUARDAR SOLICITUD =====
    function guardarSolicitud() {
        const id = document.getElementById('solicitud_id').value;
        const url = id ? 
            `/auditoria/solicitudes/${id}` : 
            '{{ route('auditoria.solicitudes.store') }}';
        
        const formData = new FormData(document.getElementById('formSolicitud'));
        
        if (id) {
            formData.append('_method', 'PUT');
        }

        const submitBtn = document.querySelector('#btnGuardar');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalElement = document.getElementById('modalNuevaSolicitud');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
                
                cargarSolicitudes();
                resetForm();
                mostrarMensajeExito(data.message || 'Solicitud guardada correctamente');
            } else {
                if (data.errors) {
                    for (const campo in data.errors) {
                        const errorDiv = document.getElementById(`error-${campo}`);
                        if (errorDiv) {
                            errorDiv.textContent = data.errors[campo][0];
                            errorDiv.style.display = 'block';
                            const input = document.getElementById(campo);
                            if (input) input.classList.add('is-invalid');
                        }
                    }
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la solicitud. Por favor, intente de nuevo.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // ===== FUNCIÓN ELIMINAR SOLICITUD CORREGIDA (SIN BOTÓN OK) =====
    function eliminarSolicitud(id, identificador) {
        event.stopPropagation();
        event.preventDefault();
        
        Swal.fire({
            title: '¿Eliminar solicitud?',
            text: `¿Estás seguro de eliminar "${identificador}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
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

                // CORRECCIÓN: Usar la ruta correcta con barra
                fetch(`/auditoria/solicitudes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la petición');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: data.message || 'Solicitud eliminada correctamente',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            cargarSolicitudes();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error desconocido',
                            confirmButtonColor: '#800000',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al eliminar la solicitud',
                        confirmButtonColor: '#800000',
                        confirmButtonText: 'Cerrar'
                    });
                });
            }
        });
    }
    
    function mostrarMensajeExito(mensaje) {
        const container = document.getElementById('mensajeExitoContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mb-3';
        alertDiv.setAttribute('role', 'alert');
        
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle me-2"></i> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        container.innerHTML = '';
        container.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

// ===== FUNCIÓN EDITAR SOLICITUD =====
    function editarSolicitud(id) {
        const solicitud = solicitudesData.find(s => s.id === id);
        if (solicitud) {
            document.getElementById('solicitud_id').value = solicitud.id;
            document.getElementById('folio_solicitud').value = solicitud.folio_solicitud || '';
            document.getElementById('responsable_accion').value = solicitud.responsable_accion || '';
            document.getElementById('actividades_verificacion').value = solicitud.actividades_verificacion || '';

            // ===== ESTATUS =====
            const estatusValue  = solicitud.estatus ? solicitud.estatus.trim() : '';
            const estatusSelect = document.getElementById('estatus');
            let optionExists = false;
            for (let i = 0; i < estatusSelect.options.length; i++) {
                if (estatusSelect.options[i].value === estatusValue) {
                    optionExists = true;
                    break;
                }
            }
            estatusSelect.value = (optionExists && estatusValue !== '') ? estatusValue : '';

            // ===== PROCESOS AUDITADOS =====
            const selectProcesos = document.getElementById('procesos_auditados');
            if (selectProcesos) {
                selectProcesos.value = solicitud.procesos_auditados || '';
            }

            // ===== TIPO DE SOLICITUD =====
            const selectTipo = document.getElementById('tipo_solicitud');
            if (selectTipo) {
                selectTipo.value = solicitud.tipo_solicitud || '';
            }

            // ===== FECHAS =====
            if (solicitud.fecha_solicitud) {
                const fecha = new Date(solicitud.fecha_solicitud);
                const año   = fecha.getFullYear();
                const mes   = String(fecha.getMonth() + 1).padStart(2, '0');
                const dia   = String(fecha.getDate()).padStart(2, '0');
                document.getElementById('fecha_solicitud').value = `${año}-${mes}-${dia}`;
            } else {
                document.getElementById('fecha_solicitud').value = '';
            }

            if (solicitud.fecha_aplicacion) {
                const fecha = new Date(solicitud.fecha_aplicacion);
                const año   = fecha.getFullYear();
                const mes   = String(fecha.getMonth() + 1).padStart(2, '0');
                document.getElementById('fecha_aplicacion').value = `${año}-${mes}`;
            } else {
                document.getElementById('fecha_aplicacion').value = '';
            }

            if (solicitud.fecha_verificacion) {
                const fecha = new Date(solicitud.fecha_verificacion);
                const año   = fecha.getFullYear();
                const mes   = String(fecha.getMonth() + 1).padStart(2, '0');
                document.getElementById('fecha_verificacion').value = `${año}-${mes}`;
            } else {
                document.getElementById('fecha_verificacion').value = '';
            }

            // ===== INFORME RELACIONADO Y FECHA DEL INFORME =====
            // Llama a la función del modal para cargar el informe y su fecha
            if (window.cargarInformeEnModal) {
                window.cargarInformeEnModal(
                    solicitud.informe_id || '',
                    solicitud.fecha_informe || ''
                );
            }

            // ===== ARCHIVO =====
            const nombreArchivoActual = document.getElementById('nombreArchivoActual');
            const nombreArchivo       = document.getElementById('nombreArchivo');
            if (solicitud.archivo_nombre) {
                if (nombreArchivoActual) nombreArchivoActual.style.display = 'block';
                if (nombreArchivo) nombreArchivo.textContent = solicitud.archivo_nombre;
            } else {
                if (nombreArchivoActual) nombreArchivoActual.style.display = 'none';
            }

            document.getElementById('modalNuevaSolicitudLabel').innerHTML = '<i class="bi bi-pencil-square me-2" style="color: #80000;"></i> Editar Solicitud de Mejora';

            const modal = new bootstrap.Modal(document.getElementById('modalNuevaSolicitud'));
            modal.show();
        }
    }

    function resetForm() {
        const form = document.getElementById('formSolicitud');
        if (form) form.reset();
        
        document.getElementById('solicitud_id').value = '';
        const nombreArchivoActual = document.getElementById('nombreArchivoActual');
        if (nombreArchivoActual) nombreArchivoActual.style.display = 'none';
        document.getElementById('modalNuevaSolicitudLabel').textContent = 'Registrar Nueva Solicitud de Mejora';
    }

        // Forzar tamaño del modal de tema
    document.addEventListener('DOMContentLoaded', function() {
        const modalTema = document.getElementById('modalTema');
        if (modalTema) {
            modalTema.addEventListener('show.bs.modal', function() {
                const dialog = modalTema.querySelector('.modal-dialog');
                if (dialog) {
                    dialog.style.maxWidth = '380px';
                    dialog.style.width = '380px';
                }
                const body = modalTema.querySelector('.modal-body');
                if (body) {
                    body.style.height = 'auto';
                    body.style.maxHeight = 'none';
                    body.style.overflow = 'visible';
                }
            });
        }
    });
</script>
@endpush