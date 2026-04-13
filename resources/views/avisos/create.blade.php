@extends('layouts.app')

@section('title', 'Crear Nuevo Aviso')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column">
                <!-- ENLACE PARA VOLVER AL LISTADO DE AVISOS -->
                <a href="{{ route('avisos.index') }}" class="text-decoration-none" title="Volver a Avisos">
                    <h1 class="h3 mb-2" style="color: #4f46e5; cursor: pointer;">
                        <i class="bi bi-arrow-left-circle me-2" style="font-size: 2rem; vertical-align: middle;"></i>
                        <i class="bi bi-megaphone me-2" style="font-size: 2rem; vertical-align: middle;"></i>
                        Nuevo Aviso
                    </h1>
                </a>
                <p class="text-muted">Complete el formulario para publicar un nuevo aviso</p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            /** VERIFICAR SI HAY ERRORES DE VALIDACIÓN **/
            // SI EXISTEN ERRORES, SE MUESTRA UN ALERTA ROJO CON LA LISTA DE ERRORES
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            /** FORMULARIO PARA CREAR UN NUEVO AVISO **/
            // METHOD="POST" - ENVÍA DATOS PARA GUARDAR
            // ACTION="{{ route('avisos.store') }}" - DIRECCIÓN DEL CONTROLADOR QUE GUARDA
            // ENCTYPE="MULTIPART/FORM-DATA" - PERMITE SUBIR ARCHIVOS
            <form action="{{ route('avisos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf  // TOKEN DE SEGURIDAD - PROTEGE CONTRA ATAQUES CSRF-"SELLO DE AUTENTICIDAD"
                
                <div class="row">
                    <div class="col-md-8">
                        /** CAMPO: TÍTULO DEL AVISO **/
                        // CAMPO OBLIGATORIO (*) - REQUERIDO PARA PUBLICAR
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Aviso *</label>
                            <input type="text" class="form-control @error('titulo') is-invalid @enderror" 
                                   id="titulo" name="titulo" value="{{ old('titulo') }}" 
                                   placeholder="Ej: Nuevo proceso de inscripción 2026" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        /** CAMPO: DESCRIPCIÓN DEL AVISO **/
                        // ÁREA DE TEXTO PARA EXPLICAR DETALLES DEL AVISO (NO OBLIGATORIO)
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="4" 
                                      placeholder="Describa el contenido del aviso...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        /** CAMPO: ARCHIVO ADJUNTO **/
                        // PERMITE SUBIR ARCHIVOS (PDF, IMÁGENES, WORD, EXCEL, ETC.)
                        <div class="mb-3">
                            <label for="archivo" class="form-label">Archivo adjunto</label>
                            <div class="border rounded p-3 text-center" style="background-color: #f8f9fa;">
                                <i class="bi bi-cloud-upload" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2 mb-2">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                                <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                <input type="file" class="form-control @error('archivo') is-invalid @enderror" 
                                       id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.zip,.rar">
                                @error('archivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            // DIV QUE MUESTRA EL NOMBRE Y TAMAÑO DEL ARCHIVO SELECCIONADO (INICIALMENTE OCULTO)
                            <div id="fileInfo" class="mt-2 small text-muted" style="display: none;">
                                <i class="bi bi-file-earmark"></i> <span id="fileName"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        /** CAMPO: FECHA DE INICIO **/
                        // FECHA Y HORA EN QUE EL AVISO COMIENZA A MOSTRARSE (OBLIGATORIO)
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                            <input type="datetime-local" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                            <small class="text-muted">El aviso comenzará a mostrarse a partir de esta fecha</small>
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        /** CAMPO: FECHA DE FIN **/
                        // FECHA Y HORA EN QUE EL AVISO DEJA DE MOSTRARSE (OBLIGATORIO)
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                            <input type="datetime-local" class="form-control @error('fecha_fin') is-invalid @enderror" 
                                   id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                            <small class="text-muted">El aviso dejará de mostrarse después de esta fecha</small>
                            @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                /** BOTONES DE ACCIÓN **/
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('avisos.index') }}" class="btn btn-secondary">
                        Cancelar  // BOTÓN PARA CANCELAR Y VOLVER AL LISTADO
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Publicar Aviso  // BOTÓN PARA GUARDAR EL AVISO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

/** BLOQUE DE JAVASCRIPT - FUNCIONALIDAD PARA MOSTRAR INFORMACIÓN DEL ARCHIVO **/
// ESTE CÓDIGO SE EJECUTA EN EL NAVEGADOR DEL USUARIO
@push('scripts')
<script>
// SELECCIONA EL INPUT DEL ARCHIVO Y LE AGREGA UN "ESCUCHADOR" PARA CUANDO CAMBIE
document.getElementById('archivo').addEventListener('change', function(e) {
    // OBTIENE EL PRIMER ARCHIVO SELECCIONADO
    const file = e.target.files[0];
    // OBTIENE EL CONTENEDOR DONDE SE MOSTRARÁ LA INFO DEL ARCHIVO
    const fileInfo = document.getElementById('fileInfo');
    // OBTIENE EL ESPACIO DONDE IRÁ EL NOMBRE DEL ARCHIVO
    const fileNameSpan = document.getElementById('fileName');
    
    // SI HAY UN ARCHIVO SELECCIONADO...
    if (file) {
        // MUESTRA EL NOMBRE DEL ARCHIVO Y SU TAMAÑO EN MEGABYTES (CON 2 DECIMALES)
        fileNameSpan.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        // HACE VISIBLE EL CONTENEDOR DE INFORMACIÓN
        fileInfo.style.display = 'block';
    } else {
        // SI NO HAY ARCHIVO, OCULTA EL CONTENEDOR
        fileInfo.style.display = 'none';
    }
});
</script>
@endpush
@endsection