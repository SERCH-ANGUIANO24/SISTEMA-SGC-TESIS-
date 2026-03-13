@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h1 class="h3 mb-2 text-dark">Dashboard Principal</h1>
                <p class="text-muted mb-0">Bienvenido, {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }}) </p>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid - Tarjetas de módulos -->
    <div class="row">
        @php
            $userRole = Auth::user()->role;
            
            $allModules = [
                [
                    'title' => 'Anexos',
                    'icon' => 'bi-folder',
                    'description' => 'Gestionar documentos anexos',
                    'color' => '#4f46e5',
                    'route' => route('anexos.index'),
                    'visible' => true // Todos pueden ver
                ],
                [
                    'title' => 'Auditorías',
                    'icon' => 'bi-clipboard-check',
                    'description' => 'Gestión de auditorías',
                    'color' => '#059669',
                    'route' => route('auditoria.dashboard'),
                    'visible' => true // Todos pueden ver
                ],
                [
                    'title' => 'Gestión Documental',
                    'icon' => 'bi-files',
                    'description' => 'Control de documentos',
                    'color' => '#dc2626',
                    'route' => route('documental.index'),
                    'visible' => true // Todos pueden ver
                ],
                [
                    'title' => 'Matriz',
                    'icon' => 'bi-grid-3x3',
                    'description' => 'Matriz de procesos',
                    'color' => '#9333ea',
                    'route' => route('matriz.index'),
                    'visible' => true // Todos pueden ver
                ],
                [
                    'title' => 'Lista Maestra',
                    'icon' => 'bi-file-earmark-text',
                    'description' => 'Formatos validados del sistema',
                    'color' => '#16a34a',
                    'route' => route('formatos.index'),
                    'visible' => true // Todos pueden ver
                ],
                [
                    'title' => 'Usuarios',
                    'icon' => 'bi-people',
                    'description' => 'Administración de usuarios',
                    'color' => '#7c3aed',
                    'route' => route('admin.usuarios.index'),
                    'visible' => in_array($userRole, ['superadmin', 'admin'])
                ],
                [
                    'title' => 'Historial de versiones',
                    'icon' => 'bi-trash',
                    'description' => 'Cambios realizados en documentos',
                    'color' => '#0891b2',
                    'route' => '#', // Módulo en desarrollo
                    'visible' => $userRole === 'superadmin' // SOLO SUPERADMIN
                ],
                [
                    'title' => 'Notificaciones',
                    'icon' => 'bi-bell',
                    'description' => 'Alertas y notificaciones',
                    'color' => '#ea580c',
                    'route' => '#', // Módulo en desarrollo
                    'visible' => true // Todos pueden ver
                ],
                [
                    'title' => 'Avisos',
                    'icon' => 'bi-megaphone',
                    'description' => 'Avisos',
                    'color' => '#4f46e5',
                    'route' => '#', // Módulo en desarrollo
                    'visible' => true // Todos pueden ver
                ]
            ];
            
            // Filtrar módulos según el rol
            $modules = array_filter($allModules, function($module) {
                return $module['visible'];
            });
        @endphp

        @foreach($modules as $module)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card dashboard-card h-100 border-0 shadow-sm" 
                 data-module="{{ $module['title'] }}"
                 data-route="{{ $module['route'] }}"
                 onclick="handleDashboardClick('{{ $module['title'] }}', '{{ $module['route'] }}')"
                 style="cursor: pointer;">
                <div class="card-body text-center p-4">
                    <div class="dashboard-icon mb-3" style="background-color: {{ $module['color'] }}20; border-color: {{ $module['color'] }}">
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
    /* Dashboard Card Styles */
    .dashboard-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        background: #fff;
        min-height: 200px;
        display: flex;
        flex-direction: column;
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
        border: 2px solid;
        transition: all 0.3s ease;
    }

    .dashboard-card:hover .dashboard-icon {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .card-text {
        flex-grow: 1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-card {
            margin-bottom: 15px;
        }
        
        .dashboard-icon {
            width: 60px;
            height: 60px;
        }
        
        .dashboard-icon i {
            font-size: 1.5rem !important;
        }
        
        .card-title {
            font-size: 1.1rem;
        }
        
        .card-text {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .dashboard-card {
            min-height: 180px;
        }
        
        .dashboard-icon {
            width: 50px;
            height: 50px;
        }
        
        .dashboard-icon i {
            font-size: 1.25rem !important;
        }
    }

    /* Animation for cards */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dashboard-card {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }

    .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
    .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
    .dashboard-card:nth-child(4) { animation-delay: 0.4s; }
    .dashboard-card:nth-child(5) { animation-delay: 0.5s; }
    .dashboard-card:nth-child(6) { animation-delay: 0.6s; }
    .dashboard-card:nth-child(7) { animation-delay: 0.7s; }
    .dashboard-card:nth-child(8) { animation-delay: 0.8s; }

    /* Custom Toast Notification */
    .custom-toast {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1050;
        min-width: 300px;
        max-width: 350px;
        animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Función para mostrar notificación tipo toast
    function showToast(moduleName) {
        // Crear el toast
        const toast = document.createElement('div');
        toast.className = 'custom-toast';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-primary text-white">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong class="me-auto">Módulo ${moduleName}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <p>El módulo <strong>${moduleName}</strong> está actualmente en desarrollo.</p>
                    <p class="mb-0"><small>Disponible próximamente</small></p>
                </div>
            </div>
        `;
        
        // Agregar al DOM
        document.body.appendChild(toast);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
        
        // Configurar botón de cerrar
        const closeBtn = toast.querySelector('.btn-close');
        closeBtn.addEventListener('click', () => {
            toast.remove();
        });
    }

    // Función para manejar clic en tarjetas del dashboard
    function handleDashboardClick(module, route) {
        console.log('Clic en módulo:', module, 'Ruta:', route);
        
        // Efecto visual de clic
        const card = event.currentTarget;
        card.style.transform = 'scale(0.98)';
        setTimeout(() => {
            card.style.transform = '';
        }, 150);
        
        // Si la ruta no es #, redirigir
        if (route && route !== '#') {
            window.location.href = route;
        } else {
            // Para módulos en desarrollo
            showToast(module);
        }
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Permitir navegación con teclado (Enter/Space)
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            card.setAttribute('tabindex', '0');
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const module = this.getAttribute('data-module');
                    const route = this.getAttribute('data-route');
                    handleDashboardClick(module, route);
                }
            });
        });
    });
</script>
@endpush



