@if($documents->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold" style="color: #800000;">
                <i class="bi bi-grid-3x3 me-2"></i>Documentos
            </h6>     
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th>Nombre</th>
                            <th>Tamaño</th>
                            <th>Fecha de Carga</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="fileTableBody">
                        @foreach($documents as $doc)
                        <tr class="document-row" 
                            data-file-id="{{ $doc->id }}"
                            data-file-name="{{ strtolower($doc->name) }}"
                            data-file-size="{{ $doc->size ?? 0 }}"
                            data-file-date="{{ $doc->created_at }}"
                            data-file-extension="{{ strtolower($doc->extension ?? '') }}">
                            <td>
                                <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                                {{ $doc->name }}.{{ $doc->extension }}
                            </td>
                            <td>
                                @if($doc->size < 1024)
                                    {{ $doc->size }} B
                                @elseif($doc->size < 1048576)
                                    {{ round($doc->size / 1024, 1) }} KB
                                @else
                                    {{ round($doc->size / 1048576, 1) }} MB
                                @endif
                            </td>
                            <td>{{ $doc->created_at->format('d/m/Y h:i A') }}</td>
                            <td class="text-end">
                                @php
                                    $ext = strtolower($doc->extension ?? '');
                                @endphp
                                
                                @if(!in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewDocumentModal{{ $doc->id }}" title="Ver documento">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                                
                                {{-- BOTÓN EDITAR --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="editDocument({{ $doc->id }})"
                                        title="Editar matriz">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                {{-- BOTÓN MOVER --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="moveDocument({{ $doc->id }}, '{{ $doc->name }}.{{ $doc->extension }}')"
                                        title="Mover matriz">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                
                                {{-- BOTÓN DESCARGAR --}}
                                <a href="{{ route('matriz.document.download', $doc->id) }}" class="btn btn-sm btn-outline-primary" title="Descargar">
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                {{-- BOTÓN ELIMINAR --}}
                                <form action="{{ route('matriz.document.destroy', $doc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta matriz?')" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    {{-- SOLO MOSTRAR EL MENSAJE SI ESTAMOS DENTRO DE UNA CARPETA --}}
    @if(isset($currentFolder) && $currentFolder)
    <div class="alert alert-info d-flex align-items-center mt-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        No hay matrices en esta ubicación.
    </div>
    @endif
@endif

<script>
// Función para abrir modal de renombrar matriz (ACTUALIZADA)
function editDocument(id) {
    event.stopPropagation();
    
    fetch(`/matriz/document/${id}/data`)
        .then(response => response.json())
        .then(data => {
            // Solo cargar el nombre del documento
            document.getElementById('edit_document_name').value = data.name;
            
            // Establecer la acción del formulario
            document.getElementById('editDocumentForm').action = `/matriz/document/${id}`;
            
            // Abrir el modal
            const modal = new bootstrap.Modal(document.getElementById('editDocumentModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la información de la matriz',
                confirmButtonColor: '#800000'
            });
        });
}

function moveDocument(id, name) {
    document.getElementById('moveDocumentName').textContent = name;
    document.getElementById('moveDocumentForm').action = `/matriz/document/${id}/move`;
    
    const select = document.getElementById('documentDestination');
    select.innerHTML = '<option value="">📁 Cargando...</option>';
    select.disabled = true;
    
    const currentFolder = {{ $currentFolder->id ?? 'null' }};
    
    fetch(`/matriz/folders/tree?current_folder=${currentFolder}`)
        .then(response => response.json())
        .then(folders => {
            select.innerHTML = '<option value="">📁 Raíz principal</option>';
            select.disabled = false;
            folders.forEach(folder => {
                const option = document.createElement('option');
                option.value = folder.id;
                option.textContent = '📁 ' + folder.full_path;
                select.appendChild(option);
            });
        });
    
    new bootstrap.Modal(document.getElementById('moveDocumentModal')).show();
}
</script>