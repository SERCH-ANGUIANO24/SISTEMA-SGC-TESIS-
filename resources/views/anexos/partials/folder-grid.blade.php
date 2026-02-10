@if($folders->count() > 0)
    <div class="row mb-4">
        @foreach($folders as $folder)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card folder-card h-100 shadow-sm" 
                 onclick="window.location='{{ route('anexos.index', ['folder' => $folder->id]) }}'"
                 style="border-top: 4px solid {{ $folder->color }};">
                <div class="card-body text-center p-3">
                    <div class="folder-icon">
                        <i class="bi bi-folder-fill" style="color: {{ $folder->color }};"></i>
                    </div>
                    <h6 class="card-title fw-bold text-truncate">{{ $folder->name }}</h6>
                    <p class="card-text small text-muted">
                        {{ $folder->documents->count() }} archivos
                    </p>
                    <form action="{{ route('anexos.folder.destroy', $folder->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger mt-2" onclick="event.stopPropagation(); return confirm('¿Eliminar carpeta y todo su contenido?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        Esta carpeta está vacía.
    </div>
@endif