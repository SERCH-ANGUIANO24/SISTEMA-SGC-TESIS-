
@if($documents->count() > 0)
<div class="card shadow-sm border-0">
    <div class="card-header bg-light py-3">
        <h5 class="mb-0 fw-bold" style="color: #800000;">
            <i class="bi bi-file-earmark-text me-2"></i>
            Documentos
            <small class="text-muted ms-2">({{ $documents->count() }} archivos)</small>
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre del Documento</th>
                        <th>Responsable</th>
                        <th>Proceso</th>
                        <th>Departamento</th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="documentTableBody">
                    @foreach($documents as $doc)
                    <tr class="document-row">
                        <td>
                            @php
                                $icon = 'bi-file-earmark';
                                $ext = strtolower($doc->extension ?? pathinfo($doc->original_name, PATHINFO_EXTENSION));
                                if(in_array($ext, ['pdf'])) $icon = 'bi-file-pdf';
                                elseif(in_array($ext, ['doc','docx'])) $icon = 'bi-file-word';
                                elseif(in_array($ext, ['xls','xlsx'])) $icon = 'bi-file-excel';
                                elseif(in_array($ext, ['jpg','jpeg','png','gif'])) $icon = 'bi-file-image';
                            @endphp
                            <i class="bi {{ $icon }} me-2" style="color: #800000;"></i>
                            {{ $doc->name }}.{{ $ext }}
                            @if($doc->size)
                                <br><small class="text-muted">{{ round($doc->size / 1024, 1) }} KB</small>
                            @endif
                        </td>
                        <td>{{ $doc->responsable ?? $doc->user->name }}</td>
                        <td>{{ $doc->proceso ?? $doc->user->proceso ?? 'N/A' }}</td>
                        <td>{{ $doc->departamento ?? $doc->user->departamento ?? 'N/A' }}</td>
                        <td>{{ $doc->fecha ? date('d/m/Y', strtotime($doc->fecha)) : date('d/m/Y', strtotime($doc->created_at)) }}</td>
                        <td>
                            @if(($doc->estatus ?? 'No Valido') == 'Valido')
                                <span class="badge bg-success">Válido</span>
                            @else
                                <span class="badge bg-danger">No Válido</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-1">
                                {{-- Ver --}}
                                @if(in_array($ext, ['pdf','jpg','jpeg','png','gif','txt']))
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        onclick="viewDocument({{ $doc->id }})"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewDocumentModal{{ $doc->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @endif
                                
                                {{-- Editar --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="editDocument({{ $doc->id }})">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                {{-- Mover --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="moveDocument({{ $doc->id }}, '{{ $doc->name }}.{{ $ext }}')">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                
                                {{-- Descargar --}}
                                <a href="{{ route('documental.document.download', $doc->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                {{-- Eliminar --}}
                                <form action="{{ route('documental.document.destroy', $doc->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este documento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editDocument(id) {
    fetch(`/documental/document/${id}/data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_document_name').value = data.name;
            document.getElementById('edit_responsable').value = data.responsable || '';
            document.getElementById('edit_proceso').value = data.proceso || '';
            document.getElementById('edit_departamento').value = data.departamento || '';
            document.getElementById('edit_estatus').value = data.estatus;
            document.getElementById('edit_observaciones').value = data.observaciones || '';
            document.getElementById('edit_fecha').value = data.fecha || '';
            
            document.getElementById('editDocumentForm').action = `/documental/document/${id}`;
            new bootstrap.Modal(document.getElementById('editDocumentModal')).show();
        });
}

function moveDocument(id, name) {
    document.getElementById('moveDocumentName').textContent = name;
    document.getElementById('moveDocumentForm').action = `/documental/document/${id}/move`;
    
    const select = document.getElementById('documentDestination');
    select.innerHTML = '<option value="">📁 Cargando...</option>';
    select.disabled = true;
    
    const currentFolder = {{ $currentFolder->id ?? 'null' }};
    
    fetch(`/documental/folders/tree?current_folder=${currentFolder}`)
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
@endif