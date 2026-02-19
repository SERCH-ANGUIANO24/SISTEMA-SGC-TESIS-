@if($folders->count() > 0)
    <div class="row g-4 mb-4">
        @foreach($folders as $folder)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card folder-card h-100 shadow-sm" 
                 data-folder-id="{{ $folder->id }}" 
                 data-folder-name="{{ strtolower($folder->name) }}" 
                 data-folder-date="{{ $folder->created_at }}"
                 data-folder-count="{{ $folder->documents->count() }}"
                 onclick="window.location.href='{{ route('matriz.index', ['folder' => $folder->id]) }}'"
                 style="cursor: pointer; border-radius: 12px; overflow: hidden; border-top: 4px solid {{ $folder->color ?? '#800000' }} !important;">
                <div class="card-body text-center p-3">
                    <div class="folder-icon mb-2">
                        <i class="bi bi-folder-fill" style="font-size: 4rem; color: {{ $folder->color ?? '#800000' }};"></i>
                    </div>
                    <h6 class="card-title fw-bold mb-0 text-truncate" title="{{ $folder->name }}">
                        {{ $folder->name }}
                    </h6>
                    <small class="text-muted">
                        {{ $folder->documents->count() }} matrices
                    </small>
                    
                    {{-- BOTONES DE ACCIÓN DIRECTA --}}
                    <div class="mt-2 d-flex justify-content-center gap-1" onclick="event.stopPropagation();">
                        {{-- RENOMBRAR --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openRenameModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Renombrar carpeta">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        {{-- MOVER --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openMoveModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Mover carpeta">
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                        
                        {{-- ELIMINAR CON SWEETALERT --}}
                        <form action="{{ route('matriz.folder.destroy', $folder->id) }}" method="POST" class="d-inline" id="delete-form-{{ $folder->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmDelete({{ $folder->id }}, '{{ addslashes($folder->name) }}')"
                                    title="Eliminar carpeta">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
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
                        <input type="text" class="form-control" id="newFolderName" name="name" required autofocus>
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

@push('styles')
{{-- SweetAlert2 CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .swal2-popup {
        font-size: 1.2rem !important;
    }
    .swal2-title {
        color: #800000 !important;
    }
    .swal2-confirm {
        background-color: #dc3545 !important;
    }
    .swal2-cancel {
        background-color: #6c757d !important;
    }
    
    /* Elimina bordes por defecto de la card (excepto el superior dinámico) */
    .folder-card {
        border: none !important;
    }
</style>
@endpush

@push('scripts')
{{-- SweetAlert2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Función para abrir modal de renombrar
    function openRenameModal(folderId, folderName) {
        event.stopPropagation();
        const form = document.getElementById('renameFolderForm');
        form.action = '{{ route("matriz.folder.rename", ["id"=> "REPLACE_ID"]) }}'.replace('REPLACE_ID', folderId);
        document.getElementById('newFolderName').value = folderName;
        
        const modal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
        modal.show();
    }

    // Función para abrir modal de mover
    function openMoveModal(folderId, folderName) {
        event.stopPropagation();
        const form = document.getElementById('moveFolderForm');
        form.action = '{{ route("matriz.folder.move", ["id" => "REPLACE_ID"]) }}'.replace('REPLACE_ID', folderId);
        document.getElementById('moveFolderName').innerHTML = folderName;
        
        const select = document.getElementById('folderDestination');
        select.innerHTML = '<option value="">📁 Cargando carpetas...</option>';
        select.disabled = true;
        
        fetch('{{ route("matriz.folders.tree") }}?current_folder=' + folderId)
            .then(response => response.json())
            .then(folders => {
                select.innerHTML = '<option value="">📁 Raíz principal</option>';
                select.disabled = false;
                
                folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    
                    let prefix = '';
                    const depth = folder.full_path.split(' / ').length - 1;
                    for (let i = 0; i < depth; i++) {
                        prefix += '  ';
                    }
                    
                    option.textContent = prefix + '📁 ' + folder.full_path;
                    select.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error al cargar carpetas:', error);
                select.innerHTML = '<option value="">❌ Error al cargar carpetas</option>';
                select.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la lista de carpetas',
                    confirmButtonColor: '#800000'
                });
            });
        
        const modal = new bootstrap.Modal(document.getElementById('moveFolderModal'));
        modal.show();
    }

    // FUNCIÓN PARA CONFIRMAR ELIMINAR CON SWEETALERT2
    function confirmDelete(folderId, folderName) {
        event.stopPropagation();
        event.preventDefault();
        
        Swal.fire({
            title: '¿Eliminar carpeta?',
            html: `
                <div style="text-align: left;">
                    <p style="font-size: 1.1rem; margin-bottom: 10px;">
                        <strong>📁 ${folderName}</strong>
                    </p>
                    <p style="color: #dc3545; font-weight: 500;">
                        ⚠️ Esta acción eliminará permanentemente:
                    </p>
                    <ul style="text-align: left; margin-bottom: 15px;">
                        <li>La carpeta <strong>"${folderName}"</strong></li>
                        <li>Todas las subcarpetas dentro de ella</li>
                        <li>Todas las matrices dentro de la carpeta</li>
                    </ul>
                    <p style="color: #856404; background-color: #fff3cd; padding: 10px; border-radius: 5px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>¡No podrás recuperar esta información después de eliminarla!</strong>
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar carpeta',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const form = document.getElementById(`delete-form-${folderId}`);
                form.submit();
            }
        });
        
        return false;
    }
</script>
@endpush