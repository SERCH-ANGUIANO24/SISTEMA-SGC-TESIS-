@if($documents->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold" style="color: #800000;">
                <i class="bi bi-file-earmark-text me-2"></i>Documentos
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
                        <tr class="formato-row" 
                            data-file-id="{{ $doc->id }}"
                            data-file-name="{{ strtolower($doc->name) }}"
                            data-file-size="{{ $doc->size }}"
                            data-file-date="{{ $doc->created_at }}"
                            data-file-extension="{{ strtolower($doc->extension) }}">
                            <td>
                                @php
                                    $icon = 'bi-file-earmark';
                                    $ext = strtolower($doc->extension ?? '');
                                    if($ext == 'pdf') $icon = 'bi-file-pdf';
                                    elseif(in_array($ext, ['xls','xlsx'])) $icon = 'bi-file-excel';
                                    elseif(in_array($ext, ['doc','docx'])) $icon = 'bi-file-word';
                                    elseif(in_array($ext, ['jpg','jpeg','png','gif'])) $icon = 'bi-file-image';
                                @endphp
                                <i class="bi {{ $icon }} me-2" style="color: #800000;"></i>
                                {{ $doc->name }}.{{ $doc->extension }}
                                {{-- ELIMINADO: ya no se muestra el departamento --}}
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
                                
                                @if(in_array($ext, ['pdf','jpg','jpeg','png','gif','txt']))
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewDocumentModal{{ $doc->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                                
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="editDocument({{ $doc->id }})"
                                        title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="moveDocument({{ $doc->id }}, '{{ $doc->name }}.{{ $doc->extension }}')"
                                        title="Mover">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                
                                <a href="{{ route('formatos.document.download', $doc->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                <form action="{{ route('formatos.document.destroy', $doc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este archivo?')">
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
        No hay documentos en esta carpeta.
    </div>
    @endif
@endif

<script>
// Función para editar documento
function editDocument(id) {
    fetch(`/formatos/document/${id}/data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_document_name').value = data.name;
            document.getElementById('edit_departamento').value = data.departamento || '';
            document.getElementById('edit_tipo_documento').value = data.tipo_documento || 'PDF';
            document.getElementById('edit_fecha_documento').value = data.fecha_documento || '';
            
            document.getElementById('editDocumentForm').action = `/formatos/document/${id}`;
            new bootstrap.Modal(document.getElementById('editDocumentModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del formato');
        });
}

// Función para mover documento
function moveDocument(id, name) {
    document.getElementById('moveDocumentName').textContent = name;
    document.getElementById('moveDocumentForm').action = `/formatos/document/${id}/move`;
    
    const select = document.getElementById('documentDestination');
    select.innerHTML = '<option value="">📁 Cargando...</option>';
    select.disabled = true;
    
    const currentFolder = {{ $currentFolder->id ?? 'null' }};
    
    fetch(`/formatos/folders/tree?current_folder=${currentFolder}`)
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
        })
        .catch(() => {
            select.innerHTML = '<option value="">❌ Error al cargar carpetas</option>';
            select.disabled = false;
        });
    
    new bootstrap.Modal(document.getElementById('moveDocumentModal')).show();
}
</script>