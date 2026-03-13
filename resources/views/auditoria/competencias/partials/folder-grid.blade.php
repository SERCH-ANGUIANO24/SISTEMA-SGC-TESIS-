@if($folders->count() > 0)
    <div class="row mb-4">
        @foreach($folders as $folder)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card folder-card h-100 shadow-sm" 
                data-folder-id="{{ $folder->id }}" 
                data-folder-name="{{ strtolower($folder->nombre) }}" 
                data-folder-date="{{ $folder->created_at }}"
                data-folder-count="{{ $folder->total_items_count }}"
                 onclick="window.location='{{ route('auditoria.competencias.index', ['folder' => $folder->id]) }}'"
                 style="border-top: 4px solid {{ $folder->color ?? '#800000' }};">
                <div class="card-body text-center p-3">
                    <div class="folder-icon">
                        <i class="bi bi-folder-fill" style="color: {{ $folder->color ?? '#800000' }};"></i>
                    </div>
                    <h6 class="card-title fw-bold text-truncate">{{ $folder->nombre }}</h6>
                    
                    
                    {{-- BOTONES DE ACCIÓN - Solo superadmin y admin --}}
                    @if(in_array($userRole, ['superadmin', 'admin']))
                    <div class="mt-2 d-flex justify-content-center gap-1" onclick="event.stopPropagation();">
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openRenameModal({{ $folder->id }}, '{{ $folder->nombre }}')"
                                title="Renombrar carpeta">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openMoveModal({{ $folder->id }}, '{{ $folder->nombre }}')"
                                title="Mover carpeta">
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                        
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="deleteElement({{ $folder->id }}, '{{ addslashes($folder->nombre) }}', 'Carpeta')"
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

{{-- MODAL RENOMBRAR CARPETA --}}
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="renameFolderForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2" style="color: #800000;"></i>
                        Renombrar Carpeta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newFolderName" class="form-label fw-bold">Nuevo nombre</label>
                        <input type="text" class="form-control" id="newFolderName" name="nombre" required autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Renombrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL MOVER CARPETA --}}
<div class="modal fade" id="moveFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="moveFolderForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #800000;"></i>
                        Mover Carpeta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <span class="fw-bold">Carpeta a mover:</span><br>
                        <span id="moveFolderName" style="color: #800000; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="folderDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="folderDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Selecciona la carpeta donde deseas mover.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-arrow-right me-1"></i> Mover aquí
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>