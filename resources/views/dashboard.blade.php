{{-- RESOURCES/VIEWS/DASHBOARD.BLADE --}}
@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid">
    <!-- =====================================================
         DASHBOARD HEADER - ENCABEZADO DEL PANEL PRINCIPAL
         ===================================================== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h1 class="h3 mb-2 text-dark">Dashboard Principal</h1>
                <p class="text-muted mb-0">Bienvenido, {{ Auth::user()->name }}</p>
            </div>
        </div>
    </div>

    <!-- =====================================================
         DASHBOARD GRID - TARJETAS DE MÓDULOS (RESPONSIVO)
         ===================================================== -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
        @php
            $userRole = Auth::user()->role;

            // CONTEO DE NOTIFICACIONES SIN LEER
            $notificacionesSinLeer = \App\Models\Notificacion::deUsuario(Auth::id())
                                    ->noLeidas()
                                    ->count();
            
            $allModules = [
                [
                    'title' => 'Anexos',
                    'icon' => 'bi-folder',
                    'description' => 'Gestión de documentos anexos',
                    'color' => '#4f46e5',
                    'route' => route('anexos.index'),
                    'visible' => true
                ],
                [
                    'title' => 'Auditorías',
                    'icon' => 'bi-clipboard-check',
                    'description' => 'Gestión de auditorías',
                    'color' => '#059669',
                    'route' => route('auditoria.dashboard'),
                    'visible' => true
                ],
                [
                    'title' => 'Gestión Documental',
                    'icon' => 'bi-files',
                    'description' => 'Control de documentos',
                    'color' => '#dc2626',
                    'route' => route('documental.index'),
                    'visible' => true
                ],
                [
                    'title' => 'Lista Maestra',
                    'icon' => 'bi-file-earmark-text',
                    'description' => 'Formatos validados del sistema',
                    'color' => '#16a34a',
                    'route' => route('formatos.index'),
                    'visible' => in_array($userRole, ['superadmin', 'admin'])
                ],
                [
                    'title' => 'Usuarios',
                    'icon' => 'bi-people',
                    'description' => 'Administración de usuarios',
                    'color' => '#7c3aed',
                    'route' => route('admin.usuarios.index'),
                    'visible' => in_array($userRole, ['superadmin', 'admin'])
                ],
                [
                    'title' => 'Historial de Versiones',
                    'icon' => 'bi-clock-history',
                    'description' => 'Registro completo de actividades',
                    'color' => '#0891b2',
                    'route' => route('historial-versiones.index'),
                    'visible' => $userRole === 'superadmin' 
                ],
                [
                    'title' => 'Notificaciones',
                    'icon' => 'bi-bell',
                    'description' => 'Alertas y notificaciones',
                    'color' => '#ea580c',
                    'route' => route('notificaciones.index'),
                    'visible' => true // TODOS PUEDEN VER
                ],
                [
                    'title' => 'Avisos',
                    'icon' => 'bi-megaphone',
                    'description' => 'Gestión de avisos y comunicados',
                    'color' => '#4f46e5',
                    'route' => route('avisos.index'),
                    'visible' => in_array($userRole, ['superadmin', 'admin']) // ESTE MODULO SOLO ESTA VISIBLE PARA ADMINISTRADOR Y SUPERAMINISTRADOR
                ]
            ];
            
            $modules = array_filter($allModules, function($module) {
                return $module['visible'];
            });
        @endphp

        @foreach($modules as $module)
        <div class="col">
            <div class="card dashboard-card h-100 border-0 shadow-sm" 
                 data-module="{{ $module['title'] }}"
                 data-route="{{ $module['route'] }}"
                 onclick="handleDashboardClick('{{ $module['title'] }}', '{{ $module['route'] }}')"
                 style="cursor: pointer;">
                <div class="card-body text-center p-4">
                    <div class="dashboard-icon mb-3" style="background-color: {{ $module['color'] }}20; border-color: {{ $module['color'] }}">
                        <i class="{{ $module['icon'] }}" style="color: {{ $module['color'] }}; font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2" style="color: {{ $module['color'] }}">{{ $module['title'] }}</h5>
                    <p class="card-text text-muted small mb-3">{{ $module['description'] }}</p>
                    <div class="mt-auto position-relative">
                        @if($module['title'] === 'Notificaciones' && $notificacionesSinLeer > 0)
                        <span class="badge rounded-pill bg-danger position-absolute"
                            style="font-size:1.85rem; top:-160px; left:10px; padding: 5px 10px;">
                            {{ $notificacionesSinLeer }}
                        </span>
                        @endif
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-arrow-right-short"></i> Acceder
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- =====================================================
     MODAL CARRUSEL PARA AVISOS
     ===================================================== -->
<div class="modal fade" id="modalAvisosCarousel" tabindex="-1" aria-labelledby="modalAvisosCarouselLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff; color: black; border-bottom: 1px solid #ccced0;">
                <h5 class="modal-title" id="modalAvisosCarouselLabel">
                    <i class="bi bi-megaphone me-2"></i>
                    Avisos Importantes
                </h5>
            </div>
            <div class="modal-body p-0">
                <div id="avisosCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner" id="avisosCarouselInner">
                        <div class="carousel-item active">
                            <div class="text-center p-5">
                                <div class="spinner-border text-danger" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-3 text-muted">Cargando avisos...</p>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#avisosCarousel" data-bs-slide="prev" id="carouselPrevBtn" style="display: none;">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#avisosCarousel" data-bs-slide="next" id="carouselNextBtn" style="display: none;">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            </div>
            <div class="modal-footer" style="border-top: none; justify-content: right;">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="background-color: #6c757d; border-radius: 4px;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     MODAL PARA EXPANDIR VISTA DE ARCHIVO
     ===================================================== -->
<div class="modal fade" id="modalExpandirArchivo" tabindex="-1" aria-labelledby="modalExpandirArchivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff; color: black;">
                <h5 class="modal-title" id="modalExpandirArchivoLabel">
                    <i class="bi bi-arrows-fullscreen me-2"></i>
                    Visualizador de Archivo
                </h5>
            </div>
            <div class="modal-body p-0" id="expandirArchivoContent" style="background-color: #f5f5f5;">
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-footer" style="border-top: none; justify-content: right;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="background-color: #6c757d; border-radius: 4px;">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    /* =====================================================
       DASHBOARD CARD STYLES - ESTILOS DE LAS TARJETAS
       ===================================================== */
    .dashboard-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        background: #fff;
        min-height: 200px;
        display: flex;
        flex-direction: column;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #737373 0%, #737373 100%);
    }

    .dashboard-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 2px solid;
        transition: all 0.3s ease;
    }

    .dashboard-card:hover .dashboard-icon {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .card-text {
        flex-grow: 1;
    }

    /* =====================================================
       ESTILOS PARA EL CARRUSEL DE AVISOS
       ===================================================== */
    .aviso-carousel-item {
        padding: 30px;
        min-height: 600px;
        max-height: 700px;
        overflow-y: auto;
    }
    
    .aviso-header {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    
    .aviso-titulo {
        font-size: 1.4rem;
        font-weight: 700;
        color: #000000;
        margin-bottom: 8px;
    }
    
    .aviso-fechas {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .aviso-fechas i {
        margin-right: 5px;
    }
    
    .aviso-contenido {
        margin-top: 20px;
    }
    
    .aviso-contenido p {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .aviso-archivo-preview {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        position: relative;
    }
    
    .aviso-archivo-preview h6 {
        color: #000000;
        margin-bottom: 15px;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .preview-content {
        max-height: 500px;
        overflow: auto;
        background-color: #fff;
        border-radius: 8px;
        position: relative;
    }
    
    /* =====================================================
       BOTÓN DE LUPA PARA EXPANDIR
       ===================================================== */
    .btn-expandir {
        position: absolute;
        top: 20px;
        right: 20px;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 10;
    }
    
    .btn-expandir:hover {
        background-color: #000000;
        transform: scale(1.1);
    }
    
    /* =====================================================
       MEJORAS PARA PDF
       ===================================================== */
    .pdf-container {
        width: 100%;
        height: 500px;
        background-color: #f5f5f5;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }
    
    .pdf-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    /* =====================================================
       MEJORAS PARA IMÁGENES
       ===================================================== */
    .preview-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: block;
        margin: 0 auto;
    }
    
    /* =====================================================
       MEJORAS PARA TEXTO
       ===================================================== */
    .preview-content pre {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        font-size: 0.9rem;
        white-space: pre-wrap;
        word-wrap: break-word;
        max-height: 500px;
        overflow: auto;
        font-family: 'Courier New', monospace;
        border: 1px solid #e9ecef;
        margin: 0;
    }
    
    /* =====================================================
       BOTÓN DE DESCARGA MEJORADO
       ===================================================== */
    .btn-descargar {
        background-color: #737372;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 24px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    
    .btn-descargar:hover {
        background-color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    .btn-descargar:active {
        transform: translateY(0);
    }
    
    .sin-archivo {
        background-color: #f8f9fa;
        border-radius: 12px;
        padding: 40px;
        text-align: center;
        color: #6c757d;
    }
    
    .sin-archivo i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-size: 60%;
        width: 40px;
        height: 40px;
    }
    
    /* =====================================================
       ESTILOS PARA EL MODAL EXPANDIDO
       ===================================================== */
    .fullscreen-content {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #fff;
    }
    
    .fullscreen-content iframe,
    .fullscreen-content img,
    .fullscreen-content pre {
        width: 100%;
        height: 100%;
        border: none;
        object-fit: contain;
        background-color: #fff;
    }
    
    .fullscreen-content pre {
        background-color: #fff;
        color: #eee;
        padding: 20px;
        overflow: auto;
        margin: 0;
    }
    
    /* =====================================================
       RESPONSIVE - MEDIA QUERIES
       ===================================================== */
    @media (max-width: 768px) {
        .dashboard-card {
            margin-bottom: 15px;
        }
        
        .dashboard-icon {
            width: 60px;
            height: 60px;
        }
        
        .dashboard-icon i {
            font-size: 1.5rem !important;
        }
        
        .card-title {
            font-size: 1.1rem;
        }
        
        .card-text {
            font-size: 0.85rem;
        }
        
        .aviso-carousel-item {
            padding: 20px;
            min-height: 500px;
        }
        
        .aviso-titulo {
            font-size: 1.1rem;
        }
        
        .pdf-container {
            height: 400px;
        }
        
        .preview-content {
            max-height: 400px;
        }
        
        .btn-expandir {
            width: 35px;
            height: 35px;
            top: 10px;
            right: 10px;
        }
    }

    @media (max-width: 576px) {
        .dashboard-card {
            min-height: 180px;
        }
        
        .dashboard-icon {
            width: 50px;
            height: 50px;
        }
        
        .dashboard-icon i {
            font-size: 1.25rem !important;
        }
        
        .aviso-carousel-item {
            padding: 15px;
            min-height: 450px;
        }
        
        .pdf-container {
            height: 350px;
        }
        
        .preview-content {
            max-height: 350px;
        }
    }

    /* =====================================================
       ANIMATION FOR CARDS - ANIMACIÓN DE ENTRADA
       ===================================================== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dashboard-card {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }

    .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
    .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
    .dashboard-card:nth-child(4) { animation-delay: 0.4s; }
    .dashboard-card:nth-child(5) { animation-delay: 0.5s; }
    .dashboard-card:nth-child(6) { animation-delay: 0.6s; }
    .dashboard-card:nth-child(7) { animation-delay: 0.7s; }
    .dashboard-card:nth-child(8) { animation-delay: 0.8s; }

    /* =====================================================
       CUSTOM TOAST NOTIFICATION - NOTIFICACIONES PERSONALIZADAS
       ===================================================== */
    .custom-toast {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1050;
        min-width: 300px;
        max-width: 350px;
        animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // =====================================================
    // VARIABLES GLOBALES
    // =====================================================
    let modalMostrado = false;
    let currentExpandUrl = '';
    let currentExpandType = '';
    let currentExpandNombre = '';
    
    // LISTA DE EXTENSIONES QUE NO SE PUEDEN VISUALIZAR (SOLO SE PUEDE DESCARGAR)
    const extensionesSinVista = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'zip', 'rar', '7z'];

    // =====================================================
    // FUNCIÓN PARA MOSTRAR TOAST (NOTIFICACIÓN TEMPORAL)
    // =====================================================
    function showToast(moduleName) {
        const toast = document.createElement('div');
        toast.className = 'custom-toast';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-primary text-white">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong class="me-auto">Módulo ${moduleName}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <p>El módulo <strong>${moduleName}</strong> está actualmente en desarrollo.</p>
                    <p class="mb-0"><small>Disponible próximamente</small></p>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast.parentNode) toast.remove(); }, 3000);
        toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
    }

    // =====================================================
    // FUNCIÓN PARA MANEJAR CLICK EN TARJETAS DEL DASHBOARD
    // =====================================================
    function handleDashboardClick(module, route) {
        const card = event.currentTarget;
        card.style.transform = 'scale(0.98)';
        setTimeout(() => card.style.transform = '', 150);
        if (route && route !== '#') window.location.href = route;
        else showToast(module);
    }

    // =====================================================
    // FUNCIÓN PARA OBTENER ICONO SEGÚN EXTENSIÓN DE ARCHIVO
    // =====================================================
    function getIconoPorExtension(filename) {
        if (!filename) return 'bi-file-earmark';
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': 'bi-file-pdf',
            'doc': 'bi-file-word',
            'docx': 'bi-file-word',
            'xls': 'bi-file-excel',
            'xlsx': 'bi-file-excel',
            'ppt': 'bi-file-ppt',
            'pptx': 'bi-file-ppt',
            'jpg': 'bi-file-image',
            'jpeg': 'bi-file-image',
            'png': 'bi-file-image',
            'gif': 'bi-file-image',
            'txt': 'bi-file-text',
            'zip': 'bi-file-zip',
            'rar': 'bi-file-zip'
        };
        return icons[ext] || 'bi-file-earmark';
    }

    // =====================================================
    // FUNCIÓN PARA FORMATEAR FECHA
    // =====================================================
    function formatFecha(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-MX', { 
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }

    // =====================================================
    // FUNCIÓN PARA ESCAPAR HTML (SEGURIDAD)
    // =====================================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // =====================================================
    // FUNCIÓN PARA EXPANDIR ARCHIVO (LUPA)
    // =====================================================
    function expandirArchivo(url, tipo, nombre) {
        currentExpandUrl = url;
        currentExpandType = tipo;
        currentExpandNombre = nombre;
        
        const modalTitle = document.getElementById('modalExpandirArchivoLabel');
        modalTitle.innerHTML = `<i class="bi bi-arrows-fullscreen me-2"></i> ${escapeHtml(nombre)}`;
        
        const contentContainer = document.getElementById('expandirArchivoContent');
        
        let contenido = '';
        
        if (tipo === 'imagen') {
            contenido = `<div class="fullscreen-content"><img src="${url}" alt="${escapeHtml(nombre)}" style="max-width: 100%; max-height: 100%; object-fit: contain;"></div>`;
        } else if (tipo === 'pdf') {
            contenido = `<div class="fullscreen-content"><iframe src="${url}#toolbar=0&navpanes=0&scrollbar=0" style="width: 100%; height: 100%; border: none;"></iframe></div>`;
        } else if (tipo === 'txt') {
            fetch(url)
                .then(response => response.text())
                .then(text => {
                    contentContainer.innerHTML = `<div class="fullscreen-content"><pre style="margin: 0; padding: 20px; background-color: #1a1a2e; color: #eee; overflow: auto; height: 100%; white-space: pre-wrap;">${escapeHtml(text)}</pre></div>`;
                })
                .catch(error => {
                    contentContainer.innerHTML = `<div class="text-center text-danger p-5">Error al cargar el archivo</div>`;
                });
            return;
        }
        
        if (contenido) {
            contentContainer.innerHTML = contenido;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('modalExpandirArchivo'));
        modal.show();
    }

    // =====================================================
    // FUNCIÓN PARA DESCARGAR ARCHIVO
    // =====================================================
    function descargarArchivo(id, nombreArchivo) {
        const downloadUrl = `{{ url('avisos') }}/${id}/download`;
        
        Swal.fire({
            title: 'Descargando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
            showConfirmButton: false
        });
        
        fetch(downloadUrl, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/octet-stream'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la descarga');
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = nombreArchivo;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            Swal.fire({
                icon: 'success',
                title: 'Descarga iniciada',
                text: 'El archivo se está descargando',
                timer: 2000,
                showConfirmButton: false
            });
        })
        .catch(error => {
            console.error('Error al descargar:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo descargar el archivo',
                confirmButtonColor: '#000000'
            });
        });
    }

    // =====================================================
    // FUNCIÓN PARA CARGAR CONTENIDO DE ARCHIVO (VISTA PREVIA)
    // =====================================================
    async function cargarContenidoArchivo(aviso) {
        const extension = aviso.archivo_nombre ? aviso.archivo_nombre.split('.').pop().toLowerCase() : '';
        const url = `{{ url('avisos') }}/${aviso.id}/ver`;
        
        const puedeVisualizar = !extensionesSinVista.includes(extension);
        
        if (!aviso.archivo_nombre) {
            return `
                <div class="sin-archivo">
                    <i class="bi bi-file-earmark"></i>
                    <p>Este aviso no tiene archivo adjunto</p>
                </div>
            `;
        }
        
        if (puedeVisualizar) {
            try {
                if (['jpg','jpeg','png','gif','bmp','webp'].includes(extension)) {
                    return `
                        <button class="btn-expandir" onclick="expandirArchivo('${url}', 'imagen', '${aviso.archivo_nombre.replace(/'/g, "\\'")}')">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                        <img src="${url}" class="img-fluid rounded" alt="Imagen" style="max-height: 450px; width: auto; margin: 0 auto; display: block;">
                    `;
                } 
                else if (extension === 'pdf') {
                    return `
                        <button class="btn-expandir" onclick="expandirArchivo('${url}', 'pdf', '${aviso.archivo_nombre.replace(/'/g, "\\'")}')">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                        <div class="pdf-container">
                            <iframe src="${url}#toolbar=0&navpanes=0&scrollbar=0" frameborder="0"></iframe>
                        </div>
                    `;
                }
                else if (extension === 'txt') {
                    const response = await fetch(url);
                    const text = await response.text();
                    return `
                        <button class="btn-expandir" onclick="expandirArchivo('${url}', 'txt', '${aviso.archivo_nombre.replace(/'/g, "\\'")}')">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                        <pre>${escapeHtml(text)}</pre>
                    `;
                }
                else {
                    return `
                        <div class="sin-archivo">
                            <i class="${getIconoPorExtension(aviso.archivo_nombre)}"></i>
                            <p>Vista previa no disponible para este tipo de archivo</p>
                            <small class="text-muted">Extensión: .${extension.toUpperCase()}</small>
                            <div class="mt-3">
                                <button onclick="descargarArchivo(${aviso.id}, '${aviso.archivo_nombre.replace(/'/g, "\\'")}')" class="btn btn-descargar">
                                    <i class="bi bi-download me-2"></i>Descargar archivo
                                </button>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error al cargar archivo:', error);
                return `
                    <div class="sin-archivo text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>Error al cargar el archivo</p>
                        <div class="mt-3">
                            <button onclick="descargarArchivo(${aviso.id}, '${aviso.archivo_nombre.replace(/'/g, "\\'")}')" class="btn btn-descargar">
                                <i class="bi bi-download me-2"></i>Descargar archivo
                            </button>
                        </div>
                    </div>
                `;
            }
        } else {
            return `
                <div class="sin-archivo">
                    <i class="${getIconoPorExtension(aviso.archivo_nombre)}"></i>
                    <p class="mt-2 mb-2"><strong>${escapeHtml(aviso.archivo_nombre)}</strong></p>
                    <p class="text-muted small">Este tipo de archivo no se puede visualizar en el navegador</p>
                    <div class="mt-3">
                        <button onclick="descargarArchivo(${aviso.id}, '${aviso.archivo_nombre.replace(/'/g, "\\'")}')" class="btn btn-descargar">
                            <i class="bi bi-download me-2"></i>Descargar archivo
                        </button>
                    </div>
                </div>
            `;
        }
    }

    // =====================================================
    // FUNCIÓN PRINCIPAL PARA CARGAR Y MOSTRAR AVISOS
    // =====================================================
    async function cargarYMostrarAvisos() {
        try {
            const response = await fetch('/api/avisos/activos', {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            const now = new Date();
            const avisosActivos = data.filter(aviso => {
                const fechaInicio = new Date(aviso.fecha_inicio);
                const fechaFin = new Date(aviso.fecha_fin);
                return aviso.activo && now >= fechaInicio && now <= fechaFin;
            });
            
            const carouselInner = document.getElementById('avisosCarouselInner');
            const prevBtn = document.getElementById('carouselPrevBtn');
            const nextBtn = document.getElementById('carouselNextBtn');
            
            if (avisosActivos.length === 0) {
                carouselInner.innerHTML = `
                    <div class="carousel-item active">
                        <div class="text-center p-5">
                            <i class="bi bi-megaphone" style="font-size: 4rem; color: #dee2e6;"></i>
                            <p class="mt-3 text-muted">No hay avisos importantes en este momento</p>
                        </div>
                    </div>
                `;
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                let itemsHtml = '';
                for (let i = 0; i < avisosActivos.length; i++) {
                    const aviso = avisosActivos[i];
                    const isActive = i === 0 ? 'active' : '';
                    
                    const contenidoArchivo = await cargarContenidoArchivo(aviso);
                    
                    itemsHtml += `
                        <div class="carousel-item ${isActive}">
                            <div class="aviso-carousel-item">
                                <div class="aviso-header">
                                    <div class="aviso-titulo">
                                        <i class="bi bi-megaphone me-2" style="color: #000000;"></i>
                                        ${escapeHtml(aviso.titulo)}
                                    </div>
                                    <div class="aviso-fechas">
                                        <i class="bi bi-calendar"></i>
                                        ${formatFecha(aviso.fecha_inicio)} - ${formatFecha(aviso.fecha_fin)}
                                    </div>
                                </div>
                                <div class="aviso-contenido">
                                    ${aviso.descripcion ? `<p>${escapeHtml(aviso.descripcion)}</p>` : ''}
                                </div>
                                ${aviso.archivo_nombre ? `
                                    <div class="aviso-archivo-preview">
                                        <h6><i class="${getIconoPorExtension(aviso.archivo_nombre)} me-2"></i>${escapeHtml(aviso.archivo_nombre)}</h6>
                                        <div class="preview-content">
                                            ${contenidoArchivo}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }
                
                carouselInner.innerHTML = itemsHtml;
                
                if (avisosActivos.length > 1) {
                    prevBtn.style.display = 'flex';
                    nextBtn.style.display = 'flex';
                } else {
                    prevBtn.style.display = 'none';
                    nextBtn.style.display = 'none';
                }
            }
            
            if (avisosActivos.length > 0 && !modalMostrado) {
                const modal = new bootstrap.Modal(document.getElementById('modalAvisosCarousel'));
                modal.show();
                modalMostrado = true;
            }
        } catch (error) {
            console.error('Error al cargar avisos:', error);
            document.getElementById('avisosCarouselInner').innerHTML = `
                <div class="carousel-item active">
                    <div class="text-center p-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        <p class="mt-3 text-danger">Error al cargar los avisos</p>
                        <small class="text-muted">Intenta recargar la página</small>
                    </div>
                </div>
            `;
        }
    }

    // =====================================================
    // EVENTO DOMContentLoaded - INICIALIZACIÓN
    // =====================================================
    document.addEventListener('DOMContentLoaded', function() {
        cargarYMostrarAvisos();
        
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            card.setAttribute('tabindex', '0');
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const module = this.getAttribute('data-module');
                    const route = this.getAttribute('data-route');
                    handleDashboardClick(module, route);
                }
            });
        });
    });
</script>
@endpush