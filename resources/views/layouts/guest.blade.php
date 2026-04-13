{{-- ============================================================ --}}
{{-- ARCHIVO: GUEST.BLADE.PHP                                    --}}
{{-- TIPO: LAYOUT (PLANTILLA BASE)                               --}}
{{-- DESCRIPCIÓN: LAYOUT PARA PÁGINAS PÚBLICAS (SIN SESIÓN).    --}}
{{-- SE USA EN: PÁGINA DE INICIO (LANDING PAGE) Y LOGIN.        --}}
{{-- INCLUYE: NAVBAR, CONTENIDO PRINCIPAL Y FOOTER.             --}}
{{-- ============================================================ --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SAMS - Sistema de Gestión de Calidad')</title>

    {{-- ================================================ --}}
    {{-- LIBRERÍAS CSS EXTERNAS                           --}}
    {{-- · BOOTSTRAP 5.3  → ESTILOS Y COMPONENTES        --}}
    {{-- · BOOTSTRAP ICONS → ÍCONOS                      --}}
    {{-- ================================================ --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* ===== VARIABLES CSS GLOBALES ===== */
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

        /* ===== ESTILOS GENERALES DEL BODY ===== */
        body {
            background: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ===== NAVBAR TRANSPARENTE (FIJO EN LA PARTE SUPERIOR) ===== */
        /* SE VUELVE SÓLIDO AL HACER SCROLL CON LA CLASE .scrolled */
        .navbar-landing {
            background: rgba(128, 0, 0, 1.0);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(183, 11, 11, 0.1);
            transition: all 0.3s ease;
            padding: 0.8rem 0;
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

        /* ===== SECCIÓN HERO (PANTALLA PRINCIPAL) ===== */
        /* FONDO CON GRADIENTE DE COLORES Y PATRÓN DE CUADRÍCULA */
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

        /* ===== TARJETAS (CARDS) ===== */
        /* .card-disabled → TARJETA DESHABILITADA (SIN HOVER) */
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

        /* ===== BOTONES PERSONALIZADOS ===== */
        /* .btn-primary-custom → BOTÓN ROJO SÓLIDO        */
        /* .btn-outline-custom → BOTÓN TRANSPARENTE       */
        /* .btn-disabled       → BOTÓN DESHABILITADO      */
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
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-outline-custom {
            border: 2px solid rgb(217, 204, 204);
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

        /* ===== TARJETAS DE ESTADÍSTICAS ===== */
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

        /* ===== SECCIONES ===== */
        .section {
            padding: 4rem 0;
        }

        .section-dark {
            background: #0f1419;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #800000;
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            margin-top: 4rem;
        }

        /* ===== ANIMACIÓN DE ENTRADA ===== */
        /* LOS ELEMENTOS CON CLASE .animate-on-scroll SE ANIMAN  */
        /* AL APARECER EN PANTALLA GRACIAS AL IntersectionObserver */
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

        /* =====================================================
           ESTILOS RESPONSIVOS - GUEST.BLADE.PHP
        ===================================================== */

        /* TABLETS (769px a 992px) */
        @media (min-width: 769px) and (max-width: 992px) {
            .navbar-landing {
                padding: 0.5rem 0 !important;
            }
            .navbar-landing .container {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 0.5rem !important;
            }
            .navbar-brand img {
                height: 35px !important;
                width: auto !important;
            }
            .right-section {
                width: auto !important;
                flex-shrink: 0 !important;
                gap: 0.5rem !important;
            }
            .right-section .d-flex.align-items-center {
                font-size: 0.7rem !important;
                white-space: nowrap !important;
            }
            .right-section .d-flex.align-items-center i {
                font-size: 0.75rem !important;
            }
            
            .hero-title {
                font-size: 2.5rem !important;
            }
            .hero-subtitle {
                font-size: 1rem !important;
            }
            
            .card-custom {
                margin-bottom: 1rem !important;
            }
            
            .footer {
                padding: 1rem 0 !important;
                margin-top: 2rem !important;
            }
            .footer p {
                font-size: 0.7rem !important;
            }
            
            .stats-number {
                font-size: 2rem !important;
            }
            .stats-label {
                font-size: 0.8rem !important;
            }
        }

        /* MÓVILES (768px y menos) */
        @media (max-width: 768px) {
            .navbar-landing {
                padding: 0.5rem 0 !important;
            }
            .navbar-landing .container {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            .navbar-brand img {
                height: 32px !important;
            }
            
            /* FECHA EN ESQUINA DERECHA DEL NAVBAR */
            .right-section {
                order: 0 !important;
                width: auto !important;
                justify-content: flex-end !important;
                margin-top: 0 !important;
            }
            .right-section .d-flex.align-items-center {
                font-size: 0.6rem !important;
            }
            .right-section .d-flex.align-items-center i {
                font-size: 0.7rem !important;
            }
            
            .hero-title {
                font-size: 2rem !important;
            }
            .hero-subtitle {
                font-size: 0.9rem !important;
            }
            
            .btn-primary-custom,
            .btn-outline-custom {
                padding: 0.6rem 1.5rem !important;
                font-size: 0.9rem !important;
            }
            
            .row-cols-md-3 .col {
                width: 50% !important;
                flex: 0 0 50% !important;
            }
            
            .footer {
                padding: 0.75rem 0 !important;
                margin-top: 1.5rem !important;
            }
            .footer p {
                font-size: 0.6rem !important;
            }
            
            .stats-number {
                font-size: 1.5rem !important;
            }
            .stats-label {
                font-size: 0.7rem !important;
            }
            .stats-card {
                padding: 1rem !important;
            }
            
            .section {
                padding: 2rem 0 !important;
            }
        }

        /* MÓVILES MUY PEQUEÑOS (480px y menos) */
        @media (max-width: 480px) {
            .navbar-brand img {
                height: 28px !important;
            }
            .right-section .d-flex.align-items-center {
                font-size: 0.55rem !important;
            }
            .right-section .d-flex.align-items-center i {
                font-size: 0.6rem !important;
            }
            .hero-title {
                font-size: 1.8rem !important;
            }
            .hero-subtitle {
                font-size: 0.8rem !important;
            }
            .btn-primary-custom,
            .btn-outline-custom {
                padding: 0.5rem 1rem !important;
                font-size: 0.8rem !important;
            }
            
            .row-cols-md-3 .col {
                width: 100% !important;
                flex: 0 0 100% !important;
            }
            
            .footer p {
                font-size: 0.55rem !important;
            }
            .stats-number {
                font-size: 1.2rem !important;
            }
        }
    </style>

    {{-- ESTILOS ADICIONALES INYECTADOS DESDE LAS VISTAS HIJAS --}}
    @stack('styles')
</head>
<body>

    {{-- ================================================ --}}
    {{-- NAVBAR FIJO EN LA PARTE SUPERIOR                 --}}
    {{-- MUESTRA EL LOGO DE LA INSTITUCIÓN Y LA FECHA     --}}
    {{-- ACTUAL EN LA ESQUINA DERECHA.                    --}}
    {{-- NO TIENE MENÚ HAMBURGUESA (SIN TOGGLER).         --}}
    {{-- ================================================ --}}
    <nav class="navbar navbar-expand fixed-top navbar-landing" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand">
                <img src="https://lh3.googleusercontent.com/proxy/iBImmZjJODGa39TgtflRih-vmGJwiTPpBotgG80_ckaAxtEWogKYQLf1ACpY-Nqr_-QnZM01aRtgtNef_Gk-m6An8VR-9ovpNw" alt="UPTEX Logo" style="height: 50px; width: auto;">
            </a>

            {{-- SECCIÓN DERECHA: FECHA ACTUAL (SE ACTUALIZA EN TIEMPO REAL) --}}
            <div class="right-section d-flex align-items-center gap-3">
                <div class="d-flex align-items-center text-white">
                    <i class="bi bi-calendar3 me-2"></i>
                    <span id="fecha-actual"></span>
                </div>
            </div>
        </div>
    </nav>

    {{-- ================================================ --}}
    {{-- CONTENIDO PRINCIPAL                              --}}
    {{-- AQUÍ SE INYECTA EL CONTENIDO DE CADA VISTA HIJA  --}}
    {{-- QUE USE ESTE LAYOUT.                             --}}
    {{-- ================================================ --}}
    <main>
        @yield('content')
    </main>

    {{-- ================================================ --}}
    {{-- FOOTER                                           --}}
    {{-- MUESTRA EL NOMBRE DEL SISTEMA Y EL AÑO ACTUAL.  --}}
    {{-- ================================================ --}}
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-white mb-0">
                        &copy; {{ date('Y') }} SAMS Infinity. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    {{-- BOOTSTRAP JS (NECESARIO PARA MODALES, DROPDOWNS, ETC.) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ============================================================
        // FUNCIÓN: actualizarFecha
        // MUESTRA LA FECHA ACTUAL EN FORMATO DD/MM/YYYY EN EL NAVBAR.
        // SE EJECUTA CADA SEGUNDO PARA MANTENERSE ACTUALIZADA.
        // ============================================================
        function actualizarFecha() {
            const hoy = new Date();
            const dia = String(hoy.getDate()).padStart(2, '0');
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const anio = hoy.getFullYear();
            const fechaElement = document.getElementById('fecha-actual');
            if (fechaElement) {
                fechaElement.textContent = `${dia}/${mes}/${anio}`;
            }
        }
        
        // ACTUALIZA LA FECHA AL CARGAR Y CADA SEGUNDO
        setInterval(actualizarFecha, 1000);
        actualizarFecha();

        // TOKEN CSRF DISPONIBLE GLOBALMENTE PARA PETICIONES AJAX
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };

        document.addEventListener('DOMContentLoaded', function() {

            // ============================================================
            // EFECTO SCROLL EN EL NAVBAR
            // AL BAJAR MÁS DE 50px → AGREGA CLASE .scrolled AL NAVBAR
            // PARA CAMBIAR SU ESTILO Y AGREGAR SOMBRA.
            // ============================================================
            const navbar = document.getElementById('mainNavbar');
            
            if (navbar) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                });
            }

            // ============================================================
            // ANIMACIÓN AL HACER SCROLL
            // LOS ELEMENTOS CON CLASE .animate-on-scroll SE ANIMAN
            // CUANDO ENTRAN AL ÁREA VISIBLE DE LA PANTALLA.
            // ============================================================
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

            // OBSERVAR TODOS LOS ELEMENTOS CON .animate-on-scroll
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });

        // ============================================================
        // MANEJO DE ELEMENTOS PROTEGIDOS (SOLO PARA USUARIOS SIN SESIÓN)
        // SI UN USUARIO NO AUTENTICADO HACE CLIC EN UN ELEMENTO CON
        // CLASE .requires-auth → LE PREGUNTA SI QUIERE IR AL LOGIN.
        // ============================================================
        @guest
        document.addEventListener('DOMContentLoaded', function() {
            const disabledElements = document.querySelectorAll('.requires-auth');
            
            disabledElements.forEach(element => {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Show login modal or redirect
                    if (confirm('Debes iniciar sesión para acceder a esta funcionalidad. ¿Ir al login?')) {
                        window.location.href = '{{ route("login") }}';
                    }
                });
            });
        });
        @endguest
    </script>

    {{-- SCRIPTS ADICIONALES INYECTADOS DESDE LAS VISTAS HIJAS --}}
    @stack('scripts')
</body>
</html>