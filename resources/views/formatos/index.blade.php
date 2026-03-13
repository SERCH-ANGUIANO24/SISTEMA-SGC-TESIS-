@extends('layouts.app')

@section('title', 'Lista Maestra - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">

    {{-- ── ENCABEZADO DEL MÓDULO ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Regresar al Dashboard">
                        <h1 class="h3 mb-0" style="color:#800000; cursor:pointer;">
                            <i class="bi bi-file-earmark-text me-2" style="font-size: 3rem; vertical-align:middle;"></i>
                            Lista Maestra
                        </h1>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ALERTAS ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="alerta-principal">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show fw-bold" role="alert" id="alerta-principal">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── FILTROS ── --}}
    <div class="row mb-4 align-items-end">
        <div class="col-md-5">
            <div class="card shadow-sm border-0" style="border-radius:8px;">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color:#333; font-size:0.85rem;">
                        <i class="bi bi-search me-1"></i> Buscar archivos
                    </label>
                    <form method="GET" action="{{ route('formatos.index') }}" id="form-nombre">
                        @if(request('version'))<input type="hidden" name="version" value="{{ request('version') }}">@endif
                        @if(request('codigo'))<input type="hidden"  name="codigo"  value="{{ request('codigo') }}">@endif
                        @if(request('clave'))<input type="hidden"   name="clave"   value="{{ request('clave') }}">@endif
                        @if(request('departamento'))<input type="hidden" name="departamento" value="{{ request('departamento') }}">@endif
                        <div class="input-group">
                            <input type="text" name="nombre" id="searchInput"
                                   class="form-control"
                                   placeholder="Buscar por nombre de archivo"
                                   value="{{ request('nombre') }}"
                                   style="background:#fff; border:1px solid #dee2e6; border-right:none;">
                            <button class="btn" type="button" id="btn-limpiar-busqueda"
                                    onclick="limpiarBuscador()"
                                    style="background:#f8f9fa; border:1px solid #dee2e6; border-left:none; color:#6c757d;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0" style="border-radius:8px;">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color:#333; font-size:0.85rem;">
                        <i class="bi bi-funnel me-1"></i> Filtrar por campo específico
                    </label>
                    <form method="GET" action="{{ route('formatos.index') }}" id="form-filtros">
                        @if(request('nombre'))<input type="hidden" name="nombre" value="{{ request('nombre') }}">@endif
                        <div class="d-flex gap-2">
                            <select id="select-tipo-campo"
                                    class="form-select"
                                    onchange="cambiarTipoCampo(this.value)"
                                    style="flex:0 0 210px; max-width:210px; border:1px solid #dee2e6;">
                                <option value="">— Elegir campo —</option>
                                <option value="version" {{ request('version') ? 'selected':'' }}>📋 Versión</option>
                                <option value="codigo"  {{ request('codigo')  ? 'selected':'' }}>🔢 Código de procedimiento</option>
                                <option value="clave"   {{ request('clave')   ? 'selected':'' }}>🔑 Clave de formato</option>
                                <option value="departamento" {{ request('departamento') ? 'selected':'' }}>🏢 Departamento</option>
                            </select>

                            <select id="select-valor-campo"
                                    name="filtro_valor"
                                    class="form-select"
                                    {{ !(request('version')||request('codigo')||request('clave')||request('departamento')) ? 'disabled':'' }}
                                    style="border:1px solid #dee2e6;">
                                <option value="">— Primero elige un campo —</option>
                                @foreach($versionesUnicas as $v)
                                    <option value="version:{{ $v }}" data-tipo="version"
                                            {{ request('version')==$v ? 'selected':'' }}
                                            style="{{ request('version') ? '':'display:none' }}">{{ $v }}</option>
                                @endforeach
                                @foreach($codigosUnicos as $c)
                                    <option value="codigo:{{ $c }}" data-tipo="codigo"
                                            {{ request('codigo')==$c ? 'selected':'' }}
                                            style="{{ request('codigo') ? '':'display:none' }}">{{ $c }}</option>
                                @endforeach
                                @foreach($clavesUnicas as $cl)
                                    <option value="clave:{{ $cl }}" data-tipo="clave"
                                            {{ request('clave')==$cl ? 'selected':'' }}
                                            style="{{ request('clave') ? '':'display:none' }}">{{ $cl }}</option>
                                @endforeach
                                @foreach($departamentosUnicos as $d)
                                    <option value="departamento:{{ $d }}" data-tipo="departamento"
                                            {{ request('departamento')==$d ? 'selected':'' }}
                                            style="{{ request('departamento') ? '':'display:none' }}">{{ $d }}</option>
                                @endforeach
                            </select>

                            <input type="hidden" name="version" id="hidden-version" value="{{ request('version') }}">
                            <input type="hidden" name="codigo"  id="hidden-codigo"  value="{{ request('codigo') }}">
                            <input type="hidden" name="clave"   id="hidden-clave"   value="{{ request('clave') }}">
                            <input type="hidden" name="departamento" id="hidden-departamento" value="{{ request('departamento') }}">

                            <button type="submit" class="btn px-3" style="background:#737373; color:white; white-space:nowrap; border:none;">
                                Aplicar
                            </button>
                            @if(request('version')||request('codigo')||request('clave')||request('departamento'))
                                <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre')])) }}"
                                   class="btn btn-outline-secondary px-3" title="Limpiar filtro">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>

                        @if(request('version')||request('codigo')||request('clave')||request('departamento'))
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @if(request('version'))
                                <span class="badge rounded-pill" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                    Versión: {{ request('version') }}
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre')])) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('codigo'))
                                <span class="badge rounded-pill" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                    Código: {{ request('codigo') }}
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre')])) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('clave'))
                                <span class="badge rounded-pill" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                    Clave: {{ request('clave') }}
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre')])) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('departamento'))
                                <span class="badge rounded-pill" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                    Departamento: {{ request('departamento') }}
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre')])) }}"
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

    {{-- ── FILTRO DE ORDENAMIENTO POR FECHA ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius:8px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <label class="form-label fw-bold mb-0" style="color:#333; font-size:0.85rem; white-space:nowrap;">
                            <i class="bi bi-calendar me-1"></i> Ordenar por fecha:
                        </label>
                        <div class="d-flex gap-2">
                            <button type="button"
                                    id="btn-orden-desc"
                                    class="btn btn-sm orden-fecha-btn activo-orden"
                                    onclick="ordenarPorFecha('desc')"
                                    style="border:1px solid #800000; background:#400080; font-size:0.8rem; padding:4px 14px; border-radius:6px;">
                                <i class="bi bi-sort-down me-1"></i> Más reciente primero
                            </button>
                            <button type="button"
                                    id="btn-orden-asc"
                                    class="btn btn-sm orden-fecha-btn"
                                    onclick="ordenarPorFecha('asc')"
                                    style="border:1px solid #dee2e6; background:#f8f9fa; color:#495057; font-size:0.8rem; padding:4px 14px; border-radius:6px;">
                                <i class="bi bi-sort-up-alt me-1"></i> Más antigua primero
                            </button>
                            <button type="button"
                                    id="btn-orden-ninguno"
                                    class="btn btn-sm orden-fecha-btn"
                                    onclick="ordenarPorFecha('ninguno')"
                                    style="border:1px solid #dee2e6; background:#f8f9fa; color:#495057; font-size:0.8rem; padding:4px 14px; border-radius:6px;">
                                <i class="bi bi-x-circle me-1"></i> Sin ordenar
                            </button>
                        </div>
                        <small class="text-muted ms-2" id="info-orden-fecha" style="font-size:0.75rem;">(ordenado: más reciente → más antiguo)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLA DE FORMATOS ── --}}
    @if($formatos->count() > 0)
    <div class="card shadow-sm border-0" style="border-radius:8px; overflow:hidden;">
        <div class="card-header d-flex align-items-center py-3 px-4"
             style="background:white; border-bottom:2px solid #f0f0f0;">
            <h6 class="mb-0 fw-bold" style="color:#333;">
                <i class="bi bi-files me-2" style="color:#800000;"></i> Documentos
            </h6>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="formatosTable" style="border-collapse: collapse;">
                    <thead style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <tr>
                            <th class="px-4 py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Nombre del Documento</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Proceso</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Departamento</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Clave</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Código</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Versión</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Ext.</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d; white-space:nowrap;">Fecha y Hora</th>
                            <th class="py-3 text-center" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6c757d;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($formatos as $i => $formato)
                        @php
                            $tipoArchivo = \App\Http\Controllers\FormatoController::tipoArchivo($formato->extension_archivo);
                            $puedeVer    = in_array($tipoArchivo, ['imagen', 'pdf']);
                        @endphp
                        <tr class="formato-row" data-file-name="{{ strtolower($formato->nombre_archivo) }}" data-version="{{ $formato->version_procedimiento }}" data-fecha="{{ $formato->created_at->format('Y-m-d H:i:s') }}" style="border-bottom:1px solid #f0f0f0;">
                            <td class="px-4 py-3" style="font-size:0.85rem; color:#333; font-weight:500; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $formato->nombre_archivo }}">
                                <i class="bi bi-file-earmark-text me-2" style="color:#800000;"></i>
                                {{ $formato->nombre_archivo }}
                            </td>

                            <td style="font-size:0.8rem; color:#495057;">
                                {{ $formato->proceso }}
                            </td>

                            <td style="font-size:0.8rem; color:#495057;">
                                {{ $formato->departamento }}
                            </td>

                            <td style="font-size:0.8rem; color:#495057;">
                                {{ $formato->clave_formato }}
                            </td>

                            <td style="font-size:0.8rem; color:#495057;">
                                {{ $formato->codigo_procedimiento }}
                            </td>

                            <td style="font-size:0.8rem; color:#495057;">
                                {{ $formato->version_procedimiento }}
                            </td>

                            <td>
                                <span class="badge ext-badge ext-{{ $tipoArchivo }}" style="font-size:0.7rem; padding:3px 8px;">
                                    {{ $formato->extension_archivo ?? 'N/A' }}
                                </span>
                            </td>

                            <td style="font-size:0.8rem; color:#6c757d; white-space:nowrap;">
                                {{ $formato->created_at->format('d/m/Y h:i A') }}
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- VER --}}
                                    @if($puedeVer)
                                    <button type="button"
                                       class="btn btn-sm btn-outline-info visualizar-archivo"
                                       style="width:30px; height:30px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:5px;"
                                       title="{{ $tipoArchivo === 'imagen' ? 'Ver imagen' : ($tipoArchivo === 'pdf' ? 'Ver PDF' : 'Ver Texto') }}"
                                       data-url="{{ route('formatos.show', $formato) }}"
                                       data-nombre="{{ $formato->nombre_archivo }}"
                                       data-tipo="{{ $tipoArchivo }}">
                                        <i class="bi bi-eye" style="font-size:0.85rem;"></i>
                                    </button>
                                    @else
                                    @if(!in_array(strtolower($formato->extension_archivo), ['doc','docx','xls','xlsx','ppt','pptx','csv']))
                                    <button class="btn btn-sm btn-outline-secondary disabled"
                                            style="width:30px; height:30px; padding:0; border-radius:5px; opacity:0.3;"
                                            title="Vista previa no disponible">
                                        <i class="bi bi-eye-slash" style="font-size:0.85rem;"></i>
                                    </button>
                                    @endif
                                    @endif

                                    {{-- DESCARGAR --}}
                                    <a href="{{ route('formatos.download', $formato) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       style="width:30px; height:30px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:5px;"
                                       title="Descargar">
                                        <i class="bi bi-download" style="font-size:0.85rem;"></i>
                                    </a>

                                    {{-- ELIMINAR — SOLO SUPERADMIN/ADMIN --}}
                                    @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                                    {{-- ELIMINAR --}}
                                    <button class="btn btn-sm btn-outline-danger eliminar-formato"
                                            style="width:30px; height:30px; padding:0; border-radius:5px;"
                                            title="Eliminar"
                                            data-id="{{ $formato->id }}"
                                            data-nombre="{{ $formato->nombre_archivo }}">
                                        <i class="bi bi-trash" style="font-size:0.85rem;"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($formatos, 'links'))
            <div class="d-flex justify-content-end py-3 px-4 border-top">
                {{ $formatos->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── MODAL PARA VISUALIZAR ARCHIVO ── --}}
    <div class="modal fade" id="modalVisualizarArchivo" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; border:none; overflow:hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background:white; color:#333; padding:1rem 1.5rem; border-bottom:1px solid #dee2e6;">
                    <h5 class="modal-title fw-bold" id="modalArchivoTitulo" style="color:#333;">
                        <i class="bi bi-file-earmark-text me-2" style="color:#800000;"></i>
                        <span id="modalArchivoNombre">Documento</span>
                    </h5>
                </div>
                <div class="modal-body p-0" style="height:70vh; background:#f5f5f5;">
                    <iframe id="visorArchivoModal" style="width:100%; height:100%; border:none;"></iframe>
                </div>
                <div class="modal-footer" style="background:white; border-top:1px solid #dee2e6; padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center;">
                    <small class="text-muted">Si el archivo no se visualiza correctamente, usa el botón de descargar.</small>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn" id="btnDescargarArchivo" style="background:#800000; color:white; border:none; padding:0.5rem 1.5rem; border-radius:6px;">
                            <i class="bi bi-download me-1"></i> Descargar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="padding:0.5rem 1.5rem; border-radius:6px;">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /container-fluid --}}


{{-- Form oculto DELETE --}}
<form id="form-eliminar" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Badges de extensión */
/* Badges de extensión */
    .ext-badge { 
        font-size:0.7rem; 
        font-weight:700; 
        padding:3px 8px; 
        border-radius:4px; 
        font-family:'Courier New',monospace;
        background: transparent !important;
        border: none !important;
    }
    .ext-imagen { color:#000000; }
    .ext-pdf    { color:#000000; }
    .ext-txt    { color:#000000; }
    .ext-office { color:#000000; }
    .ext-otro   { color:#000000; }

    /* Tabla hover */
    .table tbody tr:hover { background:#f8f9fa; }

    /* Zona upload hover */
    #upload-zona:hover { border-color:#2d9e59 !important; background:#e2f8ec !important; }
    #upload-zona.drag-over { border-color:#1a6b3a !important; background:#d1f5e0 !important; }
    #archivo-seleccionado.show { display:flex !important; }

    /* Clave warning en modal */
    #clave-warning { font-size:0.78rem; }

    /* Estilos para validación */
    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .was-validated .form-select:invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e"), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-position: right 0.75rem center, center right 2rem;
        background-size: 16px 12px, calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        background-repeat: no-repeat;
    }

    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .was-validated .form-control:invalid ~ .invalid-feedback,
    .was-validated .form-select:invalid ~ .invalid-feedback,
    .form-control.is-invalid ~ .invalid-feedback,
    .form-select.is-invalid ~ .invalid-feedback {
        display: block;
    }

    #error-archivo {
        display: none;
    }

    .was-validated #input-archivo:invalid ~ #error-archivo,
    #input-archivo.is-invalid ~ #error-archivo {
        display: block !important;
    }

    @media (max-width: 768px) {
        .table td, .table th {
            font-size: 0.8rem;
        }
    }

    /* Botones de ordenamiento de fecha */
    /*.orden-fecha-btn.activo-orden {
        border-color: #800000 !important;
        background: #fff5f5 !important;
        color: #800000 !important;
        font-weight: 600;
    }*/
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const procesosYDepartamentos = @json($procesosYDepartamentos);
    const clavesExistentes       = @json($formatos->pluck('clave_formato')->toArray());
    let formatoEditandoId        = null;
    let extensionActual          = '';
    let ordenFechaActual         = 'desc';
    let filasOriginales          = [];

    function getModal() {
        const el = document.getElementById('modalFormato');
        return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, { keyboard: true, backdrop: true });
    }

    function getModalArchivo() {
        const el = document.getElementById('modalVisualizarArchivo');
        return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, { backdrop: true });
    }

    // ── Extraer fecha del atributo data-fecha ──
    function extraerFecha(fechaStr) {
        if (!fechaStr) return new Date(0);
        const fecha = new Date(fechaStr.replace(' ', 'T'));
        return isNaN(fecha.getTime()) ? new Date(0) : fecha;
    }

    // ── Ordenar tabla por fecha ──
    function ordenarPorFecha(direccion) {
        ordenFechaActual = direccion;

        document.querySelectorAll('.orden-fecha-btn').forEach(btn => {
            btn.classList.remove('activo-orden');
            btn.style.borderColor = '#dee2e6';
            btn.style.background  = '#f8f9fa';
            btn.style.color       = '#495057';
            btn.style.fontWeight  = 'normal';
        });

        const btnActivo = document.getElementById(
            direccion === 'desc'   ? 'btn-orden-desc' :
            direccion === 'asc'    ? 'btn-orden-asc'  :
                                     'btn-orden-ninguno'
        );

        const tbody = document.querySelector('#formatosTable tbody');
        if (!tbody) return;

        const filas = Array.from(tbody.querySelectorAll('tr.formato-row'));
        if (filas.length === 0) return;

        if (filasOriginales.length === 0) {
            filasOriginales = filas.map(f => f.cloneNode(true));
        }

        const infoOrden = document.getElementById('info-orden-fecha');

        if (direccion === 'ninguno') {
            tbody.innerHTML = '';
            filasOriginales.forEach(fila => tbody.appendChild(fila.cloneNode(true)));
            reRegistrarEventosFilas();
            if (infoOrden) infoOrden.textContent = '';
            return;
        }

        filas.sort((a, b) => {
            const fechaA = extraerFecha(a.dataset.fecha || '');
            const fechaB = extraerFecha(b.dataset.fecha || '');
            return direccion === 'desc' ? fechaB - fechaA : fechaA - fechaB;
        });

        tbody.innerHTML = '';
        filas.forEach(fila => tbody.appendChild(fila));
        reRegistrarEventosFilas();

        if (infoOrden) {
            infoOrden.textContent = direccion === 'desc'
                ? '(ordenado: más reciente → más antiguo)'
                : '(ordenado: más antiguo → más reciente)';
        }
    }

    function reRegistrarEventosFilas() {}

    function limpiarBuscador() {
        const input = document.getElementById('searchInput');
        input.value = '';
        input.focus();
        document.querySelectorAll('.formato-row').forEach(row => row.style.display = '');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.querySelector('#formatosTable tbody');
        if (tbody) {
            filasOriginales = Array.from(tbody.querySelectorAll('tr.formato-row')).map(f => f.cloneNode(true));
        }

        // Inicializar orden por defecto: más reciente primero
        setTimeout(() => ordenarPorFecha('desc'), 100);

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        const inputClave = document.getElementById('input-clave');
        if (inputClave) {
            inputClave.addEventListener('input', function () {
                const val = this.value.trim();
                const warn = document.getElementById('clave-warning');
                const dup = val && clavesExistentes.some(c => c.toLowerCase() === val.toLowerCase());
                if (warn) warn.style.display = dup ? 'block' : 'none';
            });
        }

        const zona = document.getElementById('upload-zona');
        if (zona) {
            zona.addEventListener('dragover', e => { 
                e.preventDefault(); 
                zona.classList.add('drag-over'); 
            });
            zona.addEventListener('dragleave', () => zona.classList.remove('drag-over'));
            zona.addEventListener('drop', e => {
                e.preventDefault();
                zona.classList.remove('drag-over');
                const inp = document.getElementById('input-archivo');
                if (e.dataTransfer.files.length > 0) {
                    inp.files = e.dataTransfer.files;
                    mostrarArchivoSeleccionado(inp);
                }
            });
        }

        const inputArchivo = document.getElementById('input-archivo');
        if (inputArchivo) {
            inputArchivo.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    this.classList.remove('is-invalid');
                    document.getElementById('error-archivo').style.display = 'none';
                }
            });
        }

        (function() {
            const tipoActivo = @json(request('version') ? 'version' : (request('codigo') ? 'codigo' : (request('clave') ? 'clave' : (request('departamento') ? 'departamento' : ''))));
            const valorActivo = @json(request('version') ?: (request('codigo') ?: (request('clave') ?: (request('departamento') ?: ''))));
            if (tipoActivo && valorActivo) {
                const selectTipo = document.getElementById('select-tipo-campo');
                if (selectTipo) {
                    selectTipo.value = tipoActivo;
                    cambiarTipoCampo(tipoActivo);
                    const sel = document.getElementById('select-valor-campo');
                    const target = tipoActivo + ':' + valorActivo;
                    if (sel) {
                        for (let o of sel.options) {
                            if (o.value === target) {
                                o.selected = true;
                                break;
                            }
                        }
                    }
                }
            }
        })();

        const si = document.getElementById('searchInput');
        if (si) {
            si.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.formato-row').forEach(row => {
                    const nombre = (row.dataset.fileName || '');
                    row.style.display = (q === '' || nombre.includes(q)) ? '' : 'none';
                });
            });
        }

        function limpiarBuscador() {
            const input = document.getElementById('searchInput');
            input.value = '';
            input.focus();
            document.querySelectorAll('.formato-row').forEach(row => {
                row.style.display = '';
            });
        }

        setTimeout(() => {
            const a = document.getElementById('alerta-principal');
            if (a) {
                try {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(a);
                    bsAlert.close();
                } catch(e) {}
            }
        }, 5000);

        const tablaBody = document.querySelector('#formatosTable tbody');
        if (tablaBody) {
            tablaBody.addEventListener('click', function(e) {
                const visualizarBtn = e.target.closest('.visualizar-archivo');
                if (visualizarBtn) {
                    e.preventDefault();
                    const url = visualizarBtn.dataset.url;
                    const nombre = visualizarBtn.dataset.nombre;
                    const tipo = visualizarBtn.dataset.tipo;
                    
                    mostrarVistaArchivo(url, nombre, tipo);
                    return false;
                }
            });

            tablaBody.addEventListener('click', function(e) {
                const editarBtn = e.target.closest('.editar-formato');
                if (editarBtn) {
                    const id = editarBtn.dataset.id;
                    const proceso = editarBtn.dataset.proceso;
                    const departamento = editarBtn.dataset.departamento;
                    const clave = editarBtn.dataset.clave;
                    const codigo = editarBtn.dataset.codigo;
                    const version = editarBtn.dataset.version;
                    const nombre = editarBtn.dataset.nombre;
                    const extension = editarBtn.dataset.extension;
                    
                    abrirModalEditar(id, proceso, departamento, clave, codigo, version, nombre, extension);
                }
            });

            tablaBody.addEventListener('click', function(e) {
                const eliminarBtn = e.target.closest('.eliminar-formato');
                if (eliminarBtn) {
                    const id = eliminarBtn.dataset.id;
                    const nombre = eliminarBtn.dataset.nombre;
                    
                    Swal.fire({
                        title: '¿Eliminar archivo?',
                        text: `¿Estás seguro de eliminar "${nombre}"?`,
                        icon: 'warning',
                        width: '600px',
                        padding: '3rem',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-eliminar');
                            form.action = `{{ url('formatos') }}/${id}`;
                            form.submit();
                        }
                    });
                }
            });
        }

        const inputNombreEdit = document.getElementById('input-nombre-archivo-edit');
        if (inputNombreEdit) {
            inputNombreEdit.addEventListener('input', function() {
                this.value = sanitizarNombre(this.value);
                actualizarPreviewNombreEdit();
            });
        }

        const modal = document.getElementById('modalFormato');
        modal.addEventListener('hidden.bs.modal', function () {
            limpiarValidaciones();
        });

        document.getElementById('btnDescargarArchivo').addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });

        // ── Interceptar cambio en select-proceso ──
        const selectProceso = document.getElementById('select-proceso');
        if (selectProceso) {
            selectProceso.addEventListener('change', function () {
                if (this.value === '__nuevo__') {
                    this.value = '';
                    abrirModalCrearProceso();
                }
            });
        }

        // Botones de cierre del modal Gestionar
        document.getElementById('btnCerrarModalProceso')?.addEventListener('click', cerrarModalGestionar);
        document.getElementById('btnCancelarProceso')?.addEventListener('click', cerrarModalGestionar);

        // Botones de cierre del modal Crear Proceso
        document.getElementById('btnCerrarModalCrearProceso')?.addEventListener('click', cerrarModalCrearProceso);
        document.getElementById('btnCancelarCrearProceso')?.addEventListener('click', cerrarModalCrearProceso);
    });

    function mostrarVistaArchivo(url, nombre, tipo) {
        document.getElementById('modalArchivoNombre').textContent = nombre;
        document.getElementById('btnDescargarArchivo').setAttribute('data-url', url.replace('/show', '/download'));
        
        const iframe = document.getElementById('visorArchivoModal');
        const modalEl = document.getElementById('modalVisualizarArchivo');
        const modalArchivo = getModalArchivo();

        iframe.removeAttribute('srcdoc');
        iframe.src = 'about:blank';

        if (tipo === 'pdf') {
            modalEl.addEventListener('shown.bs.modal', function onShown() {
                modalEl.removeEventListener('shown.bs.modal', onShown);
                iframe.src = url;
            });
            modalArchivo.show();
        } else {
            setTimeout(() => {
                if (tipo === 'imagen') {
                    iframe.srcdoc = `
                        <html>
                        <head>
                            <style>
                                body { 
                                    margin: 0; 
                                    display: flex; 
                                    justify-content: center; 
                                    align-items: center; 
                                    min-height: 100vh; 
                                    background: #f5f5f5;
                                }
                                img { 
                                    max-width: 100%; 
                                    max-height: 90vh; 
                                    object-fit: contain; 
                                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                                }
                            </style>
                        </head>
                        <body>
                            <img src="${url}" alt="${nombre}">
                        </body>
                        </html>
                    `;
                } else if (tipo === 'txt') {
                    fetch(url)
                        .then(response => response.text())
                        .then(text => {
                            iframe.srcdoc = `
                                <html>
                                <head>
                                    <style>
                                        body { 
                                            margin: 0; 
                                            background: #1e1e1e;
                                            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
                                        }
                                        .txt-container {
                                            padding: 2rem;
                                            background: #1e1e1e;
                                            min-height: 100vh;
                                            box-sizing: border-box;
                                        }
                                        .txt-content {
                                            white-space: pre-wrap;
                                            word-wrap: break-word;
                                            line-height: 1.6;
                                            font-size: 14px;
                                            color: #d4d4d4;
                                            max-width: 900px;
                                            margin: 0 auto;
                                        }
                                        .line-number {
                                            color: #6a9955;
                                            margin-right: 1rem;
                                            user-select: none;
                                            display: inline-block;
                                            width: 40px;
                                            text-align: right;
                                        }
                                        .line {
                                            display: flex;
                                        }
                                    </style>
                                </head>
                                <body>
                                    <div class="txt-container">
                                        <div class="txt-content" id="content"></div>
                                    </div>
                                    <script>
                                        (function() {
                                            const text = ${JSON.stringify(text)};
                                            const lines = text.split('\\n');
                                            let html = '';
                                            lines.forEach((line, index) => {
                                                html += '<div class="line"><span class="line-number">' + (index + 1).toString().padStart(3, ' ') + '</span> ' + escapeHtml(line) + '</div>';
                                            });
                                            document.getElementById('content').innerHTML = html;
                                            
                                            function escapeHtml(unsafe) {
                                                return unsafe
                                                    .replace(/&/g, "&amp;")
                                                    .replace(/</g, "&lt;")
                                                    .replace(/>/g, "&gt;")
                                                    .replace(/"/g, "&quot;")
                                                    .replace(/'/g, "&#039;");
                                            }
                                        })();
                                    <\/script>
                                </body>
                                </html>
                            `;
                        })
                        .catch(() => {
                            iframe.srcdoc = `<html><body style="display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f5f5;font-family:Arial,sans-serif;"><div style="text-align:center;padding:2rem;background:white;border-radius:8px;color:#dc3545;"><h5>Error al cargar el archivo</h5><p>No se pudo cargar el contenido del archivo de texto.</p></div></body></html>`;
                        });
                } else {
                    iframe.srcdoc = `<html><body style="display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f5f5;font-family:Arial,sans-serif;"><div style="text-align:center;padding:2rem;background:white;border-radius:8px;max-width:400px;"><h5>Vista previa no disponible</h5><p>Este tipo de archivo no se puede visualizar en el navegador.</p><p class="text-muted small">Usa el botón de descargar para ver el contenido.</p></div></body></html>`;
                }
            }, 50);
            modalArchivo.show();
        }
    }

    function limpiarValidaciones() {
        const form = document.getElementById('form-formato');
        form.classList.remove('was-validated');
        
        const inputs = form.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        document.getElementById('error-archivo').style.display = 'none';
    }

    function validarFormatoNuevo() {
        const form = document.getElementById('form-formato');
        const proceso = document.getElementById('select-proceso');
        const departamento = document.getElementById('select-departamento');
        const clave = document.getElementById('input-clave');
        const codigo = document.getElementById('input-codigo');
        const version = document.getElementById('input-version');
        const archivo = document.getElementById('input-archivo');
        
        let isValid = true;
        
        if (!proceso.value) {
            proceso.classList.add('is-invalid');
            isValid = false;
        } else {
            proceso.classList.remove('is-invalid');
        }
        
        if (!departamento.value) {
            departamento.classList.add('is-invalid');
            isValid = false;
        } else {
            departamento.classList.remove('is-invalid');
        }
        
        if (!clave.value.trim()) {
            clave.classList.add('is-invalid');
            isValid = false;
        } else {
            clave.classList.remove('is-invalid');
        }
        
        if (!codigo.value.trim()) {
            codigo.classList.add('is-invalid');
            isValid = false;
        } else {
            codigo.classList.remove('is-invalid');
        }
        
        if (!version.value.trim()) {
            version.classList.add('is-invalid');
            isValid = false;
        } else {
            version.classList.remove('is-invalid');
        }
        
        if (!formatoEditandoId && (!archivo.files || archivo.files.length === 0)) {
            archivo.classList.add('is-invalid');
            document.getElementById('error-archivo').style.display = 'block';
            isValid = false;
        } else {
            archivo.classList.remove('is-invalid');
            document.getElementById('error-archivo').style.display = 'none';
        }
        
        if (!isValid) {
            form.classList.add('was-validated');
        }
        
        return isValid;
    }

    function sanitizarNombre(nombre) {
        return nombre
            .replace(/[^\w\sáéíóúÁÉÍÓÚñÑ-]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function actualizarPreviewNombreEdit() {
        const nombre = document.getElementById('input-nombre-archivo-edit').value.trim();
        const extension = document.getElementById('extension-edit-preview').textContent;
        const previewDiv = document.getElementById('nombre-edit-preview');
        const previewText = document.getElementById('preview-edit-text');
        
        if (nombre && extension) {
            const nombreFinal = sanitizarNombre(nombre) + extension;
            previewText.textContent = nombreFinal;
            previewDiv.classList.remove('d-none');
        } else {
            previewDiv.classList.add('d-none');
        }
    }

    function cargarDepartamentos(proceso, valorActual = '') {
        const sel = document.getElementById('select-departamento');
        if (!sel) return;
        const deps = procesosYDepartamentos[proceso] || [];
        sel.innerHTML = deps.length === 0
            ? '<option value="">— Sin departamentos —</option>'
            : deps.map(d => `<option value="${d}" ${d === valorActual ? 'selected' : ''}>${d}</option>`).join('');
        
        if (!formatoEditandoId) {
            sel.classList.remove('is-invalid');
        }
    }

    function mostrarArchivoSeleccionado(inp) {
        const box = document.getElementById('archivo-seleccionado');
        const lbl = document.getElementById('nombre-archivo-seleccionado');
        if (!box || !lbl) return;
        if (inp.files && inp.files[0]) {
            lbl.textContent = inp.files[0].name;
            box.classList.add('show');
            box.classList.remove('d-none');
            inp.classList.remove('is-invalid');
        } else {
            box.classList.remove('show');
            box.classList.add('d-none');
        }
    }

    function limpiarArchivo() {
        const inputArchivo = document.getElementById('input-archivo');
        inputArchivo.value = '';
        const box = document.getElementById('archivo-seleccionado');
        box.classList.remove('show');
        box.classList.add('d-none');
    }

    function abrirModalNuevo() {
        formatoEditandoId = null;

        document.getElementById('modal-titulo').textContent = 'Subir nuevo formato';
        document.getElementById('btn-guardar-texto').textContent = 'Subir Archivo';

        const form = document.getElementById('form-formato');
        form.action = "{{ route('formatos.store') }}";
        form.method = "POST";

        document.getElementById('form-method').value = 'POST';
        document.getElementById('formato-id').value = '';

        limpiarValidaciones();

        const selectProceso = document.getElementById('select-proceso');
        if (selectProceso) selectProceso.value = '';

        const selectDepartamento = document.getElementById('select-departamento');
        if (selectDepartamento) selectDepartamento.innerHTML = '<option value="">— Primero selecciona un proceso —</option>';

        const inputClave = document.getElementById('input-clave');
        if (inputClave) inputClave.value = '';

        const inputCodigo = document.getElementById('input-codigo');
        if (inputCodigo) inputCodigo.value = '';

        const inputVersion = document.getElementById('input-version');
        if (inputVersion) inputVersion.value = '';

        document.getElementById('campo-nombre-archivo').style.display = 'none';
        document.getElementById('info-archivo-actual').style.display = 'none';
        
        const inputArchivo = document.getElementById('input-archivo');
        if (inputArchivo) {
            inputArchivo.required = true;
            inputArchivo.value = '';
        }

        const archivoSeleccionado = document.getElementById('archivo-seleccionado');
        if (archivoSeleccionado) {
            archivoSeleccionado.classList.remove('show');
            archivoSeleccionado.classList.add('d-none');
        }

        const warn = document.getElementById('clave-warning');
        if (warn) warn.style.display = 'none';

        const reqArchivo = document.getElementById('req-archivo');
        if (reqArchivo) {
            reqArchivo.style.display = '';
            reqArchivo.textContent = '*';
        }

        const lblArchivoOpt = document.getElementById('lbl-archivo-opt');
        if (lblArchivoOpt) lblArchivoOpt.style.display = 'none';

        const modal = getModal();
        modal.show();
    }

    function abrirModalEditar(id, proceso, departamento, clave, codigo, version, nombreArchivo, extension) {
        formatoEditandoId = id;
        extensionActual = extension;

        document.getElementById('modal-titulo').textContent = 'Editar información del formato';
        document.getElementById('btn-guardar-texto').textContent = 'Actualizar formato';

        const form = document.getElementById('form-formato');
        form.action = `{{ url('formatos') }}/${id}`;

        document.getElementById('form-method').value = 'PUT';
        document.getElementById('formato-id').value = id;

        limpiarValidaciones();

        const selectProceso = document.getElementById('select-proceso');
        if (selectProceso) selectProceso.value = proceso;

        cargarDepartamentos(proceso, departamento);

        const inputClave = document.getElementById('input-clave');
        if (inputClave) inputClave.value = clave;

        const inputCodigo = document.getElementById('input-codigo');
        if (inputCodigo) inputCodigo.value = codigo;

        const inputVersion = document.getElementById('input-version');
        if (inputVersion) inputVersion.value = version;

        document.getElementById('campo-nombre-archivo').style.display = 'block';
        
        let nombreSinExtension = nombreArchivo;
        if (extension) {
            const extensionPattern = new RegExp('\\.' + extension + '$');
            nombreSinExtension = nombreArchivo.replace(extensionPattern, '');
        }
        
        const inputNombreEdit = document.getElementById('input-nombre-archivo-edit');
        if (inputNombreEdit) {
            inputNombreEdit.value = nombreSinExtension;
            inputNombreEdit.required = true;
        }
        
        const extensionPreview = document.getElementById('extension-edit-preview');
        if (extensionPreview) {
            extensionPreview.textContent = extension ? '.' + extension : '';
        }
        
        const infoArchivoActual = document.getElementById('info-archivo-actual');
        const nombreActualSpan = document.getElementById('nombre-archivo-actual');
        if (infoArchivoActual && nombreActualSpan) {
            nombreActualSpan.textContent = nombreArchivo;
            infoArchivoActual.style.display = 'block';
        }
        
        actualizarPreviewNombreEdit();

        const inputArchivo = document.getElementById('input-archivo');
        if (inputArchivo) {
            inputArchivo.required = false;
            inputArchivo.value = '';
        }

        const archivoSeleccionado = document.getElementById('archivo-seleccionado');
        if (archivoSeleccionado) {
            archivoSeleccionado.classList.remove('show');
            archivoSeleccionado.classList.add('d-none');
        }

        const reqArchivo = document.getElementById('req-archivo');
        if (reqArchivo) {
            reqArchivo.style.display = 'none';
            reqArchivo.textContent = '';
        }

        const lblArchivoOpt = document.getElementById('lbl-archivo-opt');
        if (lblArchivoOpt) lblArchivoOpt.style.display = 'inline';

        const warn = document.getElementById('clave-warning');
        if (warn) warn.style.display = 'none';

        const modal = getModal();
        modal.show();
    }

    function cerrarModal() {
        const modal = getModal();
        modal.hide();
    }

    function submitFormato() {
        if (formatoEditandoId) {
            const nombreEdit = document.getElementById('input-nombre-archivo-edit').value.trim();
            if (!nombreEdit) {
                alert('Debes especificar un nombre para el archivo.');
                return;
            }
            document.getElementById('form-formato').submit();
        } else {
            if (validarFormatoNuevo()) {
                document.getElementById('form-formato').submit();
            }
        }
    }

    const datosFiltro = {
        version: @json($versionesUnicas),
        codigo: @json($codigosUnicos),
        clave: @json($clavesUnicas),
        departamento: @json($departamentosUnicos),
    };
    
    const labelsFiltro = {
        version: 'Versión del procedimiento',
        codigo: 'Código de procedimiento',
        clave: 'Clave de formato',
        departamento: 'Departamento',
    };

    function cambiarTipoCampo(tipo) {
        const selValor = document.getElementById('select-valor-campo');
        const selTipo = document.getElementById('select-tipo-campo');

        if (!selValor || !selTipo) return;

        ['version', 'codigo', 'clave', 'departamento'].forEach(k => {
            const hidden = document.getElementById('hidden-' + k);
            if (hidden) hidden.value = '';
        });

        if (!tipo) {
            selValor.innerHTML = '<option value="">— Primero elige un campo —</option>';
            selValor.disabled = true;
            return;
        }
        
        selValor.disabled = false;
        const vals = datosFiltro[tipo] || [];
        selValor.innerHTML = `<option value="">— Selecciona ${labelsFiltro[tipo]} —</option>` +
            vals.map(v => `<option value="${tipo}:${v}">${v}</option>`).join('');
    }

    function marcarActivo(sel) {}

    document.addEventListener('DOMContentLoaded', () => {
        const ff = document.getElementById('form-filtros');
        if (ff) {
            ff.addEventListener('submit', function() {
                const selValor = document.getElementById('select-valor-campo');
                if (!selValor) return;

                const raw = selValor.value;
                ['version', 'codigo', 'clave', 'departamento'].forEach(k => {
                    const hidden = document.getElementById('hidden-' + k);
                    if (hidden) hidden.value = '';
                });

                if (raw && raw.includes(':')) {
                    const sep = raw.indexOf(':');
                    const campo = raw.substring(0, sep);
                    const valor = raw.substring(sep + 1);
                    const h = document.getElementById('hidden-' + campo);
                    if (h) h.value = valor;
                }
            });
        }
    });

    // ════════════════════════════════════════
    // MODAL GESTIONAR PROCESOS
    // ════════════════════════════════════════

    let procesoGestionandoNombre = null;

    function getModalGestionar() {
        const el = document.getElementById('modalNuevoProceso');
        return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, { backdrop: true, keyboard: true });
    }

    function abrirModalNuevoProceso(modo) {
        document.getElementById('vista-lista-procesos').style.display = '';
        document.getElementById('vista-deptos-proceso').style.display = 'none';
        procesoGestionandoNombre = null;
        setTimeout(() => {
            getModalGestionar().show();
            cargarListaProcesosDinamicos();
        }, 10);
    }

    function cerrarModalGestionar() {
        getModalGestionar().hide();
    }

    function cargarListaProcesosDinamicos() {
        const contenedor = document.getElementById('lista-procesos-dinamicos');
        contenedor.innerHTML = '<p class="text-muted small text-center py-3"><i class="bi bi-hourglass-split me-1"></i> Cargando...</p>';

        fetch('{{ route("formatos.procesos-departamentos.index") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                contenedor.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size:2rem; color:#dee2e6;"></i>
                        <p class="text-muted small mt-2 mb-0">No hay procesos creados aún.</p>
                        <p class="text-muted small">Usa el formulario <strong>"Subir Formato"</strong> para crear un proceso.</p>
                    </div>`;
                return;
            }

            let html = '';
            data.forEach(p => {
                const deptsJson = JSON.stringify(p.departamentos);
                html += `
                <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded"
                     style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px!important;">
                    <div>
                        <span class="fw-bold" style="font-size:0.88rem; color:#333;">${p.proceso}</span>
                        <small class="d-block text-muted" style="font-size:0.73rem;">
                            <i class="bi bi-building me-1"></i>${p.departamentos.length} departamento(s)
                        </small>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                style="height:30px; padding:0 8px; border-radius:5px; font-size:0.75rem; white-space:nowrap;"
                                title="Ver y gestionar departamentos"
                                onclick='abrirGestionDeptos(${JSON.stringify(p.proceso)}, ${deptsJson})'>
                            <i class="bi bi-building me-1" style="font-size:0.75rem;"></i>Deptos.
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                style="width:30px; height:30px; padding:0; border-radius:5px;"
                                title="Eliminar proceso y todos sus departamentos"
                                onclick='eliminarProceso(${JSON.stringify(p.proceso)})'>
                            <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                        </button>
                    </div>
                </div>`;
            });
            contenedor.innerHTML = html;
        })
        .catch(err => {
            console.error('Error cargando procesos:', err);
            contenedor.innerHTML = `
                <div class="text-center py-3">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    <p class="text-danger small mt-1 mb-2">Error al cargar procesos.</p>
                    <button class="btn btn-sm btn-outline-secondary" onclick="cargarListaProcesosDinamicos()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reintentar
                    </button>
                </div>`;
        });
    }

    function eliminarProceso(proceso) {
        Swal.fire({
            title: '¿Eliminar proceso?',
            text: `¿Estás seguro de eliminar "${proceso}" y todos sus departamentos?`,
            icon: 'warning',
            width: '600px',
            padding: '3rem',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (!result.isConfirmed) return;

        fetch('{{ route("formatos.procesos-departamentos.destroy") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ proceso }),
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!data.success) { alert('Error: ' + (data.message || 'No se pudo eliminar.')); return; }

            const selectProceso = document.getElementById('select-proceso');
            if (selectProceso) {
                const opt = selectProceso.querySelector(`option[value="${proceso}"]`);
                if (opt) opt.remove();
            }

            delete procesosYDepartamentos[proceso];

            cargarListaProcesosDinamicos();
        })
        .catch(err => {
            console.error('eliminarProceso error:', err);
            alert('Error de conexión al eliminar el proceso.');
        });
        });
    }

    function abrirGestionDeptos(proceso, departamentos) {
        procesoGestionandoNombre = proceso;
        document.getElementById('titulo-proceso-deptos').textContent = proceso;
        document.getElementById('nuevo-depto-input').value = '';
        document.getElementById('err-nuevo-depto').style.display = 'none';

        renderizarDeptosExistentes(departamentos);

        document.getElementById('vista-lista-procesos').style.display = 'none';
        document.getElementById('vista-deptos-proceso').style.display = '';

        document.getElementById('btnCancelarProceso').style.display = 'none';
    }

    function volverAListaProcesos() {
        document.getElementById('vista-lista-procesos').style.display = '';
        document.getElementById('vista-deptos-proceso').style.display = 'none';
        document.getElementById('btnCancelarProceso').style.display = '';
        procesoGestionandoNombre = null;
        cargarListaProcesosDinamicos();
    }

    function renderizarDeptosExistentes(departamentos) {
        const contenedor = document.getElementById('lista-deptos-existentes');

        if (!departamentos.length) {
            contenedor.innerHTML = '<p class="text-muted small">Sin departamentos registrados.</p>';
            return;
        }

        let html = '';
        departamentos.forEach(d => {
            html += `
            <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded depto-item"
                 style="background:#f8f9fa; border:1px solid #dee2e6;" data-depto="${d}">
                <span style="font-size:0.85rem; color:#333;">${d}</span>
                <button type="button" class="btn btn-sm btn-outline-danger"
                        style="width:26px; height:26px; padding:0; border-radius:4px;"
                        title="Eliminar departamento"
                        onclick="eliminarDepartamento('${d}')">
                    <i class="bi bi-x-lg" style="font-size:0.7rem;"></i>
                </button>
            </div>`;
        });
        contenedor.innerHTML = html;
    }

    function agregarDepartamentoAProceso() {
        const input = document.getElementById('nuevo-depto-input');
        const depto = input.value.trim().toUpperCase();
        const errEl = document.getElementById('err-nuevo-depto');

        if (!depto) { errEl.style.display = 'block'; input.focus(); return; }
        errEl.style.display = 'none';

        fetch('{{ route("formatos.procesos-departamentos.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ proceso: procesoGestionandoNombre, departamentos: [depto] }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) { alert('Error: ' + data.message); return; }

            input.value = '';

            if (!procesosYDepartamentos[procesoGestionandoNombre]) {
                procesosYDepartamentos[procesoGestionandoNombre] = [];
            }
            if (!procesosYDepartamentos[procesoGestionandoNombre].includes(depto)) {
                procesosYDepartamentos[procesoGestionandoNombre].push(depto);
            }

            renderizarDeptosExistentes(procesosYDepartamentos[procesoGestionandoNombre]);
        })
        .catch(() => alert('Error de conexión.'));
    }

    function eliminarDepartamento(departamento) {
        if (!confirm(`¿Eliminar el departamento "${departamento}" del proceso "${procesoGestionandoNombre}"?`)) return;

        fetch('{{ route("formatos.procesos-departamentos.destroyDepto") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ proceso: procesoGestionandoNombre, departamento }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) { alert('Error: ' + data.message); return; }

            if (procesosYDepartamentos[procesoGestionandoNombre]) {
                procesosYDepartamentos[procesoGestionandoNombre] =
                    procesosYDepartamentos[procesoGestionandoNombre].filter(d => d !== departamento);
            }

            renderizarDeptosExistentes(procesosYDepartamentos[procesoGestionandoNombre] || []);

            const sel = document.getElementById('select-proceso');
            if (sel && sel.value === procesoGestionandoNombre) {
                cargarDepartamentos(procesoGestionandoNombre);
            }
        })
        .catch(() => alert('Error de conexión.'));
    }

    // ════════════════════════════════════════
    // MODAL CREAR PROCESO
    // ════════════════════════════════════════

    function getModalCrearProceso() {
        const el = document.getElementById('modalCrearProceso');
        return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, { backdrop: 'static', keyboard: false });
    }

    function abrirModalCrearProceso() {
        document.getElementById('nuevo-proceso-nombre').value = '';
        document.getElementById('err-proceso-nombre').style.display = 'none';
        document.getElementById('err-deptos').style.display = 'none';
        const lista = document.getElementById('lista-departamentos-nuevos');
        lista.innerHTML = '';
        agregarFilaDepto();
        setTimeout(() => getModalCrearProceso().show(), 10);
    }

    function cerrarModalCrearProceso() {
        getModalCrearProceso().hide();
        const selectProceso = document.getElementById('select-proceso');
        if (selectProceso) selectProceso.value = '';
    }

    function agregarFilaDepto() {
        const lista = document.getElementById('lista-departamentos-nuevos');
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 mb-2 depto-row';
        row.innerHTML = `
            <input type="text" class="form-control input-depto"
                   placeholder="Ej: DIRECCIÓN GENERAL" maxlength="200">
            <button type="button" class="btn btn-outline-danger btn-sm px-2 btn-quitar-depto"
                    title="Quitar" onclick="quitarFilaDepto(this)">
                <i class="bi bi-x-lg"></i>
            </button>
        `;
        lista.appendChild(row);
        actualizarBotonesQuitar();
        row.querySelector('.input-depto').focus();
    }

    function quitarFilaDepto(btn) {
        btn.closest('.depto-row').remove();
        actualizarBotonesQuitar();
    }

    function actualizarBotonesQuitar() {
        const rows = document.querySelectorAll('#lista-departamentos-nuevos .depto-row');
        rows.forEach(row => {
            const btn = row.querySelector('.btn-quitar-depto');
            if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
        });
    }

    function guardarNuevoProceso() {
        const nombreInput = document.getElementById('nuevo-proceso-nombre');
        const nombre      = nombreInput.value.trim().toUpperCase();
        const errNombre   = document.getElementById('err-proceso-nombre');
        const errDeptos   = document.getElementById('err-deptos');

        if (!nombre) { errNombre.style.display = 'block'; nombreInput.focus(); return; }
        errNombre.style.display = 'none';

        const inputs        = document.querySelectorAll('#lista-departamentos-nuevos .input-depto');
        const departamentos = Array.from(inputs).map(i => i.value.trim().toUpperCase()).filter(v => v !== '');

        if (!departamentos.length) { errDeptos.style.display = 'block'; return; }
        errDeptos.style.display = 'none';

        const btnTexto = document.getElementById('btn-guardar-proceso-texto');
        btnTexto.textContent = 'Guardando...';

        fetch('{{ route("formatos.procesos-departamentos.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ proceso: nombre, departamentos }),
        })
        .then(r => r.json())
        .then(data => {
            btnTexto.textContent = 'Guardar proceso';
            if (!data.success) { alert('Error: ' + (data.message || 'No se pudo guardar.')); return; }

            const selectProceso = document.getElementById('select-proceso');
            const optNuevo      = selectProceso.querySelector('option[value="__nuevo__"]');

            const newOpt        = document.createElement('option');
            newOpt.value        = data.proceso;
            newOpt.textContent  = data.proceso + ' ✎';
            newOpt.dataset.dinamico = '1';
            newOpt.selected     = true;
            selectProceso.insertBefore(newOpt, optNuevo);

            procesosYDepartamentos[data.proceso] = data.departamentos;

            cargarDepartamentos(data.proceso);

            getModalCrearProceso().hide();
        })
        .catch(() => { 
            document.getElementById('btn-guardar-proceso-texto').textContent = 'Guardar proceso';
            alert('Error de conexión. Intenta de nuevo.'); 
        });
    }
</script>
@endpush