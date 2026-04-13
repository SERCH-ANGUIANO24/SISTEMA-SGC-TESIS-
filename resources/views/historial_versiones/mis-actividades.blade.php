@extends('layouts.app')

@section('title', 'Mis Actividades - Historial de Versiones')

@section('content')
<div class="container-fluid">

    {{-- ── ENCABEZADO: TÍTULO DE LA PÁGINA Y BOTÓN PARA VOLVER AL HISTORIAL GENERAL ── --}}
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    {{-- TÍTULO DE LA SECCIÓN CON ÍCONO DE PERSONA --}}
                    <h1 class="h3 mb-2 text-dark">
                        <i class="bi bi-person-workspace me-2" style="color: #000000;"></i>
                        Mis Actividades
                    </h1>
                    {{-- DESCRIPCIÓN BREVE DE LO QUE MUESTRA ESTA SECCIÓN --}}
                    <p class="text-muted mb-0">
                        Registro de todas las acciones que has realizado en el sistema
                    </p>
                </div>
                <div>
                    {{-- BOTÓN PARA REGRESAR AL HISTORIAL GENERAL DEL SISTEMA --}}
                    <a href="{{ route('historial-versiones.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al historial general
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TARJETAS DE ESTADÍSTICAS PERSONALES ── --}}
    {{-- MUESTRA EL CONTEO DE ACTIVIDADES DEL USUARIO HOY, ESTA SEMANA Y ESTE MES --}}
    <!-- Estadísticas personales -->
    <div class="row mb-4">

        {{-- TARJETA: ACTIVIDADES REALIZADAS HOY --}}
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
                            {{-- VARIABLE $totalHoy: VIENE DEL CONTROLADOR CON EL CONTEO DEL DÍA --}}
                            <h3 class="mb-0">{{ $totalHoy }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TARJETA: ACTIVIDADES REALIZADAS ESTA SEMANA --}}
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
                            {{-- VARIABLE $totalSemana: VIENE DEL CONTROLADOR CON EL CONTEO DE LA SEMANA --}}
                            <h3 class="mb-0">{{ $totalSemana }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TARJETA: ACTIVIDADES REALIZADAS ESTE MES --}}
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
                            {{-- VARIABLE $totalMes: VIENE DEL CONTROLADOR CON EL CONTEO DEL MES --}}
                            <h3 class="mb-0">{{ $totalMes }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLA DE ACTIVIDADES DEL USUARIO ── --}}
    {{-- MUESTRA TODAS LAS ACCIONES REALIZADAS POR EL USUARIO PAGINADAS --}}
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
                        {{-- BADGE CON EL TOTAL DE REGISTROS PAGINADOS --}}
                        <span class="badge bg-secondary">
                            Total: {{ $actividades->total() }} registros
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                脂
                                    <th class="py-3" style="width: 110px;">Fecha/Hora</th>
                                    <th class="py-3">Módulo</th>
                                    <th class="py-3">Acción</th>
                                    <th class="py-3">Descripción</th>
                                    <th class="py-3">IP</th>
                                    <th class="py-3" style="width: 120px;">Acciones</th>
                                 tame
                            </thead>
                            <tbody>
                                {{-- RECORRE CADA ACTIVIDAD Y LA MUESTRA EN UNA FILA --}}
                                {{-- SI NO HAY ACTIVIDADES, MUESTRA UN MENSAJE INFORMATIVO --}}
                                @forelse($actividades as $actividad)
                                <tr class="file-row">

                                    {{-- COLUMNA DE FECHA Y HORA EN FORMATO dd/mm/YYYY HH:MM:SS --}}
                                    <td class="text-nowrap small" style="font-size: 0.75rem;">
                                        {{ $actividad->created_at->format('d/m/Y H:i:s') }}
                                    </td>

                                    {{-- COLUMNA DE MÓDULO: MUESTRA EL NOMBRE DEL MÓDULO CON SU COLOR E ÍCONO --}}
                                    {{-- EL COLOR Y EL ÍCONO SON PROPIEDADES CALCULADAS EN EL MODELO --}}
                                    <td>
                                        <span class="badge" style="background: {{ $actividad->color_modulo }}; color: white; font-size: 0.7rem; padding: 0.35rem 0.7rem;">
                                            <i class="{{ $actividad->icono_modulo }} me-1"></i>
                                            {{ $actividad->nombre_modulo_formateado }}
                                        </span>
                                    </td>

                                    {{-- COLUMNA DE ACCIÓN: MUESTRA EL TIPO DE ACCIÓN CON COLOR E ÍCONO ESPECÍFICO --}}
                                    {{-- CREAR Y SUBIR = VERDE | EDITAR = AMARILLO | ELIMINAR = ROJO | RESTAURAR = CYAN --}}
                                    <td>
                                        <span class="badge" style="background: 
                                            @switch($actividad->accion)
                                                @case('CREAR') #10b981 @break
                                                @case('SUBIR') #10b981 @break
                                                @case('EDITAR') #f59e0b @break
                                                @case('ELIMINAR') #ef4444 @break
                                                @case('RESTAURAR') #06b6d4 @break
                                                @default #6b7280
                                            @endswitch; color: white; font-size: 0.7rem; padding: 0.35rem 0.7rem;">
                                            <i class="bi 
                                                @switch($actividad->accion)
                                                    @case('CREAR') bi-plus-circle @break
                                                    @case('SUBIR') bi-cloud-upload @break
                                                    @case('EDITAR') bi-pencil @break
                                                    @case('ELIMINAR') bi-trash @break
                                                    @case('RESTAURAR') bi-arrow-counterclockwise @break
                                                    @default bi-clock-history
                                                @endswitch me-1"></i>
                                            {{ str_replace('MOVIR', 'MOVER', $actividad->accion) }}
                                        </span>
                                    </td>

                                    {{-- COLUMNA DE DESCRIPCIÓN: SE TRUNCA A 60 CARACTERES Y SE VE COMPLETA AL PASAR EL CURSOR --}}
                                    <td>
                                        <span title="{{ $actividad->descripcion }}" style="font-size: 0.8rem;">
                                            {{ Str::limit($actividad->descripcion, 60) }}
                                        </span>
                                    </td>

                                    {{-- COLUMNA DE IP: MUESTRA LA DIRECCIÓN IP DESDE DONDE SE REALIZÓ LA ACCIÓN --}}
                                    <td>
                                        <small class="text-muted">{{ $actividad->ip_address ?? 'N/A' }}</small>
                                    </td>

                                    {{-- COLUMNA DE ACCIONES: MUESTRA BOTÓN RESTAURAR O IR AL MÓDULO SEGÚN EL TIPO DE ACCIÓN --}}
                                    {{-- SI LA ACCIÓN FUE ELIMINAR: MUESTRA BOTÓN PARA RESTAURAR EL ELEMENTO --}}
                                    {{-- SI ES OTRA ACCIÓN: MUESTRA BOTÓN PARA IR AL MÓDULO (SI HAY URL DISPONIBLE) --}}
                                    <td class="text-nowrap">
                                        @if($actividad->accion === 'ELIMINAR')
                                            {{-- Solo botón restaurar --}}
                                            {{-- FORMULARIO POST QUE RESTAURA EL ELEMENTO ELIMINADO --}}
                                            <form action="{{ route('historial-versiones.restaurar', $actividad->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Restaurar elemento eliminado" onclick="return confirm('¿Está seguro de que desea restaurar este elemento?')" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                                </button>
                                            </form>
                                        @else
                                            {{-- Siempre mostrar "Ir al Módulo" --}}
                                            {{-- SI TIENE URL, EL BOTÓN ES UN ENLACE; SI NO, SE MUESTRA DESHABILITADO --}}
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
                                {{-- MENSAJE CUANDO EL USUARIO AÚN NO HA REALIZADO NINGUNA ACTIVIDAD --}}
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

                {{-- PAGINACIÓN CENTRADA EN EL PIE DE LA TARJETA --}}
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-center">
                        {{ $actividades->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection