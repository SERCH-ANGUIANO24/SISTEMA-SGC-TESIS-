<x-layouts.auth>
    <style>
        /* Definir una variable para el color Guinda base y el Guinda oscuro */
        :root {
            --guinda-base: #800000;
            --guinda-darker: #5b0000;
            --guinda-light: #ac3939;
        }

        /* Estilos del contenedor de autenticación */
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #ffffffff;
            padding: 20px;
        }

        /* Estilos de la tarjeta de autenticación */
        .auth-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        /* Logo/título */
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

        /* Descripción */
        .auth-description {
            color: #666;
            margin-bottom: 30px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Alertas */
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

        .auth-alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .auth-alert .bi {
            font-size: 1.5rem;
            margin-right: 15px;
        }

        .auth-alert-danger .bi {
            color: #dc3545;
        }

        .auth-alert-success .bi {
            color: #28a745;
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
            padding: 12px 50px 12px 15px;
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

        .auth-form-control.is-valid {
            border-color: #28a745;
        }

        .auth-input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
            cursor: pointer;
            user-select: none;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .auth-input-icon.password-toggle {
            right: 15px;
        }

        .auth-input-icon.static-icon {
            right: 40px;
            cursor: default;
        }

        .auth-input-icon:hover {
            color: var(--guinda-base);
        }

        .auth-form-control:focus ~ .auth-input-icon {
            color: var(--guinda-base);
        }

        /* Contenedor para campo con múltiples iconos */
        .auth-input-wrapper {
            position: relative;
            width: 100%;
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

        /* Footer */
        .auth-footer {
            margin-top: 30px;
        }

        .auth-footer-text {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .auth-link {
            color: var(--guinda-base);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .auth-link:hover {
            color: var(--guinda-darker);
            text-decoration: underline;
        }

        /* Estado de carga */
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
            display: block;
        }

        .invalid-feedback {
            display: block !important;
        }

        /* Estilo para fortaleza de contraseña */
        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
        }

        .password-strength-meter {
            height: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-bottom: 5px;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .password-strength-text {
            font-size: 0.8rem;
            color: #666;
        }

        .strength-weak {
            background-color: #dc3545;
            width: 25%;
        }

        .strength-fair {
            background-color: #ffc107;
            width: 50%;
        }

        .strength-good {
            background-color: #28a745;
            width: 75%;
        }

        .strength-strong {
            background-color: #28a745;
            width: 100%;
        }

        /* Estilo para cuando la contraseña fue actualizada */
        .password-updated-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .password-updated-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 50px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .password-updated-icon {
            background-color: #28a745;
            color: white;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            margin: 0 auto 30px;
        }

        .password-updated-title {
            color: #155724;
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .password-updated-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .password-updated-btn {
            background-color: var(--guinda-base);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .password-updated-btn:hover {
            background-color: var(--guinda-darker);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
        }

        /* Tooltip para mostrar/ocultar contraseña */
        .password-toggle-tooltip {
            position: absolute;
            background-color: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1000;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }

        .auth-input-icon:hover .password-toggle-tooltip {
            opacity: 1;
        }
    </style>

    @if(session('success') || session('status'))
        <!-- Pantalla de éxito después de actualizar la contraseña -->
        <div class="password-updated-container">
            <div class="password-updated-card">
                <div class="password-updated-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="password-updated-title">¡Contraseña Actualizada!</h1>
                <p class="password-updated-message">
                    Tu contraseña ha sido actualizada exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.
                </p>
                <a href="{{ route('login') }}" class="password-updated-btn">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </a>
                
                <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee;">
                    <p style="color: #888; font-size: 0.9rem; margin-bottom: 15px;">
                        <i class="bi bi-shield-check"></i> Tu contraseña ha sido actualizada de forma segura
                    </p>
                    <p style="color: #888; font-size: 0.9rem;">
                        <i class="bi bi-clock-history"></i> Actualizado: {{ now()->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Formulario normal de reestablecimiento -->
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-logo">
                    <h1>Sistema de Gestión de la Calidad</h1>
                </div>
                
                <h2 class="auth-title">Reestablecimiento de contraseña</h2>
                
                <p class="auth-description">
                    Introduce tu nueva contraseña para continuar
                </p>
                
                <!-- Session Status -->
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
                
                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                    @csrf
                    
                    <!-- Token -->
                    <input type="hidden" name="token" value="{{ request()->route('token') }}">
                    
                    <!-- Email Address -->
                    <div class="auth-form-group">
                        <label for="email" class="auth-form-label">
                            <i class="bi bi-envelope"></i> Dirección de correo electrónico
                        </label>
                        <div class="auth-input-wrapper">
                            <input 
                                type="email" 
                                class="auth-form-control @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ request('email') ?? old('email') }}" 
                                placeholder="Ingresa tu correo electrónico" 
                                required 
                                autocomplete="email"
                            >
                            <i class="bi bi-envelope auth-input-icon static-icon"></i>
                        </div>
                        @error('email')
                            <div class="field-error" data-server-error="true">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Password -->
                    <div class="auth-form-group">
                        <label for="password" class="auth-form-label">
                            <i class="bi bi-lock"></i> Contraseña
                        </label>
                        <div class="auth-input-wrapper">
                            <input 
                                type="password" 
                                class="auth-form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="Ingresa tu nueva contraseña" 
                                required
                                autocomplete="new-password"
                            >
                            <i class="bi bi-lock auth-input-icon static-icon"></i>
                            <i class="bi bi-eye auth-input-icon password-toggle" id="togglePassword" title="Mostrar contraseña"></i>
                        </div>
                        
                        <!-- Indicador de fortaleza de contraseña -->
                        <div class="password-strength">
                            <div class="password-strength-meter">
                                <div class="password-strength-fill" id="passwordStrengthFill"></div>
                            </div>
                            <div class="password-strength-text" id="passwordStrengthText">
                                Seguridad de la contraseña
                            </div>
                        </div>
                        
                        @error('password')
                            <div class="field-error" data-server-error="true">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="auth-form-group">
                        <label for="password_confirmation" class="auth-form-label">
                            <i class="bi bi-lock-fill"></i> Confirmar contraseña
                        </label>
                        <div class="auth-input-wrapper">
                            <input 
                                type="password" 
                                class="auth-form-control" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                placeholder="Confirmar nueva contraseña" 
                                required
                                autocomplete="new-password"
                            >
                            <i class="bi bi-lock-fill auth-input-icon static-icon"></i>
                            <i class="bi bi-eye auth-input-icon password-toggle" id="togglePasswordConfirmation" title="Mostrar contraseña"></i>
                        </div>
                        
                        <!-- Indicador de coincidencia -->
                        <div class="password-strength">
                            <div class="password-strength-text" id="passwordMatchText">
                                Las contraseñas deben coincidir
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="auth-btn-primary" id="resetPasswordBtn" data-test="reset-password-button">
                        <i class="bi bi-key"></i>
                        Actualizar contraseña
                    </button>
                    
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('resetPasswordForm');
        
        if (form) {
            const submitBtn = document.getElementById('resetPasswordBtn');
            const originalBtnText = submitBtn.innerHTML;
            
            // Elementos del formulario
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const togglePassword = document.getElementById('togglePassword');
            const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
            const passwordStrengthFill = document.getElementById('passwordStrengthFill');
            const passwordStrengthText = document.getElementById('passwordStrengthText');
            const passwordMatchText = document.getElementById('passwordMatchText');
            
            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                // Validar contraseñas antes de enviar
                if (!validatePasswordMatch()) {
                    e.preventDefault();
                    passwordConfirmInput.classList.add('is-invalid');
                    showPasswordMatchError();
                    return;
                }
                
                if (form.checkValidity()) {
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Actualizando...';
                    submitBtn.disabled = true;
                    form.classList.add('auth-loading');
                }
            });
            
            // Toggle password visibility para campo de contraseña
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    togglePasswordVisibility(passwordInput, this);
                });
                
                // Agregar tooltip
                togglePassword.setAttribute('title', 'Mostrar contraseña');
            }
            
            // Toggle password visibility para campo de confirmación
            if (togglePasswordConfirmation) {
                togglePasswordConfirmation.addEventListener('click', function() {
                    togglePasswordVisibility(passwordConfirmInput, this);
                });
                
                // Agregar tooltip
                togglePasswordConfirmation.setAttribute('title', 'Mostrar contraseña');
            }
            
            // Función para alternar visibilidad de contraseña
            function togglePasswordVisibility(input, icon) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                // Cambiar icono
                if (type === 'text') {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                    icon.setAttribute('title', 'Ocultar contraseña');
                } else {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                    icon.setAttribute('title', 'Mostrar contraseña');
                }
            }
            
            // Validar fortaleza de contraseña en tiempo real
            passwordInput.addEventListener('input', function() {
                validatePasswordStrength(this.value);
                validatePasswordMatch();
            });
            
            passwordConfirmInput.addEventListener('input', function() {
                validatePasswordMatch();
            });
            
            // Validar email
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
            
            // Función para validar fortaleza de contraseña
            function validatePasswordStrength(password) {
                let strength = 0;
                let text = 'Seguridad de la contraseña';
                
                // Resetear indicador
                passwordStrengthFill.className = 'password-strength-fill';
                
                if (password.length === 0) {
                    passwordStrengthFill.style.width = '0%';
                    passwordStrengthText.textContent = text;
                    return;
                }
                
                // Longitud mínima
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                
                // Caracteres variados
                if (/[a-z]/.test(password)) strength++; // Minúsculas
                if (/[A-Z]/.test(password)) strength++; // Mayúsculas
                if (/[0-9]/.test(password)) strength++; // Números
                if (/[^A-Za-z0-9]/.test(password)) strength++; // Caracteres especiales
                
                // Establecer nivel de fortaleza
                let width = 0;
                let strengthClass = '';
                
                switch(strength) {
                    case 0:
                    case 1:
                        width = 25;
                        strengthClass = 'strength-weak';
                        text = 'Débil';
                        break;
                    case 2:
                    case 3:
                        width = 50;
                        strengthClass = 'strength-fair';
                        text = 'Regular';
                        break;
                    case 4:
                        width = 75;
                        strengthClass = 'strength-good';
                        text = 'Buena';
                        break;
                    case 5:
                    case 6:
                        width = 100;
                        strengthClass = 'strength-strong';
                        text = 'Fuerte';
                        break;
                }
                
                // Aplicar estilos
                passwordStrengthFill.className = 'password-strength-fill ' + strengthClass;
                passwordStrengthFill.style.width = width + '%';
                passwordStrengthText.textContent = text;
                
                // Cambiar color del borde del input según fortaleza
                if (strength >= 4) {
                    passwordInput.classList.remove('is-invalid');
                    passwordInput.classList.add('is-valid');
                } else if (password.length > 0 && strength < 4) {
                    passwordInput.classList.add('is-invalid');
                    passwordInput.classList.remove('is-valid');
                }
            }
            
            // Función para validar coincidencia de contraseñas
            function validatePasswordMatch() {
                const password = passwordInput.value;
                const confirmation = passwordConfirmInput.value;
                
                if (confirmation.length === 0) {
                    passwordMatchText.textContent = 'Las contraseñas deben coincidir';
                    passwordMatchText.style.color = '#666';
                    passwordConfirmInput.classList.remove('is-invalid', 'is-valid');
                    return false;
                }
                
                if (password === confirmation) {
                    passwordMatchText.textContent = '✓ Las contraseñas coinciden';
                    passwordMatchText.style.color = '#28a745';
                    passwordConfirmInput.classList.remove('is-invalid');
                    passwordConfirmInput.classList.add('is-valid');
                    return true;
                } else {
                    passwordMatchText.textContent = '✗ Las contraseñas no coinciden';
                    passwordMatchText.style.color = '#dc3545';
                    passwordConfirmInput.classList.add('is-invalid');
                    passwordConfirmInput.classList.remove('is-valid');
                    return false;
                }
            }
            
            function showPasswordMatchError() {
                passwordMatchText.textContent = '✗ Las contraseñas no coinciden';
                passwordMatchText.style.color = '#dc3545';
            }
            
            // Función para validar email
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
            
            function showFieldError(input, message) {
                let errorDiv = input.parentNode.parentNode.querySelector('.field-error');
                if (!errorDiv || !errorDiv.hasAttribute('data-server-error')) {
                    if (errorDiv && errorDiv.hasAttribute('data-server-error')) {
                        return;
                    }
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'field-error invalid-feedback';
                        errorDiv.style.display = 'block';
                        input.parentNode.parentNode.appendChild(errorDiv);
                    }
                    errorDiv.textContent = message;
                }
            }
            
            function hideFieldError(input) {
                const errorDiv = input.parentNode.parentNode.querySelector('.field-error');
                if (errorDiv && !errorDiv.hasAttribute('data-server-error')) {
                    errorDiv.remove();
                }
            }
            
            // Validar contraseña al perder foco
            passwordInput.addEventListener('blur', function() {
                if (this.value.length > 0 && this.value.length < 8) {
                    this.classList.add('is-invalid');
                    showFieldError(this, 'La contraseña debe tener al menos 8 caracteres');
                }
            });
            
            // Auto-validar al cargar la página si hay valores
            if (emailInput.value) validateEmail(emailInput);
            if (passwordInput.value) validatePasswordStrength(passwordInput.value);
            if (passwordConfirmInput.value) validatePasswordMatch();
            
            // Mejorar UX: Mostrar/ocultar contraseña con Shift
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Shift') {
                    if (document.activeElement === passwordInput && togglePassword) {
                        togglePasswordVisibility(passwordInput, togglePassword);
                    } else if (document.activeElement === passwordConfirmInput && togglePasswordConfirmation) {
                        togglePasswordVisibility(passwordConfirmInput, togglePasswordConfirmation);
                    }
                }
            });
            
            // Mostrar sugerencias de contraseña
            const passwordSuggestions = [
                "Usa al menos 8 caracteres",
                "Combina letras mayúsculas y minúsculas",
                "Incluye números y símbolos",
                "Evita información personal"
            ];
            
            // Agregar placeholder dinámico para contraseña
            let suggestionIndex = 0;
            function rotatePasswordSuggestion() {
                passwordInput.setAttribute('placeholder', passwordSuggestions[suggestionIndex]);
                suggestionIndex = (suggestionIndex + 1) % passwordSuggestions.length;
            }
            
            // Rotar sugerencias cada 5 segundos
            setInterval(rotatePasswordSuggestion, 5000);
            
            // Iniciar con la primera sugerencia
            rotatePasswordSuggestion();
        }
    });
    </script>
    @endpush
</x-layouts.auth>