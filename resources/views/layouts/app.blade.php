<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SAMS - Sistema de Gestión de la Calidad')</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Auth CSS solo para páginas de autenticación -->
    @if(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*'))
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    @endif
    
    <!-- Fix para texto visible -->
    <link href="{{ asset('css/fix-text.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #800000 0%, #800000 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-bg: #f8f9fa;
            --card-bg: #9b2226;
            --text-light: #ffffff;
            --text-muted: #ffffff;
            --border-color: #ffffff;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
        }

        body {
            background: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar Styles */
        .navbar-custom {
            background: var(--primary-gradient);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        /* Card Styles */
        .card-custom {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        /* Button Styles */
        .btn-primary-custom {
            background: var(--primary-gradient);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Main Content */
        .main-content {
            min-height: calc(100vh - 80px);
            padding-top: 2rem;
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--card-bg);
            border-right: 1px solid var(--border-color);
            height: calc(100vh - 80px);
            position: fixed;
            width: 250px;
            padding: 1rem;
        }

        .sidebar-link {
            color: var(--text-light);
            text-decoration: none;
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover, .sidebar-link.active {
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
        }

        .content-with-sidebar {
            margin-left: 250px;
            padding: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .content-with-sidebar {
                margin-left: 0;
                padding: 1rem;
            }
        }

        /* Alert Styles */
        .alert-custom {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }

        .alert-danger-custom {
            background: linear-gradient(135deg, #f56565, #e53e3e);
            color: white;
        }

        /* Centrado mejorado para el mensaje de bienvenida */
        .welcome-container {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .welcome-text {
            white-space: nowrap;
            font-size: 1.25rem;
        }
        
        /* Ajustes para elementos laterales */
        .left-section, .right-section {
            width: 200px; /* Ancho fijo para balancear */
        }
        
        @media (max-width: 992px) {
            .welcome-text {
                font-size: 1.1rem;
            }
            .left-section, .right-section {
                width: 150px;
            }
        }
        
        @media (max-width: 768px) {
            .welcome-container {
                position: static;
                transform: none;
                text-align: center;
                margin: 0.5rem 0;
            }
            .welcome-text {
                white-space: normal;
                font-size: 1rem;
            }
            .left-section, .right-section {
                width: auto;
            }
        }

        /* Footer Styles - Agregados desde el segundo layout */
        .footer {
            background: #800000;
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            margin-top: 4rem;
        }
    </style>

    @stack('styles')
</head>

<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container position-relative">
        
        <!-- Izquierda: UPTEX -->
        <div class="left-section">
            <a class="navbar-brand" >
                <img src="https://lh3.googleusercontent.com/proxy/iBImmZjJODGa39TgtflRih-vmGJwiTPpBotgG80_ckaAxtEWogKYQLf1ACpY-Nqr_-QnZM01aRtgtNef_Gk-m6An8VR-9ovpNw" alt="UPTEX Logo" style="height: 50px; width: auto;">
            </a>
        </div>

        <!-- Centro: Mensaje de bienvenida - SOLO PARA USUARIOS AUTENTICADOS -->
        @auth
        <div class="welcome-container">
            <span class="navbar-text fw-bold text-white welcome-text">
                Sistema de Gestión de la Calidad 
            </span>
        </div>
        @endauth

        <!-- Derecha: Usuario + Fecha - SOLO PARA USUARIOS AUTENTICADOS -->
        @auth
        <div class="right-section d-flex align-items-center gap-3">

            <!-- FECHA -->
            <div class="d-flex align-items-center text-white">
                <i class="bi bi-calendar3 me-2"></i>
                <span id="fecha-actual"></span>
            </div>

            <!-- Usuario -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('dashboard') }}">
                                <i class="bi bi-gear"></i> Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        @endauth

        <!-- Para páginas de login/register, mostrar solo el título -->
        @guest
        <div class="welcome-container">
            <span class="navbar-text fw-bold text-white welcome-text">
                Sistema de Gestión de Calidad
            </span>
        </div>
        @endguest
    </div>
</nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Success Messages -->
        @if (session('success'))
            <div class="container">
                <div class="alert alert-success-custom alert-custom alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <!-- Error Messages -->
        @if (session('error'))
            <div class="container">
                <div class="alert alert-danger-custom alert-custom alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Footer - Agregado desde el segundo layout -->
    <footer class="footer">
        <div class="row">
            <div class="col-12 text-center">
                <p class="text-white mb-0">
                    &copy; {{ date('Y') }} SAMS Infinity. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global CSRF token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        // Función para actualizar la fecha - SOLO SE EJECUTA SI EL ELEMENTO EXISTE
        function actualizarFecha() {
            const fechaElement = document.getElementById('fecha-actual');
            if (fechaElement) {
                const hoy = new Date();
                const dia = String(hoy.getDate()).padStart(2, '0');
                const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                const anio = hoy.getFullYear();
                fechaElement.textContent = `${dia}/${mes}/${anio}`;
            }
        }

        // Actualizar fecha cada segundo solo si el usuario está autenticado
        @auth
        setInterval(actualizarFecha, 1000);
        actualizarFecha();
        @endauth
    </script>

    @stack('scripts')
</body>
</html>