@extends('layouts.app')

@section('title', 'Anexos - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #800000; cursor: pointer;">
                            <i class="bi bi-folder me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                            Anexos
                        </h1>
                    </a>
                </div>

                <div class="mt-2">
                    <button type="button" class="btn text-white me-2" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="bi bi-folder-plus me-1"></i> Nueva Carpeta
                    </button>
                    
                    {{-- BOTÓN SUBIR ARCHIVO - SOLO APARECE DENTRO DE UNA CARPETA --}}
                    @if(isset($currentFolder) && $currentFolder)
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-upload me-1"></i> Subir Archivo
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        @include('anexos.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs, 'currentFolder' => $currentFolder])
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SI HAY UNA CARPETA SELECCIONADA, MOSTRAR BUSCADOR Y ORDENAR --}}
    @if(isset($currentFolder) && $currentFolder)
    <div class="row mb-4 align-items-end">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #800000;">
                        <i class="bi bi-search me-1"></i> Buscar archivos
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-color: #dee2e6;">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre de archivo" 
                               style="border-color: #dee2e6; background-color: white;">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div id="searchResults" class="mt-2 small text-muted">
                        <span id="resultCount"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label fw-bold mb-2" style="color: #800000;">
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

    {{-- INDICADOR DE CARGA --}}
    <div id="loadingSpinner" class="text-center my-5" style="display: none;">
        <div class="spinner-border" style="color: #800000;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 text-muted">Cargando archivos...</p>
    </div>

    {{-- CONTENEDOR DE CARPETAS --}}
    <div id="folderContainer">
        @include('anexos.partials.folder-grid', ['folders' => $folders])
    </div>

    {{-- CONTENEDOR DE ARCHIVOS --}}
    <div id="fileContainer">
        @include('anexos.partials.file-list', ['documents' => $documents,'currentFolder'=> $currentFolder ?? null])
    </div>
</div>

{{-- MODALES DE VISUALIZACIÓN DE DOCUMENTOS --}}
@foreach($documents as $doc)
    @php
        $extension = strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION));
    @endphp
    
    @if(!in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
    <div class="modal fade" id="viewDocumentModal{{ $doc->id }}" tabindex="-1" aria-labelledby="viewDocumentModalLabel{{ $doc->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDocumentModalLabel{{ $doc->id }}">
                        <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                        {{ $doc->name }}.{{ pathinfo($doc->original_name, PATHINFO_EXTENSION) }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg']))
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <img src="{{ route('anexos.ver.archivo', $doc->id) }}" class="img-fluid" alt="{{ $doc->name }}" style="max-height: 100%; object-fit: contain;">
                        </div>
                    @elseif(in_array($extension, ['pdf']))
                        <iframe src="{{ route('anexos.ver.archivo', $doc->id) }}" style="width: 100%; height: 100%; border: none;"></iframe>
                    @elseif(in_array($extension, ['txt']))
                        <iframe src="{{ route('anexos.ver.archivo', $doc->id) }}" style="width: 100%; height: 100%; border: none;"></iframe>
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center h-100">
                            <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                            <p class="mt-3">Vista previa no disponible para este tipo de archivo</p>
                            <a href="{{ route('anexos.document.download', $doc->id) }}" class="btn text-white mt-2" style="background-color: #800000;">
                                <i class="bi bi-download me-1"></i> Descargar para ver
                            </a>
                        </div>
                    @endif
                </div>
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
{{-- MODAL RENOMBRAR DOCUMENTO --}}
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

{{-- MODAL MOVER DOCUMENTO --}}
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
        <form action="{{ route('anexos.folder.store') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Carpeta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de Carpeta</label>
                        <input type="text" class="form-control" name="name" required autofocus>
                    </div>
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

{{-- MODAL SUBIR ARCHIVO --}}
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('anexos.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar archivo</label>
                        <input class="form-control" type="file" name="file" required>
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
    .folder-card {
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        border-radius: 12px;
    }
    .folder-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
    }
    .folder-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    .file-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .breadcrumb-item a {
        text-decoration: none;
        color: #800000;
        font-weight: 500;
    }
    /* ESTILOS PARA IGUALAR EL TAMAÑO DE ICONOS CON GESTIÓN DOCUMENTAL */
    .folder-icon i {
        font-size: 4rem;
    }
    /* ESTILO ADICIONAL PARA QUE LAS CARPETAS TENGAN LA MISMA ALTURA */
    .folder-card .card-body {
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>
@endpush

{{-- CAMBIO CLAVE: usar @prepend en lugar de @push --}}
@prepend('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar SOLO si estamos dentro de una carpeta
        @if(isset($currentFolder) && $currentFolder)
            initSearch();
            initSorting();
        @endif
    });

    // ============================================
    // 1. FUNCIONES PARA MODALES DE DOCUMENTOS
    // ============================================
    function openRenameDocumentModal(docId, docName) {
        event.stopPropagation();
        const form = document.getElementById('renameDocumentForm');
        form.action = '/anexos/document/' + docId + '/rename';
        document.getElementById('newDocumentName').value = docName;
        new bootstrap.Modal(document.getElementById('renameDocumentModal')).show();
    }

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
                folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    option.textContent = '📁 ' + folder.full_path;
                    select.appendChild(option);
                });
            })
            .catch(() => {
                select.innerHTML = '<option value="">❌ Error al cargar carpetas</option>';
                select.disabled = false;
            });
        
        new bootstrap.Modal(document.getElementById('moveDocumentModal')).show();
    }

    // ============================================
    // 2. BUSCADOR EN TIEMPO REAL
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

    let debounceTimer;
    
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
        
        fileRows.forEach(row => {
            const fileName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
            if (query === '' || fileName.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        const resultCount = document.getElementById('resultCount');
        if (resultCount) {
            resultCount.textContent = query === '' ? '' : `🔍 ${visibleCount} resultado${visibleCount !== 1 ? 's' : ''}`;
        }
        
        document.getElementById('noResultsMessage')?.remove();
        
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
    // 3. ORDENAMIENTO
    // ============================================
    function initSorting() {
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                sortItems(this.value);
            });
        }
    }
    
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