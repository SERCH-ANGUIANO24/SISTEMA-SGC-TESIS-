@extends('layouts.guest')

@section('title', 'Recuperar Contraseña - SAMS')

@section('content')

{{-- ── ESTILOS CSS DE LA PÁGINA DE RECUPERACIÓN DE CONTRASEÑA ── --}}
{{-- INCLUYE VARIABLES DE COLOR, COMPONENTES DEL FORMULARIO Y ESTILOS RESPONSIVOS --}}
<style>
    /* VARIABLES DE COLOR GUINDA USADAS EN TODA LA PÁGINA */
    :root {
        --guinda-base: #800000; /* Guinda */
        --guinda-darker: #800000; /* Guinda más oscuro para hover/activos */
        --guinda-light: #800000; /* Guinda un poco más claro para bordes secundarios */
    }

    /* CONTENEDOR PRINCIPAL: CENTRA LA TARJETA VERTICALMENTE EN TODA LA PANTALLA */
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f8faf8;
        padding: 20px;
    }

    /* TARJETA DEL FORMULARIO CON SOMBRA, BORDES REDONDEADOS Y ANCHO MÁXIMO */
    .auth-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0);
        padding: 40px;
        width: 100%;
        max-width: 400px;
        text-align: center;
    }

    /* TÍTULO PRINCIPAL DEL SISTEMA EN NEGRO */
    .auth-logo h1 {
        color: #000000; /* Cambiado a negro */
        font-size: 2.2rem;
        margin-bottom: 25px;
        font-weight: 700;
    }

    /* TÍTULO DEL FORMULARIO DE RECUPERACIÓN */
    .auth-title {
        color: #333;
        margin-bottom: 15px;
        font-weight: 600;
        font-size: 1.8rem;
    }

    /* TEXTO DESCRIPTIVO QUE EXPLICA QUÉ HACE EL FORMULARIO */
    .auth-description {
        color: #666;
        margin-bottom: 30px;
        font-size: 1rem;
        line-height: 1.5;
    }

    /* ESTILOS BASE DE LOS MENSAJES DE ALERTA (ÉXITO Y ERROR) */
    .auth-alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    /* ALERTA VERDE: SE MUESTRA CUANDO EL CORREO FUE ENVIADO CORRECTAMENTE */
    .auth-alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    /* ALERTA ROJA: SE MUESTRA CUANDO HAY ERRORES DE VALIDACIÓN */
    .auth-alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .auth-alert .bi {
        font-size: 1.5rem;
        margin-right: 15px;
    }

    /* ÍCONO DE LA ALERTA DE ÉXITO EN VERDE */
    .auth-alert-success .bi {
        color: #28a745;
    }

    /* ÍCONO DE LA ALERTA DE ERROR EN ROJO */
    .auth-alert-danger .bi {
        color: #dc3545;
    }
    
    /* GRUPO DE CAMPO DEL FORMULARIO CON ESPACIADO INFERIOR */
    .auth-form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    /* ETIQUETA DEL CAMPO CON ÍCONO DE COLOR GUINDA */
    .auth-form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
        font-size: 1rem;
    }

    .auth-form-label .bi {
        margin-right: 8px;
        color: var(--guinda-base);
    }

    /* CAMPO DE ENTRADA CON PADDING PARA DAR ESPACIO AL ÍCONO DERECHO */
    .auth-form-control {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    /* EFECTO FOCUS DEL CAMPO: BORDE Y SOMBRA AZUL CLARO */
    .auth-form-control:focus {
        border-color: #b8c9da;
        box-shadow: 0 0 0 0.25rem #b8c9da;
        outline: none;
    }

    /* BORDE ROJO CUANDO EL CAMPO TIENE ERROR DE VALIDACIÓN */
    .auth-form-control.is-invalid {
        border-color: #dc3545;
    }
    
    /* ÍCONO DECORATIVO DENTRO DEL CAMPO POSICIONADO A LA DERECHA */
    .auth-input-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 1.1rem;
    }

    /* EL ÍCONO CAMBIA AL COLOR GUINDA CUANDO EL CAMPO TIENE FOCO */
    .auth-form-control:focus + .auth-input-icon {
        color: var(--guinda-base);
    }

    /* BOTÓN PRINCIPAL DE ENVÍO CON COLOR GUINDA Y EFECTO HOVER */
    .auth-btn-primary {
        background-color: var(--guinda-base);
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

    /* EFECTO HOVER: EL BOTÓN SE ELEVA Y MUESTRA SOMBRA */
    .auth-btn-primary:hover {
        background-color: var(--guinda-darker);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
    }

    /* ESTADO DESHABILITADO: EL BOTÓN SE VUELVE GRIS Y NO SE PUEDE HACER CLIC */
    .auth-btn-primary:disabled {
        background-color: #a87e7e;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* PIE DEL FORMULARIO CON EL ENLACE PARA REGRESAR AL LOGIN */
    .auth-footer {
        margin-top: 25px;
        text-align: center;
    }

    /* ENLACE NEGRO CON TRANSICIÓN DE COLOR AL HACER HOVER */
    .auth-link {
        color: #000000; /* Cambiado a negro */
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .auth-link .bi {
        margin-right: 5px;
        color: #000000; /* Cambiado a negro */
        transition: color 0.2s ease;
    }

    /* HOVER DEL ENLACE: GRIS OSCURO */
    .auth-link:hover {
        color: #000000; /* Gris oscuro para hover */
    }

    .auth-link:hover .bi {
        color: #333333; /* Gris oscuro para hover */
    }

    /* TEXTO DE AYUDA PEQUEÑO DEBAJO DEL BOTÓN */
    .auth-help-text {
        color: #666;
        font-size: 0.9rem;
        margin-top: 20px;
    }

    /* MENSAJE DE ERROR DE VALIDACIÓN DEBAJO DE CADA CAMPO */
    .field-error {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 5px;
        text-align: left;
    }

    /* =====================================================
       ESTILOS RESPONSIVOS - RECUPERAR CONTRASEÑA
       (MISMO PATRÓN QUE LOGIN)
    ===================================================== */

    /* TABLETS (769px a 992px): REDUCE TAMAÑOS DE FUENTE Y PADDING */
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
            margin-bottom: 12px !important;
        }
        .auth-description {
            font-size: 0.9rem !important;
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
        .auth-help-text {
            font-size: 0.85rem !important;
        }
    }

    /* MÓVILES (768px y menos): AJUSTA TODOS LOS COMPONENTES PARA PANTALLA PEQUEÑA */
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
            margin-bottom: 10px !important;
        }
        .auth-description {
            font-size: 0.85rem !important;
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
        .auth-help-text {
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

    /* MÓVILES MUY PEQUEÑOS (480px y menos): REDUCE AÚN MÁS LOS TAMAÑOS */
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
        .auth-description {
            font-size: 0.8rem !important;
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
        .auth-help-text {
            font-size: 0.75rem !important;
        }
    }
</style>

{{-- ── CONTENEDOR PRINCIPAL DE LA PÁGINA ── --}}
<div class="auth-container">
    <div class="auth-card">

        {{-- TÍTULO DEL SISTEMA EN LA PARTE SUPERIOR DE LA TARJETA --}}
        <div class="auth-logo">
            <h1>
                Sistema de Gestión de la Calidad
            </h1>
        </div>
        
        {{-- TÍTULO Y DESCRIPCIÓN DEL FORMULARIO DE RECUPERACIÓN --}}
        <h2 class="auth-title">Recuperar Contraseña</h2>
        
        <p class="auth-description">
            Ingresa tu correo electrónico para recibir un enlace de recuperación de contraseña
        </p>
        
        {{-- ALERTA DE ÉXITO: SE MUESTRA CUANDO EL CORREO FUE ENVIADO CORRECTAMENTE --}}
        {{-- Laravel almacena el mensaje de éxito en session('status') --}}
        @if(session('status'))
            <div class="auth-alert auth-alert-success">
                <i class="bi bi-check-circle"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif
        
        {{-- ALERTA DE ERRORES: SE MUESTRA CUANDO EL CORREO NO EXISTE O HAY PROBLEMAS --}}
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
        
        {{-- FORMULARIO DE RECUPERACIÓN: ENVÍA EL EMAIL A LA RUTA password.email --}}
        <form method="POST" action="{{ route('password.email') }}" id="passwordResetForm">
            @csrf
            
            {{-- CAMPO DE CORREO ELECTRÓNICO CON ÍCONO Y VALIDACIÓN --}}
            <div class="auth-form-group">
                <label for="email" class="auth-form-label">
                    <i class="bi bi-envelope"></i> Correo Electrónico
                </label>
                <div style="position: relative;">
                    {{-- SI HAY ERROR, SE AGREGA LA CLASE is-invalid PARA MOSTRAR EL BORDE ROJO --}}
                    <input 
                        type="email" 
                        class="auth-form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="email@ejemplo.com" 
                        required 
                        autofocus
                    >
                    {{-- ÍCONO DECORATIVO POSICIONADO DENTRO DEL CAMPO A LA DERECHA --}}
                    <i class="bi bi-envelope auth-input-icon"></i>
                </div>
                {{-- MENSAJE DE ERROR ESPECÍFICO DEL CAMPO DE EMAIL --}}
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- BOTÓN DE ENVÍO: SE DESHABILITA Y MUESTRA "ENVIANDO..." AL HACER CLIC --}}
            <button type="submit" class="auth-btn-primary" id="resetBtn">
                <i class="bi bi-envelope-arrow-up"></i>
                Enviar enlace de recuperación
            </button>
            
            {{-- PIE DEL FORMULARIO CON ENLACE PARA REGRESAR AL LOGIN --}}
            <div class="auth-footer">
                <p class="auth-help-text">
                    <span>¿O regresar a </span>
                    <a href="{{ route('login') }}" class="auth-link">
                        iniciar sesión
                    </a>
                    <span>?</span>
                </p>
                
            </div>
        </form>
    </div>
</div>

{{-- ── SCRIPTS DE LA PÁGINA DE RECUPERACIÓN DE CONTRASEÑA ── --}}
{{-- MANEJA: ESTADO DE CARGA DEL BOTÓN Y VALIDACIÓN EN TIEMPO REAL DEL EMAIL --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordResetForm');
    const submitBtn = document.getElementById('resetBtn');
    const originalBtnText = submitBtn.innerHTML;
    const emailInput = document.getElementById('email');
    
    // AL ENVIAR EL FORMULARIO, CAMBIA EL BOTÓN A ESTADO DE CARGA
    // SE RESTAURA DESPUÉS DE 5 SEGUNDOS EN CASO DE ERROR (SI LA PÁGINA NO REDIRIGE)
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';
        submitBtn.disabled = true;
        submitBtn.classList.add('auth-loading');
        
        // Re-enable if there's an error (page doesn't redirect)
        setTimeout(() => {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            submitBtn.classList.remove('auth-loading');
        }, 5000);
    });
    
    // VALIDA EL FORMATO DEL EMAIL EN TIEMPO REAL AL PERDER EL FOCO DEL CAMPO
    emailInput.addEventListener('blur', function() {
        validateEmail(this);
    });
    
    // VERIFICA QUE EL EMAIL TENGA UN FORMATO VÁLIDO CON EXPRESIÓN REGULAR
    // AGREGA is-invalid SI EL FORMATO ES INCORRECTO O is-valid SI ES CORRECTO
    function validateEmail(input) {
        const email = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            input.classList.add('is-invalid');
            showFieldError(input, 'Por favor ingresa un correo electrónico válido');
        } else if (email) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            hideFieldError(input);
        }
    }
    
    // CREA Y MUESTRA UN MENSAJE DE ERROR DEBAJO DEL CAMPO SI NO EXISTE YA UNO
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
    
    // ELIMINA EL MENSAJE DE ERROR DEL CAMPO SI EXISTE
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