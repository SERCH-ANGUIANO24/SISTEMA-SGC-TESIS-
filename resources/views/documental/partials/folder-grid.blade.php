@if($folders->count() > 0)
    <div class="row g-4 mb-4">
        @foreach($folders as $folder)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            {{-- TARJETA DE CARPETA - AL HACER CLIC NAVEGA DENTRO DE LA CARPETA --}}
            <div class="card folder-card h-100 border-0 shadow-sm" 
                 data-folder-id="{{ $folder->id }}" 
                 data-folder-name="{{ strtolower($folder->name) }}" 
                 data-folder-date="{{ $folder->created_at }}"
                 data-folder-count="{{ $folder->documents->count() }}"
                 style="cursor: pointer; border-radius: 12px; overflow: hidden; border-top: 4px solid {{ $folder->color ?? '#800000' }} !important;"
                 onclick="window.location.href='{{ route('documental.index', ['folder' => $folder->id]) }}'">
                <div class="card-body text-center p-3">
                    {{-- ÍCONO DE LA CARPETA CON COLOR PERSONALIZADO --}}
                    <div class="folder-icon mb-2">
                        <i class="bi bi-folder-fill" style="font-size: 4rem; color: {{ $folder->color ?? '#800000' }};"></i>
                    </div>
                    {{-- NOMBRE DE LA CARPETA (CON TEXTO TRUNCADO) --}}
                    <h6 class="card-title fw-bold mb-0 text-truncate" title="{{ $folder->name }}">
                        {{ $folder->name }}
                    </h6>
                    
                    {{-- BOTONES DE ACCIÓN - SOLO PARA SUPERADMIN Y ADMIN --}}
                    @if(in_array($userRole, ['superadmin', 'admin']))
                    <div class="mt-3 d-flex justify-content-center gap-1" onclick="event.stopPropagation();">
                        {{-- BOTÓN RENOMBRAR CARPETA --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openRenameModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Renombrar carpeta">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        {{-- BOTÓN MOVER CARPETA --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openMoveModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Mover carpeta">
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                        
                        {{-- BOTÓN ELIMINAR CARPETA --}}
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDelete('{{ $folder->id }}', '{{ addslashes($folder->name) }}')"
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

{{-- MENSAJE CUANDO LA CARPETA ESTÁ VACÍA (SIN CARPETAS NI DOCUMENTOS) --}}
@if($folders->count() == 0 && (!isset($documents) || $documents->count() == 0))
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        Esta carpeta está vacía.
    </div>
@endif

{{-- MODAL PARA RENOMBRAR CARPETA --}}
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="renameFolderForm">
            @csrf {{-- TOKEN DE SEGURIDAD --}}
            @method('PUT') {{-- SIMULA EL MÉTODO HTTP PUT PARA ACTUALIZAR --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2" style="color: #000000;"></i>
                        Renombrar Carpeta
                    </h5>
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

{{-- MODAL PARA MOVER CARPETA --}}
<div class="modal fade" id="moveFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="moveFolderForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #000000;"></i>
                        Mover Carpeta
                    </h5>
                </div>
                <div class="modal-body">
                    {{-- MUESTRA EL NOMBRE DE LA CARPETA A MOVER --}}
                    <p class="mb-3">
                        <span class="fw-bold">Carpeta a mover:</span><br>
                        <span id="moveFolderName" style="color: #737373; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="folderDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="folderDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                            {{-- LAS OPCIONES DE CARPETAS SE CARGAN DINÁMICAMENTE CON JAVASCRIPT --}}
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

{{-- ESTILOS ADICIONALES --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    {{-- ESTILOS DE SWEETALERT --}}
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
    
    {{-- EFECTO HOVER DE LAS TARJETAS DE CARPETA --}}
    .folder-card {
        transition: all 0.2s;
    }
    .folder-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
    }
    .folder-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

{{-- JAVASCRIPT PARA MANEJAR LAS ACCIONES DE CARPETAS --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    {{-- ABRE EL MODAL PARA RENOMBRAR CARPETA --}}
    function openRenameModal(folderId, folderName) {
        event.stopPropagation(); {{-- EVITA QUE EL CLIC LLEGUE A LA TARJETA --}}
        const form = document.getElementById('renameFolderForm');
        {{-- CONFIGURA LA ACCIÓN DEL FORMULARIO CON EL ID DE LA CARPETA --}}
        form.action = '{{ route("documental.folder.rename", ["id"=> "REPLACE_ID"]) }}'.replace('REPLACE_ID', folderId);
        document.getElementById('newFolderName').value = folderName;
        
        const modal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
        modal.show();
    }

    {{-- ABRE EL MODAL PARA MOVER CARPETA --}}
    function openMoveModal(folderId, folderName) {
        event.stopPropagation(); {{-- EVITA QUE EL CLIC LLEGUE A LA TARJETA --}}
        const form = document.getElementById('moveFolderForm');
        {{-- CONFIGURA LA ACCIÓN DEL FORMULARIO CON EL ID DE LA CARPETA --}}
        form.action = '{{ route("documental.folder.move", ["id" => "REPLACE_ID"]) }}'.replace('REPLACE_ID', folderId);
        document.getElementById('moveFolderName').innerHTML = folderName;
        
        const select = document.getElementById('folderDestination');
        select.innerHTML = '<option value="">📁 Cargando carpetas...</option>';
        select.disabled = true;
        
        {{-- OBTIENE EL ÁRBOL DE CARPETAS DESDE EL SERVIDOR --}}
        fetch('{{ route("documental.folders.tree") }}?current_folder=' + folderId)
            .then(response => response.json())
            .then(folders => {
                select.innerHTML = '<option value="">📁 Raíz principal</option>';
                select.disabled = false;
                
                folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    
                    {{-- CREA SANGRÍA VISUAL SEGÚN LA PROFUNDIDAD DE LA CARPETA --}}
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

    {{-- CONFIRMA Y ELIMINA UNA CARPETA CON SUS SUBCARPETAS Y ARCHIVOS --}}
    function confirmDelete(folderId, folderName) {
        event.stopPropagation(); {{-- EVITA QUE EL CLIC LLEGUE A LA TARJETA --}}

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
                        <li>Todos los archivos dentro de la carpeta</li>
                    </ul>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                {{-- ENVÍA PETICIÓN DELETE AL SERVIDOR --}}
                fetch('/documental/folder/' + folderId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: data.message,
                            confirmButtonColor: '#000000',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); {{-- RECARGA LA PÁGINA --}}
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al eliminar',
                            confirmButtonColor: '#000000'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión',
                        confirmButtonColor: '#000000'
                    });
                });
            }
        });

        return false;
    }
</script>
@endpush