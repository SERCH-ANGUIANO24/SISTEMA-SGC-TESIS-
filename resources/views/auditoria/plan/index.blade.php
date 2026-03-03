@extends('layouts.app')

@section('title', 'Plan de Auditorías - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <!-- Header con ícono de carpeta -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- MENSAJE DE ÉXITO - ARRIBA DEL BOTÓN PLAN DE AUDITORÍAS -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                    <h1 class="h3 mb-0" style="color: #800000; cursor: pointer;">
                        <i class="bi bi-folder me-2" style="font-size: 2.5rem; vertical-align: middle;"></i>
                        Plan de Auditorías
                    </h1>
                </a>
                
                <!-- Botón Registrar Auditoría -->
                <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaAuditoria" style="background-color: #737373; color: white; border: none;">
                    <i class="bi bi-plus-circle"></i> Registrar Auditoría
                </button>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Buscar archivos -->
                <div class="d-flex align-items-center position-relative" style="width: 700px;">
                    <div class="position-relative flex-grow-1">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                        <input type="text" class="form-control ps-5" style="width: 100%; height: 42px; font-size: 1rem; border-radius: 4px 0 0 4px; border-right: none;" placeholder="Buscar archivos" id="buscadorArchivos">
                    </div>
                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" 
                            style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border-left: none;"
                            id="limpiarBusqueda"
                            onclick="limpiarBuscador()"
                            title="Limpiar búsqueda">
                        <i class="bi bi-x-lg" style="font-size: 1.4rem; font-weight: bold;"></i>
                    </button>
                </div>

                <!-- Ordenar por -->
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnOrdenar" style="height: 42px; background-color: white;">
                        <i class="bi bi-arrow-up-short"></i> <span id="ordenarTexto">Ordenar por</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('nombre-asc', 'Nombre (A-Z)')">Nombre (A-Z)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('nombre-desc', 'Nombre (Z-A)')">Nombre (Z-A)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-asc', 'Fecha (más antiguo)')">Fecha (más antiguo)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-desc', 'Fecha (más reciente)')">Fecha (más reciente)</a></li>
                    </ul>
                </div>

                <!-- Filtrar por Año -->
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnAnio" style="height: 42px; background-color: white;">
                        <i class="bi bi-calendar"></i> <span id="anioTexto">Filtrar por Año</span>
                    </button>
                    <ul class="dropdown-menu" id="menuAnios">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('', 'Filtrar por Año')">Todos los años</a></li>
                        @foreach($anios ?? [] as $anio)
                            <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('{{ $anio }}', 'Año {{ $anio }}')">{{ $anio }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Tipo de Auditoría -->
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnTipo" style="height: 42px; background-color: white;">
                        <i class="bi bi-building"></i> <span id="tipoTexto">Tipo de Auditoría</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('', 'Todos los tipos')">Todos los tipos</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Interna', 'Interna')" id="opcionInterna">Interna</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Externa', 'Externa')" id="opcionExterna">Externa</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Auditorías -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre de Auditoría</th>
                            <th>Tipo de Auditoría</th>
                            <th>Auditor Líder</th>
                            <th>Fecha de Auditoría</th>
                            <th>Año</th>
                            <th>Plan de Auditoría</th>
                            <th>Auditores</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody">
                        <tr>
                            <td colspan="8" class="text-center">Cargando auditorías...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA REGISTRAR/EDITAR AUDITORÍA -->
<div class="modal fade" id="modalNuevaAuditoria" tabindex="-1" aria-labelledby="modalNuevaAuditoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaAuditoriaLabel">
                    <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                    Registrar Nueva Auditoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAuditoria" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="auditoria_id" name="auditoria_id">
                    
                    <!-- DATOS DE LA AUDITORÍA -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #800000;">DATOS DE LA AUDITORÍA</h6>
                        </div>
                        
                        <!-- Nombre de Auditoría -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre de Auditoría *</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="nombre_auditoria" name="nombre_auditoria" placeholder="Ej: Auditoría Anual 2026">
                                <i class="bi bi-exclamation-circle icono-error" id="err-icon-nombre_auditoria"></i>
                            </div>
                            <div class="msg-error" id="err-nombre_auditoria">El nombre de la auditoría es requerido</div>
                        </div>
                        
                        <!-- Tipo de Auditoría -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Auditoría *</label>
                            <div class="position-relative">
                                <select class="form-control" id="tipo_auditoria" name="tipo_auditoria">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Interna">Interna</option>
                                    <option value="Externa">Externa</option>
                                </select>
                                <i class="bi bi-exclamation-circle icono-error" id="err-icon-tipo_auditoria"></i>
                            </div>
                            <div class="msg-error" id="err-tipo_auditoria">El tipo de auditoría es requerido</div>
                        </div>
                        
                        <!-- Auditor Líder -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditor Líder *</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="auditor_lider" name="auditor_lider" placeholder="Nombre del auditor líder">
                                <i class="bi bi-exclamation-circle icono-error" id="err-icon-auditor_lider"></i>
                            </div>
                            <div class="msg-error" id="err-auditor_lider">El nombre del auditor líder es requerido</div>
                        </div>
                        
                        <!-- Fecha de Auditoría -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Auditoría *</label>
                            <div class="position-relative">
                                <input type="date" class="form-control" id="fecha_auditoria" name="fecha_auditoria">
                                <i class="bi bi-exclamation-circle icono-error" id="err-icon-fecha_auditoria"></i>
                            </div>
                            <div class="msg-error" id="err-fecha_auditoria">La fecha de auditoría es requerida</div>
                        </div>
                        
                        <!-- Año -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Año *</label>
                            <div class="position-relative">
                                <input type="number" class="form-control" id="anio" name="anio" min="2000" max="2100" value="{{ date('Y') }}" placeholder="Ej: 2026">
                                <i class="bi bi-exclamation-circle icono-error" id="err-icon-anio"></i>
                            </div>
                            <div class="msg-error" id="err-anio">El año es requerido</div>
                        </div>
                        
                        <!-- Auditores (AHORA OBLIGATORIO) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditores *</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="auditores" name="auditores" placeholder="Nombre de Auditores">
                                <i class="bi bi-exclamation-circle icono-error" id="err-icon-auditores"></i>
                            </div>
                            <div class="msg-error" id="err-auditores">Los auditores son requeridos</div>
                        </div>
                    </div>
                    
                    <!-- PLAN DE AUDITORÍA (ARCHIVO) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #800000;">PLAN DE AUDITORÍA</h6>
                            <div class="border rounded p-4 bg-light">
                                <div class="text-center mb-3">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #800000;"></i>
                                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <input type="file" class="form-control" id="archivo_plan" name="archivo_plan" style="width: auto;" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.txt">
                                </div>
                                <div class="msg-error mt-2" id="err-archivo_plan" style="text-align:center;">El archivo del plan es requerido</div>
                                <div id="nombreArchivoActual" class="text-center mt-2 text-muted" style="display: none;">
                                    Archivo actual: <span id="nombreArchivo"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white" style="background-color: #800000; border: none;" id="btnGuardarAuditoria">
                        <i class="bi bi-check-circle me-1"></i> Guardar Auditoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CONTENEDOR PARA MODALES DE VISUALIZACIÓN -->
<div id="modalesContainer"></div>

<!-- MODAL RENOMBRAR DOCUMENTO -->
<div class="modal fade" id="renameDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="renameDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2" style="color: #800000;"></i>
                        Renombrar Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newDocumentName" class="form-label fw-bold">Nuevo nombre</label>
                        <input type="text" class="form-control" id="newDocumentName" name="name" required autofocus>
                        <div class="form-text">La extensión del archivo se mantendrá automáticamente.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Renombrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL MOVER DOCUMENTO -->
<div class="modal fade" id="moveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="moveDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #800000;"></i>
                        Mover Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <span class="fw-bold">Documento a mover:</span><br>
                        <span id="moveDocumentName" style="color: #800000; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="documentDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="documentDestination" name="destination_id">
                            <option value="">📁 Sin categoría</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Selecciona la carpeta donde deseas mover el documento.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-arrow-right me-1"></i> Mover aquí
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        background-color: #f8f9fa;
        color: black;
        text-align: center;
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
        border-top: 2px solid #dee2e6 !important;
        border-bottom: 2px solid #dee2e6 !important;
    }

    .table td {
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
    }
    
    /* ── Validación de campos ── */
    .msg-error {
        display: none;
        color: #800000;
        font-size: 0.82rem;
        margin-top: 4px;
    }

    .campo-invalido {
        border-color: #800000 !important;
        background-image: none !important;
        padding-right: 2.5rem;
    }

    .icono-error {
        display: none;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #800000;
        font-size: 1rem;
        pointer-events: none;
    }

    .campo-invalido ~ .icono-error,
    .campo-invalido + .icono-error {
        display: block;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%       { transform: translateX(-6px); }
        40%       { transform: translateX(6px); }
        60%       { transform: translateX(-4px); }
        80%       { transform: translateX(4px); }
    }
    .shake { animation: shake 0.4s ease; }

    .form-control:focus,
    .form-select:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
        z-index: 1;
    }
    
    .badge-interna {
        background-color: #28a745;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
    }
    
    .badge-externa {
        background-color: #dc3545;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
    }
    
    .btn-light {
        background-color: white !important;
        color: #6c757d;
        border: 1px solid #ced4da;
    }
    
    .btn-light:hover {
        background-color: #f8f9fa !important;
        border-color: #800000;
    }
    
    .btn-light i {
        color: #6c757d;
    }
    
    .btn-light.seleccionado {
        background-color: #e9ecef !important;
        border-color: #737373;
        color: #495057;
    }
    
    .btn-light.seleccionado i {
        color: #495057;
    }
    
    .dropdown-item:hover {
        background-color: #737373 !important;
        color: #ffffff !important;
    }
    
    .dropdown-item.active {
        background-color: #800000 !important;
        color: white !important;
    }
    
    #limpiarBusqueda {
        transition: all 0.2s ease;
        border-color: #ced4da;
        background-color: white;
    }
    
    #limpiarBusqueda:hover {
        background-color: #f8f9fa;
        border-color: #800000;
    }
    
    #limpiarBusqueda:hover i {
        color: #800000;
    }
    
    .btn[style*="background-color: #737373"]:hover {
        background-color: #5a5a5a !important;
        color: white !important;
    }
    
    .border.rounded.p-4.bg-light {
        border: 2px dashed #800000 !important;
        transition: all 0.3s ease;
    }
    
    .border.rounded.p-4.bg-light:hover {
        background-color: #fff0f0 !important;
        border-color: #600000 !important;
    }

    /* Estilos para los modales de visualización */
    .modal-xl {
        max-width: 90%;
    }
    
    .modal-body {
        background-color: #ffffff;
        height: 80vh;
        overflow: auto;
    }
    
    .modal-body img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .modal-body iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    /* ESTILO PARA MENSAJE DE ÉXITO */
    .alert-success {
        background-color: #48b161;
        color: #ffffff;
        border-color: #c3e6cb;
        border-radius: 8px;
        padding: 12px 20px;
        margin: 0 auto 20px auto;
        font-weight: 500;
        display: flex;
        align-items: center;
        position: relative;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        width: 95%;
        max-width: 1400px;
        min-width: 300px;
    }
    
    .alert-success i {
        font-size: 1.5rem;
        margin-right: 15px;
    }
    
    .alert-success .btn-close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem;
    }

    /* Estilos para botones de acción */
    .btn-outline-info {
        color: #0dcaf0;
        border-color: #0dcaf0;
    }
    .btn-outline-info:hover {
        color: #fff;
        background-color: #0dcaf0;
        border-color: #0dcaf0;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-primary {
        color: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-outline-primary:hover {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }
    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }

    /* Icono específico para CSV */
    .bi-file-spreadsheet {
        color: #217346;
    }

    /* SweetAlert2 personalizado */
    .swal2-popup {
        font-size: 1.2rem !important;
    }
    .swal2-title {
        color: #800000 !important;
    }
    .swal2-confirm {
        background-color: #dc3545 !important;
    }
    .swal2-cancel {
        background-color: #6c757d !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let auditoriasData = [];
    let tipoSeleccionado = '';
    let anioSeleccionado = '';
    let ordenSeleccionado = '';

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado, iniciando...');
        cargarAuditorias();
        configurarEventos();
        
        const modal = document.getElementById('modalNuevaAuditoria');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                resetForm();
                limpiarErrores();
            });
        }
    });

    function configurarEventos() {
        // Limpiar error al escribir/cambiar cada campo
        ['nombre_auditoria', 'tipo_auditoria', 'auditor_lider', 'fecha_auditoria', 'anio', 'auditores'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', () => mostrarError(id, false));
                el.addEventListener('change', () => mostrarError(id, false));
            }
        });
        
        // Archivo
        const archivoInput = document.getElementById('archivo_plan');
        if (archivoInput) {
            archivoInput.addEventListener('change', () => {
                const errArchivo = document.getElementById('err-archivo_plan');
                if (errArchivo) errArchivo.style.display = 'none';
            });
        }
        
        const buscador = document.getElementById('buscadorArchivos');
        if (buscador) {
            buscador.addEventListener('keyup', function() {
                filtrarPorBusqueda(this.value);
            });
        }
        
        // Botón guardar personalizado
        document.getElementById('btnGuardarAuditoria').addEventListener('click', function() {
            guardarAuditoria();
        });
    }

    function limpiarBuscador() {
        const buscador = document.getElementById('buscadorArchivos');
        if (buscador) {
            buscador.value = '';
            filtrarPorBusqueda('');
            buscador.focus();
        }
    }

    function seleccionarOrden(criterio, texto) {
        ordenSeleccionado = criterio;
        document.getElementById('ordenarTexto').innerText = texto;
        document.getElementById('btnOrdenar').classList.add('seleccionado');
        filtrarYRenderizar();
    }

    function seleccionarTipo(tipo, texto) {
        tipoSeleccionado = tipo;
        document.getElementById('tipoTexto').innerText = texto;

        const opcionInterna = document.getElementById('opcionInterna');
        const opcionExterna = document.getElementById('opcionExterna');

        if (opcionInterna) opcionInterna.classList.remove('active');
        if (opcionExterna) opcionExterna.classList.remove('active');

        if (tipo === '') {
            document.getElementById('btnTipo').classList.remove('seleccionado');
        } else {
            document.getElementById('btnTipo').classList.add('seleccionado');
            if (tipo === 'Interna' && opcionInterna) opcionInterna.classList.add('active');
            if (tipo === 'Externa' && opcionExterna) opcionExterna.classList.add('active');
        }

        filtrarYRenderizar();
    }

    function seleccionarAnio(anio, texto) {
        anioSeleccionado = anio;
        document.getElementById('anioTexto').innerText = texto;

        if (anio !== '') {
            document.getElementById('btnAnio').classList.add('seleccionado');
        } else {
            document.getElementById('btnAnio').classList.remove('seleccionado');
        }

        filtrarYRenderizar();
    }

    function filtrarYRenderizar() {
        let datos = [...auditoriasData];

        if (tipoSeleccionado) {
            datos = datos.filter(a => a.tipo_auditoria === tipoSeleccionado);
        }

        if (anioSeleccionado) {
            datos = datos.filter(a => String(a.anio) === String(anioSeleccionado));
        }

        const textoBuscador = document.getElementById('buscadorArchivos')?.value?.toLowerCase().trim() || '';
        if (textoBuscador) {
            datos = datos.filter(a =>
                a.nombre_auditoria.toLowerCase().includes(textoBuscador) ||
                (a.auditor_lider && a.auditor_lider.toLowerCase().includes(textoBuscador))
            );
        }

        if (ordenSeleccionado) {
            switch(ordenSeleccionado) {
                case 'nombre-asc':  datos.sort((a,b) => a.nombre_auditoria.localeCompare(b.nombre_auditoria)); break;
                case 'nombre-desc': datos.sort((a,b) => b.nombre_auditoria.localeCompare(a.nombre_auditoria)); break;
                case 'fecha-asc':   datos.sort((a,b) => new Date(a.fecha_auditoria) - new Date(b.fecha_auditoria)); break;
                case 'fecha-desc':  datos.sort((a,b) => new Date(b.fecha_auditoria) - new Date(a.fecha_auditoria)); break;
            }
        }

        renderizarTabla(datos);
    }

    function filtrarPorBusqueda(texto) {
        filtrarYRenderizar();
    }

    function cargarAuditorias() {
        let url = '{{ route("auditoria.plan.data") }}';
        let params = new URLSearchParams();
        
        if (tipoSeleccionado) params.append('tipo', tipoSeleccionado);
        if (anioSeleccionado) params.append('anio', anioSeleccionado);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        console.log('Cargando datos de:', url);
        
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error('Error ' + response.status + ': ' + text.substring(0, 100));
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            auditoriasData = data;
            
            poblarFiltroAnios(data);

            if (ordenSeleccionado) {
                ordenarPor(ordenSeleccionado);
            } else {
                renderizarTabla(data);
            }
            
            const buscador = document.getElementById('buscadorArchivos');
            if (buscador) buscador.value = '';
        })
        .catch(error => {
            console.error('Error detallado:', error);
            document.getElementById('tablaBody').innerHTML = `<tr><td colspan="8" class="text-center text-danger">
                Error al cargar las auditorías: ${error.message}
            </td></tr>`;
        });
    }

    function generarModalVisualizador(auditoria) {
        if (!auditoria.archivo_nombre) return '';
        
        const extension = auditoria.archivo_nombre.split('.').pop().toLowerCase();
        const extensionesNoVisibles = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];
        
        if (extensionesNoVisibles.includes(extension)) {
            return '';
        }
        
        const modalId = `viewDocumentModal${auditoria.id}`;
        const cacheBuster = auditoria.updated_at ? new Date(auditoria.updated_at).getTime() : Date.now();
        const url = `{{ url('auditoria/plan/ver') }}/${auditoria.id}?t=${cacheBuster}`;
        const downloadUrl = `{{ url('auditoria/plan/download') }}/${auditoria.id}`;
        
        let contenidoModal = '';
        
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'].includes(extension)) {
            contenidoModal = `
                <div class="d-flex justify-content-center align-items-center h-100">
                    <img src="${url}" class="img-fluid" alt="${auditoria.nombre_auditoria}" style="max-height: 100%; object-fit: contain;">
                </div>
            `;
        } else if (extension === 'pdf') {
            contenidoModal = `<iframe src="${url}" style="width: 100%; height: 100%; border: none;"></iframe>`;
        } else if (extension === 'txt') {
            contenidoModal = `<iframe src="${url}" style="width: 100%; height: 100%; border: none;"></iframe>`;
        } else {
            contenidoModal = `
                <div class="d-flex flex-column justify-content-center align-items-center h-100">
                    <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                    <p class="mt-3">Vista previa no disponible para este tipo de archivo</p>
                    <a href="${downloadUrl}" class="btn text-white mt-2" style="background-color: #800000;">
                        <i class="bi bi-download me-1"></i> Descargar para ver
                    </a>
                </div>
            `;
        }
        
        return `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="${modalId}Label">
                                <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                                ${auditoria.nombre_auditoria} - ${auditoria.archivo_nombre}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            ${contenidoModal}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="${downloadUrl}" class="btn text-white" style="background-color: #800000;">
                                <i class="bi bi-download me-1"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function poblarFiltroAnios(data) {
        const menu = document.getElementById('menuAnios');
        if (!menu) return;

        // Obtener años únicos de los datos
        const anios = [...new Set(data.map(a => a.anio).filter(Boolean))].sort((a, b) => b - a);

        // Reconstruir el menú manteniendo "Todos los años"
        menu.innerHTML = `<li><a class="dropdown-item" href="#" onclick="seleccionarAnio('', 'Filtrar por Año')">Todos los años</a></li>`;

        anios.forEach(anio => {
            menu.innerHTML += `<li><a class="dropdown-item" href="#" onclick="seleccionarAnio('${anio}', 'Año ${anio}')">${anio}</a></li>`;
        });
    }

    function ordenarPor(criterio) {
        if (!auditoriasData || auditoriasData.length === 0) return;
        
        let datosOrdenados = [...auditoriasData];
        
        switch(criterio) {
            case 'nombre-asc':
                datosOrdenados.sort((a, b) => a.nombre_auditoria.localeCompare(b.nombre_auditoria));
                break;
            case 'nombre-desc':
                datosOrdenados.sort((a, b) => b.nombre_auditoria.localeCompare(a.nombre_auditoria));
                break;
            case 'fecha-asc':
                datosOrdenados.sort((a, b) => new Date(a.fecha_auditoria) - new Date(b.fecha_auditoria));
                break;
            case 'fecha-desc':
                datosOrdenados.sort((a, b) => new Date(b.fecha_auditoria) - new Date(a.fecha_auditoria));
                break;
        }
        
        renderizarTabla(datosOrdenados);
    }

    function renderizarTabla(data) {
        const tbody = document.getElementById('tablaBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No hay auditorías registradas</td></tr>';
            return;
        }

        // Limpiar y generar nuevos modales
        const modalesContainer = document.getElementById('modalesContainer');
        if (modalesContainer) {
            modalesContainer.innerHTML = '';
            
            data.forEach(auditoria => {
                const modalHTML = generarModalVisualizador(auditoria);
                if (modalHTML) {
                    modalesContainer.innerHTML += modalHTML;
                }
            });
        }

        data.forEach(auditoria => {
            const tr = document.createElement('tr');
            tr.className = 'align-middle';
            
            const badgeClass = auditoria.tipo_auditoria === 'Interna' ? 'badge-interna' : 'badge-externa';
            
            const extensionesNoVisibles = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];
            let esVisible = false;
            
            if (auditoria.archivo_nombre) {
                const ext = auditoria.archivo_nombre.split('.').pop().toLowerCase();
                esVisible = !extensionesNoVisibles.includes(ext);
            }
            
            let fecha = '';
            if (auditoria.fecha_auditoria) {
                const fechaObj = new Date(auditoria.fecha_auditoria);
                fecha = fechaObj.toLocaleDateString('es-ES');
            }
            
            // Determinar el icono según la extensión del archivo
            let iconoArchivo = 'bi-file-earmark';
            if (auditoria.archivo_nombre) {
                const ext = auditoria.archivo_nombre.split('.').pop().toLowerCase();
                if (ext === 'pdf') iconoArchivo = 'bi-file-pdf';
                else if (['doc', 'docx'].includes(ext)) iconoArchivo = 'bi-file-word';
                else if (['xls', 'xlsx'].includes(ext)) iconoArchivo = 'bi-file-excel';
                else if (ext === 'csv') iconoArchivo = 'bi-file-spreadsheet';
                else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'].includes(ext)) iconoArchivo = 'bi-file-image';
            }
            
            tr.innerHTML = `
                <td class="fw-bold">${auditoria.nombre_auditoria || ''}</td>
                <td><span class="${badgeClass}">${auditoria.tipo_auditoria || ''}</span></td>
                <td>${auditoria.auditor_lider || ''}</td>
                <td>${fecha}</td>
                <td>${auditoria.anio || ''}</td>
                <td>
                    ${auditoria.archivo_nombre ? 
                        `<div>
                            <i class="bi ${iconoArchivo} me-2" style="color: #800000;"></i>
                            <span title="${auditoria.archivo_nombre}">${auditoria.archivo_nombre.substring(0, 20)}${auditoria.archivo_nombre.length > 20 ? '...' : ''}</span>
                        </div>` : 
                        '<span class="text-muted">-</span>'}
                </td>
                <td>${auditoria.auditores || '-'}</td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-1">
                        ${esVisible ? 
                            `<button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="verArchivo(${auditoria.id})" 
                                    title="Ver archivo">
                                <i class="bi bi-eye"></i>
                            </button>` : 
                            ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="editarAuditoria(${auditoria.id})"
                                title="Editar auditoría">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        
                        <a href="{{ url('auditoria/plan/download') }}/${auditoria.id}" 
                           class="btn btn-sm btn-outline-primary"
                           title="Descargar archivo">
                            <i class="bi bi-download"></i>
                        </a>
                        
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="eliminarAuditoria(${auditoria.id}, '${(auditoria.nombre_auditoria || '').replace(/'/g, "\\'")}')"
                                title="Eliminar auditoría">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function verArchivo(id) {
        console.log('Ver archivo ID:', id);
        const modalId = `viewDocumentModal${id}`;
        const modalElement = document.getElementById(modalId);
        console.log('Modal element:', modalElement);
        
        if (modalElement) {
            // Asegurarse de que no haya otros modales abiertos
            const modalesAbiertos = document.querySelectorAll('.modal.show');
            modalesAbiertos.forEach(modal => {
                const instancia = bootstrap.Modal.getInstance(modal);
                if (instancia) {
                    instancia.hide();
                }
            });
            
            // Limpiar backdrops existentes
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            
            // Quitar la clase modal-open del body
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            
            // Abrir el nuevo modal
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true
            });
            modal.show();
            
            // Evento para limpiar cuando se cierre el modal
            modalElement.addEventListener('hidden.bs.modal', function() {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            }, { once: true });
            
        } else {
            alert('Error: No se encontró el modal para visualizar el archivo');
        }
    }

    function openRenameDocumentModal(id, nombre) {
        event.stopPropagation();
        const form = document.getElementById('renameDocumentForm');
        form.action = '/auditoria/plan/' + id + '/rename';
        document.getElementById('newDocumentName').value = nombre;
        new bootstrap.Modal(document.getElementById('renameDocumentModal')).show();
    }

    function openMoveDocumentModal(id, nombre) {
        event.stopPropagation();
        const form = document.getElementById('moveDocumentForm');
        form.action = '/auditoria/plan/' + id + '/move';
        document.getElementById('moveDocumentName').innerHTML = nombre;
        
        const select = document.getElementById('documentDestination');
        select.innerHTML = '<option value="">📁 Sin categoría</option>';
        
        new bootstrap.Modal(document.getElementById('moveDocumentModal')).show();
    }

    // ── Validación del formulario ──
    function mostrarError(id, mostrar) {
        const input = document.getElementById(id);
        const msg   = document.getElementById('err-' + id);
        const icon  = document.getElementById('err-icon-' + id);
        if (!input || !msg) return;
        if (mostrar) {
            input.classList.add('campo-invalido');
            msg.style.display = 'block';
            if (icon) icon.style.display = 'block';
        } else {
            input.classList.remove('campo-invalido');
            msg.style.display = 'none';
            if (icon) icon.style.display = 'none';
        }
    }

    function validarFormulario() {
        const esEdicion = !!document.getElementById('auditoria_id').value;
        let valido = true;

        const campos = ['nombre_auditoria', 'tipo_auditoria', 'auditor_lider', 'fecha_auditoria', 'anio', 'auditores'];
        campos.forEach(id => {
            const val = document.getElementById(id)?.value?.trim();
            const vacio = !val || val === '';
            mostrarError(id, vacio);
            if (vacio) valido = false;
        });

        // Archivo solo requerido al crear
        const archivo = document.getElementById('archivo_plan');
        const errArchivo = document.getElementById('err-archivo_plan');
        if (!esEdicion && archivo && !archivo.files.length) {
            if (errArchivo) errArchivo.style.display = 'block';
            valido = false;
        } else {
            if (errArchivo) errArchivo.style.display = 'none';
        }

        // Shake en campos inválidos
        if (!valido) {
            document.querySelectorAll('.campo-invalido').forEach(el => {
                el.classList.remove('shake');
                void el.offsetWidth;
                el.classList.add('shake');
                el.addEventListener('animationend', () => el.classList.remove('shake'), { once: true });
            });
        }

        return valido;
    }

    function limpiarErrores() {
        ['nombre_auditoria', 'tipo_auditoria', 'auditor_lider', 'fecha_auditoria', 'anio', 'auditores'].forEach(id => mostrarError(id, false));
        const errArchivo = document.getElementById('err-archivo_plan');
        if (errArchivo) errArchivo.style.display = 'none';
    }

    function guardarAuditoria() {
        const id = document.getElementById('auditoria_id').value;
        const url = id ? 
            `{{ url('auditoria/plan') }}/${id}` : 
            '{{ route('auditoria.plan.store') }}';
        
        const formData = new FormData(document.getElementById('formAuditoria'));
        
        if (id) {
            formData.append('_method', 'PUT');
        }

        if (!validarFormulario()) return;

        const submitBtn = document.getElementById('btnGuardarAuditoria');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const modalElement = document.getElementById('modalNuevaAuditoria');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
                
                cargarAuditorias();
                resetForm();
                
                // Mostrar mensaje de éxito
                mostrarMensajeExito(data.message || 'Auditoría guardada correctamente');
            } else {
                alert('Error al guardar la auditoría: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la auditoría. Por favor, intente de nuevo.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // FUNCIÓN PARA ELIMINAR (COPIADA DE COMPETENCIAS)
    function eliminarAuditoria(id, nombre) {
        event.stopPropagation();
        event.preventDefault();
        
        Swal.fire({
            title: '¿Eliminar archivo?',
            text: `¿Estás seguro de eliminar "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch(`{{ url('auditoria/plan') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: data.message,
                            confirmButtonColor: '#800000',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al eliminar',
                            confirmButtonColor: '#800000'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión',
                        confirmButtonColor: '#800000'
                    });
                });
            }
        });
        
        return false;
    }
    
    function mostrarMensajeExito(mensaje) {
        // Eliminar mensajes de éxito existentes
        const existingAlerts = document.querySelectorAll('.alert-success');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mb-3';
        alertDiv.setAttribute('role', 'alert');
        
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle me-2"></i> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insertar DENTRO del primer row, ANTES del d-flex
        const firstRow = document.querySelector('.container-fluid .row:first-child .col-12');
        if (firstRow) {
            firstRow.insertBefore(alertDiv, firstRow.firstChild);
        } else {
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
        }
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
                if (alert) {
                    alert.close();
                }
            }
        }, 5000);
    }

    function editarAuditoria(id) {
        const auditoria = auditoriasData.find(a => a.id === id);
        if (auditoria) {
            document.getElementById('auditoria_id').value = auditoria.id;
            document.getElementById('nombre_auditoria').value = auditoria.nombre_auditoria;
            document.getElementById('tipo_auditoria').value = auditoria.tipo_auditoria;
            document.getElementById('auditor_lider').value = auditoria.auditor_lider;
            
            if (auditoria.fecha_auditoria) {
                const fecha = new Date(auditoria.fecha_auditoria);
                const año = fecha.getFullYear();
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                const dia = String(fecha.getDate()).padStart(2, '0');
                document.getElementById('fecha_auditoria').value = `${año}-${mes}-${dia}`;
            }
            
            document.getElementById('anio').value = auditoria.anio;
            document.getElementById('auditores').value = auditoria.auditores || '';
            
            // Quitar required del archivo cuando se edita
            const archivoInput = document.getElementById('archivo_plan');
            if (archivoInput) {
                archivoInput.removeAttribute('required');
            }
            
            const nombreArchivoActual = document.getElementById('nombreArchivoActual');
            const nombreArchivo = document.getElementById('nombreArchivo');
            
            if (auditoria.archivo_nombre) {
                if (nombreArchivoActual) nombreArchivoActual.style.display = 'block';
                if (nombreArchivo) nombreArchivo.textContent = auditoria.archivo_nombre;
            } else {
                if (nombreArchivoActual) nombreArchivoActual.style.display = 'none';
            }
            
            const modalTitle = document.getElementById('modalNuevaAuditoriaLabel');
            if (modalTitle) modalTitle.textContent = 'Editar Auditoría';
            
            const modal = new bootstrap.Modal(document.getElementById('modalNuevaAuditoria'));
            modal.show();
        }
    }

    function descargarArchivo(id) {
        window.location.href = `{{ url('auditoria/plan/download') }}/${id}`;
    }

    function resetForm() {
        const form = document.getElementById('formAuditoria');
        if (form) form.reset();
        limpiarErrores();
        
        const auditoriaId = document.getElementById('auditoria_id');
        if (auditoriaId) auditoriaId.value = '';
        
        const nombreArchivoActual = document.getElementById('nombreArchivoActual');
        if (nombreArchivoActual) nombreArchivoActual.style.display = 'none';
        
        const modalTitle = document.getElementById('modalNuevaAuditoriaLabel');
        if (modalTitle) modalTitle.textContent = 'Registrar Nueva Auditoría';
    }

    // Drag and drop
    const dropZone = document.querySelector('.border.rounded.p-4.bg-light');
    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '#e9ecef';
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
            
            const files = e.dataTransfer.files;
            const fileInput = document.getElementById('archivo_plan');
            if (fileInput && files.length > 0) {
                fileInput.files = files;
            }
        });
    }
</script>
@endpush