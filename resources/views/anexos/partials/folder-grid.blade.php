@if($folders->count() > 0)
    <div class="row mb-4">
        @foreach($folders as $folder)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card folder-card h-100 shadow-sm" 
                data-folder-id="{{ $folder->id }}" 
                data-folder-name="{{ strtolower($folder->name) }}" 
                data-folder-date="{{ $folder->created_at }}"
                data-folder-count="{{ $folder->documents->count() }}"
                 onclick="window.location='{{ route('anexos.index', ['folder' => $folder->id]) }}'"
                 style="border-top: 4px solid {{ $folder->color ?? '#800000' }};">
                <div class="card-body text-center p-3">
                    <div class="folder-icon">
                        <i class="bi bi-folder-fill" style="color: {{ $folder->color ?? '#800000' }};"></i>
                    </div>
                    <h6 class="card-title fw-bold text-truncate">{{ $folder->name }}</h6>
                    
                    @php $userRole = Auth::user()->role; @endphp
                    
                    {{-- BOTONES DE ACCIÓN - SOLO SUPERADMIN/ADMIN --}}
                    @if(in_array($userRole, ['superadmin', 'admin']))
                    <div class="mt-2 d-flex justify-content-center gap-1" onclick="event.stopPropagation();">
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openRenameModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Renombrar carpeta">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openMoveModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Mover carpeta">
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                        
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="deleteElement({{ $folder->id }}, '{{ addslashes($folder->name) }}', 'Carpeta')"
                                title="Eliminar carpeta">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

@if($folders->count() == 0 && (!isset($documents) || $documents->count() == 0))
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        Esta carpeta está vacía.
    </div>
@endif