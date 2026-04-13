@extends('layouts.app')

@section('title', 'Editar Aviso')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column">
                <!-- ENLACE PARA VOLVER AL LISTADO DE AVISOS -->
                <a href="{{ route('avisos.index') }}" class="text-decoration-none" title="Volver a Avisos">
                    <h1 class="h3 mb-2" style="color: #4f46e5; cursor: pointer;">
                        <i class="bi bi-arrow-left-circle me-2" style="font-size: 2rem; vertical-align: middle;"></i>
                        <i class="bi bi-pencil-square me-2" style="font-size: 2rem; vertical-align: middle;"></i>
                        Editar Aviso
                    </h1>
                </a>
                <p class="text-muted">Modifique la información del aviso</p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            /** VERIFICAR SI HAY ERRORES DE VALIDACIÓN **/
            // SI EXISTEN ERRORES, SE MUESTRA UN ALERTA ROJO CON LA LISTA
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            /** FORMULARIO PARA ACTUALIZAR UN AVISO EXISTENTE **/
            // METHOD="POST" PERO USA @METHOD('PUT') PARA SIMULAR PUT (ACTUALIZAR)
            // ACTION APUNTA A UPDATE CON EL ID DEL AVISO: avisos.update($aviso->id)
            // ENCTYPE="MULTIPART/FORM-DATA" - PERMITE SUBIR ARCHIVOS
            <form action="{{ route('avisos.update', $aviso->id) }}" method="POST" enctype="multipart/form-data">
                @csrf  // TOKEN DE SEGURIDAD - PROTEGE CONTRA ATAQUES CSRF
                @method('PUT')  // SIMULA EL MÉTODO HTTP PUT (PARA ACTUALIZAR)
                
                <div class="row">
                    <div class="col-md-8">
                        /** CAMPO: TÍTULO DEL AVISO **/
                        // OBLIGATORIO - USA OLD()-'MEMORIA' PARA MANTENER DATOS Y $aviso->titulo POR DEFECTO
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Aviso *</label>
                            <input type="text" class="form-control @error('titulo') is-invalid @enderror" 
                                   id="titulo" name="titulo" value="{{ old('titulo', $aviso->titulo) }}" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        /** CAMPO: DESCRIPCIÓN DEL AVISO **/
                        // ÁREA DE TEXTO - CARGA EL VALOR GUARDADO EN LA BASE DE DATOS
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $aviso->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        /** SECCIÓN: ARCHIVO ACTUAL **/
                        // MUESTRA EL ARCHIVO QUE ESTÁ ACTUALMENTE ASOCIADO AL AVISO (SI EXISTE)
                        @if($aviso->archivo_nombre)
                            <div class="mb-3">
                                <label class="form-label">Archivo actual</label>
                                <div class="border rounded p-3 bg-light">
                                    <!-- GETICONOARCHIVO() - DEVUELVE UN ÍCONO SEGÚN EL TIPO DE ARCHIVO (PDF, WORD, IMAGEN, ETC.) -->
                                    <i class="{{ $aviso->getIconoArchivo() }} me-2" style="font-size: 1.2rem;"></i>
                                    <!-- ENLACE PARA DESCARGAR EL ARCHIVO ACTUAL -->
                                    <a href="{{ route('avisos.download', $aviso->id) }}" class="text-decoration-none">
                                        {{ $aviso->archivo_nombre }}
                                    </a>
                                    <p class="small text-muted mt-1 mb-0">
                                        Tamaño: {{ number_format($aviso->tamano_archivo / 1024 / 1024, 2) }} MB
                                    </p>
                                </div>
                            </div>
                        @endif

                        /** SECCIÓN: SUBIR NUEVO ARCHIVO **/
                        // SI YA HAY ARCHIVO, ESTO SIRVE PARA REEMPLAZARLO
                        // SI NO HAY ARCHIVO, ESTO SIRVE PARA AGREGAR UNO NUEVO
                        <div class="mb-3">
                            <label for="archivo" class="form-label">
                                @if($aviso->archivo_nombre)
                                    Reemplazar archivo
                                @else
                                    Archivo adjunto
                                @endif
                            </label>
                            <div class="border rounded p-3 text-center" style="background-color: #f8f9fa;">
                                <i class="bi bi-cloud-upload" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2 mb-2">Arrastra tu archivo aquí o haz clic para seleccionar</p>
                                <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                <input type="file" class="form-control" id="archivo" name="archivo" 
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.zip,.rar">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        /** CAMPO: FECHA DE INICIO **/
                        // OBLIGATORIO - FORMATEA LA FECHA GUARDADA PARA EL INPUT DATETIME-LOCAL
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                            <input type="datetime-local" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" 
                                   value="{{ old('fecha_inicio', $aviso->fecha_inicio->format('Y-m-d\TH:i')) }}" required>
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        /** CAMPO: FECHA DE FIN **/
                        // OBLIGATORIO - FORMATEA LA FECHA GUARDADA PARA EL INPUT DATETIME-LOCAL
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                            <input type="datetime-local" class="form-control @error('fecha_fin') is-invalid @enderror" 
                                   id="fecha_fin" name="fecha_fin" 
                                   value="{{ old('fecha_fin', $aviso->fecha_fin->format('Y-m-d\TH:i')) }}" required>
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
                        <i class="bi bi-save me-2"></i>Actualizar Aviso  // BOTÓN PARA GUARDAR LOS CAMBIOS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection