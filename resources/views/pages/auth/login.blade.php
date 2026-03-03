@extends('layouts.guest')

@section('title', 'Login - SAMS')

@section('content')
<style>
    /* Definir una variable para el color Guinda base y el Guinda oscuro */
    :root {
        --guinda-base: #800000; /* Guinda */
        --guinda-darker: #5b0000; /* Guinda más oscuro para hover/activos */
        --guinda-light: #ac3939; /* Guinda un poco más claro para bordes secundarios */
    }

    /* Estilos del contenedor de autenticación */
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #ffffffff; /* Un fondo claro para la página de login */
        padding: 20px;
    }

    /* Estilos de la tarjeta de autenticación (el formulario) */
    .auth-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0);
        padding: 40px;
        width: 100%;
        max-width: 400px;
        text-align: center;
    }

    /* Estilos del logo/título principal */
    .auth-logo h1 {
        color: var(--guinda-base); /* Color guinda para el título */
        font-size: 2.2rem;
        margin-bottom: 25px;
        font-weight: 700;
    }

    /* Título del formulario */
    .auth-title {
        color: #333;
        margin-bottom: 30px;
        font-weight: 600;
        font-size: 1.8rem;
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
        color: var(--guinda-base); /* Iconos de label en guinda */
    }

    /* Campos de entrada */
    .auth-form-control {
        width: 100%;
        padding: 12px 40px 12px 15px; /* Espacio para el icono */
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .auth-form-control:focus {
        border-color: var(--guinda-base); /* Borde guinda al enfocar */
        box-shadow: 0 0 0 0.25rem rgba(128, 0, 0, 0.25); /* Sombra guinda */
        outline: none;
    }

    .auth-form-control.is-invalid {
        border-color: #dc3545;
        padding-right: 15px; /* Ajuste para el icono si Bootstrap ya lo agrega */
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
        color: var(--guinda-base); /* Icono guinda al enfocar */
    }

    /* Botón principal (Iniciar Sesión) */
    .auth-btn-primary {
        background-color: var(--guinda-base); /* Guinda */
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
        background-color: var(--guinda-darker); /* Guinda más oscuro en hover */
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
    }

    .auth-btn-primary:disabled {
        background-color: #a87e7e; /* Un guinda más claro para deshabilitado */
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Divisor */
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

    /* Botón secundario (Registrarse) */
    .auth-btn-secondary {
        background-color: #f0f0f0; /* Fondo claro para el botón secundario */
        color: var(--guinda-base); /* Texto guinda */
        border: 1px solid var(--guinda-light); /* Borde guinda más claro */
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
        text-decoration: none; /* Asegura que no tenga subrayado */
    }

    .auth-btn-secondary .bi {
        margin-right: 10px;
    }

    .auth-btn-secondary:hover {
        background-color: var(--guinda-light); /* Fondo guinda más claro en hover */
        color: white; /* Texto blanco en hover */
        border-color: var(--guinda-light);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(128, 0, 0, 0.1);
    }

    /* Pie de página del formulario (Volver al inicio) */
    .auth-footer {
        margin-top: 30px;
    }

    .auth-link {
        color: var(--guinda-base); /* Texto guinda */
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
        display: inline-flex; /* Para alinear el icono y el texto */
        align-items: center;
    }

    .auth-link .bi {
        margin-right: 5px;
        color: var(--guinda-darker); /* Flecha más oscura */
        transition: color 0.2s ease;
    }

    .auth-link:hover {
        color: var(--guinda-darker); /* Guinda más oscuro en hover */
    }

    .auth-link:hover .bi {
        color: var(--guinda-darker); /* La flecha se mantiene oscura o incluso más oscura */
    }

    /* Estilos para el estado de carga */
    .auth-loading .auth-btn-primary {
        background-color: var(--guinda-darker);
        cursor: progress;
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
        
        <h2 class="auth-title">Iniciar Sesión</h2>
        
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
        
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            
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
                    <i class="bi bi-lock auth-input-icon"></i>
                </div>
                
                <div class="auth-form-group" style="text-align: right; margin-top: 5px;">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link" style="font-size: 0.9rem;">
                            {{ __('¿Olvidaste tu contraseña?') }}
                        </a>
                    @endif
                </div>
            </div>
            
            <button type="submit" class="auth-btn-primary" id="loginBtn">
                <i class="bi bi-box-arrow-in-right"></i>
                Iniciar Sesión
            </button>
            
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    // Form submission with loading state
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Iniciando...';
        submitBtn.disabled = true;
        form.classList.add('auth-loading');
        
        // Re-enable if there's an error (page doesn't redirect)
        setTimeout(() => {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            form.classList.remove('auth-loading');
        }, 5000);
    });
    
    // Real-time validation
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    emailInput.addEventListener('blur', function() {
        validateEmail(this);
    });
    
    passwordInput.addEventListener('blur', function() {
        validatePassword(this);
    });
    
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