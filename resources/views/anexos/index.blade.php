{{--*****VISTA PRINCIPAL DEL MODULO DE ANEXOS*******--}}
{{--ESTE CODIGO ES EL DASHBOARD PRINCIPAL DEL MODULO DE ANEXOS QUE AL INGRESAR
ESTE ESTA INSPIRADO EN UNA JERARQUIA DE CARPETAS Y UN EXPLORADOR TIPO WINDOWS.
SE VISUALIZAN LAS CARPETAS Y ARCHIVOS PARA SU CONSULTA O ALMACENAMIENTO DE LOS MISMOS--}}

{{--USA EL ARCHIVO APP.BLADE.PHP PARA DETALLES DE COLOR Y DISEÑO DEL HEADER GENERAL EN DONDE SE ENCUENTRAN LAS OPCIONES QUE TIENE UN PERFIL DE USUARIO, FOOTER, ETC--}}
@extends('layouts.app')

{{--TITULO DE LA PAGINA QUE APARECE CUANDO AL INGRESAR AL MODULO--}}
@section('title', 'Anexos - Sistema de Gestión de la Calidad')
{{--MUESTRA EL CONTENIDO DEL  MODULO DE ANEXOS--}}
@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                {{--MUESTRA EL NOMBRE DEL MODULO CON UN ENLACE CON LA RUTA DE REGRESO AL DASHBOARD PRINCIPAL--}}
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #4f46e5; cursor: pointer;">
                            <i class="bi bi-folder me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                            Anexos
                        </h1>
                    </a>
                </div>

                {{-- SUPERADMIN Y ADMIN PUEDEN CREAR CARPETAS Y SUBIR ARCHIVOS --}}
                @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                <div class="mt-2">
                    <button type="button" class="btn text-white me-2" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="bi bi-folder-plus me-1"></i> Nueva Carpeta
                    </button>
                    
                    {{-- BOTÓN SUBIR ARCHIVO - SOLO APARECE DENTRO DE UNA CARPETA Y ESTA DISPONIBLE TANTO PARA ADMINS Y USUARIOS SOLO APARECE SI SE ESTA
                    DENTRO DE UNA CARPETA--}}
                    @if(isset($currentFolder) && $currentFolder)
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-upload me-1"></i> Subir Archivo
                        </button>
                    @else
                        <button type="button" class="btn text-white" style="background-color: #a9a9a9;" disabled>
                            <i class="bi bi-upload me-1"></i> Selecciona una carpeta
                        </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    {{--SE MANDA LLAMAR AL ARCHIVO BREADCRUBS PARA MOSTRAR LAS RUTAS DE CARPETAS DEBAJO DEL NOMBRE DEL MODULO SOLO CUANDO SE ESTA 
    DENTRO DE UNA CARPETA--}}
    <div class="mb-3">
        @include('anexos.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs, 'currentFolder' => $currentFolder])
    </div>

    {{--MENSAJES DE SESION APARECEN CUANDO UNA CARPETA ES CREADA, SE SUBE UN ARCHIVO SE LEIMINA ALGO, ETC--}}
    {{-- SOLO UN MENSAJE DE ÉXITO (el primero) --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessage">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    {{--MENSAJE DE ERROR--}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
{{--MENSAJE DE ADVERTENCIA--}}
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
{{--MENSAJE DE INFORMACION--}}
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- BUSCADOR Y ORDENAR - VISIBLE PARA TODOS DENTRO DE UNA CARPETA --}}
    @if(isset($currentFolder) && $currentFolder && $documents->count() > 0)
    <div class="row mb-4 align-items-end">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #000000;">
                        <i class="bi bi-search me-1"></i> Buscar archivos
                    </label>
                    {{--CONTENEDOR PARA SEPARAR EL BUSCADOR DE ARCHIVOS- Y LOS FILTROS DE BUSCAR POR--}}
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-color: #dee2e6;">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre de archivo" 
                               style="border-color: #dee2e6; background-color: white;">
                            {{--BOTON DE UNA "X" QUE ESTA EN EL BUSCADOR DE NOMBRE DE ARCHIVOS AL DARLE CLICK SE BORRA LO QUE
                            SE ESCRIBIO--}}   
                        <button class="btn btn-outline-secondary btn-clear-search" type="button" id="clearSearch" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    {{--MUESTRA CUANTOS RESULTADOS DE ARCHIVOS SE ENCONTRARON CON UN NOMBRE--}}
                    <div id="searchResults" class="mt-2 small text-muted">
                        <span id="resultCount"></span>
                    </div>
                </div>
            </div>
        </div>
        {{--CONTENEDOR DE LOS FILTROS DE ORDENAR POR FECHA ACTUAL, ANTIGUA, ETC--}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label fw-bold mb-2" style="color: #000000;">
                                <i class="bi bi-sort-down me-1"></i> Ordenar por
                            </label>
        {{--LISTA DE OPCIONES DE ORDENAR POR: NOMBRE A-Z Y Z-A, FECHA MAS RECIENTE Y MAS ANTIGUA, TAMAÑO (MAYOR Y MENOR)--}}
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

    {{-- INDICADOR DE CARGA, SE MUESTRA MIENTRAS SE CARGAN LOS ARCHIVOS --}}
    <div id="loadingSpinner" class="text-center my-5" style="display: none;">
        <div class="spinner-border" style="color: #800000;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 text-muted">Cargando archivos...</p>
    </div>

    {{-- CONTENEDOR DE CARPETAS --}}
    {{--SE MANDA LLAMAR EL ARCHIVO DE folder-grid QUE SE ENCUENTRA EN LA CAREPTA PARTIALS
    PARA QUE SE MUESTREN LAS SUBCARPETAS--}}
    <div id="folderContainer">
        @include('anexos.partials.folder-grid', [
            'folders' => $folders
        ])
    </div>

    {{-- CONTENEDOR DE ARCHIVOS --}}
    {{--SE MANDA LLAMAR AL ARCHIVO file-list.blade.php PARA QUE SE MUESTREN LOS ARCHIVOS EN
    TABLA DENTRO DE LAS CARPETAS --}}
    <div id="fileContainer">
        @include('anexos.partials.file-list', [
            'documents' => $documents,
            'currentFolder' => $currentFolder ?? null
        ])
    </div>
</div>

{{--******MODALES DESPLEGABLES(VENTANAS EMERGENTES********)--}}
{{-- MODALES DE VISUALIZACIÓN DE DOCUMENTOS (SOLO PARA EXTENSIONES VISIBLES),
SOLO SE PUEDEN VISUALIZAR ARCHIVOS PDF, IMAGENES, Y TXT --}}

@foreach($documents as $doc)
    @php
        $extension = strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION));
        $viewableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'txt'];
    @endphp
    
    @if(in_array($extension, $viewableExtensions))
    <div class="modal fade" id="viewDocumentModal{{ $doc->id }}" tabindex="-1" aria-labelledby="viewDocumentModalLabel{{ $doc->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDocumentModalLabel{{ $doc->id }}">
                        <i class="bi bi-file-earmark-text me-2" style="color: #000000;"></i>
                        {{ $doc->name }}.{{ pathinfo($doc->original_name, PATHINFO_EXTENSION) }}
                    </h5>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                {{--SE MANDA LLAMAR AL ARCHIVO document-viewer PARA QUE SE DESPLIEGUE EL MODAL DE VISUALIZACION--}}
                    @include('anexos.partials.document-viewer', [
                        'extension' => $extension,
                        'fileUrl' => route('anexos.ver.archivo', $doc->id)
                    ])
                </div>
                {{--PIE DEL MODAL (BOTONES QUE TIENE EL MODAL DEBAJO)--}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('anexos.document.download', $doc->id) }}" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-download me-1"></i> Descargar
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- MODALES PARA SUPERADMIN Y ADMIN (CREAR, RENOMBRAR, MOVER, ELIMINAR) --}}
{{--ESTOS BOTONES SOLO ESTAN DISPONIBLES PARA LOS ADMINS--}}
@if(in_array(Auth::user()->role, ['superadmin', 'admin']))
{{-- MODAL RENOMBRAR DOCUMENTO --}}
<div class="modal fade" id="renameDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="renameDocumentForm">
            @csrf
            @method('PUT')

            {{--MODAL DE RENOMBRAR ARCHIVO--}}
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
                        <input type="text" class="form-control" id="newDocumentName" name="name" required autofocus>
                        <div class="form-text">La extensión del archivo se mantendrá automáticamente.</div>
                    </div>
                </div>
                {{--BOTONES DE CANCELAR Y RENOMBRAR DEL PIE DEL MODAL--}}
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

{{-- MODAL MOVER DOCUMENTO --}}
<div class="modal fade" id="moveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
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
                {{--MUESTRA EL NOMBRE DEL DOCUMENTO A MOVER--}}
                    <p class="mb-3">
                        <span class="fw-bold">Documento a mover:</span><br>
                        <span id="moveDocumentName" style="color: #737373; font-size: 1.1rem;"></span>
                    </p>
                    {{--MUESTRA UNA LISTA DE LAS CAREPTAS CREADAS EN DONDE SE PUEDE MOVER EL ARCHIVO--}}
                    <div class="mb-3">
                        <label for="documentDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="documentDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Selecciona la carpeta donde deseas mover el documento.
                        </div>
                    </div>
                </div>
                {{--BOTON DE MOVER ARCHIVO--}}
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
{{--EL MODAL DE CREAR CARPETA MANADA LLAMAR AL METODO FOLDERSTORE DEL CONTROLADOR DE  PARA CREAR UNA NUEVA CARPETA
CON SU NOMBRE, Y COLOR DEL ICONO DEL FOLDER TAMBIEN FUNCIONA PARA CREAR SUBCARPETAS--}}
    <div class="modal-dialog">
        <form action="{{ route('anexos.folder.store') }}" method="POST">
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
                    <div class="mb-3">
                        <label class="form-label">Nombre de Carpeta</label>
                        <input type="text" class="form-control" name="name" required autofocus>
                    </div>
                    {{--MUESTRA UN PERSONALIZADOR DE COLOR DE CARPETA Y MUESTRA POR DEFECTO EL COLOR GUINDA (#800000)--}}
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

{{-- MODAL SUBIR ARCHIVO (con visualización de errores) --}}
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
    {{--EL MODAL PARA SUBIR ARCHIVO MANDA LLAMAR EL METODO UPLOAD DEL CONTROLADOR DE ANEXOSCONTROLLER PARA SUBIR ARCHIVOS--}}
        <form action="{{ route('anexos.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{--EL FORMULARIO TOMA EL ID DE LA CARPETA DE DONDE SE SUBIRA EL ARCHIVO PERO YA ESTANDO DENTRO DE LA CAREPTA DE DONDE SE
            SUBIRA--}} 
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-1" style="color: #000000;"></i>
                        Subir Archivo
                    </h5>
                </div>
                <div class="modal-body">
                {{--SELECCIONADOR DE ARCHIVOS ABRE LA VENTANA O SE DIRIGE AL EXPLORADOR DE ARCHIVOS PARA SELECCIONAR UN ARCHIVO Y SUBIRLO
                DE LO CONTRARIO MANDARA UN MENSAJE DE ERROR QUE TIENE QUE SELECCIONAR UN ARCHIVO OBLIGATORIAMENTE--}}
                    <div class="mb-3">
                        <label class="form-label">Seleccionar archivo</label>
                        <input class="form-control @error('file') is-invalid @enderror" type="file" name="file" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                {{--PIE DEL MODAL CON BOTONES DE SUBIR ARCHIVO Y CANCELAR--}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">Subir Archivo</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL RENOMBRAR CARPETA --}}
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
    {{--FORMULARIO PARA RENOMBRAR CARPETA--}}
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
                {{--CAMPO PARA ESCRIBIR EL NUEVO NOMBRE DE LA CARPETA--}}
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newFolderName" class="form-label fw-bold">Nuevo nombre</label>
                        <input type="text" class="form-control" id="newFolderName" name="name" required autofocus>
                    </div>
                </div>
                {{--BOTONES DEL PIE DEL MODAL--}}
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
    {{--FORMULARIO PARA MOVER UNA CARPETA--}}
        <form action="" method="POST" id="moveFolderForm">
        {{--@CSRF Y @METHOD SON DIRECTIVAS DE BLADE PARA GARANTIZAR LA SEGURIDAD Y EL FUNCIONAMIENTO CORRECTO PARA ACTUALIZAR DATOS--}}
        {{--USAMOS ESTOS METODOS PARA ACRTUALIZAR EL LUGAR EN DONDE SE ENCUENTRA UNA CARPETA--}}
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
                        <span id="moveFolderName" style="color: #737373; font-size: 1.1rem;"></span>
                    </p>
                    {{--ESTA PARTE MUESTRA LA LISTA DE CARPETAS EN DONDE SE SELECCIONA UNA CARPETA DE DESTINO--}}
                    <div class="mb-3">
                        <label for="folderDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="folderDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Selecciona la carpeta donde deseas mover.
                        </div>
                    </div>
                </div>
                {{--BOTONES DE PIE DEL MODAL--}}
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
@endif
{{--FIN DE LA SECCION DE CONTENIDO--}}
@endsection
{{--@PUSH('STYLES') SE USA PARA INSERTAR BLOQUES DE CSS Y  ESTILOS--}}
@push('styles')
<style>
    /* ========== ESTILOS DE LAS TARJETAS DE CARPETAS  ========== */
    .folder-card {
        transition: all 0.2s;/*ANIMACION DE MOVIMIENTO DE LAS CARDS */
        cursor: pointer;/*SE CREA LA ANIMACION AL PASAR EL PUNTERO*/
        border: none;
        border-radius: 12px;/*BORDES REDONDEADOS*/
    }
    .folder-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
    }
    /*TAMAÑOS DE ELEMENTOS DEL ICONO DE CARPETA*/
    .folder-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    /*ESTILOS DE LA TABLA ARCHIVOS*/
    .file-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    /*ESTILOS DE BREADCRUMB*/
    .breadcrumb-item a {
        text-decoration: none;
        color: #000000;
        font-weight: 500;
    }
    /*TAMAÑO DEL ICONO DE CARPETA*/
    .folder-icon i {
        font-size: 4rem;
    }
    .folder-card .card-body {
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    /*ESTILOS DE SWEETALERT2(VENTANAS DE CONFIRMACION)*/
    
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

    /* ESTILOS PARA BOTON DE LIMPIAR BUSQUEDA */
    .btn-clear-search:hover {
        background-color: #737373 !important;
        border-color: #737373 !important;
    }
    .btn-clear-search:hover i {
        color: white !important;
    }

    /* TOOLTIPS(MENSAJES QUE APARECEN AL PASAR EL PUNTERO) */
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

    /* ELIMINAR SEGUNDO MENSAJE DE ÉXITO DUPLICADO */
    .alert-success:not(:first-of-type) {
        display: none !important;
    }

    /* ========== ESTILOS RESPONSIVOS PARA CELULARES Y TABLETS ========== */
    
    /* MÓVILES (PANTALLAS MENORES A  768px) */
    @media (max-width: 768px) {
        /* HEADER - APILA LOS BOTONES DEBAJO DEL OTRO */
        .d-flex.justify-content-between {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 1rem;
        }
        /*ANCHO DE LOS BOTONES*/
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
        
        /*ESTILOS DE LOS TITULOS */
        .h3 {
            font-size: 1.5rem;
        }
        
        .h3 i {
            font-size: 2rem !important;
        }
        
        /* ESTILO PARA APILAR EL BUSCADOR Y FILTROS EN FORMA VERTICAL  */
        .row.mb-4 .col-md-6 {
            margin-bottom: 1rem;
        }
        
        /* ESTILOS PARA HACER LA TABLA RESPONSIVA Y PERMITIR LA VISUALIZACION HORIZONTAL AL ROTAR LA PANTALLA */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* TARJETAS DE CARPETAS - SE APILA 1 POR FILA EN EL MOVIL */
        #folderContainer .row > [class*="col-"] {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        /*ESTILOS PARA HACER QUE BREADCRUMBS SEA RESPONSIVO */
        .breadcrumb {
            flex-wrap: wrap;
            font-size: 0.85rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            padding-left: 0.3rem;
            padding-right: 0.3rem;
        }
        
        /* HACEN A LOS MODALES MAS RESPONSIVOS */
        .modal-dialog {
            margin: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        /* ESTILOS DE ALERTAS EN RESPONSIVO */
        .alert {
            font-size: 0.85rem;
            padding: 0.75rem;
        }
        
        /* BOTONES DE LOS MODALES APILADOS VERTICALMENTE */
        .modal-footer {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .modal-footer .btn {
            width: 100%;
            margin: 0;
        }
    }
    /*ESTILOS DE TABLET RESPONSIVO*/
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
  /* FIX ZOOM - CONTENEDOR OCUPA TODO EL ANCHO, CARPETAS MANTIENEN SU TAMAÑO */
.container-fluid {
    width: 100% !important;
    max-width: 100% !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
    margin: 0 auto !important;
}

body {
    overflow-x: hidden !important;
}

/* Esto evita que el grid de carpetas tenga márgenes que crean espacios */
#folderContainer .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}

/* Las carpetas mantienen su tamaño, solo se ajusta el contenedor */
#folderContainer [class*="col-"] {
    padding-left: 8px !important;
    padding-right: 8px !important;
}

</style>
@endpush
{{--FUNCIONLIDADES INTERACTIVAS CON JAVASCRIPT--}}
@prepend('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/*ESTE ES UN EVENTO QUE ACTIVA EL BUSCARDOR  EL FILTRO DE ORDENAR POR EN TIEMPO REAL */
    document.addEventListener('DOMContentLoaded', function() {
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

    // ============================================
    // FUNCIONES PARA DOCUMENTOS (SOLO SUPERADMIN/ADMIN)
    // ============================================
    @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
    //ESTA FUNCION ABRE EL MODAL DE RENOIMBRAR ARCHIVO O DOCUMENTO
    function openRenameDocumentModal(docId, docName) {
        event.stopPropagation();
        const form = document.getElementById('renameDocumentForm');
        form.action = '/anexos/document/' + docId + '/rename';
        document.getElementById('newDocumentName').value = docName;
        new bootstrap.Modal(document.getElementById('renameDocumentModal')).show();
    }
    //ABRE EL MODAL DE MOVER DOCUMENTOS DE CARPETAS
    function openMoveDocumentModal(docId, docName) {
        event.stopPropagation();
        const form = document.getElementById('moveDocumentForm');
        form.action = '/anexos/document/' + docId + '/move';
        document.getElementById('moveDocumentName').innerHTML = docName;
        
        const select = document.getElementById('documentDestination');
        select.innerHTML = '<option value="">📁 Cargando carpetas...</option>';
        select.disabled = true;
        
        fetch('/anexos/folders/tree?current_folder={{ $currentFolder->id ?? 'null' }}')
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
                    folders.forEach(folder => {
                        const option = document.createElement('option');
                        option.value = folder.id;
                        option.textContent = '📁 ' + folder.full_path;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
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
//ABRE EL MODAL PARA RENOMBRAR UNA CARPETA
    function openRenameModal(folderId, folderName) {
        event.stopPropagation();
        const form = document.getElementById('renameFolderForm');
        form.action = '/anexos/folder/' + folderId + '/rename';
        document.getElementById('newFolderName').value = folderName;
        new bootstrap.Modal(document.getElementById('renameFolderModal')).show();
    }
//ABRE EL MODAL DE MOVER CARPETA
    function openMoveModal(folderId, folderName) {
        event.stopPropagation();
        const form = document.getElementById('moveFolderForm');
        form.action = '/anexos/folder/' + folderId + '/move';
        document.getElementById('moveFolderName').innerHTML = folderName;
        
        const select = document.getElementById('folderDestination');
        select.innerHTML = '<option value="">📁 Cargando carpetas...</option>';
        select.disabled = true;
        
        fetch('/anexos/folders/tree?current_folder=' + folderId)
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
                    folders.forEach(folder => {
                        const option = document.createElement('option');
                        option.value = folder.id;
                        option.textContent = '📁 ' + folder.full_path;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
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
        
        new bootstrap.Modal(document.getElementById('moveFolderModal')).show();
    }
//SU FUNCION ES DE ELIMINAR UN DOCUMENTO O CARPETA
    function deleteElement(id, name, type) {
        event.stopPropagation();
        
        if (type === 'Documento') {
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
//PROCEDE CON LA ELIMINACION  CON AJAX(RECIBIR DATOS DEL SERVIDOR EN SEGUNDO PLANO SIN RECARGAR LA PAGINA)
    function proceedWithDeletion(id, type, name) {
        Swal.fire({
            title: 'Eliminando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const url = '/anexos/' + (type === 'Documento' ? 'document/' : 'folder/') + id;
        
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al eliminar',
                    confirmButtonColor: '#000000',
                    showConfirmButton: false 
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión',
                confirmButtonColor: '#000000',
                showConfirmButton: false 
            });
        });
    }
    @endif

    // ============================================
    // BUSCADOR EN TIEMPO REAL (TODOS)
    // ============================================
    function initSearch() {
        const searchInput = document.getElementById('searchInput');
        const clearButton = document.getElementById('clearSearch');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => performSearch(e.target.value), 300);
            });
        }
        
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                performSearch('');
                searchInput.focus();
            });
        }
    }

    let debounceTimer;//TEMPORIZADOR PARA NO BUSCAR EN CADA LETRA

    function performSearch(query) {
        query = query.toLowerCase().trim();
        const folderCards = document.querySelectorAll('.folder-card');
        const fileRows = document.querySelectorAll('.file-row');
        let visibleCount = 0;
        
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
        //FILTRAR ARCHIVOS
        fileRows.forEach(row => {
            const fileName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
            if (query === '' || fileName.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        //MUESTRA RESULTADOS ENCONTRADOS
        const resultCount = document.getElementById('resultCount');
        if (resultCount) {
            resultCount.textContent = query === '' ? '' : `🔍 ${visibleCount} resultado${visibleCount !== 1 ? 's' : ''}`;
        }
        
        document.getElementById('noResultsMessage')?.remove();
        //SI NO HAY RESULTADOS ENCONTRADOS CON EL NOMBRE DE UN ARCHIVO MUESTRA MENSAJE DE NO ENCONTRADO
        if (query !== '' && visibleCount === 0) {
            const folderContainer = document.getElementById('folderContainer');
            const noResultsDiv = document.createElement('div');
            noResultsDiv.id = 'noResultsMessage';
            noResultsDiv.className = 'alert alert-warning d-flex align-items-center mt-3';
            noResultsDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> No se encontraron archivos o carpetas que coincidan con "<strong>${query}</strong>"`;
            if (folderContainer) folderContainer.after(noResultsDiv);
        }
    }

    // ============================================
    // ORDENAMIENTO (TODOS)
    // ============================================
    function initSorting() {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                sortItems(this.value);
            });
        }
    }
    //SU FUNCION ES LA DE ORDENAR LOS DOCUMENTOS POR ALGUNA DE LAS OPCIONES DE NOMBRE, TAMAÑO ETC.
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
            rows.forEach(row => tableBody.appendChild(row));
        }
    }
</script>
@endprepend