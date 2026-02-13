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
                    <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                        <i class="bi bi-upload me-1"></i> Subir Archivo
                    </button>
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

    @if(isset($currentFolder) && $currentFolder)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #800000;">
                        <i class="bi bi-search me-1"></i> Buscar archivos
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" 
                               placeholder="Buscar por nombre...">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
    @if(isset($currentFolder) && $currentFolder)
        initSearch();
    @endif
});

// Búsqueda en tiempo real
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
    const rows = document.querySelectorAll('.document-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const fileName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
        if (query === '' || fileName.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endpush