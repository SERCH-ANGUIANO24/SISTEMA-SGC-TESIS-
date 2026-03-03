@extends('layouts.app')

@section('title', 'Matriz - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #800000; cursor: pointer;">
                            <i class="bi bi-grid-3x3 me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                            Matriz
                        </h1>
                    </a>
                </div>

                {{-- SOLO SUPERADMIN Y ADMIN PUEDEN CREAR CARPETAS Y SUBIR ARCHIVOS --}}
                @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
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
                @endif
            </div>
        </div>
    </div>

    <div class="mb-3">
        @include('matriz.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs, 'currentFolder' => $currentFolder])
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

    {{-- BUSCADOR Y ORDENAR - VISIBLE PARA TODOS DENTRO DE UNA CARPETA --}}
    @if(isset($currentFolder) && $currentFolder)
    <div class="row mb-4 align-items-end">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #800000;">
                        <i class="bi bi-search me-1"></i> Buscar matrices
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-color: #dee2e6;">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre de matriz" 
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
        <p class="mt-2 text-muted">Cargando matrices...</p>
    </div>

    {{-- CARPETAS --}}
    <div id="folderContainer">
        @include('matriz.partials.folder-grid', ['folders' => $folders])
    </div>

    {{-- MATRICES --}}
    <div id="documentContainer">
        @include('matriz.partials.document-table', [
            'documents' => $documents,
            'currentFolder' => $currentFolder ?? null
        ])
    </div>
</div>

{{-- MODALES --}}
@include('matriz.modals.create-folder', ['currentFolder' => $currentFolder ?? null])
@include('matriz.modals.upload-file', ['currentFolder' => $currentFolder ?? null])
@include('matriz.modals.edit-document')
@include('matriz.modals.move-document')
@include('matriz.modals.view-document', ['documents' => $documents])

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
    .folder-icon i {
        font-size: 4rem;
    }
    .folder-card .card-body {
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
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
</style>
@endpush

@prepend('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($currentFolder) && $currentFolder)
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
        
        documentRows.forEach(row => {
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
            noResultsDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> No se encontraron matrices o carpetas que coincidan con "<strong>${query}</strong>"`;
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
                sortItems(this.value);
            });
        }
    }
    
    function sortItems(sortBy) {
        const tableBody = document.getElementById('fileTableBody');
        if (!tableBody) {
            const table = document.querySelector('.table tbody');
            if (table) table.id = 'fileTableBody';
        }
        
        const tbody = document.getElementById('fileTableBody') || document.querySelector('.table tbody');
        
        if (tbody) {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                const nameA = a.dataset.fileName || a.querySelector('td:first-child')?.textContent?.toLowerCase() || '';
                const nameB = b.dataset.fileName || b.querySelector('td:first-child')?.textContent?.toLowerCase() || '';
                
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
            
            rows.forEach(row => tbody.appendChild(row));
        }
    }
</script>
@endprepend