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
                        <h1 class="h3 mb-0" style="color:#16a34a; cursor:pointer;">
                            <i class="bi bi-file-earmark-text me-2" style="font-size: 3rem; vertical-align:middle;"></i>
                            Lista Maestra
                        </h1>
                    </a>
                </div>
            </div>
        </div>
    </div>

        
        {{-- ── ALERTAS ── --}}
    

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i> {{ session('info') }}
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
                        @if(request('tipo_documento'))<input type="hidden" name="tipo_documento" value="{{ request('tipo_documento') }}">@endif
                        <div class="input-group">
                            <input type="text" name="nombre" id="searchInput"
                                   class="form-control"
                                   placeholder="Buscar por nombre de archivo"
                                   value="{{ request('nombre') }}"
                                   style="background:#fff; border:1px solid #dee2e6; border-right:none;">
                            <button class="btn btn-clear-search" type="button" id="btn-limpiar-busqueda"
                                    onclick="limpiarBuscador()"
                                    title="Limpiar búsqueda"
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
                        @if(request('tipo_documento'))<input type="hidden" name="tipo_documento" value="{{ request('tipo_documento') }}">@endif
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
                                <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'tipo_documento'=>request('tipo_documento')])) }}"
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
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'tipo_documento'=>request('tipo_documento')])) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('codigo'))
                                <span class="badge rounded-pill" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                    Código: {{ request('codigo') }}
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'tipo_documento'=>request('tipo_documento')])) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('clave'))
                                <span class="badge rounded-pill" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                    Clave: {{ request('clave') }}
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'tipo_documento'=>request('tipo_documento')])) }}"
                                       class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                                </span>
                            @endif
                            @if(request('departamento'))
                                <span class="badge rounded-pill" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                    Departamento: {{ request('departamento') }}
                                    <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'tipo_documento'=>request('tipo_documento')])) }}"
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

    {{-- ── FILTRO POR TIPO DE DOCUMENTO ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius:8px;">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color:#333; font-size:0.85rem;">
                        <i class="bi bi-tag me-1"></i> Filtrar por tipo de documento
                    </label>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'version'=>request('version'), 'codigo'=>request('codigo'), 'clave'=>request('clave'), 'departamento'=>request('departamento')])) }}"
                           class="btn btn-sm {{ !request('tipo_documento') ? 'text-black' : '' }}"
                           style="{{ !request('tipo_documento') ? 'background:#ffffff; border-color:#000000;' : 'border:1px solid #000000'; }}">
                            Todos
                        </a>
                        <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'version'=>request('version'), 'codigo'=>request('codigo'), 'clave'=>request('clave'), 'departamento'=>request('departamento'), 'tipo_documento'=>'Formato'])) }}"
                           class="btn btn-sm {{ request('tipo_documento')==='Formato' ? 'text-black' : '' }}"
                           style="{{ request('tipo_documento')==='Formato' ? 'border-color:#000000; color:black;' : 'border:1px solid #737373; color:#000000;' }}">
                            📄 Formato
                        </a>
                        <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'version'=>request('version'), 'codigo'=>request('codigo'), 'clave'=>request('clave'), 'departamento'=>request('departamento'), 'tipo_documento'=>'Procedimiento'])) }}"
                           class="btn btn-sm {{ request('tipo_documento')==='Procedimiento' ? 'text-black' : '' }}"
                           style="{{ request('tipo_documento')==='Procedimiento' ? 'border-color:#000000; color:black;' : 'border:1px solid #737373; color:#000000;' }}">
                            📋 Procedimiento
                        </a>
                        @if(request('tipo_documento'))
                            <span class="badge rounded-pill ms-2" style="background:#e8f7ee; color:#1a6b3a; border:1px solid #b8e6c9; font-size:0.78rem;">
                                Tipo: {{ request('tipo_documento') }}
                                <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre'), 'version'=>request('version'), 'codigo'=>request('codigo'), 'clave'=>request('clave'), 'departamento'=>request('departamento')])) }}"
                                   class="ms-1 text-decoration-none" style="color:#1a6b3a;">✕</a>
                            </span>
                        @endif
                    </div>
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
                                    style="border:1px solid #000000; background:#ffffff; font-size:0.8rem; padding:4px 14px; border-radius:6px;">
                                <i class="bi bi-sort-down me-1"></i> Más reciente primero
                            </button>
                            <button type="button"
                                    id="btn-orden-asc"
                                    class="btn btn-sm orden-fecha-btn"
                                    onclick="ordenarPorFecha('asc')"
                                    style="border:1px solid #000000; background:#ffffff; color:#495057; font-size:0.8rem; padding:4px 14px; border-radius:6px;">
                                <i class="bi bi-sort-up-alt me-1"></i> Más antigua primero
                            </button>
                            <button type="button"
                                    id="btn-orden-ninguno"
                                    class="btn btn-sm orden-fecha-btn"
                                    onclick="ordenarPorFecha('ninguno')"
                                    style="border:1px solid #000000; background:#ffffff; color:#495057; font-size:0.8rem; padding:4px 14px; border-radius:6px;">
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
                <i class="bi bi-files me-2" style="color:#000000;"></i> Documentos
            </h6>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="formatosTable" style="border-collapse: collapse;">
                    <thead style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                        <tr>
                            <th class="px-4 py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Nombre del Documento</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Proceso</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Departamento</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Clave</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Código</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Versión</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Tipo</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Ext.</th>
                            <th class="py-3" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000; white-space:nowrap;">Fecha y Hora</th>
                            <th class="py-3 text-center" style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#000000;">Acciones</th>
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
                                {{ $formato->nombre_archivo }}
                            </td>

                            <td style="font-size:0.8rem; color:#000000;">
                                {{ $formato->proceso }}
                            </td>

                            <td style="font-size:0.8rem; color:#000000;">
                                {{ $formato->departamento }}
                            </td>

                            <td style="font-size:0.8rem; color:#000000;">
                                {{ $formato->clave_formato ?? '—' }}
                            </td>

                            <td style="font-size:0.8rem; color:#000000;">
                                {{ $formato->codigo_procedimiento ?? '—' }}
                            </td>

                            <td style="font-size:0.8rem; color:#000000;">
                                {{ $formato->version_procedimiento ?? '—' }}
                            </td>

                            <td>
                                @if($formato->tipo_documento === 'Procedimiento')
                                    <span class="badge" style="background-color:#6f42c1; font-size:0.7rem;">Procedimiento</span>
                                @elseif($formato->tipo_documento === 'Formato' || $formato->clave_formato)
                                    <span class="badge" style="background-color:#0d6efd; font-size:0.7rem;">Formato</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge ext-badge ext-{{ $tipoArchivo }}" style="font-size:0.7rem; padding:3px 8px;">
                                    {{ $formato->extension_archivo ?? 'N/A' }}
                                </span>
                            </td>

                            <td style="font-size:0.8rem; color:#000000; white-space:nowrap;">
                                {{ $formato->created_at->format('d/m/Y h:i A') }}
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
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

                                    <a href="{{ route('formatos.download', $formato) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       style="width:30px; height:30px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:5px;"
                                       title="Descargar">
                                        <i class="bi bi-download" style="font-size:0.85rem;"></i>
                                    </a>

                                    @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                                    <form action="{{ route('formatos.destroy', $formato->id) }}" 
                                          method="POST" 
                                          class="form-eliminar-formato"
                                          style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger eliminar-formato-btn"
                                                style="width:30px; height:30px; padding:0; border-radius:5px;"
                                                title="Eliminar"
                                                data-nombre="{{ $formato->nombre_archivo }}">
                                            <i class="bi bi-trash" style="font-size:0.85rem;"></i>
                                        </button>
                                    </form>
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
    @else
    <div class="alert alert-info text-center py-5">
        <i class="bi bi-info-circle fs-1"></i>
        <p class="mt-3 mb-0">No se encontraron documentos.</p>
    </div>
    @endif

</div>

{{-- MODAL PARA VISUALIZAR ARCHIVO --}}
<div class="modal fade" id="modalVisualizarArchivo" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px; border:none; overflow:hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background:white; color:#333; padding:1rem 1.5rem; border-bottom:1px solid #dee2e6;">
                <h5 class="modal-title fw-bold" id="modalArchivoTitulo" style="color:#333;">
                    <i class="bi bi-file-earmark-text me-2" style="color:#000000;"></i>
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
                    <button type="button" 
                            data-bs-dismiss="modal"
                            style="background-color: #737373; border: none; padding: 0.5rem 1.5rem; 
                                border-radius: 6px; color: white; font-size: 1rem; cursor: pointer;">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
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

    .table tbody tr:hover { background:#f8f9fa; }

    @media (max-width: 768px) {
        .table td, .table th { font-size: 0.8rem; }
    }

    .btn-clear-search:hover {
        background-color: #737373 !important;
        border-color: #737373 !important;
    }
    .btn-clear-search:hover i {
        color: white !important;
    }

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

    function getModalArchivo() {
        const el = document.getElementById('modalVisualizarArchivo');
        return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, { backdrop: true });
    }

    function extraerFecha(fechaStr) {
        if (!fechaStr) return new Date(0);
        const fecha = new Date(fechaStr.replace(' ', 'T'));
        return isNaN(fecha.getTime()) ? new Date(0) : fecha;
    }

    function ordenarPorFecha(direccion) {
        ordenFechaActual = direccion;

        document.querySelectorAll('.orden-fecha-btn').forEach(btn => {
            btn.classList.remove('activo-orden');
            btn.style.borderColor = '#dee2e6';
            btn.style.background  = '#f8f9fa';
            btn.style.color       = '#495057';
            btn.style.fontWeight  = 'normal';
        });

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

    function reRegistrarEventosFilas() {
        document.querySelectorAll('.visualizar-archivo').forEach(btn => {
            btn.removeEventListener('click', handleVisualizarClick);
            btn.addEventListener('click', handleVisualizarClick);
        });
        
        document.querySelectorAll('.eliminar-formato-btn').forEach(btn => {
            btn.removeEventListener('click', handleEliminarClick);
            btn.addEventListener('click', handleEliminarClick);
        });
    }

    function handleVisualizarClick(e) {
        e.preventDefault();
        const btn = e.currentTarget;
        mostrarVistaArchivo(btn.dataset.url, btn.dataset.nombre, btn.dataset.tipo);
    }

    function handleEliminarClick(e) {
        e.preventDefault();
        const btn = e.currentTarget;
        const form = btn.closest('.form-eliminar-formato');
        const nombre = btn.dataset.nombre;
        
        Swal.fire({
            title: '¿Eliminar archivo?',
            text: `¿Estás seguro de eliminar "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                proceedWithDeletion(form);
            }
        });
    }

    function proceedWithDeletion(form) {
        Swal.fire({
            title: 'Eliminando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const formData = new FormData(form);
        
        // Obtener el token CSRF del meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: data.message,
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al eliminar el archivo',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Error de conexión al eliminar el archivo. Por favor, intenta de nuevo.',
                confirmButtonColor: '#dc3545'
            });
        });
    }

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
                    iframe.srcdoc = `<html><head><style>body{margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f5f5;}img{max-width:100%;max-height:90vh;object-fit:contain;box-shadow:0 4px 8px rgba(0,0,0,0.1);}</style></head><body><img src="${url}" alt="${nombre}"></body></html>`;
                } else {
                    iframe.srcdoc = `<html><body style="display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f5f5;font-family:Arial,sans-serif;"><div style="text-align:center;padding:2rem;background:white;border-radius:8px;max-width:400px;"><h5>Vista previa no disponible</h5><p>Usa el botón de descargar para ver el contenido.</p></div></body></html>`;
                }
            }, 50);
            modalArchivo.show();
        }
    }

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

        setTimeout(() => ordenarPorFecha('desc'), 100);

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
                            if (o.value === target) { o.selected = true; break; }
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

        setTimeout(() => {
            const a = document.getElementById('alerta-principal');
            if (a) {
                setTimeout(() => {
                    try { 
                        const alert = bootstrap.Alert.getOrCreateInstance(a);
                        alert.close();
                    } catch(e) {
                        a.style.display = 'none';
                    }
                }, 5000);
            }
        }, 100);

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
                    const sep   = raw.indexOf(':');
                    const campo = raw.substring(0, sep);
                    const valor = raw.substring(sep + 1);
                    const h     = document.getElementById('hidden-' + campo);
                    if (h) h.value = valor;
                }
            });
        }

        document.querySelectorAll('.visualizar-archivo').forEach(btn => {
            btn.addEventListener('click', handleVisualizarClick);
        });
        
        document.querySelectorAll('.eliminar-formato-btn').forEach(btn => {
            btn.addEventListener('click', handleEliminarClick);
        });

        const btnDescargar = document.getElementById('btnDescargarArchivo');
        if (btnDescargar) {
            btnDescargar.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                if (url) window.location.href = url;
            });
        }
    });

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
        if (!selValor) return;

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
</script>
@endpush