@extends('layouts.app')

@section('title', 'Gestión Documental - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #800000; cursor: pointer;">
                            <i class="bi bi-files me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                            Gestión Documental
                        </h1>
                    </a>
                </div>

                <div class="mt-2">
                    <button type="button" class="btn text-white me-2" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="bi bi-folder-plus me-1"></i> Nueva Carpeta
                    </button>
                    
                    {{-- Botón Subir Archivo - Solo visible en subcarpetas --}}
                    @if(isset($currentFolder) && $currentFolder && $currentFolder->parent_id !== null)
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-upload me-1"></i> Subir Archivo
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        @include('documental.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs, 'currentFolder' => $currentFolder])
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- BUSCADOR Y ORDENAR - SOLO EN SUBCARPETAS --}}
    @if(isset($currentFolder) && $currentFolder && $currentFolder->parent_id !== null)
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

    {{-- CARPETAS --}}
    <div id="folderContainer">
        @include('documental.partials.folder-grid', ['folders' => $folders])
    </div>

    {{-- DOCUMENTOS --}}
    <div id="documentContainer">
        @include('documental.partials.document-table', [
            'documents' => $documents,
            'currentFolder' => $currentFolder ?? null
        ])
    </div>
</div>

{{-- MODALES --}}
@include('documental.modals.view-document', ['documents' => $documents])
@include('documental.modals.edit-document')
@include('documental.modals.move-document')
@include('documental.modals.create-folder', ['currentFolder' => $currentFolder ?? null])
@include('documental.modals.upload-file', ['currentFolder' => $currentFolder ?? null])

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
    .document-row:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .breadcrumb-item a {
        text-decoration: none;
        color: #800000;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar SOLO si estamos dentro de una subcarpeta
    @if(isset($currentFolder) && $currentFolder && $currentFolder->parent_id !== null)
        initSearch();
        initSorting();
    @endif
});

// ============================================
// 1. BUSCADOR EN TIEMPO REAL
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
    const folderCards = document.querySelectorAll('.folder-card');
    const documentRows = document.querySelectorAll('.document-row');
    let visibleCount = 0;
    
    // Buscar en carpetas
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
    
    // Buscar en documentos
    documentRows.forEach(row => {
        const fileName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
        if (query === '' || fileName.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Actualizar contador de resultados
    const resultCount = document.getElementById('resultCount');
    if (resultCount) {
        resultCount.textContent = query === '' ? '' : `🔍 ${visibleCount} resultado${visibleCount !== 1 ? 's' : ''}`;
    }
    
    // Mostrar mensaje si no hay resultados
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
// 2. ORDENAMIENTO
// ============================================
function initSorting() {
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortDocuments(this.value);
        });
    }
}

function sortDocuments(sortBy) {
    const tableBody = document.querySelector('table tbody');
    if (tableBody) {
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const nameA = a.dataset.fileName || a.querySelector('td:first-child')?.textContent || '';
            const nameB = b.dataset.fileName || b.querySelector('td:first-child')?.textContent || '';
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

// ============================================
// 3. VALIDACIÓN ADICIONAL PARA EL MODAL DE SUBIR ARCHIVO
// ============================================
// Esta función opcional puede ser útil si quieres asegurarte
// de que el modal solo se pueda abrir en subcarpetas
function validateUploadModal() {
    const uploadButton = document.querySelector('[data-bs-target="#uploadFileModal"]');
    if (uploadButton) {
        uploadButton.addEventListener('click', function(e) {
            @if(!(isset($currentFolder) && $currentFolder && $currentFolder->parent_id !== null))
                e.preventDefault();
                alert('Solo puedes subir archivos dentro de subcarpetas.');
            @endif
        });
    }
}
</script>
@endpush