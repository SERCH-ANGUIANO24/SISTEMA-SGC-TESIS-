@extends('layouts.app')

@section('title', 'Plan de Auditorías - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none">
                    <h1 class="h3 mb-0" style="color: #800000;">
                        <i class="bi bi-folder me-2" style="font-size: 2.5rem; vertical-align: middle;"></i>
                        Plan de Auditorías
                    </h1>
                </a>

                {{-- Solo admin y superadmin pueden registrar auditorías --}}
                @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
                    <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaAuditoria" style="background-color: #737373; color: white; border: none;">
                        <i class="bi bi-plus-circle"></i> Registrar Auditoría
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Buscar archivos con X visible -->
                <div class="d-flex align-items-center position-relative" style="width: 700px;">
                    <div class="position-relative flex-grow-1">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                        <input type="text" class="form-control ps-5" style="width: 100%; height: 42px; border-radius: 4px 0 0 4px; border-right: none;" placeholder="Buscar archivos" id="buscadorArchivos">
                    </div>
                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" 
                            style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border: 1px solid #ced4da; border-left: none;"
                            id="limpiarBusqueda"
                            onclick="limpiarBuscador()"
                            title="Limpiar búsqueda">
                        <i class="bi bi-x-lg" style="font-size: 1.4rem; color: #6c757d;"></i>
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
                            <th>Fecha De Auditoría</th>
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
@if(in_array(Auth::user()->role, ['admin', 'superadmin']))
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

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre de Auditoría *</label>
                            <input type="text" class="form-control" id="nombre_auditoria" name="nombre_auditoria" placeholder="Ej: Auditoría Anual 2026">
                            <div class="msg-error" id="err-nombre_auditoria">El nombre de la auditoría es requerido</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Auditoría *</label>
                            <select class="form-control" id="tipo_auditoria" name="tipo_auditoria">
                                <option value="">-- Seleccionar --</option>
                                <option value="Interna">Interna</option>
                                <option value="Externa">Externa</option>
                            </select>
                            <div class="msg-error" id="err-tipo_auditoria">El tipo de auditoría es requerido</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditor Líder *</label>
                            <input type="text" class="form-control" id="auditor_lider" name="auditor_lider" placeholder="Nombre del auditor líder">
                            <div class="msg-error" id="err-auditor_lider">El nombre del auditor líder es requerido</div>
                        </div>

                        <!-- CAMPO ÚNICO PARA RANGO DE FECHAS -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Auditoría*</label>
                            <input type="text" class="form-control" id="rango_fechas" name="rango_fechas" placeholder="Seleccionar Fecha de Auditoría" readonly>
                            <input type="hidden" id="fecha_inicio" name="fecha_inicio">
                            <input type="hidden" id="fecha_fin" name="fecha_fin">
                            <div class="msg-error" id="err-rango_fechas">Debe seleccionar Fecha de Auditoría</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Año *</label>
                            <input type="number" class="form-control" id="anio" name="anio" min="2000" max="2100" value="{{ date('Y') }}" placeholder="Ej: 2026">
                            <div class="msg-error" id="err-anio">El año es requerido</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditores *</label>
                            <input type="text" class="form-control" id="auditores" name="auditores" placeholder="Nombre de Auditores">
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
                                    <input type="file" class="form-control" id="archivo_plan" name="archivo_plan" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.txt">
                                </div>
                                <div class="msg-error mt-2" id="err-archivo_plan">El archivo del plan es requerido</div>
                                <div id="nombreArchivoActual" class="text-center mt-2 text-muted" style="display: none;">
                                    Archivo actual: <span id="nombreArchivo"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white" style="background-color: #800000;" id="btnGuardarAuditoria">
                        <i class="bi bi-check-circle me-1"></i> Guardar Auditoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- CONTENEDOR PARA MODALES DE VISUALIZACIÓN -->
<div id="modalesContainer"></div>
@endsection

@push('styles')
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .table th {
        background-color: #f8f9fa;
        color: black;
        text-align: center;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
    }
    .msg-error {
        display: none;
        color: #800000;
        font-size: 0.82rem;
        margin-top: 4px;
    }
    .campo-invalido {
        border-color: #800000 !important;
    }
    .btn-light {
        background-color: white !important;
        color: #6c757d;
        border: 1px solid #ced4da;
    }
    .btn-light:hover {
        border-color: #800000;
    }
    .dropdown-item:hover {
        background-color: #737373 !important;
        color: #ffffff !important;
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
    .border.rounded.p-4.bg-light {
        border: 2px dashed #800000 !important;
        transition: all 0.3s ease;
    }
    .border.rounded.p-4.bg-light:hover {
        background-color: #fff0f0 !important;
        border-color: #600000 !important;
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

    /* Para que el nombre del archivo no se desborde */
    .nombre-archivo {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let auditoriasData = [];
    let tipoSeleccionado = '';
    let anioSeleccionado = '';
    let ordenSeleccionado = '';
    const userRole = '{{ Auth::user()->role }}';

    // Lista de extensiones sin vista previa (no mostrarán botón "Ver")
    const extensionesSinVista = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];

    $(document).ready(function() {
        cargarAuditorias();
        configurarEventos();
        inicializarDateRangePicker();

        $('#modalNuevaAuditoria').on('hidden.bs.modal', function () {
            resetForm();
            limpiarErrores();
        });
    });

    function inicializarDateRangePicker() {
        $('#rango_fechas').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            autoUpdateInput: false,
            startDate: moment(),
            endDate: moment()
        });

        $('#rango_fechas').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $('#fecha_inicio').val(picker.startDate.format('YYYY-MM-DD'));
            $('#fecha_fin').val(picker.endDate.format('YYYY-MM-DD'));
            $('#err-rango_fechas').hide();
        });

        $('#rango_fechas').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
        });
    }

    function configurarEventos() {
        $('#btnGuardarAuditoria').on('click', guardarAuditoria);

        $('#buscadorArchivos').on('keyup', function() {
            filtrarPorBusqueda($(this).val());
        });

        $('#limpiarBusqueda').on('click', function() {
            $('#buscadorArchivos').val('');
            filtrarPorBusqueda('');
        });

        // Validación en tiempo real (opcional)
        $('#nombre_auditoria, #tipo_auditoria, #auditor_lider, #anio, #auditores').on('input change', function() {
            const id = $(this).attr('id');
            if ($(this).val().trim()) {
                $(`#err-${id}`).hide();
                $(this).removeClass('campo-invalido');
            }
        });

        $('#archivo_plan').on('change', function() {
            if (this.files.length) $('#err-archivo_plan').hide();
        });
    }

    function cargarAuditorias() {
        let url = '{{ route("auditoria.plan.data") }}';
        let params = new URLSearchParams();
        if (tipoSeleccionado) params.append('tipo', tipoSeleccionado);
        if (anioSeleccionado) params.append('anio', anioSeleccionado);
        if (params.toString()) url += '?' + params.toString();

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            auditoriasData = data;
            poblarFiltroAnios(data);
            renderizarTabla(data);
        })
        .catch(error => {
            console.error(error);
            $('#tablaBody').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos</td></tr>');
        });
    }

    function poblarFiltroAnios(data) {
        const anios = [...new Set(data.map(a => a.anio).filter(Boolean))].sort((a,b) => b-a);
        let html = '<li><a class="dropdown-item" href="#" onclick="seleccionarAnio(\'\', \'Filtrar por Año\')">Todos los años</a></li>';
        anios.forEach(anio => {
            html += `<li><a class="dropdown-item" href="#" onclick="seleccionarAnio('${anio}', 'Año ${anio}')">${anio}</a></li>`;
        });
        $('#menuAnios').html(html);
    }

    function renderizarTabla(data) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center py-4">No hay auditorías registradas</td></tr>');
            return;
        }

        $('#modalesContainer').empty();

        data.forEach(auditoria => {
            if (auditoria.archivo_nombre) {
                const modal = generarModalVisualizador(auditoria);
                if (modal) $('#modalesContainer').append(modal);
            }

            const badgeClass = auditoria.tipo_auditoria === 'Interna' ? 'badge-interna' : 'badge-externa';

            // Calcular rango y días
            let fechas = '-';
            if (auditoria.fecha_inicio && auditoria.fecha_fin) {
                const inicio = moment(auditoria.fecha_inicio);
                const fin = moment(auditoria.fecha_fin);
                const dias = fin.diff(inicio, 'days') + 1;
                fechas = inicio.format('DD/MM/YYYY') + ' - ' + fin.format('DD/MM/YYYY') + ` (${dias} días)`;
            }

            // Determinar si el archivo tiene vista previa (botón "Ver")
            let tieneVista = false;
            let iconoArchivo = 'bi-file-earmark';
            if (auditoria.archivo_nombre) {
                const ext = auditoria.archivo_nombre.split('.').pop().toLowerCase();
                tieneVista = !extensionesSinVista.includes(ext);
                // Asignar ícono según extensión
                if (ext === 'pdf') iconoArchivo = 'bi-file-pdf';
                else if (['doc', 'docx'].includes(ext)) iconoArchivo = 'bi-file-word';
                else if (['xls', 'xlsx'].includes(ext)) iconoArchivo = 'bi-file-excel';
                else if (ext === 'csv') iconoArchivo = 'bi-file-spreadsheet';
                else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'].includes(ext)) iconoArchivo = 'bi-file-image';
                else if (ext === 'txt') iconoArchivo = 'bi-file-text';
            }

            {{-- Acciones según el rol del usuario --}}
            let acciones = '';
            
            @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
                {{-- Admin y superadmin tienen todas las acciones --}}
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${tieneVista ? '<button class="btn btn-sm btn-outline-info" onclick="verArchivo('+auditoria.id+')" title="Ver"><i class="bi bi-eye"></i></button>' : ''}
                        <button class="btn btn-sm btn-outline-secondary" onclick="editarAuditoria(${auditoria.id})" title="Editar"><i class="bi bi-pencil-square"></i></button>
                        <a href="{{ url('auditoria/plan/download') }}/${auditoria.id}" class="btn btn-sm btn-outline-primary" title="Descargar"><i class="bi bi-download"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarAuditoria(${auditoria.id}, '${auditoria.nombre_auditoria}')" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </div>
                `;
            @else
                {{-- Usuario normal solo puede ver y descargar --}}
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${tieneVista ? '<button class="btn btn-sm btn-outline-info" onclick="verArchivo('+auditoria.id+')" title="Ver"><i class="bi bi-eye"></i></button>' : ''}
                        <a href="{{ url('auditoria/plan/download') }}/${auditoria.id}" class="btn btn-sm btn-outline-primary" title="Descargar"><i class="bi bi-download"></i></a>
                    </div>
                `;
            @endif

            // Mostrar nombre completo del archivo con ícono
            let archivoMostrar = '-';
            if (auditoria.archivo_nombre) {
                archivoMostrar = `
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <i class="bi ${iconoArchivo}" style="color: #800000; font-size: 1.2rem;"></i>
                        <span class="nombre-archivo" title="${auditoria.archivo_nombre}">${auditoria.archivo_nombre}</span>
                    </div>
                `;
            }

            const row = `
                <tr>
                    <td class="fw-bold">${auditoria.nombre_auditoria || ''}</td>
                    <td><span class="${badgeClass}">${auditoria.tipo_auditoria || ''}</span></td>
                    <td>${auditoria.auditor_lider || ''}</td>
                    <td>${fechas}</td>
                    <td>${auditoria.anio || ''}</td>
                    <td>${archivoMostrar}</td>
                    <td>${auditoria.auditores || '-'}</td>
                    <td class="text-end">${acciones}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function generarModalVisualizador(auditoria) {
        if (!auditoria.archivo_nombre) return '';
        const extension = auditoria.archivo_nombre.split('.').pop().toLowerCase();
        const url = `{{ url('auditoria/plan/ver') }}/${auditoria.id}`;
        const downloadUrl = `{{ url('auditoria/plan/download') }}/${auditoria.id}`;
        const modalId = `viewDocumentModal${auditoria.id}`;

        let contenido = '';
        // Imágenes
        if (['jpg','jpeg','png','gif'].includes(extension)) {
            contenido = `<img src="${url}" class="img-fluid" style="max-height: 100%;">`;
        } 
        // PDF y TXT se muestran en iframe
        else if (extension === 'pdf' || extension === 'txt') {
            contenido = `<iframe src="${url}" style="width:100%;height:100%;border:none;"></iframe>`;
        } 
        // Resto: sin vista previa
        else {
            contenido = `
                <div class="d-flex flex-column justify-content-center align-items-center h-100">
                    <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                    <p class="mt-3">Vista previa no disponible para este tipo de archivo</p>
                </div>
            `;
        }

        return `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${auditoria.archivo_nombre}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0" style="height:70vh;">${contenido}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="${downloadUrl}" class="btn text-white" style="background-color:#800000;" download>Descargar</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function verArchivo(id) {
        const modal = $(`#viewDocumentModal${id}`);
        if (modal.length) new bootstrap.Modal(modal[0]).show();
    }

    function validarFormulario() {
        let valido = true;
        const campos = ['nombre_auditoria', 'tipo_auditoria', 'auditor_lider', 'anio', 'auditores'];
        campos.forEach(id => {
            const valor = $('#'+id).val()?.trim();
            if (!valor) {
                $(`#err-${id}`).show();
                $('#'+id).addClass('campo-invalido');
                valido = false;
            } else {
                $(`#err-${id}`).hide();
                $('#'+id).removeClass('campo-invalido');
            }
        });

        if (!$('#fecha_inicio').val() || !$('#fecha_fin').val()) {
            $('#err-rango_fechas').show();
            $('#rango_fechas').addClass('campo-invalido');
            valido = false;
        } else {
            $('#err-rango_fechas').hide();
            $('#rango_fechas').removeClass('campo-invalido');
        }

        const esEdicion = !!$('#auditoria_id').val();
        const archivo = $('#archivo_plan')[0].files[0];
        if (!esEdicion && !archivo) {
            $('#err-archivo_plan').show();
            valido = false;
        } else {
            $('#err-archivo_plan').hide();
        }

        return valido;
    }

    function limpiarErrores() {
        $('.msg-error').hide();
        $('.campo-invalido').removeClass('campo-invalido');
    }

    function resetForm() {
        $('#formAuditoria')[0].reset();
        $('#auditoria_id').val('');
        $('#rango_fechas').val('');
        $('#fecha_inicio').val('');
        $('#fecha_fin').val('');
        $('#nombreArchivoActual').hide();
        limpiarErrores();
    }

    function guardarAuditoria() {
        if (!validarFormulario()) return;

        const id = $('#auditoria_id').val();
        const url = id ? `{{ url('auditoria/plan') }}/${id}` : '{{ route('auditoria.plan.store') }}';
        const formData = new FormData($('#formAuditoria')[0]);
        if (id) formData.append('_method', 'PUT');

        $('#btnGuardarAuditoria').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Guardando...');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        })
        .then(data => {
            if (data.success) {
                $('#modalNuevaAuditoria').modal('hide');
                cargarAuditorias();
                resetForm();
                mostrarMensajeExito(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error(error);
            let mensaje = 'Error al guardar.';
            if (error.errors) {
                mensaje = Object.values(error.errors).flat().join('\n');
            } else if (error.message) {
                mensaje = error.message;
            }
            alert(mensaje);
        })
        .finally(() => {
            $('#btnGuardarAuditoria').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Guardar Auditoría');
        });
    }

    function editarAuditoria(id) {
        const auditoria = auditoriasData.find(a => a.id === id);
        if (!auditoria) return;

        $('#auditoria_id').val(auditoria.id);
        $('#nombre_auditoria').val(auditoria.nombre_auditoria);
        $('#tipo_auditoria').val(auditoria.tipo_auditoria);
        $('#auditor_lider').val(auditoria.auditor_lider);
        $('#anio').val(auditoria.anio);
        $('#auditores').val(auditoria.auditores || '');

        if (auditoria.fecha_inicio && auditoria.fecha_fin) {
            const inicio = moment(auditoria.fecha_inicio);
            const fin = moment(auditoria.fecha_fin);
            $('#rango_fechas').val(inicio.format('DD/MM/YYYY') + ' - ' + fin.format('DD/MM/YYYY'));
            $('#fecha_inicio').val(auditoria.fecha_inicio);
            $('#fecha_fin').val(auditoria.fecha_fin);
        } else {
            $('#rango_fechas').val('');
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
        }

        if (auditoria.archivo_nombre) {
            $('#nombreArchivo').text(auditoria.archivo_nombre);
            $('#nombreArchivoActual').show();
        } else {
            $('#nombreArchivoActual').hide();
        }

        $('#modalNuevaAuditoria .modal-title').text('Editar Auditoría');
        $('#modalNuevaAuditoria').modal('show');
    }

    function eliminarAuditoria(id, nombre) {
        Swal.fire({
            title: '¿Eliminar?',
            text: `¿Estás seguro de eliminar "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('auditoria/plan') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }

    function mostrarMensajeExito(mensaje) {
        const alerta = `
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle me-2"></i> ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.container-fluid .row:first .col-12').prepend(alerta);
        setTimeout(() => $('.alert-success').alert('close'), 5000);
    }

    // Funciones de filtros
    function seleccionarOrden(criterio, texto) {
        ordenSeleccionado = criterio;
        $('#ordenarTexto').text(texto);
        $('#btnOrdenar').addClass('seleccionado');
        filtrarYRenderizar();
    }

    function seleccionarTipo(tipo, texto) {
        tipoSeleccionado = tipo;
        $('#tipoTexto').text(texto);
        if (tipo) $('#btnTipo').addClass('seleccionado'); else $('#btnTipo').removeClass('seleccionado');
        filtrarYRenderizar();
    }

    function seleccionarAnio(anio, texto) {
        anioSeleccionado = anio;
        $('#anioTexto').text(texto);
        if (anio) $('#btnAnio').addClass('seleccionado'); else $('#btnAnio').removeClass('seleccionado');
        filtrarYRenderizar();
    }

    function filtrarYRenderizar() {
        let datos = auditoriasData.filter(a => {
            if (tipoSeleccionado && a.tipo_auditoria !== tipoSeleccionado) return false;
            if (anioSeleccionado && String(a.anio) !== String(anioSeleccionado)) return false;
            return true;
        });

        const texto = $('#buscadorArchivos').val().toLowerCase().trim();
        if (texto) {
            datos = datos.filter(a => a.nombre_auditoria.toLowerCase().includes(texto) || (a.auditor_lider && a.auditor_lider.toLowerCase().includes(texto)));
        }

        if (ordenSeleccionado) {
            switch(ordenSeleccionado) {
                case 'nombre-asc':
                    datos.sort((a,b) => a.nombre_auditoria.localeCompare(b.nombre_auditoria));
                    break;
                case 'nombre-desc':
                    datos.sort((a,b) => b.nombre_auditoria.localeCompare(a.nombre_auditoria));
                    break;
                case 'fecha-asc':
                    datos.sort((a,b) => new Date(a.fecha_inicio) - new Date(b.fecha_inicio));
                    break;
                case 'fecha-desc':
                    datos.sort((a,b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio));
                    break;
            }
        }

        renderizarTabla(datos);
    }

    function filtrarPorBusqueda() {
        filtrarYRenderizar();
    }
</script>
@endpush