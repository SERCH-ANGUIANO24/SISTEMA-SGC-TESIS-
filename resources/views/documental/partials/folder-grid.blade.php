@if($folders->count() > 0)
    <div class="row g-4 mb-4">
        @foreach($folders as $folder)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card folder-card h-100 border-0 shadow-sm" 
                 onclick="window.location.href='{{ route('documental.index', ['folder' => $folder->id]) }}'"
                 style="cursor: pointer; border-radius: 12px; overflow: hidden;">
                <div class="card-body text-center p-3">
                    <div class="folder-icon mb-2">
                        <i class="bi bi-folder-fill" style="font-size: 4rem; color: {{ $folder->color ?? '#800000' }};"></i>
                    </div>
                    <h6 class="card-title fw-bold mb-0 text-truncate" title="{{ $folder->name }}">
                        {{ $folder->name }}
                    </h6>
                    <small class="text-muted">
                        {{ $folder->documents->count() }} archivos
                    </small>
                    
                    {{-- Botón eliminar solo si está vacía --}}
                    @if($folder->documents->count() == 0 && $folder->subfolders->count() == 0)
                    <div class="mt-2">
                        <form action="{{ route('documental.folder.destroy', $folder->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('¿Eliminar carpeta {{ $folder->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="event.stopPropagation()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif