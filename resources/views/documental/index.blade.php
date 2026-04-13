@extends('layouts.app')

@section('title', 'Gestión Documental - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">
    {{-- HEADER - TÍTULO Y BOTONES DE ACCIÓN --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                        <h1 class="h3 mb-2" style="color: #dc2626; cursor: pointer;">
                            <i class="bi bi-files me-2" style="font-size: 2.5rem; vertical-align: middle;"></i>
                            Gestión Documental
                        </h1>
                    </a>
                </div>

                {{-- BOTONES DE ACCIÓN SEGÚN ROL Y CONTEXTO --}}
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    {{-- BOTÓN NUEVA CARPETA - SOLO PARA ADMIN --}}
                    @if(in_array($userRole, ['superadmin', 'admin']))
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                            <i class="bi bi-folder-plus me-1"></i> Nueva Carpeta
                        </button>
                    @endif

                    {{-- BOTÓN SUBIR ARCHIVO - SOLO SI ESTAMOS DENTRO DE UNA CARPETA --}}
                    @if(isset($currentFolder) && $currentFolder)
                        <button type="button" class="btn text-white" style="background-color: #737373;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-upload me-1"></i> Subir Archivo
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- (BREADCRUMB) PARA NAVEGACIÓN --}}
    <div class="mb-3">
        @include('documental.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs, 'currentFolder' => $currentFolder])
    </div>

    {{-- SECCIÓN DE FILTROS Y BÚSQUEDA - SOLO SI HAY DOCUMENTOS Y ESTAMOS EN UNA CARPETA --}}
    @if(isset($currentFolder) && $currentFolder && $documents->count() > 0)
    @php
        $hasAdminDocs = $versionesUnicas->count() > 0
                     || $codigosUnicos->count() > 0
                     || $clavesUnicas->count() > 0;
    @endphp

    {{-- FILA 1: BUSCADOR + ORDENAMIENTO (RESPONSIVO) --}}
    <div class="row mb-3 g-3">
        {{-- BUSCADOR DE ARCHIVOS --}}
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #000000;">
                        <i class="bi bi-search me-1"></i> Buscar archivos
                    </label>
                    <div class="d-flex">
                        <div class="position-relative flex-grow-1">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.9rem;"></i>
                            <input type="text" id="searchInput"
                                   class="form-control ps-5"
                                   placeholder="Buscar por nombre de archivo"
                                   style="height: 42px; border-radius: 4px 0 0 4px; border-right: none;">
                        </div>
                        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button"
                                id="clearSearch" title="Limpiar búsqueda"
                                onclick="limpiarBuscador()"
                                style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; border-left: none;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div id="searchResults" class="mt-2 small text-muted">
                        <span id="resultCount"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SELECTOR DE ORDENAMIENTO --}}
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-3">
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

    {{-- FILA 2: FILTRO POR TIPO DE DOCUMENTO (FORMATO O PROCEDIMIENTO) --}}
    @if($documents->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color: #000000;">
                        <i class="bi bi-tag me-1"></i> Filtrar por tipo de documento
                    </label>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <button type="button" class="btn btn-sm filtro-tipo btn-filtro-tipo no-tema activo-tipo"
                                onclick="filtrarPorTipo('')"
                                id="filtro-tipo-todos"
                                style="border:1px solid #000000; background:white; color:black;">
                            Todos
                        </button>
                        <button type="button" class="btn btn-sm filtro-tipo btn-filtro-tipo no-tema"
                                onclick="filtrarPorTipo('Formato')"
                                id="filtro-tipo-formato"
                                style="border:1px solid #000000; background:white; color:#000000;">
                            📄 Formato
                        </button>
                        <button type="button" class="btn btn-sm filtro-tipo btn-filtro-tipo no-tema"
                                onclick="filtrarPorTipo('Procedimiento')"
                                id="filtro-tipo-procedimiento"
                                style="border:1px solid #000000; background:white; color:#000000;">
                            📋 Procedimiento
                        </button>
                        <small class="text-muted ms-2" id="info-filtro-tipo" style="font-size:0.78rem;"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="mb-4"></div>
    @endif

    @endif {{-- FIN DEL @IF(ISSET($CURRENTFOLDER) && $CURRENTFOLDER) --}}

    {{-- INDICADOR DE CARGA (SPINNER) --}}
    <div id="loadingSpinner" class="text-center my-5" style="display: none;">
        <div class="spinner-border" style="color: #800000;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2 text-muted">Cargando archivos...</p>
    </div>

    {{-- CUADRÍCULA DE CARPETAS (RESPONSIVA) --}}
    <div id="folderContainer">
        @include('documental.partials.folder-grid', [
            'folders'  => $folders,
            'userRole' => $userRole
        ])
    </div>

    {{-- TABLA DE DOCUMENTOS (RESPONSIVA) --}}
    <div id="documentContainer">
        @include('documental.partials.document-table', [
            'documents'     => $documents,
            'currentFolder' => $currentFolder ?? null,
            'userRole'      => $userRole
        ])
    </div>
</div>

{{-- MODALES DEL SISTEMA --}}
@include('documental.modals.view-document',   ['documents' => $documents])
@include('documental.modals.edit-document')
@include('documental.modals.edit-admin-document')
@include('documental.modals.move-document')
@include('documental.modals.create-folder',   ['currentFolder' => $currentFolder ?? null])
@include('documental.modals.upload-file',     ['currentFolder' => $currentFolder ?? null])

@endsection

{{-- ESTILOS CSS DE LA PÁGINA --}}
@push('styles')
<style>
    {{-- ESTILOS DE LAS TARJETAS DE CARPETA --}}
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
    .breadcrumb-item a  { text-decoration: none; color: #000000; font-weight: 500; }
    .filtro-tipo.activo-tipo {
        background-color: #ffffff !important;
        color: black !important;
        border-color: #737373 !important;
    }

    {{-- ESTILOS RESPONSIVOS PARA TABLETS --}}
    @media (min-width: 769px) and (max-width: 992px) {
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .table {
            min-width: 950px !important;
            width: max-content !important;
        }
        .table th, .table td {
            white-space: nowrap !important;
            font-size: 0.75rem !important;
            padding: 8px 6px !important;
        }
        .btn-sm {
            padding: 0.15rem 0.3rem !important;
            font-size: 0.65rem !important;
        }
        .folder-icon {
            font-size: 2rem !important;
        }
        .folder-card .card-title {
            font-size: 0.85rem !important;
        }
        .modal-dialog {
            max-width: 95% !important;
            margin: 1rem auto !important;
        }
        .d-flex.gap-2.flex-wrap {
            gap: 0.5rem !important;
        }
        .btn {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.75rem !important;
        }
    }

    {{-- ESTILOS RESPONSIVOS PARA MÓVILES --}}
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        .h3 {
            font-size: 1.5rem !important;
        }
        .h3 i {
            font-size: 2rem !important;
        }
        .d-flex.flex-column.flex-md-row {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.75rem !important;
        }
        .d-flex.flex-wrap.gap-2 {
            width: 100% !important;
        }
        .d-flex.flex-wrap.gap-2 .btn {
            flex: 1 !important;
            text-align: center !important;
        }
        
        {{-- BUSCADOR --}}
        .d-flex {
            width: 100% !important;
        }
        #searchInput {
            font-size: 0.85rem !important;
            height: 38px !important;
        }
        #clearSearch {
            width: 38px !important;
            height: 38px !important;
        }
        
        {{-- SELECT ORDENAR --}}
        #sortSelect {
            font-size: 0.85rem !important;
            padding: 8px 10px !important;
        }
        
        {{-- TABLA - SCROLL HORIZONTAL --}}
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .table {
            min-width: 800px !important;
            width: max-content !important;
        }
        .table th, .table td {
            white-space: nowrap !important;
            font-size: 0.7rem !important;
            padding: 6px 4px !important;
        }
        
        {{-- BOTONES DE ACCIÓN EN TABLA --}}
        .btn-sm {
            padding: 0.15rem 0.25rem !important;
            font-size: 0.6rem !important;
        }
        .btn-sm i {
            font-size: 0.65rem !important;
        }
        
        {{-- TARJETAS DE CARPETAS EN MÓVIL --}}
        .folder-card {
            margin-bottom: 0.75rem !important;
        }
        .folder-icon {
            font-size: 1.8rem !important;
        }
        .folder-card .card-title {
            font-size: 0.8rem !important;
        }
        .folder-card .text-muted {
            font-size: 0.65rem !important;
        }
        
        {{-- GRID DE CARPETAS - 2 COLUMNAS EN MÓVIL --}}
        .row-cols-1 .col {
            width: 50% !important;
            flex: 0 0 50% !important;
        }
        
        {{-- MODALES --}}
        .modal-dialog {
            margin: 0.5rem !important;
        }
        .modal-body {
            padding: 0.75rem !important;
        }
        .modal-footer {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        .modal-footer .btn {
            flex: 1 !important;
        }
        
        {{-- BREADCRUMBS --}}
        .breadcrumb {
            font-size: 0.75rem !important;
            flex-wrap: wrap !important;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
        
        {{-- FILTROS DE TIPO --}}
        .btn-filtro-tipo {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.5rem !important;
        }
    }

    {{-- ESTILOS RESPONSIVOS PARA MÓVILES MUY PEQUEÑOS --}}
    @media (max-width: 480px) {
        .table th, .table td {
            font-size: 0.65rem !important;
            padding: 4px 3px !important;
        }
        .btn-sm {
            padding: 0.1rem 0.2rem !important;
            font-size: 0.55rem !important;
        }
        .btn-sm i {
            font-size: 0.55rem !important;
        }
        .folder-icon {
            font-size: 1.5rem !important;
        }
        .folder-card .card-title {
            font-size: 0.75rem !important;
        }
        
        {{-- GRID DE CARPETAS - 1 COLUMNA EN MÓVIL MUY PEQUEÑO --}}
        .row-cols-1 .col {
            width: 100% !important;
            flex: 0 0 100% !important;
        }
        
        .btn-filtro-tipo {
            font-size: 0.65rem !important;
            padding: 0.15rem 0.4rem !important;
        }
    }
</style>
@endpush

{{-- JAVASCRIPT PARA FILTROS, BÚSQUEDA Y ORDENAMIENTO --}}
@push('scripts')
<script>
{{-- DATOS PARA FILTROS DE CAMPOS (VERSIONES, CÓDIGOS, CLAVES) --}}
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

{{-- VARIABLE GLOBAL PARA EL FILTRO POR TIPO DE DOCUMENTO --}}
let tipoFiltroActivo = '';

{{-- FUNCIÓN PARA FILTRAR DOCUMENTOS POR TIPO (FORMATO O PROCEDIMIENTO) --}}
function filtrarPorTipo(tipo) {
    tipoFiltroActivo = tipo;

    {{-- ACTUALIZAR ESTILO DE BOTONES --}}
    document.querySelectorAll('.filtro-tipo').forEach(btn => {
        btn.style.background  = 'white';
        btn.style.color       = btn.id === 'filtro-tipo-formato'       ? '#000000' :
                                 btn.id === 'filtro-tipo-procedimiento' ? '#000000' : '#ffffff';
        btn.style.borderColor = btn.id === 'filtro-tipo-formato'       ? '#000000' :
                                 btn.id === 'filtro-tipo-procedimiento' ? '#000000' : '#ffffff';
    });

    const btnActivo = document.getElementById(
        tipo === ''              ? 'filtro-tipo-todos' :
        tipo === 'Formato'       ? 'filtro-tipo-formato' :
                                   'filtro-tipo-procedimiento'
    );
    if (btnActivo) {
        btnActivo.style.background  = '#ffffff';
        btnActivo.style.color       = 'black';
        btnActivo.style.borderColor = '#000000';
    }

    {{-- FILTRAR FILAS DE DOCUMENTOS --}}
    let visible = 0;
    document.querySelectorAll('.document-row').forEach(row => {
        const tipoDoc = row.dataset.tipoDocumento || '';
        const mostrar = tipo === '' || tipoDoc === tipo;
        row.style.display = mostrar ? '' : 'none';
        if (mostrar) visible++;
    });

    const info = document.getElementById('info-filtro-tipo');
    if (info) {
        info.textContent = tipo === '' ? '' : `${visible} documento${visible !== 1 ? 's' : ''}`;
    }
}

{{-- INICIALIZA LA PÁGINA CUANDO EL DOM ESTÁ LISTO --}}
document.addEventListener('DOMContentLoaded', function () {

    {{-- BUSCADOR EN TIEMPO REAL SOBRE FILAS VISIBLES --}}
    const si = document.getElementById('searchInput');
    if (si) {
        si.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.document-row').forEach(row => {
                const name    = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                const tipoDoc = row.dataset.tipoDocumento || '';
                const pasaTipo = tipoFiltroActivo === '' || tipoDoc === tipoFiltroActivo;
                const show = pasaTipo && (q === '' || name.includes(q));
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

    {{-- ORDENAR DOCUMENTOS --}}
    const ss = document.getElementById('sortSelect');
    if (ss) ss.addEventListener('change', () => sortDocuments(ss.value));

    {{-- RESTAURAR ESTADO DEL FILTRO DE CAMPO SI HAY UN FILTRO ACTIVO EN LA URL --}}
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

    {{-- AL HACER SUBMIT DEL FORM DE FILTROS: MAPEAR VALOR AL HIDDEN CORRECTO --}}
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

{{-- CAMBIAR OPCIONES DEL SELECT VALOR SEGÚN CAMPO ELEGIDO --}}
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

{{-- LIMPIAR BUSCADOR EN TIEMPO REAL --}}
function limpiarBuscador() {
    const input = document.getElementById('searchInput');
    if (input) {
        input.value = '';
        input.dispatchEvent(new Event('input'));
        input.focus();
    }
}

{{-- ORDENAR TABLA DE DOCUMENTOS EN EL CLIENTE --}}
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