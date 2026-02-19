@extends('layouts.app')

@section('title', 'Formatos - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #800000; cursor: pointer;">
                            <i class="bi bi-file-earmark-text me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                            FORMATOS
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
        @include('formatos.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs, 'currentFolder' => $currentFolder])
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SI HAY UNA CARPETA SELECCIONADA, MOSTRAR BUSCADOR Y ORDENADOR --}}
    @if(isset($currentFolder) && $currentFolder)
    <div class="row mb-4 align-items-end">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #800000;">
                        <i class="bi bi-search me-1"></i> Buscar formatos
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-color: #dee2e6;">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre de formato" 
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
        <p class="mt-2 text-muted">Cargando formatos...</p>
    </div>

    {{-- CONTENEDOR DE CARPETAS --}}
    <div id="folderContainer">
        @include('formatos.partials.folder-grid', ['folders' => $folders])
    </div>

    {{-- CONTENEDOR DE FORMATOS --}}
    <div id="fileContainer">
        @include('formatos.partials.formatos-list', ['documents' => $documents, 'currentFolder' => $currentFolder ?? null])
    </div>
</div>

{{-- MODALES --}}
@include('formatos.modals.create-folder', ['currentFolder' => $currentFolder ?? null])
@include('formatos.modals.upload-file', ['currentFolder' => $currentFolder ?? null])
@include('formatos.modals.edit-document')
@include('formatos.modals.move-document')
@include('formatos.modals.view-document', ['documents' => $documents])

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
    .formato-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .breadcrumb-item a {
        text-decoration: none;
        color: #16a34a;
        font-weight: 500;
    }
    .formato-card {
        transition: all 0.2s;
        border: none;
        border-radius: 12px;
    }
    .formato-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
    }
    /* ESTILOS PARA IGUALAR EL TAMAÑO DE ICONOS */
    .folder-icon i {
        font-size: 4rem;
    }
    .folder-card .card-body {
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        form.action = '/formatos/document/' + docId + '/rename';
        document.getElementById('newDocumentName').value = docName;
        new bootstrap.Modal(document.getElementById('renameDocumentModal')).show();
    }

    function openMoveDocumentModal(docId, docName) {
        event.stopPropagation();
        const form = document.getElementById('moveDocumentForm');
        form.action = '/formatos/document/' + docId + '/move';
        document.getElementById('moveDocumentName').innerHTML = docName;
        
        const select = document.getElementById('documentDestination');
        select.innerHTML = '<option value="">📁 Cargando carpetas...</option>';
        select.disabled = true;
        
        fetch('/formatos/folders/tree?current_folder={{ $currentFolder->id ?? 'null' }}')
            .then(response => response.json())
            .then(folders => {
                select.innerHTML = '<option value="">📁 Raíz principal</option>';
                select.disabled = false;
                
                // Función para crear la indentación visual
                function getIndent(level) {
                    return ' '.repeat(level) + '└─ ';
                }
                
                folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    // Crear una representación visual de la jerarquía
                    const indent = folder.level > 0 ? getIndent(folder.level) : '';
                    option.textContent = '📁 ' + indent + folder.name;
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
    let debounceTimer;
    
    function initSearch() {
        const searchInput = document.getElementById('searchInput');
        const clearButton = document.getElementById('clearSearch');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => performSearch(e.target.value), 300);
            });

            // Búsqueda al presionar Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    performSearch(e.target.value);
                }
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

    function performSearch(query) {
        query = query.toLowerCase().trim();
        const formatRows = document.querySelectorAll('.formato-row');
        let visibleCount = 0;
        
        formatRows.forEach(row => {
            const formatName = row.querySelector('.formato-nombre')?.textContent.toLowerCase() || '';
            if (query === '' || formatName.includes(query)) {
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
        
        // Eliminar mensaje anterior si existe
        document.getElementById('noResultsMessage')?.remove();
        
        // Mostrar mensaje si no hay resultados
        if (query !== '' && visibleCount === 0) {
            const fileContainer = document.getElementById('fileContainer');
            const noResultsDiv = document.createElement('div');
            noResultsDiv.id = 'noResultsMessage';
            noResultsDiv.className = 'alert alert-warning d-flex align-items-center mt-3';
            noResultsDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> No se encontraron formatos que coincidan con "<strong>${query}</strong>"`;
            if (fileContainer) fileContainer.appendChild(noResultsDiv);
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
        const tableBody = document.getElementById('formatosTableBody');
        if (!tableBody) return;
        
        const rows = Array.from(tableBody.querySelectorAll('tr.formato-row'));
        
        // Filtrar solo las filas que tienen datos
        const dataRows = rows.filter(row => row.dataset.fileName !== undefined);
        
        dataRows.sort((a, b) => {
            const nameA = a.dataset.fileName || '';
            const nameB = b.dataset.fileName || '';
            const dateA = a.dataset.fileDate || '';
            const dateB = b.dataset.fileDate || '';
            const sizeA = parseInt(a.dataset.fileSize) || 0;
            const sizeB = parseInt(b.dataset.fileSize) || 0;
            
            switch(sortBy) {
                case 'name_asc':
                    return nameA.localeCompare(nameB);
                case 'name_desc':
                    return nameB.localeCompare(nameA);
                case 'date_desc':
                    return new Date(dateB) - new Date(dateA);
                case 'date_asc':
                    return new Date(dateA) - new Date(dateB);
                case 'size_desc':
                    return sizeB - sizeA;
                case 'size_asc':
                    return sizeA - sizeB;
                default:
                    return 0;
            }
        });
        
        // Reordenar las filas en el DOM
        dataRows.forEach(row => tableBody.appendChild(row));
        
        // Aplicar el filtro de búsqueda actual después de ordenar
        const searchInput = document.getElementById('searchInput');
        if (searchInput && searchInput.value) {
            performSearch(searchInput.value);
        }
    }

    // ============================================
    // 4. FUNCIÓN PARA ACTUALIZAR VISTA (útil para llamadas AJAX)
    // ============================================
    window.refreshView = function() {
        @if(isset($currentFolder) && $currentFolder)
            const searchInput = document.getElementById('searchInput');
            if (searchInput && searchInput.value) {
                performSearch(searchInput.value);
            }
            
            const sortSelect = document.getElementById('sortSelect');
            if (sortSelect && sortSelect.value) {
                sortItems(sortSelect.value);
            }
        @endif
    };
</script>
@endpush