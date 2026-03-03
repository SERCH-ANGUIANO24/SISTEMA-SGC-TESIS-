{{-- resources/views/auditoria/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Auditorías - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column">
                <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                    <h1 class="h3 mb-2" style="color: #800000; cursor: pointer;">
                        <i class="bi bi-folder me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                        Auditorias
                    </h1>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
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

        @foreach($modules as $module)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card dashboard-card h-100 border-0 shadow-sm" 
                 onclick="window.location.href='{{ route($module['route']) }}'"
                 style="cursor: pointer;">
                <div class="card-body text-center p-4">
                    <div class="dashboard-icon mb-3" style="background-color: {{ $module['color'] }}20;">
                        <i class="{{ $module['icon'] }}" style="color: {{ $module['color'] }}; font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2" style="color: {{ $module['color'] }}">{{ $module['title'] }}</h5>
                    <p class="card-text text-muted small mb-3">{{ $module['description'] }}</p>
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

@push('styles')
<style>
    .dashboard-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        background: #fff;
        min-height: 200px;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #800000 0%, #800000 100%);
    }

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

    .dashboard-card:hover .dashboard-icon {
        transform: scale(1.1);
    }

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