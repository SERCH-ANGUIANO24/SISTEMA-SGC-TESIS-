@if($documents->count() > 0)
@php
    $hasUserDocuments = $documents->contains(function($doc) {
        return !in_array($doc->user->role ?? null, ['superadmin', 'admin']);
    });
    $hasAdminDocuments = $documents->contains(function($doc) {
        return in_array($doc->user->role ?? null, ['superadmin', 'admin']);
    });
    $esAdmin = in_array(Auth::user()->role, ['superadmin', 'admin']);
@endphp
@php
    $hayDocsMios = $documents->contains(function($doc) {
        return $doc->user_id === Auth::id()
            && !in_array($doc->user->role ?? null, ['superadmin', 'admin']);
    });
@endphp
<div class="card shadow-sm border-0">
    <div class="card-header bg-light py-3">
        <h6 class="mb-0 fw-bold" style="color: #000000;">
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
                        {{-- Columna Estatus: visible para admin y para el propio usuario que subió docs --}}
                        @if($esAdmin || $hayDocsMios)
                        <th>Estatus</th>
                        @endif
                        <th>Tipo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="documentTableBody">
                    @foreach($documents as $doc)
                    @php
                        $uploaderRole    = $doc->user->role ?? null;
                        $uploadedByAdmin = in_array($uploaderRole, ['superadmin', 'admin']);
                        $ext = strtolower($doc->extension ?? pathinfo($doc->original_name, PATHINFO_EXTENSION));
                        $estatus = $doc->estatus ?? 'Pendiente';

                        // Usuarios normales NO ven docs de otros usuarios con estatus Pendiente o No Valido
                        // Solo ven sus propios docs o docs Válidos
                        $esMiDoc = $doc->user_id === Auth::id();
                        $esValido = $estatus === 'Valido';

                        // Si es usuario (no admin): ocultar fila si el doc no es suyo Y no es válido
                        if (!$esAdmin && !$uploadedByAdmin && !$esMiDoc && !$esValido) {
                            continue;
                        }
                        // Si es usuario (no admin): ocultar fila si es su doc pero está Pendiente o No Valido
                        // NO — el usuario sí puede ver sus propios docs en cualquier estatus
                    @endphp
                    <tr class="document-row"
                        data-file-id="{{ $doc->id }}"
                        data-file-name="{{ strtolower($doc->name) }}"
                        data-file-size="{{ $doc->size }}"
                        data-file-date="{{ $doc->created_at }}"
                        data-file-extension="{{ $ext }}"
                        data-tipo-documento="{{ $doc->tipo_documento ?? '' }}">

                        {{-- Nombre --}}
                        <td>
                            @php
                                $icon = 'bi-file-earmark';
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
                            @if($doc->observaciones && $esAdmin)
                                <br>
                                <small class="text-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    {{ $doc->observaciones }}
                                </small>
                            @endif
                        </td>

                        {{-- Responsable --}}
                        <td>{{ $doc->responsable ?? $doc->user->name ?? 'N/A' }}</td>

                        {{-- Proceso --}}
                        <td>{{ $doc->proceso ?? $doc->user->proceso ?? 'N/A' }}</td>

                        {{-- Departamento --}}
                        <td>{{ $doc->departamento ?? $doc->user->departamento ?? 'N/A' }}</td>

                        {{-- Tamaño --}}
                        <td>
                            @if($doc->size)
                                @if($doc->size < 1024) {{ $doc->size }} B
                                @elseif($doc->size < 1048576) {{ round($doc->size / 1024, 1) }} KB
                                @else {{ round($doc->size / 1048576, 1) }} MB
                                @endif
                            @else N/A @endif
                        </td>

                        {{-- Fecha --}}
                        <td>{{ $doc->created_at->format('d/m/Y h:i A') }}</td>

                        {{-- Estatus: visible para admin y para el propio usuario que subió el doc --}}
                        @if($esAdmin || ($hayDocsMios && $esMiDoc && !$uploadedByAdmin))
                        <td>
                            @if($uploadedByAdmin)
                                <span class="badge bg-success">Válido</span>
                            @else
                                @if($estatus == 'Valido')
                                    <span class="badge bg-success">Válido</span>
                                @elseif($estatus == 'No Valido')
                                    <span class="badge bg-danger">No Válido</span>
                                @else
                                    <span class="badge bg-warning text-white">Pendiente</span>
                                @endif
                            @endif
                        </td>
                        @endif

                        {{-- Tipo --}}
                        <td>
                            @if($doc->tipo_documento === 'Formato')
                                <span class="badge" style="background-color:#0d6efd;">Formato</span>
                            @elseif($doc->tipo_documento === 'Procedimiento')
                                <span class="badge" style="background-color:#6f42c1;">Procedimiento</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td class="text-end" style="white-space:nowrap;">
                            <div class="d-flex justify-content-end gap-1">
                                @php
                                    $textExtensions    = ['txt', 'php', 'js', 'css', 'html', 'xml', 'json', 'sql', 'md'];
                                    $previewExtensions = array_merge(['pdf'], $imageExtensions, $textExtensions);
                                @endphp

                                {{-- VER: admin siempre, usuarios solo si el doc es Válido o es su propio doc --}}
                                @if(in_array($ext, $previewExtensions) && ($esAdmin || $esValido || $esMiDoc))
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="viewDocument({{ $doc->id }})"
                                        title="Ver documento"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewDocumentModal{{ $doc->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @endif

                                {{-- EDITAR ESTATUS - Solo para docs de usuario (NO para docs de admin) --}}
                                @if(in_array($userRole, ['superadmin', 'admin']) && !$uploadedByAdmin)
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="editDocument({{ $doc->id }})" title="Editar estatus">

                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @endif

                                {{-- MOVER - Solo admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']))
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="moveDocument({{ $doc->id }}, '{{ $doc->name }}.{{ $ext }}')" title="Mover Archivo">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                @endif

                                {{-- DESCARGAR: admin siempre, usuarios si el doc es Válido o es su propio doc --}}
                                @if($esAdmin || $esValido || $esMiDoc)
                                <a href="{{ route('documental.document.download', $doc->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Descargar archivo">
                                    <i class="bi bi-download"></i>
                                </a>
                                @endif

                                {{-- ELIMINAR - Solo admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']))
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteDocument({{ $doc->id }}, '{{ addslashes($doc->name) }}', '{{ $ext }}')"
                                        title="Eliminar archivo">
                                    <i class="bi bi-trash"></i>
                                </button>
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
            document.getElementById('edit_fecha').value         = data.fecha || '';

            if (typeof setModoUsuario === 'function') {
                setModoUsuario(!data.uploaded_by_admin, data.tipo_documento || '');
            }

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

function deleteDocument(id, name, ext) {
    event.stopPropagation();
    const fullName = name + '.' + ext;
    Swal.fire({
        title: '¿Eliminar archivo?',
        text: `¿Estás seguro de eliminar "${fullName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Eliminando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            fetch('/documental/document/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: '¡Eliminado!', text: data.message, confirmButtonColor: '#800000', timer: 2000 })
                    .then(() => { location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al eliminar', confirmButtonColor: '#800000' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión', confirmButtonColor: '#800000' });
            });
        }
    });
    return false;
}
</script>
@endif