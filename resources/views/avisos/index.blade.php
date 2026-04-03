{{-- resources/views/avisos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión de Avisos')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                    <h1 class="h3 mb-0" style="color: #4f46e5;">
                        <i class="bi bi-megaphone me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                        Avisos
                    </h1>
                </a>
                @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
                    <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevoAviso" style="background-color: #737373; color: white; border: none;">
                        <i class="bi bi-plus-circle"></i> Nuevo Aviso
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Buscar -->
                <div class="d-flex align-items-center position-relative" style="width: 700px;">
                    <div class="position-relative flex-grow-1">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                        <input type="text" class="form-control ps-5" style="width: 100%; height: 42px; border-radius: 4px 0 0 4px; border-right: none;" placeholder="Buscar avisos" id="buscadorAvisos">
                    </div>
                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center btn-clear-search" 
                            style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border: 1px solid #ced4da; border-left: none; transition: all 0.2s;"
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
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('titulo-asc', 'Título (A-Z)')">Título (A-Z)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('titulo-desc', 'Título (Z-A)')">Título (Z-A)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-asc', 'Fecha (más antiguo)')">Fecha (más antiguo)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-desc', 'Fecha (más reciente)')">Fecha (más reciente)</a></li>
                    </ul>
                </div>

                <!-- Filtrar por Estado -->
                <div class="dropdown">
                    <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnEstado" style="height: 42px; background-color: white;">
                        <i class="bi bi-funnel"></i> <span id="estadoTexto">Filtrar por Estado</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="seleccionarEstado('', 'Todos los estados')">Todos los estados</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarEstado('activo', 'Activos')">Activos</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarEstado('programado', 'Programados')">Programados</a></li>
                        <li><a class="dropdown-item" href="#" onclick="seleccionarEstado('expirado', 'Expirados')">Expirados</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Avisos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Archivo</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                            <th>Visitas</th>
                            <th>Creado por</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody">
                        <tr>
                            <td colspan="8" class="text-center">Cargando avisos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA REGISTRAR/EDITAR AVISO -->
@if(in_array(Auth::user()->role, ['admin', 'superadmin']))
<div class="modal fade" id="modalNuevoAviso" tabindex="-1" aria-labelledby="modalNuevoAvisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoAvisoLabel">
                    <i class="bi bi-plus-circle me-2" style="color: #000000;"></i>
                    Nuevo Aviso
                </h5>
            </div>
            <form id="formAviso" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="aviso_id" name="aviso_id">

                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #000000;">DATOS DEL AVISO</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Título del Aviso *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: Nuevo proceso de inscripción 2026">
                            <div class="msg-error" id="err-titulo">El título es requerido</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Inicio *</label>
                            <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio">
                            <div class="msg-error" id="err-fecha_inicio">La fecha de inicio es requerida</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Fin *</label>
                            <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin">
                            <div class="msg-error" id="err-fecha_fin">La fecha de fin es requerida</div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #000000;">ARCHIVO ADJUNTO</h6>
                            <div class="border rounded p-4 bg-light">
                                <div class="text-center mb-3">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #000000;"></i>
                                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.zip,.rar">
                                </div>
                                <div class="msg-error mt-2" id="err-archivo">El archivo es requerido</div>
                                <div id="nombreArchivoActual" class="text-center mt-2 text-muted" style="display: none;">
                                    Archivo actual: <span id="nombreArchivo"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white" style="background-color: #800000;" id="btnGuardarAviso">
                        <i class="bi bi-check-circle me-1"></i> Guardar Aviso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- CONTENEDOR PARA MODALES DE VISUALIZACIÓN -->
<div id="modalesContainer"></div>

<!-- MODAL ÚNICO PARA VISUALIZAR ARCHIVOS (IGUAL QUE PLAN DE AUDITORÍAS) -->
<div class="modal fade" id="modalVerArchivo" tabindex="-1" aria-labelledby="modalVerArchivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerArchivoLabel">Visualizador de Archivo</h5>
            </div>
            <div class="modal-body p-0">
                <iframe id="visorArchivo" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    .badge-activo {
        background-color: #28a745;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
    }
    .badge-programado {
        background-color: #ffc107;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
    }
    .badge-expirado {
        background-color: #dc3545;
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 5px;
        font-size: 0.8rem;
    }
    .border.rounded.p-4.bg-light {
        border: 2px dashed #000000 !important;
        transition: all 0.3s ease;
    }
    .border.rounded.p-4.bg-light:hover {
        background-color: #fff !important;
        border-color: #000000 !important;
    }
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
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }
    .nombre-archivo {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        vertical-align: middle;
    }
    .btn-clear-search:hover {
        background-color: #737373 !important;
        border-color: #737373 !important;
    }
    .btn-clear-search:hover i {
        color: white !important;
    }
    
    /* ===== ESTILOS DE SWEETALERT (IGUAL QUE PLAN DE AUDITORÍAS) ===== */
    .swal2-popup {
        font-size: 1.2rem !important;
    }
    .swal2-title {
        color: #000000 !important;
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let avisosData = [];
    let estadoSeleccionado = '';
    let ordenSeleccionado = '';
    const userRole = '{{ Auth::user()->role }}';
    
    // Lista de extensiones que NO se pueden visualizar
    const extensionesSinVista = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'zip', 'rar', '7z'];

    $(document).ready(function() {
        cargarAvisos();
        configurarEventos();

        $('#modalNuevoAviso').on('hidden.bs.modal', function () {
            resetForm();
            limpiarErrores();
        });
        
        $('#modalNuevoAviso').on('show.bs.modal', function () {
            if ($('#aviso_id').val()) {
                $('#modalNuevoAvisoLabel').html('<i class="bi bi-pencil-square me-2" style="color: #000000;"></i> Editar Aviso');
            } else {
                $('#modalNuevoAvisoLabel').html('<i class="bi bi-plus-circle me-2" style="color: #000000;"></i> Nuevo Aviso');
            }
        });
    });

    function configurarEventos() {
        $('#btnGuardarAviso').on('click', guardarAviso);

        $('#buscadorAvisos').on('keyup', function() {
            filtrarPorBusqueda($(this).val());
        });

        $('#limpiarBusqueda').on('click', function() {
            $('#buscadorAvisos').val('');
            filtrarPorBusqueda('');
        });

        $('#titulo, #fecha_inicio, #fecha_fin').on('input change', function() {
            const id = $(this).attr('id');
            if ($(this).val().trim()) {
                $(`#err-${id}`).hide();
                $(this).removeClass('campo-invalido');
            }
        });

        $('#archivo').on('change', function() {
            if (this.files.length) $('#err-archivo').hide();
        });
    }

    function cargarAvisos() {
        let url = '/api/avisos/activos';

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            avisosData = data;
            renderizarTabla(data);
        })
        .catch(error => {
            console.error('Error al cargar avisos:', error);
            $('#tablaBody').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos</td></tr>');
        });
    }

    function getEstadoAviso(aviso) {
        const now = moment();
        const fechaInicio = moment(aviso.fecha_inicio);
        const fechaFin = moment(aviso.fecha_fin);
        
        if (!aviso.activo) return 'inactivo';
        if (now.isBefore(fechaInicio)) return 'programado';
        if (now.isAfter(fechaFin)) return 'expirado';
        return 'activo';
    }

    function getEstadoBadge(estado) {
        switch(estado) {
            case 'activo':
                return '<span class="badge-activo">Activo</span>';
            case 'programado':
                return '<span class="badge-programado">Programado</span>';
            case 'expirado':
                return '<span class="badge-expirado">Expirado</span>';
            default:
                return '<span class="badge-expirado">Inactivo</span>';
        }
    }

    function renderizarTabla(data) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (data.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center py-4">No hay avisos registrados</td></tr>');
            return;
        }

        data.forEach(aviso => {
            const estado = getEstadoAviso(aviso);
            const badgeEstado = getEstadoBadge(estado);

            let iconoArchivo = 'bi-file-earmark';
            let puedeVisualizar = false;
            
            if (aviso.archivo_nombre) {
                const ext = aviso.archivo_nombre.split('.').pop().toLowerCase();
                if (ext === 'pdf') iconoArchivo = 'bi-file-pdf';
                else if (['doc', 'docx'].includes(ext)) iconoArchivo = 'bi-file-word';
                else if (['xls', 'xlsx'].includes(ext)) iconoArchivo = 'bi-file-excel';
                else if (ext === 'csv') iconoArchivo = 'bi-file-spreadsheet';
                else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'].includes(ext)) iconoArchivo = 'bi-file-image';
                else if (ext === 'txt') iconoArchivo = 'bi-file-text';
                
                // Verificar si se puede visualizar (NO está en la lista de extensiones sin vista)
                puedeVisualizar = !extensionesSinVista.includes(ext);
            }

            // ACCIONES: Solo botón Ver (ojo) si se puede visualizar, Editar y Eliminar
            let acciones = '';
            @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${aviso.archivo_nombre && puedeVisualizar ? '<button class="btn btn-sm btn-outline-info" onclick="verArchivo('+aviso.id+')" title="Ver Archivo"><i class="bi bi-eye"></i></button>' : ''}
                        <button class="btn btn-sm btn-outline-secondary" onclick="editarAviso(${aviso.id})" title="Editar"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarAviso(${aviso.id}, '${aviso.titulo.replace(/'/g, "\\'")}')" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </div>
                `;
            @else
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${aviso.archivo_nombre && puedeVisualizar ? '<button class="btn btn-sm btn-outline-info" onclick="verArchivo('+aviso.id+')" title="Ver Archivo"><i class="bi bi-eye"></i></button>' : ''}
                    </div>
                `;
            @endif

            let archivoMostrar = '-';
            if (aviso.archivo_nombre) {
                archivoMostrar = `
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <i class="bi ${iconoArchivo}" style="color: #000000; font-size: 1.2rem;"></i>     
                        <span class="nombre-archivo" title="${aviso.archivo_nombre}">${aviso.archivo_nombre}</span>
                    </div>
                `;
            }

            const fechaInicio = moment(aviso.fecha_inicio).format('DD/MM/YYYY HH:mm');
            const fechaFin = moment(aviso.fecha_fin).format('DD/MM/YYYY HH:mm');

            const row = `
                <tr>
                    <td class="fw-bold">${aviso.titulo || ''}</td>
                    <td>${archivoMostrar}</td>
                    <td>${fechaInicio}</td>
                    <td>${fechaFin}</td>
                    <td>${badgeEstado}</td>
                    <td><i class="bi bi-eye me-1"></i>${aviso.visitas || 0}</td>
                    <td><small>${aviso.creador ? aviso.creador.name : '-'}</small><br><small class="text-muted">${moment(aviso.created_at).format('DD/MM/YYYY')}</small></td>
                    <td class="text-end">${acciones}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Función para ver archivo - IGUAL QUE PLAN DE AUDITORÍAS
    function verArchivo(id) {
        const aviso = avisosData.find(a => a.id === id);
        if (!aviso || !aviso.archivo_nombre) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se encontró el archivo',
                confirmButtonColor: '#800000'
            });
            return;
        }

        const url = `{{ url('avisos') }}/${id}/ver`;
        const extension = aviso.archivo_nombre.split('.').pop().toLowerCase();
        
        // Para imágenes, mostrar directamente en iframe
        if (['jpg','jpeg','png','gif','bmp','webp'].includes(extension)) {
            $('#modalVerArchivoLabel').text(aviso.archivo_nombre);
            $('#visorArchivo').attr('src', url);
            $('#modalVerArchivo').modal('show');
        } 
        // Para PDF y TXT
        else if (extension === 'pdf' || extension === 'txt') {
            $('#modalVerArchivoLabel').text(aviso.archivo_nombre);
            $('#visorArchivo').attr('src', url);
            $('#modalVerArchivo').modal('show');
        }
        // Para otros tipos, mostrar mensaje
        else {
            Swal.fire({
                icon: 'info',
                title: 'Vista previa no disponible',
                text: 'Este tipo de archivo no se puede visualizar directamente en el navegador.',
                confirmButtonColor: '#800000'
            });
        }
    }

    function validarFormulario() {
        let valido = true;
        const campos = ['titulo', 'fecha_inicio', 'fecha_fin'];
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

        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        if (fechaInicio && fechaFin && fechaFin <= fechaInicio) {
            $('#err-fecha_fin').text('La fecha de fin debe ser posterior a la fecha de inicio').show();
            $('#fecha_fin').addClass('campo-invalido');
            valido = false;
        }

        const esEdicion = !!$('#aviso_id').val();
        const archivo = $('#archivo')[0].files[0];
        if (!esEdicion && !archivo) {
            $('#err-archivo').show();
            valido = false;
        } else {
            $('#err-archivo').hide();
        }

        return valido;
    }

    function limpiarErrores() {
        $('.msg-error').hide();
        $('.campo-invalido').removeClass('campo-invalido');
        $('#err-fecha_fin').text('La fecha de fin es requerida');
    }

    function resetForm() {
        $('#formAviso')[0].reset();
        $('#aviso_id').val('');
        $('#nombreArchivoActual').hide();
        limpiarErrores();
    }

    function guardarAviso() {
        if (!validarFormulario()) return;

        const id = $('#aviso_id').val();
        const url = id ? `{{ url('avisos') }}/${id}` : '{{ route("avisos.store") }}';
        const formData = new FormData($('#formAviso')[0]);
        if (id) formData.append('_method', 'PUT');

        $('#btnGuardarAviso').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Guardando...');

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
                $('#modalNuevoAviso').modal('hide');
                cargarAvisos();
                resetForm();
                mostrarMensajeExito(data.message);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al guardar',
                    confirmButtonColor: '#800000'
                });
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
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#800000'
            });
        })
        .finally(() => {
            $('#btnGuardarAviso').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Guardar Aviso');
        });
    }

    function editarAviso(id) {
        const aviso = avisosData.find(a => a.id === id);
        if (!aviso) return;

        $('#aviso_id').val(aviso.id);
        $('#titulo').val(aviso.titulo);
        $('#fecha_inicio').val(moment(aviso.fecha_inicio).format('YYYY-MM-DDTHH:mm'));
        $('#fecha_fin').val(moment(aviso.fecha_fin).format('YYYY-MM-DDTHH:mm'));

        if (aviso.archivo_nombre) {
            $('#nombreArchivo').text(aviso.archivo_nombre);
            $('#nombreArchivoActual').show();
        } else {
            $('#nombreArchivoActual').hide();
        }

        $('#modalNuevoAviso').modal('show');
    }

    function eliminarAviso(id, titulo) {
        Swal.fire({
            title: '¿Eliminar aviso?',
            text: `¿Estás seguro de eliminar "${titulo}"?`,
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
                    },
                    showConfirmButton: false
                });

                fetch(`{{ url('avisos') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            cargarAvisos();
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
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión',
                        confirmButtonColor: '#800000'
                    });
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

    function seleccionarOrden(criterio, texto) {
        ordenSeleccionado = criterio;
        $('#ordenarTexto').text(texto);
        filtrarYRenderizar();
    }

    function seleccionarEstado(estado, texto) {
        estadoSeleccionado = estado;
        $('#estadoTexto').text(texto);
        filtrarYRenderizar();
    }

    function filtrarYRenderizar() {
        let datos = [...avisosData];

        if (estadoSeleccionado) {
            datos = datos.filter(a => getEstadoAviso(a) === estadoSeleccionado);
        }

        const texto = $('#buscadorAvisos').val().toLowerCase().trim();
        if (texto) {
            datos = datos.filter(a => a.titulo.toLowerCase().includes(texto));
        }

        if (ordenSeleccionado) {
            switch(ordenSeleccionado) {
                case 'titulo-asc':
                    datos.sort((a,b) => a.titulo.localeCompare(b.titulo));
                    break;
                case 'titulo-desc':
                    datos.sort((a,b) => b.titulo.localeCompare(a.titulo));
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
    
    function limpiarBuscador() {
        $('#buscadorAvisos').val('');
        filtrarPorBusqueda('');
    }
</script>
@endpush