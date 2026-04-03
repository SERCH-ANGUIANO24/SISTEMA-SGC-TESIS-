{{-- resources/views/avisos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Crear Nuevo Aviso')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column">
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
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('avisos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Título del Aviso -->
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Aviso *</label>
                            <input type="text" class="form-control @error('titulo') is-invalid @enderror" 
                                   id="titulo" name="titulo" value="{{ old('titulo') }}" 
                                   placeholder="Ej: Nuevo proceso de inscripción 2026" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Descripción -->
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
                        <!-- Archivo -->
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
                            <div id="fileInfo" class="mt-2 small text-muted" style="display: none;">
                                <i class="bi bi-file-earmark"></i> <span id="fileName"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Fecha Inicio -->
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
                        <!-- Fecha Fin -->
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

                <!-- Botones -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('avisos.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Publicar Aviso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('archivo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('fileInfo');
    const fileNameSpan = document.getElementById('fileName');
    
    if (file) {
        fileNameSpan.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        fileInfo.style.display = 'block';
    } else {
        fileInfo.style.display = 'none';
    }
});
</script>
@endpush
@endsection