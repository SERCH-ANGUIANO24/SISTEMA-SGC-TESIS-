{{-- VISTA PRINCIPAL DEL MÓDULO DE COMPETENCIAS --}}
{{-- MUESTRA CARPETAS Y DOCUMENTOS, CON OPCIONES DE BUSCAR, ORDENAR, SUBIR Y GESTIONAR ARCHIVOS --}}
@extends('layouts.app')

@section('title', 'Competencias - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">

    {{-- ENCABEZADO: TÍTULO Y BOTONES DE ACCIÓN --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex flex-column">
                    {{-- TÍTULO CON ENLACE AL DASHBOARD --}}
                    <a href="{{ route('auditoria.dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #7c3aed; cursor: pointer;">
                            <i class="bi-person-workspace me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                            Competencias
                        </h1>
                    </a>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    {{-- BOTÓN NUEVA CARPETA - Solo superadmin, admin Y AUDITOR LIDER--}}
                    {{-- SOLO SE MUESTRA SI EL USUARIO TIENE PERMISO auditoria-access --}}
                    @can('auditoria-access')
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                            <i class="bi bi-folder-plus me-1"></i> Nueva Carpeta
                        </button>
                    @endcan
                    
                    {{-- BOTÓN SUBIR ARCHIVO - TODOS los usuarios pueden subir archivos --}}
                    {{-- SI HAY UNA CARPETA SELECCIONADA, MUESTRA EL BOTÓN ACTIVO --}}
                    @if(isset($currentFolder) && $currentFolder)
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-upload me-1"></i> Subir Archivo
                        </button>
                    @else
                        {{-- Si no hay carpeta seleccionada, mostrar mensaje o botón deshabilitado --}}
                        {{-- SI NO HAY CARPETA SELECCIONADA, MUESTRA EL BOTÓN DESHABILITADO --}}
                        <button type="button" class="btn text-white" style="background-color: #a9a9a9;" disabled>
                            <i class="bi bi-upload me-1"></i> Selecciona una carpeta
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- BREADCRUMBS: RUTA DE NAVEGACIÓN ENTRE CARPETAS --}}
    <div class="mb-3">
        @include('auditoria.competencias.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs, 'currentFolder' => $currentFolder])
    </div>

    {{-- SOLO UN MENSAJE DE ÉXITO --}}
    {{-- ALERTA DE ÉXITO --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessage">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ALERTA DE ERROR --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ALERTA DE ADVERTENCIA --}}
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ALERTA INFORMATIVA --}}
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- BUSCADOR Y ORDENADOR: SOLO SE MUESTRAN SI HAY UNA CARPETA SELECCIONADA --}}
    @if(isset($currentFolder) && $currentFolder)
    <div class="row mb-4 align-items-end">

        {{-- CAMPO DE BÚSQUEDA DE ARCHIVOS --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #000000;">
                        <i class="bi bi-search me-1"></i> Buscar archivos
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-color: #dee2e6;">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre de archivo" 
                               style="border-color: #dee2e6; background-color: white;">
                        {{-- BOTÓN PARA LIMPIAR EL CAMPO DE BÚSQUEDA --}}
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    {{-- CONTADOR DE RESULTADOS DE BÚSQUEDA --}}
                    <div id="searchResults" class="mt-2 small text-muted">
                        <span id="resultCount"></span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- SELECTOR DE ORDENAMIENTO DE ARCHIVOS --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label fw-bold mb-2" style="color: #000000;">
                                <i class="bi bi-sort-down me-1"></i> Ordenar por
                            </label>
                            <select id="sortSelect" class="form-select">
                                <option value="name_asc">📄 Nombre (A-Z)</option>
                                <option value="name_desc">📄 Nombre (Z-A)</option>
                                <option value="date_desc">📅 Fecha (más reciente)</option>
                                <option value="date_asc">📅 Fecha (más antiguo)</option>
                                <option value="size_desc">📊 Tamaño (mayor a menor)</option>
                                <option value="size_asc">📊 Tamaño (menor a mayor)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- SPINNER DE CARGA (SE MUESTRA MIENTRAS SE CARGAN LOS ARCHIVOS) --}}
    <div id="loadingSpinner" class="text-center my-5" style="display: none;">
        <div class="spinner-border" style="color: #800000;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 text-muted">Cargando archivos...</p>
    </div>

    {{-- CARPETAS --}}
    {{-- INCLUYE EL PARTIAL QUE RENDERIZA EL GRID DE CARPETAS --}}
    <div id="folderContainer">
        @include('auditoria.competencias.partials.folder-grid', [
            'folders' => $folders,
            'userRole' => $userRole
        ])
    </div>

    {{-- DOCUMENTOS --}}
    {{-- INCLUYE EL PARTIAL QUE RENDERIZA LA LISTA DE ARCHIVOS --}}
    <div id="fileContainer">
        @include('auditoria.competencias.partials.file-list', [
            'documents' => $documents,
            'currentFolder' => $currentFolder ?? null,
            'userRole' => $userRole
        ])
    </div>
</div>

{{-- MODALES DE VISUALIZACIÓN DE DOCUMENTOS --}}
{{-- SE GENERA UN MODAL POR CADA DOCUMENTO QUE SEA VISUALIZABLE EN PANTALLA (NO WORD, EXCEL, ETC.) --}}
@foreach($documents as $doc)
    @php
        $extension = strtolower($doc->archivo_extension ?? '');
        // FORMATOS QUE NO SE PUEDEN PREVISUALIZAR EN EL NAVEGADOR
        $noViewable = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];
    @endphp
    
    {{-- SOLO CREA EL MODAL SI EL ARCHIVO ES VISUALIZABLE --}}
    @if(!in_array($extension, $noViewable))
    <div class="modal fade" id="viewDocumentModal{{ $doc->id }}" tabindex="-1" aria-labelledby="viewDocumentModalLabel{{ $doc->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDocumentModalLabel{{ $doc->id }}">
                        <i class="bi bi-file-earmark-text me-2" style="color: #000000;"></i>
                        {{ $doc->nombre }}.{{ $doc->archivo_extension }}
                    </h5>
                </div>
                {{-- ÁREA DE PREVISUALIZACIÓN DEL DOCUMENTO (80% DE LA ALTURA DE PANTALLA) --}}
                <div class="modal-body p-0" style="height: 80vh;">
                    @include('auditoria.competencias.partials.document-viewer', [
                        'extension' => $extension,
                        'fileUrl' => route('auditoria.competencias.document.ver', $doc->id),
                        'docId' => $doc->id
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    {{-- BOTÓN PARA DESCARGAR EL DOCUMENTO --}}
                    <a href="{{ route('auditoria.competencias.document.download', $doc->id) }}" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-download me-1"></i> Descargar
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- MODAL RENOMBRAR CARPETA --}}
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- EL ACTION SE ASIGNA DINÁMICAMENTE CON JAVASCRIPT SEGÚN LA CARPETA SELECCIONADA --}}
        <form action="" method="POST" id="renameFolderForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2" style="color: #000000;"></i>
                        Renombrar Carpeta
                    </h5>

                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newFolderName" class="form-label fw-bold">Nuevo nombre</label>
                        {{-- EL VALOR SE LLENA DINÁMICAMENTE CON EL NOMBRE ACTUAL DE LA CARPETA --}}
                        <input type="text" class="form-control" id="newFolderName" name="nombre" required autofocus>
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

{{-- MODAL RENOMBRAR DOCUMENTO --}}
<div class="modal fade" id="renameDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- EL ACTION SE ASIGNA DINÁMICAMENTE CON JAVASCRIPT SEGÚN EL DOCUMENTO SELECCIONADO --}}
        <form action="" method="POST" id="renameDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2" style="color: #000000;"></i>
                        Renombrar Documento
                    </h5>

                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newDocumentName" class="form-label fw-bold">Nuevo nombre</label>
                        {{-- EL VALOR SE LLENA DINÁMICAMENTE CON EL NOMBRE ACTUAL DEL DOCUMENTO --}}
                        <input type="text" class="form-control" id="newDocumentName" name="nombre" required autofocus>
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

{{-- MODAL MOVER CARPETA --}}
<div class="modal fade" id="moveFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- EL ACTION SE ASIGNA DINÁMICAMENTE CON JAVASCRIPT SEGÚN LA CARPETA A MOVER --}}
        <form action="" method="POST" id="moveFolderForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #000000;"></i>
                        Mover Carpeta
                    </h5>

                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <span class="fw-bold">Carpeta a mover:</span><br>
                        {{-- EL NOMBRE SE LLENA DINÁMICAMENTE CON JAVASCRIPT --}}
                        <span id="moveFolderName" style="color: #737373; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="folderDestination" class="form-label fw-bold">Seleccionar destino</label>
                        {{-- LAS OPCIONES SE CARGAN DINÁMICAMENTE VÍA FETCH CUANDO SE ABRE EL MODAL --}}
                        <select class="form-select" id="folderDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Selecciona la carpeta donde deseas mover la carpeta.
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

{{-- MODAL MOVER DOCUMENTO --}}
<div class="modal fade" id="moveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- EL ACTION SE ASIGNA DINÁMICAMENTE CON JAVASCRIPT SEGÚN EL DOCUMENTO A MOVER --}}
        <form action="" method="POST" id="moveDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #000000;"></i>
                        Mover Documento
                    </h5>

                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <span class="fw-bold">Documento a mover:</span><br>
                        {{-- EL NOMBRE SE LLENA DINÁMICAMENTE CON JAVASCRIPT --}}
                        <span id="moveDocumentName" style="color: #737373; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="documentDestination" class="form-label fw-bold">Seleccionar destino</label>
                        {{-- LAS OPCIONES SE CARGAN DINÁMICAMENTE VÍA FETCH CUANDO SE ABRE EL MODAL --}}
                        <select class="form-select" id="documentDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
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

{{-- MODAL CREAR CARPETA --}}
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- ENVÍA EL ID DE LA CARPETA ACTUAL PARA CREAR LA NUEVA COMO SUBCARPETA --}}
        <form action="{{ route('auditoria.competencias.folder.store') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-folder-plus me-1" style="color: #000000;"></i>
                        Agregar Carpeta
                    </h5>

                </div>
                <div class="modal-body">
                    {{-- NOMBRE DE LA NUEVA CARPETA --}}
                    <div class="mb-3">
                        <label class="form-label">Nombre de Carpeta</label>
                        <input type="text" class="form-control" name="nombre" required autofocus>
                    </div>
                    {{-- COLOR VISUAL PARA IDENTIFICAR LA CARPETA --}}
                    <div class="mb-3">
                        <label class="form-label">Color Visual</label>
                        <input type="color" class="form-control form-control-color" name="color" value="#800000" style="width: 100%; height: 40px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">Crear Carpeta</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL SUBIR ARCHIVO (con icono agregado) --}}
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- ENVÍA EL ARCHIVO Y EL ID DE LA CARPETA DESTINO --}}
        <form action="{{ route('auditoria.competencias.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-cloud-upload me-2" style="color: #000000;"></i>
                        Subir Archivo
                    </h5>

                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar archivo</label>
                        <input class="form-control" type="file" name="archivo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">Subir Archivo</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ========== ESTILOS EXISTENTES (NO MODIFICADOS) ========== */

    /* ESTILOS DE LAS TARJETAS DE CARPETAS */
    .folder-card {
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        border-radius: 12px;
    }
    /* EFECTO HOVER EN TARJETAS DE CARPETAS */
    .folder-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
    }
    .folder-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    /* EFECTO HOVER EN FILAS DE ARCHIVOS */
    .file-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .breadcrumb-item a {
        text-decoration: none;
        color: #000000;
        font-weight: 500;
    }
    .folder-icon i {
        font-size: 4rem;
    }
    .folder-card .card-body {
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    /* ESTILOS PARA LAS ALERTAS DE SWEETALERT2 */
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

    /* ELIMINAR SEGUNDO MENSAJE DE ÉXITO DUPLICADO */
    .alert-success:not(:first-of-type) {
        display: none !important;
    }

    /* ========== NUEVOS ESTILOS RESPONSIVOS ========== */
    
    /* MÓVILES (pantallas menores a 768px) */
    @media (max-width: 768px) {
        /* Header - Apilar botones */
        .d-flex.justify-content-between {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 1rem;
        }
        
        .d-flex.justify-content-between .mt-2 {
            margin-top: 0 !important;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .d-flex.justify-content-between .mt-2 .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        
        /* Título más pequeño en móvil */
        .h3 {
            font-size: 1.5rem;
        }
        
        .h3 i {
            font-size: 2rem !important;
        }
        
        /* Buscador y ordenador - apilar en móvil */
        .row.mb-4 .col-md-6 {
            margin-bottom: 1rem;
        }
        
        /* Tabla responsiva - permitir scroll horizontal */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Tarjetas de carpetas - 1 por fila en móvil */
        #folderContainer .row > [class*="col-"] {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        /* Breadcrumbs responsivo */
        .breadcrumb {
            flex-wrap: wrap;
            font-size: 0.85rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            padding-left: 0.3rem;
            padding-right: 0.3rem;
        }
        
        /* Modales responsivos */
        .modal-dialog {
            margin: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        /* Alertas */
        .alert {
            font-size: 0.85rem;
            padding: 0.75rem;
        }
        
        /* Botones en modales apilados */
        .modal-footer {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .modal-footer .btn {
            width: 100%;
            margin: 0;
        }
        
        /* Botón de limpiar búsqueda hover mejorado */
        .btn-outline-secondary:hover {
            background-color: #737373 !important;
            border-color: #737373 !important;
        }
        .btn-outline-secondary:hover i {
            color: white !important;
        }
    }
    
    /* TABLETS (pantallas entre 768px y 1024px) */
    @media (min-width: 768px) and (max-width: 1024px) {
        /* Tarjetas de carpetas - 2 por fila en tablet */
        #folderContainer .row > [class*="col-"] {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        /* Header - botones en línea pero más compactos */
        .d-flex.justify-content-between .mt-2 .btn {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }
        
        /* Tamaño de fuente ajustado */
        .h3 {
            font-size: 1.75rem;
        }
        
        .h3 i {
            font-size: 2.5rem !important;
        }
    }
    
    /* PANTALLAS PEQUEÑAS (máximo 576px) */
    @media (max-width: 576px) {
        /* Contenedor más compacto */
        .container-fluid {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        
        /* Título aún más pequeño */
        .h3 {
            font-size: 1.25rem;
        }
        
        /* Botones muy pequeños */
        .btn {
            font-size: 0.8rem;
            padding: 0.4rem 0.75rem;
        }
        
        /* Iconos de carpetas más pequeños */
        .folder-icon i {
            font-size: 2.5rem !important;
        }
        
        .folder-card .card-body {
            min-height: 130px;
            padding: 0.75rem;
        }
        
        .card-title {
            font-size: 0.9rem;
        }
        
        /* Tabla - ocultar columnas menos importantes en móvil muy pequeño */
        .file-table th:nth-child(3),
        .file-table td:nth-child(3),
        .file-table th:nth-child(4),
        .file-table td:nth-child(4) {
            display: none;
        }
        
        /* Input group más compacto */
        .input-group-text,
        .input-group .form-control,
        .input-group .btn {
            font-size: 0.8rem;
        }
    }
    
    /* MEJORAS PARA LA TABLA (responsive con overflow) */
    .file-table-container {
        overflow-x: auto;
        width: 100%;
    }
    
    .file-table {
        min-width: 500px;
    }
    
    /* MEJORAS PARA EL GRID DE CARPETAS (responsive con grid) */
    #folderContainer .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -0.5rem;
        margin-left: -0.5rem;
    }
    
    #folderContainer .row > [class*="col-"] {
        padding-right: 0.5rem;
        padding-left: 0.5rem;
    }
    
    /* Ajustes para pantallas extra grandes (escritorio) */
    @media (min-width: 1400px) {
        #folderContainer .row > [class*="col-"] {
            flex: 0 0 20%;
            max-width: 20%;
        }
    }
    
    /* Mejora para dispositivos con pantalla muy ancha */
    @media (min-width: 1920px) {
        .container-fluid {
            max-width: 1800px;
            margin: 0 auto;
        }
    }
    
    /* Ajuste para orientación landscape en móviles */
    @media (max-width: 768px) and (orientation: landscape) {
        .folder-card .card-body {
            min-height: 140px;
        }
        
        #folderContainer .row > [class*="col-"] {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
    
    /* Ajustes para el buscador responsivo */
    @media (max-width: 768px) {
        .card-body.p-3 {
            padding: 1rem !important;
        }
        
        .form-label {
            font-size: 0.85rem;
        }
        
        .form-select {
            font-size: 0.85rem;
        }
    }

    /* Tooltips personalizados */
    [title] {
        position: relative;
        cursor: help;
    }
    
    .tooltip {
        --bs-tooltip-bg: #737373;
        --bs-tooltip-color: #ffffff;
        font-size: 0.875rem;
    }
    
    .tooltip .tooltip-inner {
        background-color: #737373;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .tooltip.bs-tooltip-top .tooltip-arrow::before {
        border-top-color: #737373;
    }
    
    .tooltip.bs-tooltip-bottom .tooltip-arrow::before {
        border-bottom-color: #737373;
    }
    
    .tooltip.bs-tooltip-start .tooltip-arrow::before {
        border-left-color: #737373;
    }
    
    .tooltip.bs-tooltip-end .tooltip-arrow::before {
        border-right-color: #737373;
    }
    /* FIX TOTAL ZOOM OUT - IGNORAR COMPLETAMENTE EL ZOOM */
    html {
        zoom: reset;
    }

    .container-fluid {
        min-width: 320px;
    }

    /* Congelar el header para que no se mueva */
    .row.mb-3:first-child {
        position: relative;
        width: 100%;
        display: block;
    }

    .row.mb-3:first-child .col-12 {
        width: 100%;
    }

    .row.mb-3:first-child .col-12 > div {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
    }

    .row.mb-3:first-child .col-12 > div > div:first-child {
        flex: 0 0 auto !important;
        width: auto !important;
    }

    .row.mb-3:first-child .col-12 > div > div:last-child {
        flex: 0 0 auto !important;
        width: auto !important;
        margin-left: auto !important;
    }

    .row.mb-3:first-child h1.h3 {
        font-size: 1.75rem !important;
        white-space: nowrap !important;
    }

    /* Evitar que cualquier cosa haga wrap */
    body, .container-fluid, .row, .col-12, .d-flex {
        max-width: 100%;
        overflow-x: visible;
    }

        /* FIX RESPONSIVO BOTONES EN MÓVIL */
    @media (max-width: 768px) {
        .row.mb-3:first-child .col-12 > div {
            flex-direction: column !important;
            flex-wrap: wrap !important;
            align-items: stretch !important;
        }

        .row.mb-3:first-child .col-12 > div > div:last-child {
            margin-left: 0 !important;
            width: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 0.5rem !important;
        }

        .row.mb-3:first-child .col-12 > div > div:last-child .btn {
            width: 100% !important;
            background-color: #4a6fa5 !important;
            border-color: #4a6fa5 !important;
        }

        .row.mb-3:first-child h1.h3 {
            white-space: normal !important;
        }
    }
</style>
@endpush

@prepend('scripts')
{{-- LIBRERÍA SWEETALERT2 PARA LAS ALERTAS DE CONFIRMACIÓN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // SI HAY UNA CARPETA SELECCIONADA, INICIALIZA EL BUSCADOR Y EL ORDENADOR
        @if(isset($currentFolder) && $currentFolder)
            initSearch();
            initSorting();
        @endif
        
        // ELIMINAR CUALQUIER MENSAJE DE ÉXITO DUPLICADO
        const successAlerts = document.querySelectorAll('.alert-success');
        if (successAlerts.length > 1) {
            for (let i = 1; i < successAlerts.length; i++) {
                successAlerts[i].remove();
            }
        }
    });

    // FUNCIONES PARA MODALES DE RENOMBRAR Y MOVER (ahora con envío tradicional)

    // ABRE EL MODAL DE RENOMBRAR CARPETA Y ASIGNA EL ACTION Y EL NOMBRE ACTUAL
    function openRenameModal(folderId, folderName) {
        event.stopPropagation();
        const form = document.getElementById('renameFolderForm');
        form.action = '/auditoria/competencias/folder/' + folderId + '/rename';
        document.getElementById('newFolderName').value = folderName;
        new bootstrap.Modal(document.getElementById('renameFolderModal')).show();
    }

    // ABRE EL MODAL DE RENOMBRAR DOCUMENTO Y ASIGNA EL ACTION Y EL NOMBRE ACTUAL
    function openRenameDocumentModal(docId, docName) {
        event.stopPropagation();
        const form = document.getElementById('renameDocumentForm');
        form.action = '/auditoria/competencias/document/' + docId + '/rename';
        document.getElementById('newDocumentName').value = docName;
        new bootstrap.Modal(document.getElementById('renameDocumentModal')).show();
    }

    // ABRE EL MODAL DE MOVER CARPETA Y CARGA LAS CARPETAS DISPONIBLES VÍA FETCH
    function openMoveModal(folderId, folderName) {
        event.stopPropagation();
        const form = document.getElementById('moveFolderForm');
        form.action = '/auditoria/competencias/folder/' + folderId + '/move';
        document.getElementById('moveFolderName').innerHTML = folderName;
        
        const select = document.getElementById('folderDestination');
        select.innerHTML = '<option value="">📁 Cargando carpetas...</option>';
        select.disabled = true;
        
        // CARGA EL ÁRBOL DE CARPETAS DISPONIBLES EXCLUYENDO LA CARPETA ACTUAL
        fetch('/auditoria/competencias/folders/tree?current_folder=' + folderId)
            .then(response => response.json())
            .then(folders => {
                select.innerHTML = '<option value="">📁 Raíz principal</option>';
                select.disabled = false;
                
                if (folders.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = '📁 No hay otras carpetas disponibles';
                    option.disabled = true;
                    select.appendChild(option);
                } else {
                    // AGREGA CADA CARPETA COMO OPCIÓN EN EL SELECT
                    folders.forEach(folder => {
                        const option = document.createElement('option');
                        option.value = folder.id;
                        option.textContent = '📁 ' + folder.full_path;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                // SI FALLA LA CARGA, MUESTRA ERROR EN EL SELECT Y EN UNA ALERTA
                console.error('Error al cargar carpetas:', error);
                select.innerHTML = '<option value="">❌ Error al cargar carpetas</option>';
                select.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la lista de carpetas',
                    confirmButtonColor: '#800000'
                });
            });
        
        new bootstrap.Modal(document.getElementById('moveFolderModal')).show();
    }

    // ABRE EL MODAL DE MOVER DOCUMENTO Y CARGA LAS CARPETAS DISPONIBLES VÍA FETCH
    function openMoveDocumentModal(docId, docName) {
        event.stopPropagation();
        const form = document.getElementById('moveDocumentForm');
        form.action = '/auditoria/competencias/document/' + docId + '/move';
        document.getElementById('moveDocumentName').innerHTML = docName;
        
        const select = document.getElementById('documentDestination');
        select.innerHTML = '<option value="">📁 Cargando carpetas...</option>';
        select.disabled = true;
        
        // CARGA EL ÁRBOL COMPLETO DE CARPETAS PARA EL DESTINO DEL DOCUMENTO
        fetch('/auditoria/competencias/folders/tree?current_folder=null')
            .then(response => response.json())
            .then(folders => {
                select.innerHTML = '<option value="">📁 Raíz principal</option>';
                select.disabled = false;
                
                if (folders.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = '📁 No hay otras carpetas disponibles';
                    option.disabled = true;
                    select.appendChild(option);
                } else {
                    // AGREGA CADA CARPETA COMO OPCIÓN EN EL SELECT
                    folders.forEach(folder => {
                        const option = document.createElement('option');
                        option.value = folder.id;
                        option.textContent = '📁 ' + folder.full_path;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                // SI FALLA LA CARGA, MUESTRA ERROR EN EL SELECT Y EN UNA ALERTA
                console.error('Error:', error);
                select.innerHTML = '<option value="">❌ Error al cargar carpetas</option>';
                select.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la lista de carpetas',
                    confirmButtonColor: '#800000'
                });
            });
        
        new bootstrap.Modal(document.getElementById('moveDocumentModal')).show();
    }

    // FUNCIÓN PARA ELIMINAR (SweetAlert, AJAX)
    // MUESTRA UNA ALERTA DE CONFIRMACIÓN ANTES DE ELIMINAR UN ARCHIVO O CARPETA
    function deleteElement(id, name, type) {
        event.stopPropagation();
        event.preventDefault();
        
        if (type === 'Documento') {
            // Diseño simple para archivos
            // ALERTA SIMPLE PARA ELIMINAR UN DOCUMENTO
            Swal.fire({
                title: '¿Eliminar archivo?',
                text: `¿Estás seguro de eliminar "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithDeletion(id, type, name);
                }
            });
        } else {
            // Diseño detallado para carpetas
            // ALERTA DETALLADA PARA ELIMINAR UNA CARPETA (ADVIERTE QUE SE ELIMINA TODO SU CONTENIDO)
            Swal.fire({
                title: '¿Eliminar ' + type.toLowerCase() + '?',
                html: `
                    <div style="text-align: left;">
                        <p style="font-size: 1.1rem; margin-bottom: 10px;">
                            <strong>${type === 'Carpeta' ? '📁' : '📄'} ${name}</strong>
                        </p>
                        <p style="color: #dc3545; font-weight: 500;">
                            ⚠️ Esta acción eliminará permanentemente:
                        </p>
                        <ul style="text-align: left; margin-bottom: 15px;">
                            <li>La ${type.toLowerCase()} <strong>"${name}"</strong></li>
                            ${type === 'Carpeta' ? '<li>Todas las subcarpetas dentro de ella</li>' : ''}
                            <li>Todos los archivos dentro ${type === 'Carpeta' ? 'de la carpeta' : ''}</li>
                        </ul>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithDeletion(id, type, name);
                }
            });
        }
        
        return false;
    }

    // Función auxiliar para realizar la eliminación (AJAX)
    // EJECUTA EL DELETE VÍA FETCH Y RECARGA LA PÁGINA SI FUE EXITOSO
    function proceedWithDeletion(id, type, name) {
        // MUESTRA UN SPINNER MIENTRAS SE PROCESA LA ELIMINACIÓN
        Swal.fire({
            title: 'Eliminando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // CONSTRUYE LA URL SEGÚN SI ES DOCUMENTO O CARPETA
        const url = '/auditoria/competencias/' + (type === 'Documento' ? 'document/' : 'folder/') + id;
        
        fetch(url, {
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
                // ELIMINACIÓN EXITOSA: MUESTRA MENSAJE Y RECARGA LA PÁGINA
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: data.message,
                    confirmButtonColor: '#000000',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                // ERROR DESDE EL SERVIDOR: MUESTRA EL MENSAJE DE ERROR
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al eliminar',
                    confirmButtonColor: '#000000'
                });
            }
        })
        .catch(error => {
            // ERROR DE CONEXIÓN: MUESTRA ALERTA GENÉRICA
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión',
                confirmButtonColor: '#000000'
            });
        });
    }

    // BUSCADOR
    // INICIALIZA LOS EVENTOS DEL INPUT DE BÚSQUEDA Y EL BOTÓN DE LIMPIAR
    function initSearch() {
        const searchInput = document.getElementById('searchInput');
        const clearButton = document.getElementById('clearSearch');
        
        if (searchInput) {
            // USA DEBOUNCE DE 300ms PARA NO BUSCAR EN CADA TECLA PRESIONADA
            searchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => performSearch(e.target.value), 300);
            });
        }
        
        if (clearButton) {
            // LIMPIA EL INPUT Y RESTAURA TODOS LOS ELEMENTOS VISIBLES
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                performSearch('');
                searchInput.focus();
            });
        }
    }

    let debounceTimer;
    
    // FILTRA CARPETAS Y ARCHIVOS EN TIEMPO REAL SEGÚN EL TEXTO INGRESADO
    function performSearch(query) {
        query = query.toLowerCase().trim();
        const folderCards = document.querySelectorAll('.folder-card');
        const fileRows = document.querySelectorAll('.file-row');
        let visibleCount = 0;
        
        // MUESTRA U OCULTA CADA TARJETA DE CARPETA SEGÚN EL NOMBRE
        folderCards.forEach(card => {
            const folderName = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const parentCol = card.closest('.col');
            if (parentCol) {
                if (query === '' || folderName.includes(query)) {
                    parentCol.style.display = '';
                    visibleCount++;
                } else {
                    parentCol.style.display = 'none';
                }
            }
        });
        
        // MUESTRA U OCULTA CADA FILA DE ARCHIVO SEGÚN EL NOMBRE
        fileRows.forEach(row => {
            const fileName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
            if (query === '' || fileName.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // ACTUALIZA EL CONTADOR DE RESULTADOS
        const resultCount = document.getElementById('resultCount');
        if (resultCount) {
            resultCount.textContent = query === '' ? '' : `🔍 ${visibleCount} resultado${visibleCount !== 1 ? 's' : ''}`;
        }
        
        // ELIMINA EL MENSAJE DE "SIN RESULTADOS" ANTERIOR SI EXISTE
        document.getElementById('noResultsMessage')?.remove();
        
        // SI NO HAY RESULTADOS, MUESTRA UN MENSAJE DE ADVERTENCIA
        if (query !== '' && visibleCount === 0) {
            const folderContainer = document.getElementById('folderContainer');
            const noResultsDiv = document.createElement('div');
            noResultsDiv.id = 'noResultsMessage';
            noResultsDiv.className = 'alert alert-warning d-flex align-items-center mt-3';
            noResultsDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> No se encontraron archivos o carpetas que coincidan con "<strong>${query}</strong>"`;
            if (folderContainer) folderContainer.after(noResultsDiv);
        }
    }

    // ORDENAMIENTO
    // INICIALIZA EL EVENTO DEL SELECT DE ORDENAMIENTO
    function initSorting() {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                sortItems(this.value);
            });
        }
    }
    
    // ORDENA LAS FILAS DE ARCHIVOS SEGÚN EL CRITERIO SELECCIONADO
    function sortItems(sortBy) {
        const tableBody = document.getElementById('fileTableBody');
        if (tableBody) {
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                const nameA = a.dataset.fileName || '';
                const nameB = b.dataset.fileName || '';
                const dateA = a.dataset.fileDate || '';
                const dateB = b.dataset.fileDate || '';
                const sizeA = parseInt(a.dataset.fileSize) || 0;
                const sizeB = parseInt(b.dataset.fileSize) || 0;
                
                // APLICA EL CRITERIO DE ORDENAMIENTO SELECCIONADO
                switch(sortBy) {
                    case 'name_asc': return nameA.localeCompare(nameB);
                    case 'name_desc': return nameB.localeCompare(nameA);
                    case 'date_desc': return new Date(dateB) - new Date(dateA);
                    case 'date_asc': return new Date(dateA) - new Date(dateB);
                    case 'size_desc': return sizeB - sizeA;
                    case 'size_asc': return sizeA - sizeB;
                    default: return 0;
                }
            });
            // REINSERTA LAS FILAS EN EL NUEVO ORDEN
            rows.forEach(row => tableBody.appendChild(row));
        }
    }
</script>
@endprepend