@extends('layouts.app')

@section('title', 'Solicitudes de Mejora - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <!-- Header con ícono de carpeta -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- MENSAJE DE ÉXITO -->
            <div id="mensajeExitoContainer"></div>
            
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                    <h1 class="h3 mb-0" style="color: #800000; cursor: pointer;">
                        <i class="bi bi-folder me-2" style="font-size: 2.5rem; vertical-align: middle;"></i>
                        Solicitud de Mejora
                    </h1>
                </a>
                
                {{-- Solo admin y superadmin pueden registrar solicitudes --}}
                @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
                <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud" style="background-color: #737373; color: white; border: none;">
                    <i class="bi bi-plus-circle"></i> Registrar Solicitud
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    @include('auditoria.solicitudes.partials.filtros')

    <!-- TABLA DE SOLICITUDES -->
    @include('auditoria.solicitudes.partials.tabla')
</div>

<!-- MODAL PARA REGISTRAR/EDITAR SOLICITUD -->
@if(in_array(Auth::user()->role, ['admin', 'superadmin']))
@include('auditoria.solicitudes.modal.modal_solicitud')
@endif

<!-- MODAL PARA VER ARCHIVOS -->
@include('auditoria.solicitudes.modal.modal_ver_archivo')

<!-- MODAL PARA VER CALENDARIO -->
@include('auditoria.solicitudes.modal.modal_calendario')

<!-- CONTENEDOR PARA MODALES DINÁMICOS -->
<div id="modalesContainer"></div>
@endsection

@push('styles')
<style>
    /* ===== ESTILOS DE LA CARD (TABLA) ===== */
    .table {
        font-size: 0.9rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        color: #212529;
        text-align: center;
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
        border-top: 2px solid #dee2e6 !important;
        font-weight: 600;
        padding: 12px 8px;
    }

    .table td {
        vertical-align: middle;
        border-left: none !important;
        border-right: none !important;
        padding: 12px 8px;
    }
    
    /* Badges */
    .badge-no-atendida {
        background-color: #fd7e14;
        color: white;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .badge-proceso {
        background-color: #ffc107;
        color: white;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .badge-cerrado {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .btn-light {
        background-color: white !important;
        color: #6c757d;
        border: 1px solid #ced4da;
        height: 42px;
        padding: 0 15px;
    }
    
    .btn-light:hover {
        background-color: #f8f9fa !important;
        border-color: #800000;
    }
    
    .btn-light.seleccionado {
        background-color: #e9ecef !important;
        border-color: #737373;
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
        width: 42px;
        height: 42px;
    }
    
    #limpiarBusqueda:hover {
        background-color: #f8f9fa;
        border-color: #800000;
    }
    
    #limpiarBusqueda:hover i {
        color: #800000;
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
    }
    
    .border.rounded.p-4.bg-light {
        border: 2px dashed #800000 !important;
        transition: all 0.3s ease;
    }
    
    .border.rounded.p-4.bg-light:hover {
        background-color: #fff0f0 !important;
        border-color: #600000 !important;
    }

    .modal-xl {
        max-width: 90%;
    }
    
    .modal-body {
        background-color: #ffffff;
        height: 80vh;
        overflow: auto;
    }
    
    .modal-body iframe,
    .modal-body embed {
        width: 100%;
        height: 100%;
        border: none;
    }

    .documento-nombre {
        color: #495057;
        font-size: 0.9rem;
    }
    
    /* ===== ESTILOS DE BOTONES ===== */
    .btn-outline-info {
        color: #0dcaf0;
        border-color: #0dcaf0;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-info:hover {
        color: #fff;
        background-color: #0dcaf0;
        border-color: #0dcaf0;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-secondary:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-warning {
        color: #6f42c1;
        border-color: #6f42c1;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-warning:hover {
        color: #fff;
        background-color: #6f42c1;
        border-color: #6f42c1;
    }
    .btn-outline-warning i {
        color: #6f42c1;
    }
    .btn-outline-warning:hover i {
        color: #fff;
    }

    .btn-outline-primary {
        color: #0d6efd;
        border-color: #0d6efd;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-primary:hover {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    .btn-outline-warning {
        --bs-btn-color: #6f42c1;
        --bs-btn-border-color: #6f42c1;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #6f42c1;
        --bs-btn-hover-border-color: #6f42c1;
        --bs-btn-focus-shadow-rgb: 111, 66, 193;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #6f42c1;
        --bs-btn-active-border-color: #6f42c1;
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

    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* ===== ESTILOS MEJORADOS PARA EL CRONÓMETRO ===== */
    .cronometro-info {
        background-color: #e9ecef;
        border-left: 4px solid #6c757d;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .cronometro-activo {
        background-color: #f8f9fa;
        border-left: 4px solid #6c757d;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .cronometro-completado {
        background-color: #f1f3f5;
        border-left: 4px solid #495057;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .cronometro-completado strong {
        color: #495057;
        font-size: 1.2rem;
        display: block;
        margin-bottom: 8px;
    }
    
    .cronometro-completado i {
        color: #6c757d;
        font-size: 2rem;
        margin-bottom: 10px;
    }
    
    /* Estilo para alertas en gris */
    .alert-info-custom {
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        color: #495057;
        border-radius: 6px;
        padding: 12px 15px;
    }
    
    .alert-warning-custom {
        background-color: #f8f9fa;
        border: 1px solid #adb5bd;
        color: #495057;
        border-radius: 6px;
        padding: 12px 15px;
    }
    
    .alert-secondary-custom {
        background-color: #f1f3f5;
        border: 1px solid #6c757d;
        color: #495057;
        border-radius: 6px;
        padding: 12px 15px;
    }

    /* Estilo para solicitud cerrada */
    .solicitud-cerrada {
        background-color: #f8f9fa;
        border: 2px solid #6c757d;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        margin: 20px 0;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    
    .solicitud-cerrada i {
        font-size: 4rem;
        color: #6c757d;
        margin-bottom: 15px;
    }
    
    .solicitud-cerrada h4 {
        color: #495057;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .solicitud-cerrada p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let solicitudesData = [];
    let estatusSeleccionado = '';
    let anioSeleccionado = '';
    let ordenSeleccionado = '';
    const userRole = '{{ Auth::user()->role }}';

    document.addEventListener('DOMContentLoaded', function() {
        cargarSolicitudes();
        configurarEventos();
        
        @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
        const modal = document.getElementById('modalNuevaSolicitud');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                resetForm();
            });
        }
        @endif
    });

    function configurarEventos() {
        @if(in_array(Auth::user()->role, ['admin', 'superadmin']))
        const form = document.getElementById('formSolicitud');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                guardarSolicitud();
            });
        }
        @endif
        
        const buscador = document.getElementById('buscadorArchivos');
        if (buscador) {
            buscador.addEventListener('keyup', function() {
                filtrarPorBusqueda(this.value);
            });
        }
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
        ordenarPor(criterio);
    }

    function seleccionarEstatus(estatus, texto) {
        estatusSeleccionado = estatus;
        document.getElementById('estatusTexto').innerText = texto;
        document.getElementById('btnEstatus').classList.add('seleccionado');
        cargarSolicitudes();
    }

    function seleccionarAnio(anio, texto) {
        anioSeleccionado = anio;
        document.getElementById('anioTexto').innerText = texto;
        
        if (anio !== '') {
            document.getElementById('btnAnio').classList.add('seleccionado');
        } else {
            document.getElementById('btnAnio').classList.remove('seleccionado');
        }
        
        cargarSolicitudes();
    }

    function ordenarPor(criterio) {
        if (!solicitudesData || solicitudesData.length === 0) return;
        
        let datosOrdenados = [...solicitudesData];
        
        switch(criterio) {
            case 'nombre-asc':
                datosOrdenados.sort((a, b) => (a.folio_solicitud || '').localeCompare(b.folio_solicitud || ''));
                break;
            case 'nombre-desc':
                datosOrdenados.sort((a, b) => (b.folio_solicitud || '').localeCompare(a.folio_solicitud || ''));
                break;
            case 'fecha-asc':
                datosOrdenados.sort((a, b) => new Date(a.fecha_solicitud) - new Date(b.fecha_solicitud));
                break;
            case 'fecha-desc':
                datosOrdenados.sort((a, b) => new Date(b.fecha_solicitud) - new Date(a.fecha_solicitud));
                break;
        }
        
        renderizarTabla(datosOrdenados);
    }

    function filtrarPorBusqueda(texto) {
        if (!solicitudesData || solicitudesData.length === 0) return;
        
        texto = texto.toLowerCase().trim();
        
        if (texto === '') {
            renderizarTabla(solicitudesData);
            return;
        }
        
        const datosFiltrados = solicitudesData.filter(solicitud => 
            (solicitud.responsable_accion && solicitud.responsable_accion.toLowerCase().includes(texto)) ||
            (solicitud.folio_solicitud && solicitud.folio_solicitud.toLowerCase().includes(texto))
        );
        
        renderizarTabla(datosFiltrados);
    }

    function cargarSolicitudes() {
        let url = '{{ route("auditoria.solicitudes.data") }}';
        let params = new URLSearchParams();
        
        if (estatusSeleccionado) params.append('estatus', estatusSeleccionado);
        if (anioSeleccionado) params.append('anio', anioSeleccionado);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            solicitudesData = data;
            
            if (ordenSeleccionado) {
                ordenarPor(ordenSeleccionado);
            } else {
                renderizarTabla(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tablaBody').innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error al cargar las solicitudes</td></tr>';
        });
    }

    /**
     * Determina si un archivo se puede visualizar en el navegador.
     * @param {string} filename Nombre del archivo con extensión.
     * @returns {boolean}
     */
    function esArchivoVisualizable(filename) {
        if (!filename) return false;
        const ext = filename.split('.').pop().toLowerCase();
        const visualizables = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'txt'];
        return visualizables.includes(ext);
    }

    function renderizarTabla(data) {
        const tbody = document.getElementById('tablaBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4">No hay solicitudes de mejora registradas</td></tr>';
            return;
        }

        data.forEach(solicitud => {
            const tr = document.createElement('tr');
            
            const fechaSolicitud = solicitud.fecha_solicitud ? new Date(solicitud.fecha_solicitud).toLocaleDateString('es-ES') : '';
            
            // Periodos: extraer mes y año (MM/YYYY)
            const periodoAplicacion = solicitud.fecha_aplicacion 
                ? new Date(solicitud.fecha_aplicacion).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit' }).replace(/\//g, '/')
                : '';
            const periodoVerificacion = solicitud.fecha_verificacion 
                ? new Date(solicitud.fecha_verificacion).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit' }).replace(/\//g, '/')
                : '-';
            
            // Badge según estatus
            let badgeClass = '';
            if (solicitud.estatus === 'No Atendida') badgeClass = 'badge-no-atendida';
            else if (solicitud.estatus === 'En Proceso') badgeClass = 'badge-proceso';
            else if (solicitud.estatus === 'Cerrado') badgeClass = 'badge-cerrado';
            
            // Construir celda de documento con ícono y nombre
            let documentoHtml = '';
            if (solicitud.archivo_nombre) {
                const ext = solicitud.archivo_nombre.split('.').pop().toLowerCase();
                let icono = 'bi-file-earmark';
                let color = '#800000';
                
                if (['pdf'].includes(ext)) {
                    icono = 'bi-file-pdf';
                    color = '#dc3545';
                } else if (['doc', 'docx'].includes(ext)) {
                    icono = 'bi-file-word';
                    color = '#2b5797';
                } else if (['xls', 'xlsx'].includes(ext)) {
                    icono = 'bi-file-excel';
                    color = '#1e7145';
                } else if (['ppt', 'pptx'].includes(ext)) {
                    icono = 'bi-file-ppt';
                    color = '#d14430';
                } else if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) {
                    icono = 'bi-file-image';
                    color = '#28a745';
                } else if (ext === 'csv') {
                    icono = 'bi-file-spreadsheet';
                    color = '#217346';
                } else if (['txt'].includes(ext)) {
                    icono = 'bi-file-text';
                    color = '#6c757d';
                } else {
                    icono = 'bi-file-earmark';
                    color = '#800000';
                }
                
                documentoHtml = `
                    <i class="bi ${icono}" style="color: ${color}; margin-right: 4px;"></i>
                    <span class="documento-nombre" title="${solicitud.archivo_nombre}">${solicitud.archivo_nombre}</span>
                `;
            } else {
                documentoHtml = '<span class="text-muted">—</span>';
            }
            
            // Determinar si se muestra el botón "Ver"
            const visualizable = esArchivoVisualizable(solicitud.archivo_nombre);
            
            // Acciones según el rol del usuario
            let acciones = '';
            
            if (userRole === 'admin' || userRole === 'superadmin') {
                // Admin y superadmin tienen todas las acciones
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${solicitud.archivo_nombre && visualizable ? 
                            `<button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="verArchivo(${solicitud.id})" 
                                    title="Ver archivo">
                                <i class="bi bi-eye"></i>
                            </button>` : ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="editarSolicitud(${solicitud.id})"
                                title="Editar solicitud">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="verCalendario(${solicitud.id})"
                                title="Ver fechas">
                            <i class="bi bi-calendar"></i>
                        </button>
                        
                        ${solicitud.archivo_nombre ? 
                            `<a href="{{ url('auditoria/solicitudes/download') }}/${solicitud.id}" 
                               class="btn btn-sm btn-outline-primary"
                               title="Descargar archivo">
                                <i class="bi bi-download"></i>
                            </a>` : ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="eliminarSolicitud(${solicitud.id}, '${(solicitud.folio_solicitud || '').replace(/'/g, "\\'")}')"
                                title="Eliminar solicitud">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
            } else {
                // Usuario normal solo puede ver calendario, ver archivo y descargar
                acciones = `
                    <div class="d-flex justify-content-end gap-1">
                        ${solicitud.archivo_nombre && visualizable ? 
                            `<button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="verArchivo(${solicitud.id})" 
                                    title="Ver archivo">
                                <i class="bi bi-eye"></i>
                            </button>` : ''}
                        
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="verCalendario(${solicitud.id})"
                                title="Ver fechas">
                            <i class="bi bi-calendar"></i>
                        </button>
                        
                        ${solicitud.archivo_nombre ? 
                            `<a href="{{ url('auditoria/solicitudes/download') }}/${solicitud.id}" 
                               class="btn btn-sm btn-outline-primary"
                               title="Descargar archivo">
                                <i class="bi bi-download"></i>
                            </a>` : ''}
                    </div>
                `;
            }
            
            tr.innerHTML = `
                <td class="text-center">${fechaSolicitud}</td>
                <td class="text-center">${solicitud.folio_solicitud || '-'}</td>
                <td>${solicitud.responsable_accion || ''}</td>
                <td class="text-center">${periodoAplicacion}</td>
                <td>${solicitud.actividades_verificacion ? 
                    (solicitud.actividades_verificacion.length > 30 ? 
                        solicitud.actividades_verificacion.substring(0, 30) + '...' : 
                        solicitud.actividades_verificacion) : 
                    '-'}</td>
                <td>${documentoHtml}</td>
                <td class="text-center">${periodoVerificacion}</td>
                <td class="text-center"><span class="${badgeClass}">${solicitud.estatus || ''}</span></td>
                <td class="text-end">${acciones}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function verArchivo(id) {
        const solicitud = solicitudesData.find(s => s.id === id);
        if (!solicitud) return;

        const url = `{{ url('auditoria/solicitudes/ver') }}/${id}`;
        const downloadUrl = `{{ url('auditoria/solicitudes/download') }}/${id}`;
        const ext = solicitud.archivo_nombre.split('.').pop().toLowerCase();
        
        // Verificar si es visualizable (aunque ya pasamos el filtro, por si acaso)
        const visualizable = esArchivoVisualizable(solicitud.archivo_nombre);
        
        let contenidoVisor = '';
        if (visualizable) {
            // Para PDF y txt se puede usar embed; para imágenes también.
            if (ext === 'pdf') {
                contenidoVisor = `<embed src="${url}" type="application/pdf" width="100%" height="80vh" />`;
            } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext)) {
                contenidoVisor = `<img src="${url}" style="max-width:100%; max-height:80vh; display:block; margin:auto;" />`;
            } else if (ext === 'txt') {
                contenidoVisor = `<iframe src="${url}" style="width:100%; height:80vh;"></iframe>`;
            } else {
                contenidoVisor = `<iframe src="${url}" style="width:100%; height:80vh;"></iframe>`;
            }
        } else {
            contenidoVisor = `
                <div class="text-center p-5">
                    <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                    <p class="mt-3">Este tipo de archivo no se puede visualizar en el navegador.</p>
                    <a href="${downloadUrl}" class="btn text-white" style="background-color:#800000;">Descargar archivo</a>
                </div>
            `;
        }

        const modalHtml = `
            <div class="modal fade" id="viewDocumentModal${id}" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Visualizar Archivo: ${solicitud.archivo_nombre}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            ${contenidoVisor}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="${downloadUrl}" class="btn text-white" style="background-color:#800000;">Descargar</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const container = document.getElementById('modalesContainer');
        container.innerHTML = modalHtml;
        
        const modal = new bootstrap.Modal(document.getElementById(`viewDocumentModal${id}`));
        modal.show();
    }

    /**
     * Calcula los días hábiles entre dos fechas (excluye sábados y domingos)
     */
    function businessDaysBetween(start, end) {
        let count = 0;
        const cur = new Date(start);
        cur.setHours(0, 0, 0, 0);
        const endDate = new Date(end);
        endDate.setHours(0, 0, 0, 0);
        
        while (cur <= endDate) {
            const day = cur.getDay();
            // 0 = domingo, 6 = sábado
            if (day !== 0 && day !== 6) {
                count++;
            }
            cur.setDate(cur.getDate() + 1);
        }
        return count;
    }

    /**
     * Calcula la fecha después de sumar días hábiles
     */
    function addBusinessDays(startDate, days) {
        let result = new Date(startDate);
        result.setHours(0, 0, 0, 0);
        let addedDays = 0;
        
        while (addedDays < days) {
            result.setDate(result.getDate() + 1);
            const day = result.getDay();
            if (day !== 0 && day !== 6) {
                addedDays++;
            }
        }
        return result;
    }

    function verCalendario(id) {
        const solicitud = solicitudesData.find(s => s.id === id);
        if (!solicitud) return;

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        const fechaSolicitud = new Date(solicitud.fecha_solicitud);
        fechaSolicitud.setHours(0, 0, 0, 0);
        
        // IMPORTANTE: Tomar el primer día del mes de aplicación
        const fechaAplicacion = new Date(solicitud.fecha_aplicacion);
        fechaAplicacion.setDate(1); // Forzar al primer día del mes
        fechaAplicacion.setHours(0, 0, 0, 0);
        
        // ===== SOLICITUD CERRADA - MOSTRAR SOLO MENSAJE SIN DETALLES =====
        if (solicitud.estatus === 'Cerrado') {
            const contenidoCerrado = `
                <div class="p-4 text-center">
                    <div class="solicitud-cerrada">
                        <i class="bi bi-check-circle-fill"></i>
                        <h4>SOLICITUD CERRADA</h4>
                        <p>Esta solicitud de mejora ha sido cerrada</p>
                        <p class="text-muted">y ya no está activa.</p>
                        <p class="text-muted small mt-3">No se requiere seguimiento adicional.</p>
                    </div>
                </div>
            `;
            
            document.getElementById('calendarioContent').innerHTML = contenidoCerrado;
            const modal = new bootstrap.Modal(document.getElementById('calendarioModal'));
            modal.show();
            return;
        }
        
        // ===== SOLO PARA ESTATUS NO CERRADOS =====
        
        // Calcular días para la fecha de aplicación (alerta)
        const diffTime = fechaAplicacion - hoy;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        let alertaAplicacion = '';
        if (diffDays <= 3 && diffDays >= 0) {
            alertaAplicacion = `<div class="alert-warning-custom mt-2"><i class="bi bi-exclamation-triangle me-2"></i> ¡Faltan ${diffDays} días para la fecha de aplicación!</div>`;
        } else if (diffDays < 0) {
            alertaAplicacion = `<div class="alert-secondary-custom mt-2"><i class="bi bi-calendar-x me-2"></i> La fecha de aplicación ya pasó.</div>`;
        }

        // ===== LÓGICA DEL CRONÓMETRO DE 15 DÍAS HÁBILES CON NUEVO DISEÑO =====
        let cronometroHTML = '';
        let cronometroClass = 'cronometro-info';
        
        if (hoy < fechaAplicacion) {
            // Aún no comienza el periodo de aplicación
            cronometroHTML = `
                <div class="text-center">
                    <i class="bi bi-info-circle" style="font-size: 2rem; color: #6c757d;"></i>
                    <p class="mt-2 mb-1">El periodo de aplicación comenzará el</p>
                    <strong>${fechaAplicacion.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>
                    <p class="text-muted small mt-2">El cronómetro de 15 días hábiles iniciará en esa fecha.</p>
                </div>
            `;
            cronometroClass = 'cronometro-info';
        } 
        else if (hoy >= fechaAplicacion) {
            // Ya comenzó el periodo de aplicación - calcular días hábiles transcurridos
            const diasHabilesTranscurridos = businessDaysBetween(fechaAplicacion, hoy);
            
            if (diasHabilesTranscurridos >= 15) {
                // Ya pasaron los 15 días hábiles - NUEVO DISEÑO GRIS
                cronometroHTML = `
                    <div class="text-center">
                        <i class="bi bi-calendar-check" style="font-size: 2.5rem; color: #495057;"></i>
                        <h5 class="mt-2 fw-bold" style="color: #495057;">YA PASARON LOS 15 DÍAS HÁBILES</h5>
                        <p class="mb-1">Han transcurrido <strong>${diasHabilesTranscurridos}</strong> días hábiles</p>
                        <p class="text-muted small">desde el inicio del periodo de aplicación</p>
                    </div>
                `;
                cronometroClass = 'cronometro-completado';
            } else {
                // Está dentro del periodo de 15 días
                const diasRestantes = 15 - diasHabilesTranscurridos;
                const fechaLimite = addBusinessDays(fechaAplicacion, 15);
                
                cronometroHTML = `
                    <div class="text-center">
                        <i class="bi bi-hourglass-split" style="font-size: 2rem; color: #6c757d;"></i>
                        <h6 class="mt-2 fw-bold">Cronómetro de días hábiles (15 días)</h6>
                        <p class="mb-1">Han transcurrido <strong>${diasHabilesTranscurridos}</strong> días hábiles</p>
                        <p class="mb-2">Faltan <strong>${diasRestantes}</strong> días hábiles para completar 15</p>
                        <p class="text-muted small">Fecha límite: ${fechaLimite.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    </div>
                `;
                cronometroClass = 'cronometro-activo';
            }
        }

        const contenido = `
            <div class="p-3">
                <h6 class="fw-bold">Detalle de fechas</h6>
                <ul class="list-unstyled">
                    <li><strong>Fecha de Solicitud:</strong> ${fechaSolicitud.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</li>
                    <li><strong>Periodo de Aplicación:</strong> ${fechaAplicacion.toLocaleDateString('es-ES', { year: 'numeric', month: 'long' })} 
                        <small class="text-muted">(Se considera como fecha de inicio: ${fechaAplicacion.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })})</small>
                    </li>
                    ${solicitud.fecha_verificacion ? `<li><strong>Periodo de Verificación:</strong> ${new Date(solicitud.fecha_verificacion).toLocaleDateString('es-ES', { year: 'numeric', month: 'long' })}</li>` : ''}
                    <li><strong>Responsable:</strong> ${solicitud.responsable_accion}</li>
                    <li><strong>No. Identificación:</strong> ${solicitud.folio_solicitud || '-'}</li>
                </ul>
                
                ${alertaAplicacion}
                
                <div class="mt-3 p-3 rounded ${cronometroClass}">
                    ${cronometroHTML}
                    <small class="text-muted d-block mt-2 text-center">
                        <i class="bi bi-info-circle" style="font-size: 1rem; vertical-align: middle;"></i> 
                        Días hábiles: lunes a viernes, excluyendo sábados y domingos.
                    </small>
                </div>
            </div>
        `;

        document.getElementById('calendarioContent').innerHTML = contenido;
        const modal = new bootstrap.Modal(document.getElementById('calendarioModal'));
        modal.show();
    }

    // ===== FUNCIÓN GUARDAR SOLICITUD =====
    function guardarSolicitud() {
        const id = document.getElementById('solicitud_id').value;
        const url = id ? 
            `/auditoria/solicitudes/${id}` : 
            '{{ route('auditoria.solicitudes.store') }}';
        
        const formData = new FormData(document.getElementById('formSolicitud'));
        
        if (id) {
            formData.append('_method', 'PUT');
        }

        const submitBtn = document.querySelector('#btnGuardar');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalElement = document.getElementById('modalNuevaSolicitud');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
                
                cargarSolicitudes();
                resetForm();
                mostrarMensajeExito(data.message || 'Solicitud guardada correctamente');
            } else {
                if (data.errors) {
                    for (const campo in data.errors) {
                        const errorDiv = document.getElementById(`error-${campo}`);
                        if (errorDiv) {
                            errorDiv.textContent = data.errors[campo][0];
                            errorDiv.style.display = 'block';
                            const input = document.getElementById(campo);
                            if (input) input.classList.add('is-invalid');
                        }
                    }
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la solicitud. Por favor, intente de nuevo.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // ===== FUNCIÓN ELIMINAR SOLICITUD CORREGIDA =====
    function eliminarSolicitud(id, identificador) {
        event.stopPropagation();
        event.preventDefault();
        
        Swal.fire({
            title: '¿Eliminar solicitud?',
            text: `¿Estás seguro de eliminar "${identificador}"?`,
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

                // CORRECCIÓN: Usar la ruta correcta con barra
                fetch(`/auditoria/solicitudes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la petición');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: data.message || 'Solicitud eliminada correctamente',
                            confirmButtonColor: '#800000',
                            timer: 2000
                        }).then(() => {
                            cargarSolicitudes();
                            mostrarMensajeExito('Solicitud eliminada correctamente');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error desconocido',
                            confirmButtonColor: '#800000'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al eliminar la solicitud',
                        confirmButtonColor: '#800000'
                    });
                });
            }
        });
    }
    
    function mostrarMensajeExito(mensaje) {
        const container = document.getElementById('mensajeExitoContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mb-3';
        alertDiv.setAttribute('role', 'alert');
        
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle me-2"></i> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        container.innerHTML = '';
        container.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // ===== FUNCIÓN EDITAR SOLICITUD =====
    function editarSolicitud(id) {
        const solicitud = solicitudesData.find(s => s.id === id);
        if (solicitud) {
            document.getElementById('solicitud_id').value = solicitud.id;
            document.getElementById('folio_solicitud').value = solicitud.folio_solicitud || '';
            document.getElementById('responsable_accion').value = solicitud.responsable_accion;
            document.getElementById('actividades_verificacion').value = solicitud.actividades_verificacion || '';
            
            // ===== CORRECCIÓN IMPORTANTE PARA ESTATUS =====
            // Limpiar espacios y asegurar que coincida exactamente
            const estatusValue = solicitud.estatus ? solicitud.estatus.trim() : '';
            const estatusSelect = document.getElementById('estatus');
            
            // Verificar si el valor existe en las opciones
            let optionExists = false;
            for (let i = 0; i < estatusSelect.options.length; i++) {
                if (estatusSelect.options[i].value === estatusValue) {
                    optionExists = true;
                    break;
                }
            }
            
            if (optionExists && estatusValue !== '') {
                estatusSelect.value = estatusValue;
            } else {
                // Si no existe o está vacío, establecer un valor por defecto
                console.warn('Valor de estatus no válido:', estatusValue);
                estatusSelect.value = 'En Proceso'; // Valor por defecto
            }
            
            // Fechas
            if (solicitud.fecha_solicitud) {
                const fecha = new Date(solicitud.fecha_solicitud);
                const año = fecha.getFullYear();
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                const dia = String(fecha.getDate()).padStart(2, '0');
                document.getElementById('fecha_solicitud').value = `${año}-${mes}-${dia}`;
            }
            
            if (solicitud.fecha_aplicacion) {
                const fecha = new Date(solicitud.fecha_aplicacion);
                const año = fecha.getFullYear();
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                document.getElementById('fecha_aplicacion').value = `${año}-${mes}`;
            }
            
            if (solicitud.fecha_verificacion) {
                const fecha = new Date(solicitud.fecha_verificacion);
                const año = fecha.getFullYear();
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                document.getElementById('fecha_verificacion').value = `${año}-${mes}`;
            } else {
                document.getElementById('fecha_verificacion').value = '';
            }
            
            // Archivo
            const nombreArchivoActual = document.getElementById('nombreArchivoActual');
            const nombreArchivo = document.getElementById('nombreArchivo');
            
            if (solicitud.archivo_nombre) {
                if (nombreArchivoActual) nombreArchivoActual.style.display = 'block';
                if (nombreArchivo) nombreArchivo.textContent = solicitud.archivo_nombre;
            } else {
                if (nombreArchivoActual) nombreArchivoActual.style.display = 'none';
            }
            
            document.getElementById('modalNuevaSolicitudLabel').textContent = 'Editar Solicitud de Mejora';
            
            const modal = new bootstrap.Modal(document.getElementById('modalNuevaSolicitud'));
            modal.show();
        }
    }

    function resetForm() {
        const form = document.getElementById('formSolicitud');
        if (form) form.reset();
        
        document.getElementById('solicitud_id').value = '';
        const nombreArchivoActual = document.getElementById('nombreArchivoActual');
        if (nombreArchivoActual) nombreArchivoActual.style.display = 'none';
        document.getElementById('modalNuevaSolicitudLabel').textContent = 'Registrar Nueva Solicitud de Mejora';
    }
</script>
@endpush