{{-- VISTA PRINCIPAL DEL MÓDULO HISTORIAL DE VERSIONES --}}
{{-- MUESTRA UN REGISTRO DE TODAS LAS ACTIVIDADES DEL SISTEMA CON FILTROS POR ACCIÓN Y USUARIO --}}
@extends('layouts.app')

@section('title', 'Historial de Versiones - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">

    {{-- ENCABEZADO CON TÍTULO Y ENLACE AL DASHBOARD --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #0891b2; cursor: pointer;">
                            <i class="bi bi-clock-history me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                            Historial de Versiones
                        </h1>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TARJETA DE FILTROS: PERMITE FILTRAR POR ACCIÓN Y POR USUARIO --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel me-2" style="color: #000000;"></i>
                        Filtros de búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    {{-- FORMULARIO GET — ENVÍA LOS FILTROS COMO PARÁMETROS EN LA URL --}}
                    <form method="GET" action="{{ route('historial-versiones.index') }}" id="filtrosForm">
                        <div class="row g-3 mb-3">

                            {{-- FILTRO POR TIPO DE ACCIÓN (CREAR, EDITAR, ELIMINAR, ETC.) --}}
                            <div class="col-md-5">
                                <label class="form-label fw-bold" style="color: #000000;">Acción</label>
                                <select name="accion" class="form-select">
                                    <option value="todos">Todas</option>
                                    {{-- GENERA UNA OPCIÓN POR CADA TIPO DE ACCIÓN DISPONIBLE --}}
                                    @foreach($acciones as $a)
                                        <option value="{{ $a }}" {{ $accion == $a ? 'selected' : '' }}>
                                            {{ $a }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- FILTRO POR USUARIO --}}
                            <div class="col-md-5">
                                <label class="form-label fw-bold" style="color: #000000;">Usuario</label>
                                <select name="usuario_id" class="form-select">
                                    <option value="todos">Todos los usuarios</option>
                                    {{-- GENERA UNA OPCIÓN POR CADA USUARIO DEL SISTEMA --}}
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->id }}" {{ $usuario_id == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- BOTONES FILTRAR Y LIMPIAR --}}
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    {{-- BOTÓN FILTRAR — ENVÍA EL FORMULARIO --}}
                                    <button type="submit" class="btn btn-outline-secondary flex-fill" style="border-color: #dee2e6; background-color: white; color: #6c757d;" 
                                            onmouseover="this.style.backgroundColor='#737373'; this.style.color='white'; this.style.borderColor='#737373'"
                                            onmouseout="this.style.backgroundColor='white'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6'">
                                        <i class="bi bi-search me-2"></i>Filtrar
                                    </button>
                                    {{-- BOTÓN LIMPIAR — NAVEGA AL ÍNDICE SIN FILTROS --}}
                                    <a href="{{ route('historial-versiones.index') }}" class="btn btn-outline-secondary flex-fill" style="border-color: #dee2e6; background-color: white; color: #6c757d;"
                                       onmouseover="this.style.backgroundColor='#737373'; this.style.color='white'; this.style.borderColor='#737373'"
                                       onmouseout="this.style.backgroundColor='white'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6'">
                                         <i class="bi bi-eraser me-2"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- BOTÓN BORRAR TODO EL HISTORIAL — ALINEADO A LA DERECHA --}}
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                {{-- LLAMA A LA CONFIRMACIÓN CON SWEETALERT2 ANTES DE BORRAR --}}
                                <button type="button" id="btnBorrarTodo" class="btn btn-outline-secondary" 
                                        style="border-color: #dee2e6; background-color: white; color: #6c757d;"
                                        onmouseover="this.style.backgroundColor='#737373'; this.style.color='white'; this.style.borderColor='#737373'"
                                        onmouseout="this.style.backgroundColor='white'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6'">
                                    <i class="bi bi-trash3 me-2"></i>Borrar Todo el Historial
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE ACTIVIDADES --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2" style="color: #000000;"></i>
                            Registro de Actividades
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                            <thead class="bg-light">
                                    <th class="py-3" style="color: #000000; width: 110px;">Fecha/Hora</th>
                                    <th class="py-3" style="color: #000000;">Usuario</th>
                                    <th class="py-3" style="color: #000000">Módulo</th>         {{-- MÓDULO DONDE SE REALIZÓ LA ACCIÓN --}}
                                    <th class="py-3" style="color: #000000;">Acción</th>         {{-- TIPO DE ACCIÓN (CREAR, EDITAR, ELIMINAR, ETC.) --}}
                                    <th class="py-3" style="color: #000000;">Descripción</th>    {{-- DESCRIPCIÓN BREVE DE LO QUE SE HIZO --}}
                                    <th class="py-3" style="color: #000000; width: 120px;">Acciones</th>
                            </thead>
                            <tbody>
                                {{-- ITERA CADA ACTIVIDAD DEL HISTORIAL --}}
                                {{-- SI NO HAY ACTIVIDADES, MUESTRA EL BLOQUE @empty --}}
                                @forelse($actividades as $actividad)
                                <tr class="file-row">

                                    {{-- FECHA Y HORA DE LA ACTIVIDAD --}}
                                    <td class="text-nowrap small" style="font-size: 0.75rem;">
                                        {{ $actividad->created_at->format('d/m/Y H:i:s') }}
                                    </td>

                                    {{-- USUARIO QUE REALIZÓ LA ACCIÓN CON SU ROL --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-person" style="color: #737373;"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $actividad->usuario_nombre }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $actividad->usuario_rol }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- BADGE DEL MÓDULO CON COLOR E ÍCONO DINÁMICO --}}
                                    <td>
                                        <span class="badge" style="background: {{ $actividad->color_modulo }}; color: white; font-size: 0.7rem; padding: 0.35rem 0.7rem;">
                                            <i class="{{ $actividad->icono_modulo }} me-1"></i>
                                            {{ $actividad->nombre_modulo_formateado }}
                                        </span>
                                    </td>

                                    {{-- BADGE DE LA ACCIÓN CON COLOR E ÍCONO DINÁMICO --}}
                                    {{-- CORRIGE EL TYPO "MOVIR" → "MOVER" EN LA VISUALIZACIÓN --}}
                                    <td>
                                        <span class="badge" style="background-color: {{ $actividad->color_accion }}; color: white; font-size: 0.7rem; padding: 0.35rem 0.7rem;">
                                            <i class="{{ $actividad->icono_accion }} me-1"></i>
                                            {{ str_replace('MOVIR', 'MOVER', $actividad->accion) }}
                                        </span>
                                    </td>

                                    {{-- DESCRIPCIÓN TRUNCADA A 80 CARACTERES (EL TOOLTIP MUESTRA EL TEXTO COMPLETO) --}}
                                    <td>
                                        <span title="{{ $actividad->descripcion }}" style="font-size: 0.8rem;">
                                            {{ Str::limit($actividad->descripcion, 80) }}
                                        </span>
                                    </td>

                                    {{-- BOTÓN DE ACCIÓN: "RESTAURAR" SI FUE ELIMINADO, "IR AL MÓDULO" EN CASO CONTRARIO --}}
                                    <td class="text-nowrap">
                                        @if($actividad->accion === 'ELIMINAR')
                                            {{-- FORMULARIO OCULTO PARA RESTAURAR — SE ENVÍA AL CONFIRMAR CON SWEETALERT2 --}}
                                            <form action="{{ route('historial-versiones.restaurar', $actividad->id) }}" method="POST" style="display: inline;" id="form-restaurar-{{ $actividad->id }}">
                                                @csrf
                                                {{-- LLAMA A confirmarRestauracion() QUE MUESTRA UNA ALERTA ANTES DE ENVIAR --}}
                                                <button type="button" class="btn btn-sm btn-outline-success restaurar-btn" 
                                                        onclick="confirmarRestauracion({{ $actividad->id }}, '{{ addslashes($actividad->descripcion) }}')"
                                                        style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                </button>
                                            </form>
                                        @else
                                            {{-- SI TIENE URL DE DETALLE, MUESTRA ENLACE AL MÓDULO --}}
                                            @if($actividad->detalle_url)
                                                <a href="{{ $actividad->detalle_url }}" class="btn btn-sm btn-outline-primary" title="Ir al módulo" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                                    <i class="bi bi-box-arrow-in-right"></i> Ir al Módulo
                                                </a>
                                            @else
                                                {{-- SI NO HAY URL DISPONIBLE, MUESTRA EL BOTÓN DESHABILITADO --}}
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="No disponible" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                                    <i class="bi bi-box-arrow-in-right"></i> Ir al Módulo
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                {{-- MENSAJE CUANDO NO HAY ACTIVIDADES QUE COINCIDAN CON LOS FILTROS --}}
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                        <h5 class="text-muted">No hay actividades registradas</h5>
                                        <p class="text-muted">Los filtros aplicados no coinciden con ningún registro</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PAGINACIÓN — PRESERVA LOS FILTROS ACTIVOS AL NAVEGAR ENTRE PÁGINAS --}}
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-center">
                        {{ $actividades->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* HOVER EN FILAS DE LA TABLA */
    .file-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    /* CURSOR DE AYUDA EN ELEMENTOS CON TOOLTIP */
    [title] {
        position: relative;
        cursor: help;
    }
    
    /* ESTILOS DE LOS TOOLTIPS */
    .tooltip {
        --bs-tooltip-bg: #737373;
        --bs-tooltip-color: #ffffff;
        font-size: 0.875rem;
    }
    
    .tooltip .tooltip-inner {
        background-color: #737373;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    /* FLECHAS DE LOS TOOLTIPS SEGÚN POSICIÓN */
    .tooltip.bs-tooltip-top .tooltip-arrow::before {
        border-top-color: #737373;
    }
    
    .tooltip.bs-tooltip-bottom .tooltip-arrow::before {
        border-bottom-color: #737373;
    }
    
    .tooltip.bs-tooltip-start .tooltip-arrow::before {
        border-left-color: #737373;
    }
    
    .tooltip.bs-tooltip-end .tooltip-arrow::before {
        border-right-color: #737373;
    }

    /* EFECTO HOVER EN BOTONES */
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        transition: all 0.2s;
    }
    
    a {
        transition: all 0.2s;
    }
    
    a:hover {
        opacity: 0.8;
    }

    /* ESTILOS DE LAS TARJETAS */
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: 2px solid #f0f0f0;
    }
    
    .badge {
        font-weight: 500;
    }
    
    /* ESTILOS DE LOS INPUTS Y SELECTS */
    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
    }
    
    /* FOCUS CON COLOR GRIS EN LUGAR DEL AZUL POR DEFECTO */
    .form-select:focus, .form-control:focus {
        border-color: #737373;
        box-shadow: 0 0 0 0.2rem rgba(115, 115, 115, 0.25);
    }
    
    .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    /* ESTILOS DE LA PAGINACIÓN */
    .pagination {
        margin-bottom: 0 !important;
        gap: 3px !important;
    }
    .pagination .page-link {
        padding: 0.2rem 0.6rem !important;
        font-size: 0.75rem !important;
        line-height: 1.2 !important;
        border-radius: 6px !important;
    }
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0.2rem 0.6rem !important;
        font-size: 0.75rem !important;
    }
    .pagination .page-link i {
        font-size: 0.7rem !important;
    }
    
    /* PADDING Y ALINEACIÓN DE CELDAS */
    .table td, .table th {
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
    }
    
    .table .badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.7rem;
    }
    
    /* FECHA EN COLOR GRIS SUAVE */
    .table td:first-child {
        font-size: 0.7rem;
        color: #6c757d;
    }

    /* ESTILOS PARA SWEETALERT2 */
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

    /* =====================================================
       ESTILOS RESPONSIVOS - HISTORIAL DE VERSIONES
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
            font-size: 0.7rem !important;
            padding: 8px 6px !important;
        }
        .btn-sm {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.65rem !important;
        }
        .badge {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        .card-header h5 {
            font-size: 0.9rem !important;
        }
        .form-label {
            font-size: 0.8rem !important;
        }
        .form-select {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.5rem !important;
        }
        .btn {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.75rem !important;
        }
        .pagination .page-link {
            padding: 0.15rem 0.5rem !important;
            font-size: 0.7rem !important;
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
        
        /* Filtros en columna */
        .row.g-3 {
            flex-direction: column !important;
        }
        .row.g-3 > .col-md-5,
        .row.g-3 > .col-md-2 {
            width: 100% !important;
        }
        .col-md-2.d-flex.align-items-end {
            margin-top: 0.5rem !important;
        }
        .d-flex.gap-2.w-100 {
            flex-direction: row !important;
        }
        
        /* Botón Borrar Todo */
        .row.mt-3 .col-12.d-flex.justify-content-end {
            justify-content: center !important;
        }
        #btnBorrarTodo {
            width: 100% !important;
            text-align: center !important;
        }
        
        /* Tabla - scroll horizontal */
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
        
        /* Badges en tabla */
        .table .badge {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        
        /* Botones de acción en tabla */
        .btn-sm {
            padding: 0.15rem 0.25rem !important;
            font-size: 0.6rem !important;
        }
        .btn-sm i {
            font-size: 0.65rem !important;
        }
        
        /* Tarjeta de filtros */
        .card-header .py-3 {
            padding: 0.75rem !important;
        }
        .card-body {
            padding: 1rem !important;
        }
        
        /* Usuario en tabla */
        .d-flex.align-items-center .rounded-circle {
            width: 28px !important;
            height: 28px !important;
            padding: 0.25rem !important;
        }
        .d-flex.align-items-center .rounded-circle i {
            font-size: 0.8rem !important;
        }
        .d-flex.align-items-center div strong {
            font-size: 0.7rem !important;
        }
        .d-flex.align-items-center div small {
            font-size: 0.6rem !important;
        }
        
        /* Paginación */
        .pagination {
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 2px !important;
        }
        .pagination .page-link {
            padding: 0.15rem 0.4rem !important;
            font-size: 0.65rem !important;
        }
        
        /* Título de la tabla */
        .card-header .d-flex.justify-content-between {
            flex-direction: column !important;
            gap: 0.5rem !important;
            align-items: flex-start !important;
        }
        .card-header h5 {
            font-size: 0.85rem !important;
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
        .table .badge {
            font-size: 0.55rem !important;
            padding: 0.15rem 0.3rem !important;
        }
        .pagination .page-link {
            padding: 0.1rem 0.35rem !important;
            font-size: 0.6rem !important;
        }
        .form-label {
            font-size: 0.75rem !important;
        }
        .form-select {
            font-size: 0.7rem !important;
            padding: 0.4rem 0.5rem !important;
        }
        .btn {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.5rem !important;
        }
        .card-header h5 {
            font-size: 0.8rem !important;
        }
        .table td:first-child {
            font-size: 0.6rem !important;
        }
        .d-flex.align-items-center .rounded-circle {
            width: 24px !important;
            height: 24px !important;
        }
        .d-flex.align-items-center .rounded-circle i {
            font-size: 0.7rem !important;
        }
    }
</style>
@endpush

@push('scripts')
{{-- LIBRERÍA SWEETALERT2 PARA CONFIRMACIONES --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // MUESTRA CONFIRMACIÓN CON SWEETALERT2 ANTES DE RESTAURAR UN ELEMENTO ELIMINADO
    // INTENTA EXTRAER EL NOMBRE DEL ELEMENTO DESDE LA DESCRIPCIÓN
    function confirmarRestauracion(id, descripcion) {
        let elementoNombre = '';

        // BUSCA EL NOMBRE DEL ELEMENTO EN LA DESCRIPCIÓN CON DIFERENTES PATRONES
        const matchDoc = descripcion.match(/documento ['"]([^'"]+)['"]/i);
        const matchSol = descripcion.match(/solicitud ['"]([^'"]+)['"]/i);
        const matchEntre = descripcion.match(/["']([^'"]+)["']/);
        
        if (matchDoc) {
            elementoNombre = matchDoc[1];      // NOMBRE DE DOCUMENTO
        } else if (matchSol) {
            elementoNombre = matchSol[1];      // NOMBRE DE SOLICITUD
        } else if (matchEntre) {
            elementoNombre = matchEntre[1];    // CUALQUIER TEXTO ENTRE COMILLAS
        } else {
            // SI NO HAY PATRÓN, USA LOS PRIMEROS 30 CARACTERES DE LA DESCRIPCIÓN
            elementoNombre = descripcion.length > 30 ? descripcion.substring(0, 30) + '...' : descripcion;
        }
        
        Swal.fire({
            title: '¿Restaurar elemento?',
            text: `¿Estás seguro de restaurar "${elementoNombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            // SI EL USUARIO CONFIRMA, ENVÍA EL FORMULARIO DE RESTAURACIÓN
            if (result.isConfirmed) {
                document.getElementById(`form-restaurar-${id}`).submit();
            }
        });
    }

    // MANEJADOR DEL BOTÓN "BORRAR TODO EL HISTORIAL"
    // MUESTRA CONFIRMACIÓN CON SWEETALERT2 Y ENVÍA UN FORMULARIO DINÁMICO CON DELETE
    document.getElementById('btnBorrarTodo').addEventListener('click', function() {
        Swal.fire({
            title: '¿Borrar todo el historial?',
            text: 'Esta acción eliminará TODOS los registros del historial de versiones. ¡No podrás recuperarlos!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, borrar todo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // MUESTRA SPINNER MIENTRAS SE PROCESA LA ELIMINACIÓN
                Swal.fire({
                    title: 'Borrando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    showConfirmButton: false
                });

                // ✅ Crear formulario dinámico con POST y _method DELETE
                // SE CREA UN FORMULARIO EN TIEMPO REAL PORQUE HTML NO SOPORTA method="DELETE" NATIVO
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("historial-versiones.borrar-todo") }}';
                form.style.display = 'none';
                
                // CAMPO OCULTO CSRF — REQUERIDO POR LARAVEL EN TODAS LAS PETICIONES POST
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // CAMPO OCULTO _method — INDICA A LARAVEL QUE ES UNA PETICIÓN DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                // AGREGA EL FORMULARIO AL BODY Y LO ENVÍA
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>
@endpush