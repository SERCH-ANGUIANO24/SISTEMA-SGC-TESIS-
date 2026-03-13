<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SAMS - Sistema de Gestión de Calidad' }}</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #800000 0%, #800000 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --hero-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            --dark-bg: #ffffff;
            --card-bg: #9B2226;
            --text-light: #ffffff;
            --text-muted: #ffffff;
            --border-color: #ffffff;
        }

        body {
            background: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar Transparente para Landing */
        .navbar-landing {
            background: rgba(128, 0, 0, 1.0);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(183, 11, 11, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-landing.scrolled {
            background: var(--primary-gradient);
            box-shadow: 0 4px 6px rgba(196, 121, 121, 0.1);
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
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        /* SOLUCIÓN: Fijar la fecha a la derecha */
        .navbar-date-container {
            margin-left: auto;
            display: flex;
            align-items: center;
        }

        .date-display {
            display: flex;
            align-items: center;
            color: white;
            white-space: nowrap;
        }

        /* Hero Section */
        .hero-section {
            background: var(--hero-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Cards */
        .card-custom {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            height: 100%;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
        }

        .card-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .card-disabled:hover {
            transform: none;
        }

        /* Buttons */
        .btn-primary-custom {
            background: #ac3939;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-outline-custom {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
            background: transparent;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
            text-decoration: none;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Stats Section */
        .stats-card {
            background: var(--primary-gradient);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Section Spacing */
        .section {
            padding: 4rem 0;
        }

        .section-dark {
            background: #0f1419;
        }

        /* Footer */
        .footer {
            background: #800000;
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            margin-top: 4rem;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .btn-primary-custom,
            .btn-outline-custom {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }

            .navbar-date-container {
                margin-left: 0;
                margin-top: 10px;
            }
        }
        
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-landing" id="mainNavbar">
        <div class="container">
            <!-- Izquierda: Marca UPTEX -->
            <a class="navbar-brand" >
                <img src="https://lh3.googleusercontent.com/proxy/iBImmZjJODGa39TgtflRih-vmGJwiTPpBotgG80_ckaAxtEWogKYQLf1ACpY-Nqr_-QnZM01aRtgtNef_Gk-m6An8VR-9ovpNw" alt="UPTEX Logo" style="height: 50px; width: auto;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                </ul>

                <!-- Derecha: Fecha (se mantiene fija a la derecha) -->
                <div class="navbar-date-container">
                    <div class="date-display">
                        <i class="bi bi-calendar3 me-2"></i>
                        <span id="fecha-actual"></span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
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
        // Función para actualizar la fecha actual
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
        
        // Actualizar fecha cada segundo y al cargar la página
        setInterval(actualizarFecha, 1000);
        actualizarFecha();

        // Global CSRF token
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        // Navbar scroll effect
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.getElementById('mainNavbar');
            
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, observerOptions);

            // Observe all elements with animate class
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>