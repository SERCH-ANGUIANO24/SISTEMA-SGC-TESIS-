@extends('layouts.app')

@section('title', 'Mis Actividades - Historial de Versiones')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2 text-dark">
                        <i class="bi bi-person-workspace me-2" style="color: #000000;"></i>
                        Mis Actividades
                    </h1>
                    <p class="text-muted mb-0">
                        Registro de todas las acciones que has realizado en el sistema
                    </p>
                </div>
                <div>
                    <a href="{{ route('historial-versiones.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al historial general
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas personales -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" style="background: #000000;">
                                <i class="bi bi-calendar-day" style="color: #737373; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Actividades hoy</h6>
                            <h3 class="mb-0">{{ $totalHoy }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" style="background: #000000;">
                                <i class="bi bi-calendar-week" style="color: #737373; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Esta semana</h6>
                            <h3 class="mb-0">{{ $totalSemana }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" style="background: #000000;">
                                <i class="bi bi-calendar-month" style="color: #737373; font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Este mes</h6>
                            <h3 class="mb-0">{{ $totalMes }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de mis actividades -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            Mis últimas actividades
                        </h5>
                        <span class="badge bg-secondary">
                            Total: {{ $actividades->total() }} registros
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3">Fecha/Hora</th>
                                    <th class="py-3">Módulo</th>
                                    <th class="py-3">Acción</th>
                                    <th class="py-3">Descripción</th>
                                    <th class="py-3">Importancia</th>
                                    <th class="py-3">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actividades as $actividad)
                                <tr>
                                    <td>
                                        <div>{{ $actividad->created_at->format('d/m/Y H:i:s') }}</div>
                                        <small class="text-muted">{{ $actividad->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(strtolower($actividad->modulo)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: 
                                            @switch($actividad->accion)
                                                @case('CREAR') #000000 @break
                                                @case('EDITAR') #000000 @break
                                                @case('ELIMINAR') #000000 @break
                                                @case('RESTAURAR') #000000 @break
                                                @default #6b7280
                                            @endswitch; color: white;">
                                            <i class="bi 
                                                @switch($actividad->accion)
                                                    @case('CREAR') bi-plus-circle @break
                                                    @case('EDITAR') bi-pencil @break
                                                    @case('ELIMINAR') bi-trash @break
                                                    @case('RESTAURAR') bi-arrow-counterclockwise @break
                                                    @default bi-clock-history
                                                @endswitch me-1"></i>
                                            {{ $actividad->accion }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $actividad->descripcion }}">
                                            {{ Str::limit($actividad->descripcion, 60) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: 
                                            @switch($actividad->nivel_importancia)
                                                @case('bajo') #e2e8f0; color: #475569 @break
                                                @case('normal') #000000 @break
                                                @case('alto') #000000 @break
                                                @case('critico') #000000 @break
                                                @default #6b7280
                                            @endswitch; color: white;">
                                            {{ ucfirst($actividad->nivel_importancia) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $actividad->ip_address ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                        <h5 class="text-muted">No has realizado ninguna actividad aún</h5>
                                        <p class="text-muted">Las acciones que realices en el sistema aparecerán aquí</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-center">
                        {{ $actividades->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection