@extends('layouts.guest')

@section('title', 'Login - SAMS')

@section('content')
<style>
    /* =====================================================
       DEFINIR UNA VARIABLE PARA EL COLOR GUINDA BASE Y EL GUINDA OSCURO
       ===================================================== */
    :root {
        --guinda-base: #800000; /* GUINDA */
        --guinda-darker: #5b0000; /* GUINDA MÁS OSCURO PARA HOVER/ACTIVOS */
        --guinda-light: #ac3939; /* GUINDA UN POCO MÁS CLARO PARA BORDES SECUNDARIOS */
    }

    /* =====================================================
       ESTILOS DEL CONTENEDOR DE AUTENTICACIÓN
       ===================================================== */
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #ffffffff; /* UN FONDO CLARO PARA LA PÁGINA DE LOGIN */
        padding: 20px;
    }

    /* =====================================================
       ESTILOS DE LA TARJETA DE AUTENTICACIÓN (EL FORMULARIO)
       ===================================================== */
    .auth-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0);
        padding: 40px;
        width: 100%;
        max-width: 400px;
        text-align: center;
    }

    /* =====================================================
       ESTILOS DEL LOGO/TÍTULO PRINCIPAL
       ===================================================== */
    .auth-logo h1 {
        color: #000000; /* COLOR GUINDA PARA EL TÍTULO */
        font-size: 2.2rem;
        margin-bottom: 25px;
        font-weight: 700;
    }

    /* =====================================================
       TÍTULO DEL FORMULARIO
       ===================================================== */
    .auth-title {
        color: #333;
        margin-bottom: 30px;
        font-weight: 600;
        font-size: 1.8rem;
    }

    /* =====================================================
       MENSAJES DE ALERTA/ERROR
       ===================================================== */
    .auth-alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .auth-alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .auth-alert .bi {
        font-size: 1.5rem;
        margin-right: 15px;
        color: #dc3545;
    }
    
    /* =====================================================
       GRUPOS DE FORMULARIO
       ===================================================== */
    .auth-form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .auth-form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
        font-size: 1rem;
    }

    .auth-form-label .bi {
        margin-right: 8px;
        color: var(--guinda-base); /* ICONOS DE LABEL EN GUINDA */
    }

    /* =====================================================
       CAMPOS DE ENTRADA
       ===================================================== */
    .auth-form-control {
        width: 100%;
        padding: 12px 40px 12px 15px; /* ESPACIO PARA EL ICONO */
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .auth-form-control:focus {
        border-color: var(#737373); /* BORDE GRIS AL ENFOCAR */
        box-shadow: 0 0 0 0.25rem #b8c9da; /* SOMBRA AZUL*/
        outline: none;
    }

    .auth-form-control.is-invalid {
        border-color: #dc3545;
        padding-right: 15px; /* AJUSTE PARA EL ICONO SI BOOTSTRAP YA LO AGREGA */
    }
    
    .auth-input-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 1.1rem;
    }

    .auth-form-control:focus + .auth-input-icon {
        color: var(--guinda-base); /* ICONO GUINDA AL ENFOCAR */
    }

    /* =====================================================
       BOTÓN PRINCIPAL (INICIAR SESIÓN)
       ===================================================== */
    .auth-btn-primary {
        background-color: #800000; /* GUINDA */
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 25px;
        width: 100%;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
    }

    .auth-btn-primary .bi {
        margin-right: 10px;
    }

    .auth-btn-primary:hover {
        background-color: #800000; /* GUINDA MÁS OSCURO EN HOVER */
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
    }

    .auth-btn-primary:disabled {
        background-color: #a87e7e; /* UN GUINDA MÁS CLARO PARA DESHABILITADO */
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* =====================================================
       DIVISOR
       ===================================================== */
    .auth-divider {
        margin: 30px 0;
        position: relative;
        text-align: center;
    }

    .auth-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background-color: #e0e0e0;
        z-index: 1;
    }

    .auth-divider span {
        background-color: #ffffff;
        padding: 0 15px;
        position: relative;
        z-index: 2;
        color: #777;
    }

    /* =====================================================
       BOTÓN SECUNDARIO (REGISTRARSE)
       ===================================================== */
    .auth-btn-secondary {
        background-color: #f0f0f0; /* FONDO CLARO PARA EL BOTÓN SECUNDARIO */
        color: var(--guinda-base); /* TEXTO GUINDA */
        border: 1px solid var(--guinda-light); /* BORDE GUINDA MÁS CLARO */
        border-radius: 8px;
        padding: 12px 25px;
        width: 100%;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none; /* ASEGURA QUE NO TENGA SUBRAYADO */
    }

    .auth-btn-secondary .bi {
        margin-right: 10px;
    }

    .auth-btn-secondary:hover {
        background-color: #800000; /* FONDO GUINDA MÁS CLARO EN HOVER */
        color: white; /* TEXTO BLANCO EN HOVER */
        border-color: var(--guinda-light);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(128, 0, 0, 0.1);
    }

    /* =====================================================
       PIE DE PÁGINA DEL FORMULARIO (VOLVER AL INICIO)
       ===================================================== */
    .auth-footer {
        margin-top: 30px;
    }

    .auth-link {
        color: #000; 
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
        display: inline-flex; 
        align-items: center;
    }

    .auth-link .bi {
        margin-right: 5px;
        color: #000;
        transition: color 0.2s ease;
    }

    .auth-link:hover {
        color: #000; 
    }

    .auth-link:hover .bi {
        color: #000; 
    }

    /* =====================================================
       ESTILOS PARA EL ESTADO DE CARGA
       ===================================================== */
    .auth-loading .auth-btn-primary {
        background-color:#800000;
        cursor: progress;
    }

    /* =====================================================
       FEEDBACK DE VALIDACIÓN
       ===================================================== */
    .field-error {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 5px;
        text-align: left;
    }

    /* =====================================================
       ESTILOS RESPONSIVOS - LOGIN
       (MISMO PATRÓN QUE LOS MÓDULOS ANTERIORES)
    ===================================================== */

    /* TABLETS (769PX A 992PX) */
    @media (min-width: 769px) and (max-width: 992px) {
        .auth-card {
            padding: 30px !important;
            max-width: 380px !important;
        }
        .auth-logo h1 {
            font-size: 1.8rem !important;
        }
        .auth-title {
            font-size: 1.5rem !important;
            margin-bottom: 25px !important;
        }
        .auth-form-label {
            font-size: 0.9rem !important;
        }
        .auth-form-control {
            padding: 10px 35px 10px 12px !important;
            font-size: 0.9rem !important;
        }
        .auth-btn-primary {
            padding: 10px 20px !important;
            font-size: 1rem !important;
        }
        .auth-link {
            font-size: 0.85rem !important;
        }
    }

    /* MÓVILES (768PX Y MENOS) */
    @media (max-width: 768px) {
        .auth-container {
            padding: 15px !important;
        }
        .auth-card {
            padding: 25px 20px !important;
            max-width: 100% !important;
            border-radius: 12px !important;
        }
        .auth-logo h1 {
            font-size: 1.5rem !important;
            margin-bottom: 20px !important;
        }
        .auth-title {
            font-size: 1.3rem !important;
            margin-bottom: 20px !important;
        }
        .auth-form-group {
            margin-bottom: 15px !important;
        }
        .auth-form-label {
            font-size: 0.85rem !important;
            margin-bottom: 5px !important;
        }
        .auth-form-label .bi {
            font-size: 0.85rem !important;
        }
        .auth-form-control {
            padding: 10px 35px 10px 12px !important;
            font-size: 0.85rem !important;
        }
        .auth-input-icon {
            font-size: 0.9rem !important;
            right: 12px !important;
        }
        .auth-btn-primary {
            padding: 10px 15px !important;
            font-size: 0.9rem !important;
            margin-top: 5px !important;
        }
        .auth-btn-primary .bi {
            font-size: 0.9rem !important;
        }
        .auth-footer {
            margin-top: 20px !important;
        }
        .auth-link {
            font-size: 0.8rem !important;
        }
        .auth-link .bi {
            font-size: 0.8rem !important;
        }
        .auth-alert {
            padding: 10px 12px !important;
            font-size: 0.8rem !important;
        }
        .auth-alert .bi {
            font-size: 1.1rem !important;
            margin-right: 10px !important;
        }
        .field-error {
            font-size: 0.75rem !important;
        }
    }

    /* MÓVILES MUY PEQUEÑOS (480PX Y MENOS) */
    @media (max-width: 480px) {
        .auth-card {
            padding: 20px 15px !important;
        }
        .auth-logo h1 {
            font-size: 1.3rem !important;
        }
        .auth-title {
            font-size: 1.2rem !important;
        }
        .auth-form-control {
            padding: 8px 30px 8px 10px !important;
            font-size: 0.8rem !important;
        }
        .auth-form-label {
            font-size: 0.8rem !important;
        }
        .auth-btn-primary {
            padding: 8px 12px !important;
            font-size: 0.85rem !important;
        }
        .auth-link {
            font-size: 0.75rem !important;
        }
        .auth-link .bi {
            font-size: 0.75rem !important;
        }
    }
</style>

<!-- =====================================================
     CONTENEDOR PRINCIPAL DEL LOGIN
     ===================================================== -->
<div class="auth-container">
    <div class="auth-card">
        
        <!-- =====================================================
             LOGO/TÍTULO DEL SISTEMA
             ===================================================== -->
        <div class="auth-logo">
            <h1>
                Sistema de Gestión de la Calidad
            </h1>
        </div>
        
        <!-- =====================================================
             TÍTULO DEL FORMULARIO DE LOGIN
             ===================================================== -->
        <h2 class="auth-title">Iniciar Sesión</h2>
        
        <!-- =====================================================
             MUESTRA DE ERRORES DEL SERVIDOR (VALIDACIÓN DE LARAVEL)
             ===================================================== -->
        @if($errors->any())
            <div class="auth-alert auth-alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- =====================================================
             FORMULARIO DE LOGIN - ENVÍA LOS DATOS A LA RUTA 'LOGIN'
             ===================================================== -->
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf  <!-- TOKEN DE SEGURIDAD DE LARAVEL -->
            
            <!-- =====================================================
                 CAMPO: CORREO ELECTRÓNICO (EMAIL)
                 ===================================================== -->
            <div class="auth-form-group">
                <label for="email" class="auth-form-label">
                    <i class="bi bi-envelope"></i> Email
                </label>
                <div style="position: relative;">
                    <input 
                        type="email" 
                        class="auth-form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="Email" 
                        required 
                        autofocus
                    >
                    <i class="bi bi-envelope auth-input-icon"></i>
                </div>
            </div>
            
            <!-- =====================================================
                 CAMPO: CONTRASEÑA (PASSWORD)
                 ===================================================== -->
            <div class="auth-form-group">
                <label for="password" class="auth-form-label">
                    <i class="bi bi-lock"></i> Contraseña
                </label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        class="auth-form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        placeholder="Password" 
                        required
                    >
                    <button type="button" id="togglePassword" 
                        style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; color: #999; font-size: 1.1rem; display: none;"
                        title="Mostrar/ocultar contraseña">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>

                    <i class="bi bi-lock auth-input-icon"></i>
                </div>
                
                <!-- =====================================================
                     ENLACE PARA RECUPERAR CONTRASEÑA (SI EXISTE LA RUTA)
                     ===================================================== -->
                <div class="auth-form-group" style="text-align: right; margin-top: 5px;">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link" style="font-size: 0.9rem;">
                            {{ __('¿Olvidaste tu contraseña?') }}
                        </a>
                    @endif
                </div>
            </div>
            
            <!-- =====================================================
                 BOTÓN PARA ENVIAR EL FORMULARIO (INICIAR SESIÓN)
                 ===================================================== -->
            <button type="submit" class="auth-btn-primary" id="loginBtn">
                <i class="bi bi-box-arrow-in-right"></i>
                Iniciar Sesión
            </button>
            
            <!-- =====================================================
                 ENLACE PARA VOLVER A LA PÁGINA DE INICIO
                 ===================================================== -->
            <div class="auth-footer">
                <p>
                    <a href="{{ route('home') }}" class="auth-link">
                        <i class="bi bi-arrow-left"></i> Volver al inicio
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>

<!-- =====================================================
     JAVASCRIPT - VALIDACIONES EN TIEMPO REAL Y ESTADO DE CARGA
     ===================================================== -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // =====================================================
    // OBTENER REFERENCIAS A LOS ELEMENTOS DEL FORMULARIO
    // =====================================================
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    // =====================================================
    // FORM SUBMISSION WITH LOADING STATE
    // CAMBIA EL TEXTO DEL BOTÓN Y LO DESHABILITA MIENTRAS SE ENVÍA
    // =====================================================
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Iniciando...';
        submitBtn.disabled = true;
        form.classList.add('auth-loading');
        
        // RE-ENABLE IF THERE'S AN ERROR (PAGE DOESN'T REDIRECT)
        // RESTAURA EL BOTÓN SI HAY UN ERROR (LA PÁGINA NO REDIRIGE)
        setTimeout(() => {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            form.classList.remove('auth-loading');
        }, 5000);
    });
    
    // =====================================================
    // REAL-TIME VALIDATION
    // VALIDACIONES EN TIEMPO REAL PARA EMAIL Y CONTRASEÑA
    // =====================================================
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    // =====================================================
// MOSTRAR / OCULTAR CONTRASEÑA
// FUNCIONA EN TODOS LOS NAVEGADORES
// =====================================================
    const togglePassword = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');

    togglePassword.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('bi-eye', !isPassword);
        toggleIcon.classList.toggle('bi-eye-slash', isPassword);
    });

        // MUESTRA EL BOTÓN DEL OJO SOLO CUANDO HAY TEXTO EN EL CAMPO
    passwordInput.addEventListener('input', function() {
        togglePassword.style.display = this.value.length > 0 ? 'block' : 'none';
        // SI SE BORRA TODO EL TEXTO, VUELVE A TIPO PASSWORD
        if (this.value.length === 0) {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    });

    
    // VALIDAR EMAIL CUANDO PIERDE EL FOCO (BLUR)
    emailInput.addEventListener('blur', function() {
        validateEmail(this);
    });
    
    // VALIDAR CONTRASEÑA CUANDO PIERDE EL FOCO (BLUR)
    passwordInput.addEventListener('blur', function() {
        validatePassword(this);
    });
    
    // =====================================================
    // FUNCIÓN PARA VALIDAR EL FORMATO DEL EMAIL
    // =====================================================
    function validateEmail(input) {
        const email = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            input.classList.add('is-invalid');
            showFieldError(input, 'Por favor ingresa un email válido');
        } else if (email) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            hideFieldError(input);
        }
    }
    
    // =====================================================
    // FUNCIÓN PARA VALIDAR LA LONGITUD DE LA CONTRASEÑA (MÍNIMO 8 CARACTERES)
    // =====================================================
    function validatePassword(input) {
        const password = input.value;
        
        if (password && password.length < 8) {
            input.classList.add('is-invalid');
            showFieldError(input, 'La contraseña debe tener al menos 8 caracteres');
        } else if (password) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            hideFieldError(input);
        }
    }
    
    // =====================================================
    // FUNCIÓN PARA MOSTRAR UN MENSAJE DE ERROR DEBAJO DEL CAMPO
    // =====================================================
    function showFieldError(input, message) {
        let errorDiv = input.parentNode.parentNode.querySelector('.field-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error invalid-feedback';
            errorDiv.style.display = 'block';
            input.parentNode.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
    
    // =====================================================
    // FUNCIÓN PARA ELIMINAR EL MENSAJE DE ERROR
    // =====================================================
    function hideFieldError(input) {
        const errorDiv = input.parentNode.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
});
</script>
@endpush
@endsection