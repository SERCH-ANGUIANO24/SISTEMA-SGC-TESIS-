@if($documents->count() > 0)
@php
    $hasUserDocuments = $documents->contains(function($doc) {
        return !in_array($doc->user->role ?? null, ['superadmin', 'admin']);
    });
    $hasAdminDocuments = $documents->contains(function($doc) {
        return in_array($doc->user->role ?? null, ['superadmin', 'admin']);
    });
@endphp
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
                        @if($hasAdminDocuments)
                        <th>Clave Formato</th>
                        <th>Código Proc.</th>
                        <th>Versión Proc.</th>
                        @endif
                        <th>Tamaño</th>
                        <th>Fecha y Hora</th>
                        @if($hasUserDocuments)
                        <th>Estatus</th>
                        @endif
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="documentTableBody">
                    @foreach($documents as $doc)
                    @php
                        $uploaderRole = $doc->user->role ?? null;
                        $uploadedByAdmin = in_array($uploaderRole, ['superadmin', 'admin']);
                    @endphp
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
                                
                                $displayName = $doc->name;
                                if(preg_match('/^[0-9_]+(.+)$/', $displayName, $matches)) {
                                    $displayName = $matches[1];
                                }
                            @endphp
                            <i class="bi {{ $icon }} me-2" style="color: #800000;"></i>
                            <span title="{{ $doc->original_name }}">{{ $displayName }}.{{ $ext }}</span>
                            
                            @if($doc->observaciones)
                                <br>
                                <small class="text-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    {{ $doc->observaciones }}
                                </small>
                            @endif
                        </td>
                        <td>{{ $doc->responsable ?? $doc->user->name ?? 'N/A' }}</td>
                        <td>{{ $doc->proceso ?? $doc->user->proceso ?? 'N/A' }}</td>
                        <td>{{ $doc->departamento ?? $doc->user->departamento ?? 'N/A' }}</td>
                        @if($hasAdminDocuments)
                        <td>{{ $uploadedByAdmin ? ($doc->clave_formato ?? '—') : '—' }}</td>
                        <td>{{ $uploadedByAdmin ? ($doc->codigo_procedimiento ?? '—') : '—' }}</td>
                        <td>{{ $uploadedByAdmin ? ($doc->version_procedimiento ?? '—') : '—' }}</td>
                        @endif
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
                            @if($hasUserDocuments && !$uploadedByAdmin)
                                @if(($doc->estatus ?? 'Pendiente') == 'Valido')
                                    <span class="badge bg-success">Válido</span>
                                @elseif(($doc->estatus ?? 'Pendiente') == 'No Valido')
                                    <span class="badge bg-danger">No Válido</span>
                                @else
                                    <span class="badge bg-warning text-white">Pendiente</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-end" style="white-space:nowrap;">
                            <div class="d-flex justify-content-end gap-1">
                                @php
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'ico', 'tiff', 'tif'];
                                    $textExtensions = ['txt', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'];
                                    $previewExtensions = array_merge(['pdf'], $imageExtensions, $textExtensions);
                                @endphp

                                {{-- VER - Visible para todos --}}
                                @if(in_array($ext, $previewExtensions))
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        onclick="viewDocument({{ $doc->id }})"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewDocumentModal{{ $doc->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @endif

                                {{-- EDITAR - Solo superadmin y admin, y solo si NO fue subido por admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']) && !$uploadedByAdmin)
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="editDocument({{ $doc->id }})">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @endif

                                {{-- EDITAR ADMIN - Solo superadmin y admin, solo en documentos subidos por ellos --}}
                                @if(in_array($userRole, ['superadmin', 'admin']) && $uploadedByAdmin)
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="editAdminDocument({{ $doc->id }})">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @endif
                                
                                {{-- MOVER - Solo superadmin y admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']))
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="moveDocument({{ $doc->id }}, '{{ $doc->name }}.{{ $ext }}')">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                @endif
                                
                                {{-- DESCARGAR - Visible para todos --}}
                                <a href="{{ route('documental.document.download', $doc->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                {{-- ELIMINAR - Solo superadmin y admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']))
                                <form action="{{ route('documental.document.destroy', $doc->id) }}" 
                                      method="POST" 
                                      class="d-inline" 
                                      id="delete-form-{{ $doc->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteDocument({{ $doc->id }}, '{{ addslashes($doc->name) }}', '{{ $ext }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function editDocument(id) {
    fetch(`/documental/document/${id}/data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_document_name').value = data.name || '';
            document.getElementById('edit_responsable').value   = data.responsable || '';
            document.getElementById('edit_proceso').value       = data.proceso || '';
            document.getElementById('edit_departamento').value  = data.departamento || '';
            document.getElementById('edit_estatus').value       = data.estatus || 'Pendiente';
            document.getElementById('edit_observaciones').value = data.observaciones || '';

            // Fecha: el servidor devuelve 'YYYY-MM-DDTHH:mm' ya en timezone correcto
            document.getElementById('edit_fecha').value = data.fecha || '';

            // Deshabilitar campos de info si el doc fue subido por usuario (no admin)
            if (typeof setModoUsuario === 'function') {
                setModoUsuario(!data.uploaded_by_admin);
            }

            // Disparar toggle de observaciones según estatus actual
            document.getElementById('edit_estatus').dispatchEvent(new Event('change'));

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

// Nueva función para eliminar con SweetAlert2
function deleteDocument(id, name, ext) {
    event.preventDefault();
    event.stopPropagation();
    
    const fullName = name + '.' + ext;
    
    Swal.fire({
        title: '¿Eliminar documento?',
        html: `
            <div style="text-align: left;">
                <center>
                <p style="font-size: 1.1rem; margin-bottom: 10px;">
                    ¿Estás seguro de eliminar  "<strong>📄 ${fullName}</strong>"?
                </p>
                </center>
                
                
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            document.getElementById(`delete-form-${id}`).submit();
        }
    });
    
    return false;
}
</script>
@endif