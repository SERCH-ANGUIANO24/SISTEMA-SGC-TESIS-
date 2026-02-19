@extends('layouts.guest')

@section('title', 'Recuperar Contraseña - SAMS')

@section('content')
<style>
    /* Definir una variable para el color Guinda base y el Guinda oscuro */
    :root {
        --guinda-base: #800000; /* Guinda */
        --guinda-darker: #800000; /* Guinda más oscuro para hover/activos */
        --guinda-light: #800000; /* Guinda un poco más claro para bordes secundarios */
    }

    /* Estilos del contenedor de autenticación */
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f8f9fa;
        padding: 20px;
    }

    /* Estilos de la tarjeta de autenticación */
    .auth-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 40px;
        width: 100%;
        max-width: 400px;
        text-align: center;
    }

    /* Estilos del logo/título principal */
    .auth-logo h1 {
        color: var(--guinda-base);
        font-size: 2.2rem;
        margin-bottom: 25px;
        font-weight: 700;
    }

    /* Título del formulario */
    .auth-title {
        color: #333;
        margin-bottom: 15px;
        font-weight: 600;
        font-size: 1.8rem;
    }

    /* Descripción del formulario */
    .auth-description {
        color: #666;
        margin-bottom: 30px;
        font-size: 1rem;
        line-height: 1.5;
    }

    /* Mensajes de alerta/error */
    .auth-alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .auth-alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .auth-alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .auth-alert .bi {
        font-size: 1.5rem;
        margin-right: 15px;
    }

    .auth-alert-success .bi {
        color: #28a745;
    }

    .auth-alert-danger .bi {
        color: #dc3545;
    }
    
    /* Grupos de formulario */
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
        color: var(--guinda-base);
    }

    /* Campos de entrada */
    .auth-form-control {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .auth-form-control:focus {
        border-color: var(--guinda-base);
        box-shadow: 0 0 0 0.25rem rgba(128, 0, 0, 0.25);
        outline: none;
    }

    .auth-form-control.is-invalid {
        border-color: #dc3545;
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
        color: var(--guinda-base);
    }

    /* Botón principal */
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

    .auth-btn-primary:hover {
        background-color: var(--guinda-darker);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
    }

    .auth-btn-primary:disabled {
        background-color: #a87e7e;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Enlaces y pie de página */
    .auth-footer {
        margin-top: 25px;
        text-align: center;
    }

    .auth-link {
        color: var(--guinda-base);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .auth-link .bi {
        margin-right: 5px;
        color: var(--guinda-darker);
        transition: color 0.2s ease;
    }

    .auth-link:hover {
        color: var(--guinda-darker);
    }

    .auth-link:hover .bi {
        color: var(--guinda-darker);
    }

    /* Texto de ayuda/alternativa */
    .auth-help-text {
        color: #666;
        font-size: 0.9rem;
        margin-top: 20px;
    }

    /* Feedback de validación */
    .field-error {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 5px;
        text-align: left;
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <h1>
                Sistema de Gestión de la Calidad
            </h1>
        </div>
        
        <h2 class="auth-title">Recuperar Contraseña</h2>
        
        <p class="auth-description">
            Ingresa tu correo electrónico para recibir un enlace de recuperación de contraseña
        </p>
        
        @if(session('status'))
            <div class="auth-alert auth-alert-success">
                <i class="bi bi-check-circle"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif
        
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
        
        <form method="POST" action="{{ route('password.email') }}" id="passwordResetForm">
            @csrf
            
            <div class="auth-form-group">
                <label for="email" class="auth-form-label">
                    <i class="bi bi-envelope"></i> Correo Electrónico
                </label>
                <div style="position: relative;">
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
                    <i class="bi bi-envelope auth-input-icon"></i>
                </div>
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
            
            <button type="submit" class="auth-btn-primary" id="resetBtn">
                <i class="bi bi-envelope-arrow-up"></i>
                Enviar enlace de recuperación
            </button>
            
            <div class="auth-footer">
                <p class="auth-help-text">
                    <span>¿O regresar a </span>
                    <a href="{{ route('login') }}" class="auth-link">
                        {{ __('iniciar sesión') }}
                    </a>
                    <span>?</span>
                </p>
                
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordResetForm');
    const submitBtn = document.getElementById('resetBtn');
    const originalBtnText = submitBtn.innerHTML;
    const emailInput = document.getElementById('email');
    
    // Form submission with loading state
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
    
    // Real-time email validation
    emailInput.addEventListener('blur', function() {
        validateEmail(this);
    });
    
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