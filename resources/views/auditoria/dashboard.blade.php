{{-- resources/views/auditoria/dashboard.blade.php --}}
{{-- VISTA PRINCIPAL DEL MÓDULO DE AUDITORÍAS --}}
{{-- MUESTRA LAS TARJETAS DE ACCESO RÁPIDO A LOS SUBMÓDULOS DEL SISTEMA --}}
@extends('layouts.app')

@section('title', 'Auditorías - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">

    {{-- ── ENCABEZADO: TÍTULO DEL MÓDULO CON ENLACE AL DASHBOARD PRINCIPAL ── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column">
                {{-- AL HACER CLIC EN EL TÍTULO, NAVEGA AL DASHBOARD PRINCIPAL DEL SISTEMA --}}
                <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                    <h1 class="h3 mb-2" style="color: #059669; cursor: pointer;">
                        <i class="bi-clipboard-check me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                        Auditorias
                    </h1>
                </a>
            </div>
        </div>
    </div>

    {{-- ── CUADRÍCULA DE TARJETAS DE SUBMÓDULOS ── --}}
    <div class="row">

        {{-- DEFINE LOS MÓDULOS DISPONIBLES EN EL DASHBOARD DE AUDITORÍAS --}}
        {{-- CADA MÓDULO TIENE: TÍTULO, ÍCONO, DESCRIPCIÓN, COLOR Y RUTA --}}
        @php
            $modules = [
                [
                    'title' => 'Plan de Auditoría',
                    'icon' => 'bi-calendar-check',
                    'description' => 'Gestión y planificación de auditorías',
                    'color' => '#4f46e5',
                    'route' => 'auditoria.plan.index'
                ],
                [
                    'title' => 'Informes',
                    'icon' => 'bi-file-text',
                    'description' => 'Informes de auditorías realizadas',
                    'color' => '#059669',
                    'route' => 'informes-auditoria.index'
                ],
                [
                    'title' => 'Solicitud de Mejora',
                    'icon' => 'bi-arrow-up-circle',
                    'description' => 'Solicitudes de mejora continua',
                    'color' => '#dc2626',
                    'route' => 'auditoria.solicitudes.index'
                ],
                [
                    'title' => 'Competencias',
                    'icon' => 'bi-person-workspace',
                    'description' => 'Gestión de competencias del personal',
                    'color' => '#7c3aed',
                    'route' => 'auditoria.competencias.index'
                ]
            ];
        @endphp

        {{-- RECORRE CADA MÓDULO Y LO MUESTRA COMO UNA TARJETA CLICKEABLE --}}
        {{-- AL HACER CLIC EN LA TARJETA, NAVEGA A LA RUTA DEL MÓDULO CORRESPONDIENTE --}}
        @foreach($modules as $module)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card dashboard-card h-100 border-0 shadow-sm" 
                 onclick="window.location.href='{{ route($module['route']) }}'"
                 style="cursor: pointer;">
                <div class="card-body text-center p-4">

                    {{-- ÍCONO DEL MÓDULO CON FONDO DE COLOR SEMITRANSPARENTE --}}
                    {{-- EL COLOR DEL FONDO USA EL COLOR DEL MÓDULO CON 20% DE OPACIDAD --}}
                    <div class="dashboard-icon mb-3" style="background-color: {{ $module['color'] }}20;">
                        <i class="{{ $module['icon'] }}" style="color: {{ $module['color'] }}; font-size: 2rem;"></i>
                    </div>

                    {{-- TÍTULO DEL MÓDULO EN EL COLOR ASIGNADO --}}
                    <h5 class="card-title fw-bold mb-2" style="color: {{ $module['color'] }}">{{ $module['title'] }}</h5>

                    {{-- DESCRIPCIÓN BREVE DEL MÓDULO --}}
                    <p class="card-text text-muted small mb-3">{{ $module['description'] }}</p>

                    {{-- BADGE "ACCEDER" QUE INDICA QUE LA TARJETA ES CLICKEABLE --}}
                    <div class="mt-auto">
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-arrow-right-short"></i> Acceder
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

{{-- ── ESTILOS CSS DE LAS TARJETAS DEL DASHBOARD ── --}}
{{-- INCLUYE ANIMACIONES DE HOVER Y DISEÑO RESPONSIVO PARA MÓVILES --}}
@push('styles')
<style>
    /* ESTILOS BASE DE LA TARJETA: BORDES REDONDEADOS Y ALTURA MÍNIMA */
    .dashboard-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        background: #fff;
        min-height: 200px;
    }

    /* EFECTO HOVER: LA TARJETA SE ELEVA Y MUESTRA MÁS SOMBRA AL PASAR EL CURSOR */
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    /* LÍNEA DECORATIVA EN LA PARTE SUPERIOR DE CADA TARJETA */
    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #737373 0%, #737373 100%);
    }

    /* CÍRCULO DE FONDO DEL ÍCONO: CENTRADO Y CON TRANSICIÓN SUAVE */
    .dashboard-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    /* EFECTO HOVER EN EL ÍCONO: SE AGRANDA LIGERAMENTE AL PASAR EL CURSOR SOBRE LA TARJETA */
    .dashboard-card:hover .dashboard-icon {
        transform: scale(1.1);
    }

    /* ESTILOS RESPONSIVOS PARA MÓVILES (768px Y MENOS) */
    /* REDUCE EL TAMAÑO DEL CÍRCULO Y DEL ÍCONO EN PANTALLAS PEQUEÑAS */
    @media (max-width: 768px) {
        .dashboard-icon {
            width: 60px;
            height: 60px;
        }
        
        .dashboard-icon i {
            font-size: 1.5rem !important;
        }
    }
</style>
@endpush