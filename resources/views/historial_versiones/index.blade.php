@extends('layouts.app')

@section('title', 'Historial de Versiones - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2 text-dark">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: #800000;">
                            <i class="bi bi-clock-history me-2" style="color: #800000;"></i>
                            Historial de Versiones
                        </a>
                    </h1>
                    <p class="text-muted mb-0">
                        Registro completo de todas las acciones realizadas en el sistema
                    </p>
                </div>
                <div>
                    <span class="badge p-2" style="background-color: #737373; color: white;">
                        <i class="bi bi-database me-1"></i>
                        Total: {{ number_format($estadisticas['total_general']) }} registros
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>
                        Filtros de búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('historial-versiones.index') }}" id="filtrosForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Módulo</label>
                                <select name="modulo" class="form-select">
                                    <option value="todos">Todos los módulos</option>
                                    @foreach($modulos as $m)
                                        <option value="{{ $m }}" {{ $modulo == $m ? 'selected' : '' }}>
                                            {{ ucfirst(strtolower($m)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Acción</label>
                                <select name="accion" class="form-select">
                                    <option value="todos">Todas</option>
                                    @foreach($acciones as $a)
                                        <option value="{{ $a }}" {{ $accion == $a ? 'selected' : '' }}>
                                            {{ $a }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Usuario</label>
                                <select name="usuario_id" class="form-select">
                                    <option value="todos">Todos los usuarios</option>
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->id }}" {{ $usuario_id == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Importancia</label>
                                <select name="importancia" class="form-select">
                                    <option value="todos">Todas</option>
                                    @foreach($importancias as $imp)
                                        <option value="{{ $imp }}" {{ $importancia == $imp ? 'selected' : '' }}>
                                            {{ ucfirst($imp) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" 
                                       value="{{ $fecha_inicio }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" name="fecha_fin" class="form-control" 
                                       value="{{ $fecha_fin }}">
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-search me-2"></i>Filtrar
                                </button>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('historial-versiones.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-eraser me-2"></i>Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de actividades -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            Registro de Actividades
                        </h5>
                        <span class="badge" style="background-color: #737373; color: white;">
                            Mostrando {{ $actividades->firstItem() ?? 0 }} - {{ $actividades->lastItem() ?? 0 }} de {{ $actividades->total() }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3">Fecha/Hora</th>
                                    <th class="py-3">Usuario</th>
                                    <th class="py-3">Acción</th>
                                    <th class="py-3">Descripción</th>
                                    <th class="py-3">Importancia</th>
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
                                        <span class="badge" style="background-color: #737373; color: white;">
                                            <i class="{{ $actividad->icono_accion }} me-1"></i>
                                            {{ $actividad->accion }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $actividad->descripcion }}">
                                            {{ Str::limit($actividad->descripcion, 80) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #737373; color: white;">
                                            {{ ucfirst($actividad->nivel_importancia) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
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
                        {{ $actividades->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection