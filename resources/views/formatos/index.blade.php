@extends('layouts.app')

@section('title', 'Formatos - Sistema de Gestión de la Calidad')

@section('content')
<div class="container-fluid py-4">

    {{-- ── ENCABEZADO DEL MÓDULO ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column">
                    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Regresar al Dashboard">
                        <h1 class="h3 mb-0" style="color:#800000; cursor:pointer;">
                            <i class="bi bi-file-earmark-text me-2" style="font-size:2.6rem; vertical-align:middle;"></i>
                            FORMATOS
                        </h1>
                    </a>
                    <small class="text-muted ms-1" style="font-size:0.75rem; letter-spacing:0.5px; text-transform:uppercase;">
                        Sistema de Gestión de la Calidad
                    </small>
                </div>

                <div class="mt-2">
                    <button type="button" class="btn text-white"
                            style="background-color:#800000;"
                            onclick="abrirModalNuevo()">
                        <i class="bi bi-upload me-1"></i> Subir Formato
                    </button>
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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i> <strong>Errores en el formulario:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── FILTROS ── --}}
    <div class="row mb-4 align-items-end">
        <div class="col-md-5">
            <div class="card shadow-sm border-0" style="border-radius:12px;">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color:#800000; font-size:0.82rem;">
                        <i class="bi bi-search me-1"></i> Buscar por nombre
                    </label>
                    <form method="GET" action="{{ route('formatos.index') }}" id="form-nombre">
                        {{-- conservar otros filtros activos --}}
                        @if(request('version'))<input type="hidden" name="version" value="{{ request('version') }}">@endif
                        @if(request('codigo'))<input type="hidden"  name="codigo"  value="{{ request('codigo') }}">@endif
                        @if(request('clave'))<input type="hidden"   name="clave"   value="{{ request('clave') }}">@endif
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-secondary"></i>
                            </span>
                            <input type="text" name="nombre" id="searchInput"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="Buscar por nombre de formato"
                                   value="{{ request('nombre') }}"
                                   style="background:#fff;">
                            <button class="btn btn-outline-secondary" type="submit" title="Buscar">
                                <i class="bi bi-search"></i>
                            </button>
                            @if(request('nombre'))
                                <a href="{{ route('formatos.index', array_filter(['version'=>request('version'),'codigo'=>request('codigo'),'clave'=>request('clave')])) }}"
                                   class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm border-0" style="border-radius:12px;">
                <div class="card-body p-3">
                    <label class="form-label fw-bold mb-2" style="color:#800000; font-size:0.82rem;">
                        <i class="bi bi-funnel me-1"></i> Filtrar por campo específico
                    </label>
                    <form method="GET" action="{{ route('formatos.index') }}" id="form-filtros">
                        @if(request('nombre'))<input type="hidden" name="nombre" value="{{ request('nombre') }}">@endif
                        <div class="d-flex gap-2">
                            <select id="select-tipo-campo"
                                    class="form-select {{ (request('version')||request('codigo')||request('clave')) ? 'border-success' : '' }}"
                                    onchange="cambiarTipoCampo(this.value)"
                                    style="flex:0 0 210px; max-width:210px;">
                                <option value="">— Elegir campo —</option>
                                <option value="version" {{ request('version') ? 'selected':'' }}>📋 Versión</option>
                                <option value="codigo"  {{ request('codigo')  ? 'selected':'' }}>🔢 Código de procedimiento</option>
                                <option value="clave"   {{ request('clave')   ? 'selected':'' }}>🔑 Clave de formato</option>
                            </select>

                            <select id="select-valor-campo"
                                    name="filtro_valor"
                                    class="form-select {{ (request('version')||request('codigo')||request('clave')) ? 'border-success' : '' }}"
                                    onchange="marcarActivo(this)"
                                    {{ !(request('version')||request('codigo')||request('clave')) ? 'disabled':'' }}>
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
                            </select>

                            <input type="hidden" name="version" id="hidden-version" value="{{ request('version') }}">
                            <input type="hidden" name="codigo"  id="hidden-codigo"  value="{{ request('codigo') }}">
                            <input type="hidden" name="clave"   id="hidden-clave"   value="{{ request('clave') }}">

                            <button type="submit" class="btn text-white px-3" style="background-color:#800000; white-space:nowrap;">
                                <i class="bi bi-search"></i> Aplicar
                            </button>
                            @if(request('version')||request('codigo')||request('clave'))
                                <a href="{{ route('formatos.index', array_filter(['nombre'=>request('nombre')])) }}"
                                   class="btn btn-outline-secondary px-3" title="Limpiar filtro">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>

                        {{-- Tags filtros activos --}}
                        @if(request('version')||request('codigo')||request('clave'))
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
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABLA DE FORMATOS ── --}}
    <div class="card shadow-sm border-0" style="border-radius:12px; overflow:hidden;">
        <div class="card-header d-flex align-items-center justify-content-between py-3 px-4"
             style="background:linear-gradient(135deg, #800000, #9b2226); border:none;">
            <h6 class="mb-0 text-white fw-bold">
                <i class="bi bi-table me-2"></i> Documentos y Formatos registrados
            </h6>
            <span class="badge rounded-pill bg-white" style="color:#800000; font-size:0.8rem;">
                {{ $formatos->count() }} {{ $formatos->count()==1 ? 'registro':'registros' }}
            </span>
        </div>

        <div class="card-body p-0">
            @if($formatos->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="formatosTable">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th class="px-4 py-3" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d; white-space:nowrap;">#</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Proceso</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Departamento</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Clave Formato</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Código Proced.</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Versión</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Nombre Archivo</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Ext.</th>
                            <th class="py-3"       style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d; white-space:nowrap;">Fecha Subida</th>
                            <th class="py-3 text-center" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px; color:#6c757d;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($formatos as $i => $formato)
                        @php
                            $tipoArchivo = \App\Http\Controllers\FormatoController::tipoArchivo($formato->extension_archivo);
                            $puedeVer    = in_array($tipoArchivo, ['imagen', 'pdf']);
                        @endphp
                        <tr class="formato-row" data-file-name="{{ strtolower($formato->nombre_archivo) }}">
                            <td class="px-4 text-muted" style="font-size:0.8rem;">{{ $i + 1 }}</td>

                            <td>
                                <span class="badge rounded-pill"
                                      style="background:#fff3e0; color:#e65100; font-size:0.68rem; font-weight:700; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block;"
                                      title="{{ $formato->proceso }}">
                                    {{ $formato->proceso }}
                                </span>
                            </td>

                            <td style="font-size:0.83rem; color:#495057; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                title="{{ $formato->departamento }}">
                                {{ $formato->departamento }}
                            </td>

                            <td>
                                <code style="font-size:0.78rem; color:#800000; background:#fff5f5; padding:2px 6px; border-radius:4px;">
                                    {{ $formato->clave_formato }}
                                </code>
                            </td>

                            <td>
                                <code style="font-size:0.78rem; color:#1a6b3a; background:#f0fff4; padding:2px 6px; border-radius:4px;">
                                    {{ $formato->codigo_procedimiento }}
                                </code>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill" style="font-size:0.72rem;">
                                    {{ $formato->version_procedimiento }}
                                </span>
                            </td>

                            <td class="formato-nombre"
                                style="font-size:0.84rem; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                title="{{ $formato->nombre_archivo }}">
                                {{ $formato->nombre_archivo }}
                            </td>

                            <td>
                                @php
                                    $extClasses = [
                                        'imagen' => 'badge-img',
                                        'pdf'    => 'badge-pdf',
                                        'office' => 'badge-office',
                                        'otro'   => 'badge-otro',
                                    ];
                                @endphp
                                <span class="badge ext-badge ext-{{ $tipoArchivo }}">
                                    {{ $formato->extension_archivo ?? 'N/A' }}
                                </span>
                            </td>

                            <td style="font-size:0.78rem; color:#6c757d; white-space:nowrap;">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ $formato->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">

                                    {{-- VER: imágenes y PDF --}}
                                    @if($puedeVer)
                                    <a href="{{ route('formatos.show', $formato) }}"
                                       class="btn btn-sm btn-outline-primary visualizar-archivo"
                                       style="width:30px; height:30px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:7px; cursor:pointer;"
                                       title="{{ $tipoArchivo === 'imagen' ? 'Ver imagen' : 'Ver PDF' }}"
                                       data-id="{{ $formato->id }}">
                                        <i class="bi bi-eye" style="font-size:0.8rem;"></i>
                                    </a>
                                    @else
                                    <button class="btn btn-sm btn-outline-secondary disabled"
                                            style="width:30px; height:30px; padding:0; border-radius:7px; opacity:0.3; cursor:not-allowed;"
                                            title="Vista previa no disponible ({{ $formato->extension_archivo }})">
                                        <i class="bi bi-eye-slash" style="font-size:0.8rem;"></i>
                                    </button>
                                    @endif

                                    {{-- DESCARGAR --}}
                                    <a href="{{ route('formatos.download', $formato) }}"
                                       class="btn btn-sm btn-outline-success"
                                       style="width:30px; height:30px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:7px;"
                                       title="Descargar">
                                        <i class="bi bi-download" style="font-size:0.8rem;"></i>
                                    </a>

                                    {{-- EDITAR --}}
                                    <button class="btn btn-sm btn-outline-warning editar-formato"
                                            style="width:30px; height:30px; padding:0; border-radius:7px;"
                                            title="Editar información"
                                            data-id="{{ $formato->id }}"
                                            data-proceso="{{ $formato->proceso }}"
                                            data-departamento="{{ $formato->departamento }}"
                                            data-clave="{{ $formato->clave_formato }}"
                                            data-codigo="{{ $formato->codigo_procedimiento }}"
                                            data-version="{{ $formato->version_procedimiento }}"
                                            data-nombre="{{ $formato->nombre_archivo }}"
                                            data-extension="{{ $formato->extension_archivo }}">
                                        <i class="bi bi-pencil" style="font-size:0.8rem;"></i>
                                    </button>

                                    {{-- ELIMINAR --}}
                                    <button class="btn btn-sm btn-outline-danger eliminar-formato"
                                            style="width:30px; height:30px; padding:0; border-radius:7px;"
                                            title="Eliminar"
                                            data-id="{{ $formato->id }}"
                                            data-nombre="{{ $formato->nombre_archivo }}">
                                        <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-x" style="font-size:4rem; color:#dee2e6;"></i>
                <h5 class="mt-3 text-muted">No hay formatos registrados</h5>
                <p class="text-muted small">Haz clic en <strong>"Subir Formato"</strong> para agregar el primer documento.</p>
                <button class="btn text-white mt-2" style="background-color:#800000;" onclick="abrirModalNuevo()">
                    <i class="bi bi-upload me-1"></i> Subir Formato
                </button>
            </div>
            @endif
        </div>
    </div>

</div>{{-- /container-fluid --}}

{{-- ══════ MODAL SUBIR / EDITAR ══════ --}}
<div class="modal fade" id="modalFormato" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:14px; border:none;">

            <div class="modal-header border-bottom" style="border-radius:14px 14px 0 0; padding:1.1rem 1.4rem;">
                <h5 class="modal-title fw-bold" id="modal-titulo" style="color:#1a1a1a; font-size:1.05rem;">Subir Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="form-formato" method="POST" enctype="multipart/form-data" action="{{ route('formatos.store') }}">
                    @csrf
                    <input type="hidden" name="_method"    id="form-method"  value="POST">
                    <input type="hidden" name="formato_id" id="formato-id"   value="">

                    <div class="row g-3">

                        {{-- Proceso --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-uppercase" style="color:#800000; letter-spacing:0.4px;">
                                Proceso <span class="text-danger">*</span>
                            </label>
                            <select name="proceso" id="select-proceso" class="form-select" required
                                    onchange="cargarDepartamentos(this.value)">
                                <option value="">— Selecciona un proceso —</option>
                                @foreach(array_keys($procesosYDepartamentos) as $proc)
                                    <option value="{{ $proc }}">{{ $proc }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Departamento --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-uppercase" style="color:#800000; letter-spacing:0.4px;">
                                Departamento <span class="text-danger">*</span>
                            </label>
                            <select name="departamento" id="select-departamento" class="form-select" required>
                                <option value="">— Primero selecciona un proceso —</option>
                            </select>
                        </div>

                        {{-- Clave de formato --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase" style="color:#800000; letter-spacing:0.4px;">
                                Clave de formato <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="clave_formato" id="input-clave"
                                   class="form-control" placeholder="Ej: FO-SGC-001"
                                   required maxlength="100">
                            <div id="clave-warning" class="alert alert-warning py-1 px-2 mt-1 mb-0 small fw-bold" style="display:none;">
                                <i class="bi bi-exclamation-triangle me-1"></i> LA CLAVE DE FORMATO ESTÁ REPETIDA, MODIFÍCALA
                            </div>
                        </div>

                        {{-- Código de procedimiento --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase" style="color:#800000; letter-spacing:0.4px;">
                                Código de procedimiento <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="codigo_procedimiento" id="input-codigo"
                                   class="form-control" placeholder="Ej: PR-001"
                                   required maxlength="100">
                        </div>

                        {{-- Versión --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-uppercase" style="color:#800000; letter-spacing:0.4px;">
                                Versión del procedimiento <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="version_procedimiento" id="input-version"
                                   class="form-control" placeholder="Ej: 1.0 / v2 / Rev. A"
                                   required maxlength="50">
                        </div>

                        {{-- NUEVO CAMPO: NOMBRE DEL ARCHIVO PARA EDITAR --}}
                        <div class="col-12" id="campo-nombre-archivo" style="display: none;">
                            <label class="form-label fw-bold small text-uppercase" style="color:#800000; letter-spacing:0.4px;">
                                Nombre del archivo <span class="text-danger">*</span>
                                <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" title="Puedes cambiar el nombre con el que se guarda el archivo. No es necesario incluir la extensión."></i>
                            </label>
                            <div class="input-group">
                                <input type="text" name="nombre_archivo" id="input-nombre-archivo-edit"
                                       class="form-control" placeholder="Ej: Formato de inscripción 2024"
                                       maxlength="255">
                                <span class="input-group-text" id="extension-edit-preview"></span>
                            </div>
                            <small class="text-muted">El nombre será sanitizado automáticamente (sin caracteres especiales). La extensión se mantendrá automáticamente.</small>
                            <div id="nombre-edit-preview" class="mt-2 p-2 bg-light rounded d-none">
                                <small class="fw-bold">Vista previa:</small>
                                <span id="preview-edit-text" class="ms-2"></span>
                            </div>
                        </div>

                        {{-- Zona de carga --}}
                        <div class="col-12">
                            <label class="form-label fw-bold small text-uppercase" style="color:#800000; letter-spacing:0.4px;">
                                Archivo <span class="text-danger" id="req-archivo">*</span>
                                <span id="lbl-archivo-opt" class="text-muted fw-normal" style="display:none; text-transform:none; letter-spacing:0;">
                                    (opcional — deja vacío para conservar el actual)
                                </span>
                            </label>
                            <div id="upload-zona" class="upload-zona-bs rounded-3 p-4 text-center position-relative"
                                 style="border:2px dashed #b8e6c9; background:#f0fff4; cursor:pointer; transition:all 0.2s;">
                                <input type="file" name="archivo" id="input-archivo"
                                       class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                       style="cursor:pointer; z-index:2;"
                                       onchange="mostrarArchivoSeleccionado(this)">
                                <i class="bi bi-cloud-upload" style="font-size:2.2rem; color:#2d9e59;"></i>
                                <p class="mb-0 mt-2 fw-500" style="color:#1a6b3a; font-size:0.9rem;">
                                    Arrastra tu archivo aquí o <strong>haz clic para seleccionar</strong>
                                </p>
                                <small class="text-muted">Imágenes, PDF, Word, Excel, CSV y más · Máx. 20 MB</small>
                            </div>
                            <div id="archivo-seleccionado" class="d-none align-items-center gap-2 mt-2 p-2 rounded-2"
                                 style="background:#fff; border:1.5px solid #b8e6c9;">
                                <i class="bi bi-file-earmark-check text-success"></i>
                                <span id="nombre-archivo-seleccionado" class="small fw-500 text-truncate"></span>
                                <button type="button" class="btn btn-sm btn-link ms-auto" onclick="limpiarArchivo()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Información del archivo actual (solo visible en edición) --}}
                        <div id="info-archivo-actual" class="col-12" style="display: none;">
                            <div class="alert alert-info py-2 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Archivo actual:</strong> <span id="nombre-archivo-actual"></span>
                                <small class="d-block mt-1">Si seleccionas un archivo nuevo, se reemplazará el actual.</small>
                            </div>
                        </div>

                    </div>{{-- /row --}}
                </form>
            </div>

            <div class="modal-footer border-top" style="padding:0.9rem 1.4rem; border-radius:0 0 14px 14px;">
                <button type="button" class="btn px-4 fw-500"
                        style="background:#6c757d; color:#fff; border:none; border-radius:6px;"
                        onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn px-4 fw-bold text-white"
                        style="background:#800000; border:none; border-radius:6px;"
                        onmouseover="this.style.background='#9b2226'" onmouseout="this.style.background='#800000'"
                        onclick="submitFormato()">
                    <span id="btn-guardar-texto">Subir Archivo</span>
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Iframe oculto para visualizar archivos sin abrir nueva pestaña --}}
<iframe id="visor-archivo" style="display:none;"></iframe>

{{-- Form oculto DELETE --}}
<form id="form-eliminar" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<style>
    /* Badges de extensión */
    .ext-badge { font-size:0.7rem; font-weight:700; padding:3px 8px; border-radius:4px; font-family:'Courier New',monospace; }
    .ext-imagen { background:#e8f4fd; color:#1a70b8; border:1px solid #bee3f8; }
    .ext-pdf    { background:#fdedec; color:#c0392b; border:1px solid #fab3ad; }
    .ext-office { background:#eafaf1; color:#1d7a40; border:1px solid #a9dfbf; }
    .ext-otro   { background:#f5f0ff; color:#6b46c1; border:1px solid #d6bcfa; }

    /* Tabla hover */
    .table-hover tbody tr:hover { background:rgba(128,0,0,0.03); }

    /* Zona upload hover */
    #upload-zona:hover { border-color:#2d9e59 !important; background:#e2f8ec !important; }
    #upload-zona.drag-over { border-color:#1a6b3a !important; background:#d1f5e0 !important; }
    #archivo-seleccionado.show { display:flex !important; }

    /* Botones de acción */
    .btn-outline-primary { border-color:#0d6efd; color:#0d6efd; }
    .btn-outline-success { border-color:#198754; color:#198754; }
    .btn-outline-warning { border-color:#ffc107; color:#856404; }
    .btn-outline-danger  { border-color:#dc3545; color:#dc3545; }
    .btn-outline-primary:hover { background:#0d6efd; color:#fff; }
    .btn-outline-success:hover { background:#198754; color:#fff; }
    .btn-outline-warning:hover { background:#ffc107; color:#000; }
    .btn-outline-danger:hover  { background:#dc3545; color:#fff; }

    /* Clave warning en modal */
    #clave-warning { font-size:0.78rem; }

    /* Filtro select con color cuando activo */
    .border-success { border-color:#198754 !important; }
    
    /* Asegurar que los botones sean clickeables */
    .formato-row .btn {
        position: relative;
        z-index: 10;
        pointer-events: auto;
    }
    
    .table td .d-flex {
        position: relative;
        z-index: 15;
    }
</style>
@endpush

@push('scripts')
<script>
    const procesosYDepartamentos = @json($procesosYDepartamentos);
    const clavesExistentes       = @json($formatos->pluck('clave_formato')->toArray());
    let formatoEditandoId        = null;
    let extensionActual          = '';

    // Función para obtener el modal de Bootstrap
    function getModal() {
        const el = document.getElementById('modalFormato');
        return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, { keyboard: true, backdrop: true });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Validación clave duplicada
        const inputClave = document.getElementById('input-clave');
        if (inputClave) {
            inputClave.addEventListener('input', function () {
                const val = this.value.trim();
                const warn = document.getElementById('clave-warning');
                const dup = val && clavesExistentes.some(c => c.toLowerCase() === val.toLowerCase());
                if (warn) warn.style.display = dup ? 'block' : 'none';
            });
        }

        // Drag & drop zona de archivo
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

        // Re-abrir modal si hay errores de validación
        @if($errors->any())
            setTimeout(() => abrirModalNuevo(), 100);
        @endif

        // Inicializar filtro combinado al cargar si hay filtro en URL
        (function() {
            const tipoActivo = @json(request('version') ? 'version' : (request('codigo') ? 'codigo' : (request('clave') ? 'clave' : '')));
            const valorActivo = @json(request('version') ?: (request('codigo') ?: (request('clave') ?: '')));
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
                        sel.classList.add('border-success');
                    }
                }
            }
        })();

        // Búsqueda en tiempo real dentro de la tabla (solo visual, no recarga)
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

        // Auto-ocultar alertas de éxito a los 5s
        setTimeout(() => {
            const a = document.getElementById('alerta-principal');
            if (a) {
                try {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(a);
                    bsAlert.close();
                } catch(e) {}
            }
        }, 5000);

        // Delegación de eventos para botones de acción
        const tablaBody = document.querySelector('#formatosTable tbody');
        if (tablaBody) {
            // Visualizar archivo
            tablaBody.addEventListener('click', function(e) {
                const visualizarBtn = e.target.closest('.visualizar-archivo');
                if (visualizarBtn) {
                    e.preventDefault();
                    const url = visualizarBtn.href;
                    if (url) {
                        const iframe = document.getElementById('visor-archivo');
                        iframe.src = url;
                        iframe.style.display = 'block';
                        iframe.style.width = '100%';
                        iframe.style.height = '80vh';
                        iframe.style.position = 'fixed';
                        iframe.style.top = '10%';
                        iframe.style.left = '0';
                        iframe.style.zIndex = '9999';
                        iframe.style.backgroundColor = '#fff';
                        
                        const cerrarBtn = document.createElement('button');
                        cerrarBtn.innerHTML = '✕ Cerrar';
                        cerrarBtn.style.position = 'fixed';
                        cerrarBtn.style.top = '5%';
                        cerrarBtn.style.right = '20px';
                        cerrarBtn.style.zIndex = '10000';
                        cerrarBtn.style.padding = '10px 20px';
                        cerrarBtn.style.backgroundColor = '#800000';
                        cerrarBtn.style.color = '#fff';
                        cerrarBtn.style.border = 'none';
                        cerrarBtn.style.borderRadius = '5px';
                        cerrarBtn.style.cursor = 'pointer';
                        cerrarBtn.id = 'cerrar-visor';
                        cerrarBtn.onclick = function() {
                            iframe.style.display = 'none';
                            this.remove();
                        };
                        document.body.appendChild(cerrarBtn);
                    }
                    return false;
                }
            });

            // Editar formato
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

            // Eliminar formato
            tablaBody.addEventListener('click', function(e) {
                const eliminarBtn = e.target.closest('.eliminar-formato');
                if (eliminarBtn) {
                    const id = eliminarBtn.dataset.id;
                    const nombre = eliminarBtn.dataset.nombre;
                    
                    if (confirm(`¿Eliminar el formato "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                        const form = document.getElementById('form-eliminar');
                        form.action = `{{ url('formatos') }}/${id}`;
                        form.submit();
                    }
                }
            });
        }

        // Evento para el campo de nombre de archivo en edición
        const inputNombreEdit = document.getElementById('input-nombre-archivo-edit');
        if (inputNombreEdit) {
            inputNombreEdit.addEventListener('input', function() {
                this.value = sanitizarNombre(this.value);
                actualizarPreviewNombreEdit();
            });
        }
    });

    // ── Función para sanitizar nombre ──
    function sanitizarNombre(nombre) {
        return nombre
            .replace(/[^\w\sáéíóúÁÉÍÓÚñÑ-]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    // ── Actualizar preview del nombre en edición ──
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

    // ── Cargar departamentos ──
    function cargarDepartamentos(proceso, valorActual = '') {
        const sel = document.getElementById('select-departamento');
        if (!sel) return;
        const deps = procesosYDepartamentos[proceso] || [];
        sel.innerHTML = deps.length === 0
            ? '<option value="">— Sin departamentos —</option>'
            : deps.map(d => `<option value="${d}" ${d === valorActual ? 'selected' : ''}>${d}</option>`).join('');
    }

    // ── Archivo seleccionado ──
    function mostrarArchivoSeleccionado(inp) {
        const box = document.getElementById('archivo-seleccionado');
        const lbl = document.getElementById('nombre-archivo-seleccionado');
        if (!box || !lbl) return;
        if (inp.files && inp.files[0]) {
            lbl.textContent = inp.files[0].name;
            box.classList.add('show');
            box.classList.remove('d-none');
        } else {
            box.classList.remove('show');
            box.classList.add('d-none');
        }
    }

    // ── Limpiar archivo seleccionado ──
    function limpiarArchivo() {
        const inputArchivo = document.getElementById('input-archivo');
        inputArchivo.value = '';
        const box = document.getElementById('archivo-seleccionado');
        box.classList.remove('show');
        box.classList.add('d-none');
    }

    // ── Abrir modal nuevo ──
    function abrirModalNuevo() {
        formatoEditandoId = null;

        document.getElementById('modal-titulo').textContent = 'Subir nuevo formato';
        document.getElementById('btn-guardar-texto').textContent = 'Subir Archivo';

        const form = document.getElementById('form-formato');
        form.action = "{{ route('formatos.store') }}";
        form.method = "POST";

        document.getElementById('form-method').value = 'POST';
        document.getElementById('formato-id').value = '';

        // Limpiar campos
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

        // Ocultar campos de edición
        document.getElementById('campo-nombre-archivo').style.display = 'none';
        document.getElementById('info-archivo-actual').style.display = 'none';
        
        // Mostrar campo de archivo como requerido
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

    // ── Abrir modal editar con campo para renombrar ──
    function abrirModalEditar(id, proceso, departamento, clave, codigo, version, nombreArchivo, extension) {
        formatoEditandoId = id;
        extensionActual = extension;

        document.getElementById('modal-titulo').textContent = 'Editar información del formato';
        document.getElementById('btn-guardar-texto').textContent = 'Actualizar formato';

        const form = document.getElementById('form-formato');
        form.action = `{{ url('formatos') }}/${id}`;

        document.getElementById('form-method').value = 'PUT';
        document.getElementById('formato-id').value = id;

        // Llenar campos básicos
        const selectProceso = document.getElementById('select-proceso');
        if (selectProceso) selectProceso.value = proceso;

        cargarDepartamentos(proceso, departamento);

        const inputClave = document.getElementById('input-clave');
        if (inputClave) inputClave.value = clave;

        const inputCodigo = document.getElementById('input-codigo');
        if (inputCodigo) inputCodigo.value = codigo;

        const inputVersion = document.getElementById('input-version');
        if (inputVersion) inputVersion.value = version;

        // MOSTRAR CAMPO PARA RENOMBRAR ARCHIVO
        document.getElementById('campo-nombre-archivo').style.display = 'block';
        
        // Extraer nombre sin extensión
        let nombreSinExtension = nombreArchivo;
        if (extension) {
            // Eliminar la extensión del nombre
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
        
        // Mostrar información del archivo actual
        const infoArchivoActual = document.getElementById('info-archivo-actual');
        const nombreActualSpan = document.getElementById('nombre-archivo-actual');
        if (infoArchivoActual && nombreActualSpan) {
            nombreActualSpan.textContent = nombreArchivo;
            infoArchivoActual.style.display = 'block';
        }
        
        // Actualizar preview
        actualizarPreviewNombreEdit();

        // Configurar archivo como opcional
        const inputArchivo = document.getElementById('input-archivo');
        if (inputArchivo) {
            inputArchivo.required = false;
            inputArchivo.value = '';
        }

        // Ocultar archivo seleccionado
        const archivoSeleccionado = document.getElementById('archivo-seleccionado');
        if (archivoSeleccionado) {
            archivoSeleccionado.classList.remove('show');
            archivoSeleccionado.classList.add('d-none');
        }

        // Configurar labels de archivo
        const reqArchivo = document.getElementById('req-archivo');
        if (reqArchivo) {
            reqArchivo.style.display = 'none';
            reqArchivo.textContent = '';
        }

        const lblArchivoOpt = document.getElementById('lbl-archivo-opt');
        if (lblArchivoOpt) lblArchivoOpt.style.display = 'inline';

        // Ocultar warning de clave
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
        // Validar el campo de nombre en modo edición
        if (formatoEditandoId) {
            const nombreEdit = document.getElementById('input-nombre-archivo-edit').value.trim();
            if (!nombreEdit) {
                alert('Debes especificar un nombre para el archivo.');
                return;
            }
        }
        document.getElementById('form-formato').submit();
    }

    // ── Filtro combinado tipo + valor ──
    const datosFiltro = {
        version: @json($versionesUnicas),
        codigo: @json($codigosUnicos),
        clave: @json($clavesUnicas),
    };
    
    const labelsFiltro = {
        version: 'Versión del procedimiento',
        codigo: 'Código de procedimiento',
        clave: 'Clave de formato',
    };

    function cambiarTipoCampo(tipo) {
        const selValor = document.getElementById('select-valor-campo');
        const selTipo = document.getElementById('select-tipo-campo');

        if (!selValor || !selTipo) return;

        ['version', 'codigo', 'clave'].forEach(k => {
            const hidden = document.getElementById('hidden-' + k);
            if (hidden) hidden.value = '';
        });

        if (!tipo) {
            selValor.innerHTML = '<option value="">— Primero elige un campo —</option>';
            selValor.disabled = true;
            selValor.classList.remove('border-success');
            selTipo.classList.remove('border-success');
            return;
        }
        
        selTipo.classList.add('border-success');
        selValor.disabled = false;
        const vals = datosFiltro[tipo] || [];
        selValor.innerHTML = `<option value="">— Selecciona ${labelsFiltro[tipo]} —</option>` +
            vals.map(v => `<option value="${tipo}:${v}">${v}</option>`).join('');
        selValor.classList.remove('border-success');
    }

    function marcarActivo(sel) {
        if (sel) sel.classList.toggle('border-success', !!sel.value);
    }

    // Evento para el formulario de filtros
    document.addEventListener('DOMContentLoaded', () => {
        const ff = document.getElementById('form-filtros');
        if (ff) {
            ff.addEventListener('submit', function() {
                const selValor = document.getElementById('select-valor-campo');
                if (!selValor) return;

                const raw = selValor.value;
                ['version', 'codigo', 'clave'].forEach(k => {
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
</script>
@endpush