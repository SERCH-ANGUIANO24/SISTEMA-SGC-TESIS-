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
                    @if(in_array($userRole, ['superadmin', 'admin']))
                        <button type="button" class="btn text-white me-2" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                            <i class="bi bi-folder-plus me-1"></i> Nueva Carpeta
                        </button>
                    @endif

                    @php
                        $hayArchivosDeAdmin   = isset($documents) ? $documents->contains(fn($d) => in_array($d->user->role ?? null, ['superadmin', 'admin'])) : false;
                        $hayArchivosDeUsuario = isset($documents) ? $documents->contains(fn($d) => !in_array($d->user->role ?? null, ['superadmin', 'admin'])) : false;
                    @endphp

                    {{-- Usuarios: solo en subcarpetas Y solo si NO hay archivos subidos por admin --}}
                    @if(!in_array($userRole, ['superadmin', 'admin']) && isset($currentFolder) && $currentFolder && $currentFolder->parent_id !== null && !$hayArchivosDeAdmin)
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-upload me-1"></i> Subir Archivo
                        </button>
                    @endif

                    {{-- Admin/Superadmin: en cualquier carpeta Y solo si NO hay archivos subidos por usuarios --}}
                    @if(in_array($userRole, ['superadmin', 'admin']) && isset($currentFolder) && $currentFolder && !$hayArchivosDeUsuario)
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

    {{-- ============================================================
         CONTROLES: visibles SIEMPRE que se esté dentro de una carpeta
         (para todos los roles)
         ============================================================ --}}
    @if(isset($currentFolder) && $currentFolder && $currentFolder->parent_id !== null)
    @php
        $hasAdminDocs = $versionesUnicas->count() > 0
                     || $codigosUnicos->count() > 0
                     || $clavesUnicas->count() > 0;
    @endphp

    {{-- FILA 1: Buscar + Ordenar (siempre visibles juntos) --}}
    <div class="row mb-3 align-items-stretch">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #800000;">
                        <i class="bi bi-search me-1"></i> Buscar archivos
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-color: #dee2e6;">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" id="searchInput"
                               class="form-control border-start-0 ps-0"
                               placeholder="Buscar por nombre de archivo"
                               style="border-color: #dee2e6; background-color: white;">
                        <button class="btn btn-outline-secondary" type="button"
                                id="clearSearch" title="Limpiar búsqueda"
                                onclick="limpiarBuscador()">
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
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
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

    {{-- FILA 2: Filtros de campo (solo si hay documentos subidos por admin) --}}
    @if($hasAdminDocs)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #800000;">
                        <i class="bi bi-funnel me-1"></i> Filtrar por campo específico
                    </label>
                    <form method="GET" action="{{ route('documental.index') }}" id="form-filtros">
                        <input type="hidden" name="folder" value="{{ $currentFolder->id }}">

                        <div class="d-flex gap-2 flex-wrap">
                            <select id="select-tipo-campo"
                                    class="form-select"
                                    onchange="cambiarTipoCampo(this.value)"
                                    style="flex: 0 0 210px; max-width: 210px; border: 1px solid #dee2e6;">
                                <option value="">— Elegir campo —</option>
                                <option value="version" {{ request('version') ? 'selected' : '' }}>📋 Versión</option>
                                <option value="codigo"  {{ request('codigo')  ? 'selected' : '' }}>🔢 Código de procedimiento</option>
                                <option value="clave"   {{ request('clave')   ? 'selected' : '' }}>🔑 Clave de formato</option>
                            </select>

                            <select id="select-valor-campo"
                                    name="filtro_valor"
                                    class="form-select"
                                    {{ !(request('version') || request('codigo') || request('clave')) ? 'disabled' : '' }}
                                    style="flex: 1; min-width: 180px; max-width: 300px; border: 1px solid #dee2e6;">
                                <option value="">— Primero elige un campo —</option>
                                @foreach($versionesUnicas as $v)
                                    <option value="version:{{ $v }}" data-tipo="version"
                                        {{ request('version') == $v ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                                @foreach($codigosUnicos as $c)
                                    <option value="codigo:{{ $c }}" data-tipo="codigo"
                                        {{ request('codigo') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                                @foreach($clavesUnicas as $cl)
                                    <option value="clave:{{ $cl }}" data-tipo="clave"
                                        {{ request('clave') == $cl ? 'selected' : '' }}>{{ $cl }}</option>
                                @endforeach
                            </select>

                            <input type="hidden" name="version" id="hidden-version" value="{{ request('version') }}">
                            <input type="hidden" name="codigo"  id="hidden-codigo"  value="{{ request('codigo') }}">
                            <input type="hidden" name="clave"   id="hidden-clave"   value="{{ request('clave') }}">

                            <button type="submit" class="btn px-3"
                                    style="background: #800000; color: white; white-space: nowrap; border: none;">
                                Aplicar
                            </button>

                            @if(request('version') || request('codigo') || request('clave'))
                                <a href="{{ route('documental.index', ['folder' => $currentFolder->id]) }}"
                                   class="btn btn-outline-secondary px-3" title="Limpiar filtro">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>

                        {{-- Badges filtros activos --}}
                        @if(request('version') || request('codigo') || request('clave'))
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @if(request('version'))
                                <span class="badge rounded-pill"
                                      style="background:#e8f7ee;color:#1a6b3a;border:1px solid #b8e6c9;font-size:0.78rem;">
                                    Versión: {{ request('version') }}
                                    <a href="{{ route('documental.index', ['folder' => $currentFolder->id]) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('codigo'))
                                <span class="badge rounded-pill"
                                      style="background:#e8f7ee;color:#1a6b3a;border:1px solid #b8e6c9;font-size:0.78rem;">
                                    Código: {{ request('codigo') }}
                                    <a href="{{ route('documental.index', ['folder' => $currentFolder->id]) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('clave'))
                                <span class="badge rounded-pill"
                                      style="background:#e8f7ee;color:#1a6b3a;border:1px solid #b8e6c9;font-size:0.78rem;">
                                    Clave: {{ request('clave') }}
                                    <a href="{{ route('documental.index', ['folder' => $currentFolder->id]) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="mb-4"></div>
    @endif

    @endif {{-- fin @if(isset($currentFolder) && $currentFolder) --}}

    {{-- INDICADOR DE CARGA --}}
    <div id="loadingSpinner" class="text-center my-5" style="display: none;">
        <div class="spinner-border" style="color: #800000;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 text-muted">Cargando archivos...</p>
    </div>

    {{-- CARPETAS --}}
    <div id="folderContainer">
        @include('documental.partials.folder-grid', [
            'folders'  => $folders,
            'userRole' => $userRole
        ])
    </div>

    {{-- DOCUMENTOS --}}
    <div id="documentContainer">
        @include('documental.partials.document-table', [
            'documents'     => $documents,
            'currentFolder' => $currentFolder ?? null,
            'userRole'      => $userRole
        ])
    </div>
</div>

{{-- MODALES --}}
@include('documental.modals.view-document',   ['documents' => $documents])
@include('documental.modals.edit-document')
@include('documental.modals.edit-admin-document')
@include('documental.modals.move-document')
@include('documental.modals.create-folder',   ['currentFolder' => $currentFolder ?? null])
@include('documental.modals.upload-file',     ['currentFolder' => $currentFolder ?? null])

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
    .folder-icon   { font-size: 2.5rem; margin-bottom: 0.5rem; }
    .document-row:hover { background-color: rgba(0,0,0,0.02); }
    .breadcrumb-item a  { text-decoration: none; color: #800000; font-weight: 500; }
</style>
@endpush

@push('scripts')
<script>
const datosFiltro = {
    version: @json($versionesUnicas),
    codigo:  @json($codigosUnicos),
    clave:   @json($clavesUnicas),
};

const labelsFiltro = {
    version: 'Versión del procedimiento',
    codigo:  'Código de procedimiento',
    clave:   'Clave de formato',
};

document.addEventListener('DOMContentLoaded', function () {

    // Buscar en tiempo real sobre filas visibles
    const si = document.getElementById('searchInput');
    if (si) {
        si.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.document-row').forEach(row => {
                const name = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                const show = q === '' || name.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            const rc = document.getElementById('resultCount');
            if (rc) rc.textContent = q === '' ? '' : `🔍 ${visible} resultado${visible !== 1 ? 's' : ''}`;

            document.getElementById('noResultsMessage')?.remove();
            if (q !== '' && visible === 0) {
                const fc  = document.getElementById('folderContainer');
                const div = document.createElement('div');
                div.id        = 'noResultsMessage';
                div.className = 'alert alert-warning d-flex align-items-center mt-3';
                div.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> No se encontraron archivos que coincidan con "<strong>${q}</strong>"`;
                if (fc) fc.after(div);
            }
        });
    }

    // Ordenar documentos
    const ss = document.getElementById('sortSelect');
    if (ss) ss.addEventListener('change', () => sortDocuments(ss.value));

    // Restaurar estado del filtro de campo si hay un filtro activo en la URL
    (function () {
        const tipoActivo  = @json(request('version') ? 'version' : (request('codigo') ? 'codigo' : (request('clave') ? 'clave' : '')));
        const valorActivo = @json(request('version') ?: (request('codigo') ?: (request('clave') ?: '')));
        if (!tipoActivo || !valorActivo) return;

        const selectTipo = document.getElementById('select-tipo-campo');
        if (!selectTipo) return;
        selectTipo.value = tipoActivo;
        cambiarTipoCampo(tipoActivo);

        const sel    = document.getElementById('select-valor-campo');
        const target = tipoActivo + ':' + valorActivo;
        if (sel) {
            for (let o of sel.options) {
                if (o.value === target) { o.selected = true; break; }
            }
        }
    })();

    // Al hacer submit del form de filtros: mapear valor al hidden correcto
    const ff = document.getElementById('form-filtros');
    if (ff) {
        ff.addEventListener('submit', function () {
            ['version', 'codigo', 'clave'].forEach(k => {
                const h = document.getElementById('hidden-' + k);
                if (h) h.value = '';
            });
            const raw = document.getElementById('select-valor-campo')?.value || '';
            if (raw.includes(':')) {
                const sep   = raw.indexOf(':');
                const campo = raw.substring(0, sep);
                const valor = raw.substring(sep + 1);
                const h     = document.getElementById('hidden-' + campo);
                if (h) h.value = valor;
            }
        });
    }
});

// Cambiar opciones del select valor según campo elegido
function cambiarTipoCampo(tipo) {
    const sel = document.getElementById('select-valor-campo');
    if (!sel) return;

    ['version', 'codigo', 'clave'].forEach(k => {
        const h = document.getElementById('hidden-' + k);
        if (h) h.value = '';
    });

    if (!tipo) {
        sel.innerHTML = '<option value="">— Primero elige un campo —</option>';
        sel.disabled  = true;
        return;
    }

    sel.disabled  = false;
    const vals    = datosFiltro[tipo] || [];
    sel.innerHTML =
        `<option value="">— Selecciona ${labelsFiltro[tipo]} —</option>` +
        vals.map(v => `<option value="${tipo}:${v}">${v}</option>`).join('');
}

// Limpiar buscador en tiempo real
function limpiarBuscador() {
    const input = document.getElementById('searchInput');
    if (input) {
        input.value = '';
        input.dispatchEvent(new Event('input'));
        input.focus();
    }
}

// Ordenar tabla de documentos en el cliente
function sortDocuments(sortBy) {
    const tb = document.querySelector('table tbody');
    if (!tb) return;
    const rows = Array.from(tb.querySelectorAll('tr'));
    rows.sort((a, b) => {
        const nA = a.dataset.fileName || a.querySelector('td:first-child')?.textContent || '';
        const nB = b.dataset.fileName || b.querySelector('td:first-child')?.textContent || '';
        const dA = a.dataset.fileDate || '';
        const dB = b.dataset.fileDate || '';
        const sA = parseInt(a.dataset.fileSize) || 0;
        const sB = parseInt(b.dataset.fileSize) || 0;
        switch (sortBy) {
            case 'name_asc':  return nA.localeCompare(nB);
            case 'name_desc': return nB.localeCompare(nA);
            case 'date_desc': return new Date(dB) - new Date(dA);
            case 'date_asc':  return new Date(dA) - new Date(dB);
            case 'size_desc': return sB - sA;
            case 'size_asc':  return sA - sB;
            default: return 0;
        }
    });
    rows.forEach(r => tb.appendChild(r));
}
</script>
@endpush