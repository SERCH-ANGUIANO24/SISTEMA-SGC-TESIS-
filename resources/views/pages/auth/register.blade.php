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
            max-width: 500px;
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

        /* Estilos específicos para select */
        .auth-form-control.select-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px 12px;
            padding-right: 40px;
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

        /* Pantalla de éxito después del registro */
        .registration-success-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .registration-success-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 50px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            animation: slideInUp 0.5s ease;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .registration-success-icon {
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
            animation: checkmarkPop 0.6s ease 0.3s both;
        }

        @keyframes checkmarkPop {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .registration-success-title {
            color: #155724;
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .registration-success-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .registration-success-btn {
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
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .registration-success-btn:hover {
            background-color: var(--guinda-darker);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
            color: white;
            text-decoration: none;
        }

        .user-info-box {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }

        .user-info-item {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .user-info-item i {
            color: var(--guinda-base);
        }

        .countdown-text {
            margin-top: 20px;
            color: #888;
            font-size: 0.9rem;
        }
    </style>

    @if(session('registration_success'))
        <!-- Pantalla de éxito después del registro -->
        <div class="registration-success-container">
            <div class="registration-success-card">
                <div class="registration-success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="registration-success-title">¡Registro Exitoso!</h1>
                <p class="registration-success-message">
                    Tu cuenta ha sido creada exitosamente. Ahora puedes iniciar sesión con tus credenciales.
                </p>
                
                <a href="{{ route('login') }}" class="registration-success-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Iniciar Sesión</span>
                </a>
                
                <div class="user-info-box">
                    <div class="user-info-item">
                        <i class="bi bi-person-check"></i>
                        <span><strong>Usuario:</strong> {{ session('registered_name') ?? 'Usuario' }}</span>
                    </div>
                    <div class="user-info-item">
                        <i class="bi bi-envelope"></i>
                        <span><strong>Email:</strong> {{ session('registered_email') ?? old('email') }}</span>
                    </div>
                    @if(session('registered_proceso'))
                    <div class="user-info-item">
                        <i class="bi bi-gear"></i>
                        <span><strong>Proceso:</strong> {{ session('registered_proceso') }}</span>
                    </div>
                    @endif
                    @if(session('registered_departamento'))
                    <div class="user-info-item">
                        <i class="bi bi-building"></i>
                        <span><strong>Departamento:</strong> {{ session('registered_departamento') }}</span>
                    </div>
                    @endif
                    <div class="user-info-item">
                        <i class="bi bi-clock-history"></i>
                        <span><strong>Fecha:</strong> {{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <p class="countdown-text">
                    Serás redirigido al login en <span id="countdown">10</span> segundos...
                </p>
            </div>
        </div>

        @push('scripts')
        <script>
        // Auto-redirect después de 10 segundos
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = '{{ route('login') }}';
            }
        }, 1000);
        </script>
        @endpush
    @else
        <!-- Formulario normal de registro -->
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-logo">
                    <h1>Sistema de Gestión de la Calidad</h1>
                </div>
                
                <h2 class="auth-title">Registro de usuarios</h2>
                
                <p class="auth-description">
                    Ingresa los datos para crear una cuenta de usuario
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
                
                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf
                    
                    <!-- Nombre Completo -->
                    <div class="auth-form-group">
                        <label for="name" class="auth-form-label">
                            <i class="bi bi-person"></i> Nombre Completo
                        </label>
                        <div class="auth-input-wrapper">
                            <input 
                                type="text" 
                                class="auth-form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}" 
                                placeholder="Ejem. Juan Napoleón" 
                                required 
                                autofocus
                                autocomplete="name"
                            >
                            <i class="bi bi-person auth-input-icon static-icon"></i>
                        </div>
                        @error('name')
                            <div class="field-error" data-server-error="true">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Email -->
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
                                value="{{ old('email') }}" 
                                placeholder="ejemplo@uptex.edu.mx" 
                                required
                                autocomplete="email"
                            >
                            <i class="bi bi-envelope auth-input-icon static-icon"></i>
                        </div>
                        @error('email')
                            <div class="field-error" data-server-error="true">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Proceso -->
                    <div class="auth-form-group">
                        <label for="proceso" class="auth-form-label">
                            <i class="bi bi-gear"></i> Proceso
                        </label>
                        <div class="auth-input-wrapper">
                            <select 
                                class="auth-form-control select-control @error('proceso') is-invalid @enderror" 
                                id="proceso" 
                                name="proceso" 
                                required
                            >
                                <option value="">Selecciona un proceso</option>
                                <option value="Planeación" {{ old('proceso') == 'Planeación' ? 'selected' : '' }}>Planeación</option>
                                <option value="Preinscripción" {{ old('proceso') == 'Preinscripción' ? 'selected' : '' }}>Preinscripción</option>
                                <option value="Inscripción" {{ old('proceso') == 'Inscripción' ? 'selected' : '' }}>Inscripción</option>
                                <option value="Reinscripción" {{ old('proceso') == 'Reinscripción' ? 'selected' : '' }}>Reinscripción</option>
                                <option value="Titulación" {{ old('proceso') == 'Titulación' ? 'selected' : '' }}>Titulación</option>
                                <option value="Enseñanza/Aprendizaje" {{ old('proceso') == 'Enseñanza/Aprendizaje' ? 'selected' : '' }}>Enseñanza/Aprendizaje</option>
                                <option value="Contratación o Control de Personal" {{ old('proceso') == 'Contratación o Control de Personal' ? 'selected' : '' }}>Contratación o Control de Personal</option>
                                <option value="Vinculación" {{ old('proceso') == 'Vinculación' ? 'selected' : '' }}>Vinculación</option>
                                <option value="TI" {{ old('proceso') == 'TI' ? 'selected' : '' }}>TI</option>
                                <option value="Gestión de Recursos" {{ old('proceso') == 'Gestión de Recursos' ? 'selected' : '' }}>Gestión de Recursos</option>
                                <option value="Laboratorios y Talleres" {{ old('proceso') == 'Laboratorios y Talleres' ? 'selected' : '' }}>Laboratorios y Talleres</option>
                                <option value="Centro de Información" {{ old('proceso') == 'Centro de Información' ? 'selected' : '' }}>Centro de Información</option>
                            </select>
                        </div>
                        @error('proceso')
                            <div class="field-error" data-server-error="true">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Departamento -->
                    <div class="auth-form-group">
                        <label for="departamento" class="auth-form-label">
                            <i class="bi bi-building"></i> Departamento
                        </label>
                        <div class="auth-input-wrapper">
                            <select 
                                class="auth-form-control select-control @error('departamento') is-invalid @enderror" 
                                id="departamento" 
                                name="departamento" 
                                required
                            >
                                <option value="">Selecciona un departamento</option>
                                <option value="Rectoría" data-proceso="Planeación">Rectoría</option>
                                <option value="Dirección Académica" data-proceso="Planeación,Enseñanza/Aprendizaje">Dirección Académica</option>
                                <option value="Dirección de Administración y Finanzas" data-proceso="Planeación">Dirección de Administración y Finanzas</option>
                                <option value="Servicios Escolares" data-proceso="Preinscripción,Inscripción,Reinscripción,Titulación">Servicios Escolares</option>
                                <option value="Recursos Humanos" data-proceso="Contratación o Control de Personal">Recursos Humanos</option>
                                <option value="Vinculación" data-proceso="Vinculación">Vinculación</option>
                                <option value="Sistemas Computacionales" data-proceso="TI">Sistemas Computacionales</option>
                                <option value="Recursos Financieros" data-proceso="Gestión de Recursos">Recursos Financieros</option>
                                <option value="Almacén" data-proceso="Gestión de Recursos">Almacén</option>
                                <option value="Encargado/a de Laboratorios" data-proceso="Laboratorios y Talleres">Encargado/a de Laboratorios</option>
                                <option value="Biblioteca" data-proceso="Centro de Información">Biblioteca</option>
                            </select>
                        </div>
                        @error('departamento')
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
                                placeholder="Crea una contraseña segura" 
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
                                placeholder="Confirmar contraseña" 
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
                    
                    <button type="submit" class="auth-btn-primary" id="registerBtn">
                        <i class="bi bi-person-plus"></i>
                        Registrar usuario
                    </button>
                    
                    <div class="auth-footer">
                        <p class="auth-footer-text">
                            <span>¿Ya tienes cuenta?</span>
                            <a href="{{ route('login') }}" class="auth-link">Inicia sesión</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        
        if (form) {
            const submitBtn = document.getElementById('registerBtn');
            const originalBtnText = submitBtn.innerHTML;
            
            // Elementos del formulario
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const procesoSelect = document.getElementById('proceso');
            const departamentoSelect = document.getElementById('departamento');
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const togglePassword = document.getElementById('togglePassword');
            const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
            const passwordStrengthFill = document.getElementById('passwordStrengthFill');
            const passwordStrengthText = document.getElementById('passwordStrengthText');
            const passwordMatchText = document.getElementById('passwordMatchText');
            
            // Función para filtrar departamentos según el proceso seleccionado
            function filterDepartamentos() {
                const selectedProceso = procesoSelect.value;
                const options = departamentoSelect.querySelectorAll('option');
                
                // Resetear selección
                departamentoSelect.value = '';
                
                options.forEach(option => {
                    if (option.value === '') {
                        // Siempre mostrar la opción por defecto
                        option.style.display = 'block';
                        return;
                    }
                    
                    const procesosPermitidos = option.getAttribute('data-proceso') || '';
                    const procesosArray = procesosPermitidos.split(',');
                    
                    if (selectedProceso === '') {
                        // Si no hay proceso seleccionado, ocultar todas las opciones excepto la primera
                        option.style.display = 'none';
                    } else if (procesosArray.includes(selectedProceso)) {
                        // Mostrar solo las opciones que pertenecen al proceso seleccionado
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // Validar el select de departamento después del filtro
                if (selectedProceso !== '') {
                    validateSelect(departamentoSelect);
                }
            }
            
            // Evento change en el select de proceso
            procesoSelect.addEventListener('change', filterDepartamentos);
            
            // Ejecutar filtro inicial si hay un valor guardado
            if (procesoSelect.value) {
                filterDepartamentos();
            }
            
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
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Registrando...';
                    submitBtn.disabled = true;
                    form.classList.add('auth-loading');
                }
            });
            
            // Toggle password visibility
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    togglePasswordVisibility(passwordInput, this);
                });
            }
            
            if (togglePasswordConfirmation) {
                togglePasswordConfirmation.addEventListener('click', function() {
                    togglePasswordVisibility(passwordConfirmInput, this);
                });
            }
            
            function togglePasswordVisibility(input, icon) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
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
            
            // Validaciones en tiempo real
            nameInput.addEventListener('blur', function() {
                validateName(this);
            });
            
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
            
            procesoSelect.addEventListener('change', function() {
                validateSelect(this);
            });
            
            departamentoSelect.addEventListener('change', function() {
                validateSelect(this);
            });
            
            passwordInput.addEventListener('input', function() {
                validatePasswordStrength(this.value);
                validatePasswordMatch();
            });
            
            passwordConfirmInput.addEventListener('input', function() {
                validatePasswordMatch();
            });
            
            function validateName(input) {
                const name = input.value.trim();
                
                if (name && name.length < 3) {
                    input.classList.add('is-invalid');
                    showFieldError(input, 'El nombre debe tener al menos 3 caracteres');
                } else if (name) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    hideFieldError(input);
                }
            }
            
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
            
            function validateSelect(select) {
                if (select.value) {
                    select.classList.remove('is-invalid');
                    select.classList.add('is-valid');
                    hideFieldError(select);
                } else {
                    select.classList.add('is-invalid');
                    showFieldError(select, 'Por favor selecciona una opción');
                }
            }
            
            function validatePasswordStrength(password) {
                let strength = 0;
                let text = 'Seguridad de la contraseña';
                
                passwordStrengthFill.className = 'password-strength-fill';
                
                if (password.length === 0) {
                    passwordStrengthFill.style.width = '0%';
                    passwordStrengthText.textContent = text;
                    return;
                }
                
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
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
                
                passwordStrengthFill.className = 'password-strength-fill ' + strengthClass;
                passwordStrengthFill.style.width = width + '%';
                passwordStrengthText.textContent = text;
                
                if (strength >= 4) {
                    passwordInput.classList.remove('is-invalid');
                    passwordInput.classList.add('is-valid');
                } else if (password.length > 0 && strength < 4) {
                    passwordInput.classList.add('is-invalid');
                    passwordInput.classList.remove('is-valid');
                }
            }
            
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
            
            passwordInput.addEventListener('blur', function() {
                if (this.value.length > 0 && this.value.length < 8) {
                    this.classList.add('is-invalid');
                    showFieldError(this, 'La contraseña debe tener al menos 8 caracteres');
                }
            });
            
            // Auto-validar al cargar
            if (nameInput.value) validateName(nameInput);
            if (emailInput.value) validateEmail(emailInput);
            if (procesoSelect.value) {
                validateSelect(procesoSelect);
                filterDepartamentos();
            }
            if (departamentoSelect.value) validateSelect(departamentoSelect);
            if (passwordInput.value) validatePasswordStrength(passwordInput.value);
            if (passwordConfirmInput.value) validatePasswordMatch();
            
            // Placeholder dinámico
            const passwordSuggestions = [
                "Usa al menos 8 caracteres",
                "Combina mayúsculas y minúsculas",
                "Incluye números y símbolos",
                "Evita información personal"
            ];
            
            let suggestionIndex = 0;
            function rotatePasswordSuggestion() {
                if (passwordInput.value === '') {
                    passwordInput.setAttribute('placeholder', passwordSuggestions[suggestionIndex]);
                    suggestionIndex = (suggestionIndex + 1) % passwordSuggestions.length;
                }
            }
            
            setInterval(rotatePasswordSuggestion, 5000);
            rotatePasswordSuggestion();
        }
    });
    </script>
    @endpush
</x-layouts.auth>