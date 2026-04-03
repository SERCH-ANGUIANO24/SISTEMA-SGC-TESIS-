@extends('layouts.app')

@section('title', 'Historial de Versiones - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
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
                    <form method="GET" action="{{ route('historial-versiones.index') }}" id="filtrosForm">
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-bold" style="color: #000000;">Acción</label>
                                <select name="accion" class="form-select">
                                    <option value="todos">Todas</option>
                                    @foreach($acciones as $a)
                                        <option value="{{ $a }}" {{ $accion == $a ? 'selected' : '' }}>
                                            {{ $a }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-bold" style="color: #000000;">Usuario</label>
                                <select name="usuario_id" class="form-select">
                                    <option value="todos">Todos los usuarios</option>
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->id }}" {{ $usuario_id == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-outline-secondary flex-fill" style="border-color: #dee2e6; background-color: white; color: #6c757d;" 
                                            onmouseover="this.style.backgroundColor='#737373'; this.style.color='white'; this.style.borderColor='#737373'"
                                            onmouseout="this.style.backgroundColor='white'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6'">
                                        <i class="bi bi-search me-2"></i>Filtrar
                                    </button>
                                    <a href="{{ route('historial-versiones.index') }}" class="btn btn-outline-secondary flex-fill" style="border-color: #dee2e6; background-color: white; color: #6c757d;"
                                       onmouseover="this.style.backgroundColor='#737373'; this.style.color='white'; this.style.borderColor='#737373'"
                                       onmouseout="this.style.backgroundColor='white'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6'">
                                         <i class="bi bi-eraser me-2"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
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
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                    <th class="py-3" style="color: #000000; width: 110px;">Fecha/Hora</th>
                                    <th class="py-3" style="color: #000000;">Usuario</th>
                                    <th class="py-3" style="color: #000000">Módulo</th>
                                    <th class="py-3" style="color: #000000;">Acción</th>
                                    <th class="py-3" style="color: #000000;">Descripción</th>
                                    <th class="py-3" style="color: #000000; width: 120px;">Acciones</th>
                            </thead>
                            <tbody>
                                @forelse($actividades as $actividad)
                                <tr class="file-row">
                                    <td class="text-nowrap small" style="font-size: 0.75rem;">
                                        {{ $actividad->created_at->format('d/m/Y H:i:s') }}
                                    </td>
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
                                    <td>
                                        <span class="badge" style="background: {{ $actividad->color_modulo }}; color: white; font-size: 0.7rem; padding: 0.35rem 0.7rem;">
                                            <i class="{{ $actividad->icono_modulo }} me-1"></i>
                                            {{ $actividad->nombre_modulo_formateado }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $actividad->color_accion }}; color: white; font-size: 0.7rem; padding: 0.35rem 0.7rem;">
                                            <i class="{{ $actividad->icono_accion }} me-1"></i>
                                            {{ str_replace('MOVIR', 'MOVER', $actividad->accion) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $actividad->descripcion }}" style="font-size: 0.8rem;">
                                            {{ Str::limit($actividad->descripcion, 80) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        @if($actividad->accion === 'ELIMINAR')
                                            <form action="{{ route('historial-versiones.restaurar', $actividad->id) }}" method="POST" style="display: inline;" id="form-restaurar-{{ $actividad->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-success restaurar-btn" 
                                                        onclick="confirmarRestauracion({{ $actividad->id }}, '{{ addslashes($actividad->descripcion) }}')"
                                                        style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                </button>
                                            </form>
                                        @else
                                            @if($actividad->detalle_url)
                                                <a href="{{ $actividad->detalle_url }}" class="btn btn-sm btn-outline-primary" title="Ir al módulo" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                                    <i class="bi bi-box-arrow-in-right"></i> Ir al Módulo
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="No disponible" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                                    <i class="bi bi-box-arrow-in-right"></i> Ir al Módulo
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @empty
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
    .file-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    [title] {
        position: relative;
        cursor: help;
    }
    
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
    
    .form-select, .form-control {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #737373;
        box-shadow: 0 0 0 0.2rem rgba(115, 115, 115, 0.25);
    }
    
    .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

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
    
    .table td, .table th {
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
    }
    
    .table .badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.7rem;
    }
    
    .table td:first-child {
        font-size: 0.7rem;
        color: #6c757d;
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmarRestauracion(id, descripcion) {
        let elementoNombre = '';
        const matchDoc = descripcion.match(/documento ['"]([^'"]+)['"]/i);
        const matchSol = descripcion.match(/solicitud ['"]([^'"]+)['"]/i);
        const matchEntre = descripcion.match(/["']([^'"]+)["']/);
        
        if (matchDoc) {
            elementoNombre = matchDoc[1];
        } else if (matchSol) {
            elementoNombre = matchSol[1];
        } else if (matchEntre) {
            elementoNombre = matchEntre[1];
        } else {
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
            if (result.isConfirmed) {
                document.getElementById(`form-restaurar-${id}`).submit();
            }
        });
    }

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
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("historial-versiones.borrar-todo") }}';
                form.style.display = 'none';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>
@endpush