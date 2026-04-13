@extends('layouts.guest')

@section('content')
<!-- =====================================================
     SECCIÓN PRINCIPAL DE BIENVENIDA
     ===================================================== -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <!-- =====================================================
                     LOGO DE LA UNIVERSIDAD
                     ===================================================== -->
                <div class="mb-4">
                    <img src="https://lh3.googleusercontent.com/proxy/iBImmZjJODGa39TgtflRih-vmGJwiTPpBotgG80_ckaAxtEWogKYQLf1ACpY-Nqr_-QnZM01aRtgtNef_Gk-m6An8VR-9ovpNw" alt="Logo UPTEX" 
                         class="img-fluid" style="max-height: 200px; border: 3px solid rgba(255,255,255,0.2);">
                </div>

                <!-- =====================================================
                     TÍTULO PRINCIPAL - TEXTO DE BIENVENIDA
                     ===================================================== -->
                <h1 class="hero-title">Bienvenido<br>
                    <span class="text-gradient">al Sistema de Gestión de la Calidad</span>
                </h1>

<!-- =====================================================
     SECCIÓN PARA USUARIOS NO AUTENTICADOS
     ===================================================== -->
@guest
<section class="section section-dark">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-6 fw-bold text-black mb-4">¿Listo para comenzar?</h2>
                <p class="lead text-white-75 mb-5">
                    Inicia sesión y accede a todas las funcionalidades.
                </p>
                
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-outline-custom btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endguest

@endsection

@push('styles')
<style>
    /* =====================================================
       CAMBIO DE COLORES - TEXTO NEGRO Y FONDO GRIS CLARO
       ===================================================== */
    body {
        background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%) !important;
        color: #000000 !important;
    }

    .hero-section {
        background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%) !important;
        color: #000000 !important;
    }

    .hero-title {
        color: #000000 !important;
    }

    .text-gradient {
        background: linear-gradient(135deg, #000000 0%, #333333 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }

    /* =====================================================
       ESTILOS ESPECÍFICOS SOLO PARA EL CUADRO "¿LISTO PARA COMENZAR?"
       ===================================================== */
    .section-dark {
        background: #800000 !important;
        color: #000000 !important;
        border-radius: 10px;
        margin: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0); /* BORDE DE COLOR DE LA CARD */
    }

    .section-dark h2,
    .section-dark .display-6 {
        color: #ffffffff !important; 
    }

    .section-dark .lead {
        color: #ffffffff !important;
    }

    .section-dark .btn-outline-custom {
        background: transparent !important;
        color: #ffffffff !important;
        border: 2px solid #ffffffff !important;
    }

    .section-dark .btn-outline-custom:hover {
        background: #9B2226 !important;
        color: #ffffff !important;
    }
    /* FIN DE ESTILOS ESPECÍFICOS */

    .btn-outline-custom {
        background: transparent !important;
        color: #000000 !important;
        border: 2px solid #000000 !important;
    }

    .btn-outline-custom:hover {
        background: #000000 !important;
        color: #ffffff !important;
    }

    /* =====================================================
        EFECTO DE TEXTO CON DEGRADADO ( EN NEGRO)
       ===================================================== */
    .text-gradient {
        background: linear-gradient(135deg, #000000 0%, #333333 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* =====================================================
       ESTILOS RESPONSIVOS 
       ===================================================== */

    /* TABLETS (769PX A 992PX) */
    @media (min-width: 769px) and (max-width: 992px) {
        .hero-title {
            font-size: 2.2rem !important;
            line-height: 1.3 !important;
        }
        
        .hero-section .container img {
            max-height: 150px !important;
        }
        
        .section-dark {
            margin: 15px !important;
            padding: 0.5rem 1rem !important;
        }
        
        .section-dark h2.display-6 {
            font-size: 1.5rem !important;
        }
        
        .section-dark .lead {
            font-size: 1rem !important;
            margin-bottom: 1.5rem !important;
        }
        
        .btn-outline-custom.btn-lg {
            padding: 0.5rem 1.5rem !important;
            font-size: 0.9rem !important;
        }
    }

    /* MÓVILES (768PX Y MENOS) */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 1.8rem !important;
            line-height: 1.3 !important;
        }
        
        .hero-section .container img {
            max-height: 120px !important;
        }
        
        .hero-section .container {
            padding: 1rem !important;
        }
        
        .section-dark {
            margin: 10px !important;
            padding: 0.5rem 0.8rem !important;
            border-radius: 8px !important;
        }
        
        .section-dark h2.display-6 {
            font-size: 1.3rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        .section-dark .lead {
            font-size: 0.9rem !important;
            margin-bottom: 1rem !important;
        }
        
        .btn-outline-custom.btn-lg {
            padding: 0.4rem 1.2rem !important;
            font-size: 0.85rem !important;
        }
        
        .d-flex.flex-column.flex-sm-row {
            flex-direction: column !important;
            gap: 0.75rem !important;
        }
        
        .btn-outline-custom {
            width: 100% !important;
            text-align: center !important;
            justify-content: center !important;
        }
    }

    /* MÓVILES MUY PEQUEÑOS (480PX Y MENOS) */
    @media (max-width: 480px) {
        .hero-title {
            font-size: 1.4rem !important;
        }
        
        .hero-section .container img {
            max-height: 100px !important;
        }
        
        .section-dark h2.display-6 {
            font-size: 1.1rem !important;
        }
        
        .section-dark .lead {
            font-size: 0.8rem !important;
        }
        
        .btn-outline-custom.btn-lg {
            padding: 0.35rem 1rem !important;
            font-size: 0.8rem !important;
        }
        
        .text-gradient {
            font-size: 1.4rem !important;
        }
    }

    @media (max-width: 576px) {
        .hero-title {
            font-size: 2.5rem;
            line-height: 1.2;
        }
        
        .stats-card {
            padding: 1.5rem 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
        }
        
        .section-dark {
            margin: 10px;
        }
    }

    .card-disabled::after {
        content: 'Requiere inicio de sesión';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.85rem;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .card-disabled:hover::after {
        opacity: 1;
    }

    .card-disabled {
        position: relative;
    }
</style>
@endpush

@push('scripts')
<script>
    // =====================================================
    // DESPLAZAMIENTO SUAVE PARA ENLACES DE ANCLA
    // =====================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // =====================================================
    // ANIMACIÓN DE NÚMEROS DE ESTADÍSTICAS AL HACER SCROLL
    // =====================================================
    document.addEventListener('DOMContentLoaded', function() {
        const statsNumbers = document.querySelectorAll('.stats-number');
        
        const animateNumbers = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const finalNumber = target.textContent;
                    const isPercentage = finalNumber.includes('%');
                    const isFraction = finalNumber.includes('/');
                    
                    if (!isPercentage && !isFraction) {
                        // ANIMACIÓN DE CONTEO PARA VALORES NUMÉRICOS
                        const number = parseInt(finalNumber.replace('+', ''));
                        let current = 0;
                        const increment = number / 50;
                        
                        const counter = setInterval(() => {
                            current += increment;
                            if (current >= number) {
                                target.textContent = finalNumber;
                                clearInterval(counter);
                            } else {
                                target.textContent = Math.floor(current) + (finalNumber.includes('+') ? '+' : '');
                            }
                        }, 40);
                    }
                    
                    observer.unobserve(target);
                }
            });
        };

        const observer = new IntersectionObserver(animateNumbers, {
            threshold: 0.5
        });

        statsNumbers.forEach(stat => observer.observe(stat));
    });
</script>
@endpush