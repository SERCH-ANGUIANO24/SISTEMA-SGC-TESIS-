@if($documents->count() > 0)
<div class="card shadow-sm border-0">
    <div class="card-header bg-light py-3">
        <h6 class="mb-0 fw-bold" style="color: #800000;">
            <i class="bi-files me-2"></i>
            Documentos
        </h6>
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
                        <th>Tamaño</th>
                        <th>Fecha y Hora</th>
                        <th>Estatus</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="documentTableBody">
                    @foreach($documents as $doc)
                    <tr class="document-row"
                        data-file-id="{{ $doc->id }}"
                        data-file-name="{{ strtolower($doc->name) }}"
                        data-file-size="{{ $doc->size }}"
                        data-file-date="{{ $doc->created_at }}"
                        data-file-extension="{{ strtolower($doc->extension ?? pathinfo($doc->original_name, PATHINFO_EXTENSION)) }}">
                        <td>
                            @php
                                $icon = 'bi-file-earmark';
                                $ext = strtolower($doc->extension ?? pathinfo($doc->original_name, PATHINFO_EXTENSION));
                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'ico', 'tiff', 'tif'];
                                
                                if(in_array($ext, ['pdf'])) $icon = 'bi-file-pdf';
                                elseif(in_array($ext, ['doc','docx'])) $icon = 'bi-file-word';
                                elseif(in_array($ext, ['xls','xlsx'])) $icon = 'bi-file-excel';
                                elseif(in_array($ext, $imageExtensions)) $icon = 'bi-file-image';
                                
                                // Limpiar el nombre para mostrar (quitar números y guiones bajos si es necesario)
                                $displayName = $doc->name;
                                // Si el nombre tiene muchos números, mostrar solo la parte legible
                                if(preg_match('/^[0-9_]+(.+)$/', $displayName, $matches)) {
                                    $displayName = $matches[1];
                                }
                            @endphp
                            <i class="bi {{ $icon }} me-2" style="color: #800000;"></i>
                            <span title="{{ $doc->original_name }}">{{ $displayName }}.{{ $ext }}</span>
                        </td>
                        <td>{{ $doc->responsable ?? $doc->user->name ?? 'N/A' }}</td>
                        <td>{{ $doc->proceso ?? $doc->user->proceso ?? 'N/A' }}</td>
                        <td>{{ $doc->departamento ?? $doc->user->departamento ?? 'N/A' }}</td>
                        <td>
                            @if($doc->size)
                                @if($doc->size < 1024)
                                    {{ $doc->size }} B
                                @elseif($doc->size < 1048576)
                                    {{ round($doc->size / 1024, 1) }} KB
                                @else
                                    {{ round($doc->size / 1048576, 1) }} MB
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            {{ $doc->created_at->format('d/m/Y h:i A') }}
                        </td>
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
                                @php
                                    // === PARTE MODIFICADA: Todas las extensiones que pueden tener vista previa ===
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'ico', 'tiff', 'tif'];
                                    $textExtensions = ['txt', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'];
                                    $previewExtensions = array_merge(['pdf'], $imageExtensions, $textExtensions);
                                @endphp

                                @if(in_array($ext, $previewExtensions))
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
            
            // Usar created_at para el input datetime-local si fecha no está disponible
            if (data.fecha) {
                const fecha = new Date(data.fecha);
                const year = fecha.getFullYear();
                const month = String(fecha.getMonth() + 1).padStart(2, '0');
                const day = String(fecha.getDate()).padStart(2, '0');
                const hours = String(fecha.getHours()).padStart(2, '0');
                const minutes = String(fecha.getMinutes()).padStart(2, '0');
                document.getElementById('edit_fecha').value = `${year}-${month}-${day}T${hours}:${minutes}`;
            } else {
                document.getElementById('edit_fecha').value = '';
            }
            
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