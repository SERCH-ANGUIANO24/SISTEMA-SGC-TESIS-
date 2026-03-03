@if($documents->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold" style="color: #800000;">
                <i class="bi bi-file-earmark me-2"></i>Documentos
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
                        <tr class="file-row" 
                            data-file-id="{{ $doc->id }}"
                            data-file-name="{{ strtolower($doc->nombre) }}"
                            data-file-size="{{ $doc->archivo_tamano }}"
                            data-file-date="{{ $doc->created_at }}"
                            data-file-extension="{{ strtolower($doc->archivo_extension ?? '') }}">
                            <td>
                                <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                                {{ $doc->nombre }}.{{ $doc->archivo_extension }}
                            </td>
                            <td>{{ $doc->formatted_size }}</td>
                            <td>{{ $doc->created_at->format('d/m/Y h:i A') }}</td>
                            <td class="text-end">
                                @php
                                    $ext = strtolower($doc->archivo_extension ?? '');
                                    // Extensiones que NO se pueden ver
                                    $noViewable = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];
                                @endphp
                                
                                {{-- VER - Visible para todos --}}
                                @if(!in_array($ext, $noViewable))
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewDocumentModal{{ $doc->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                                
                                {{-- RENOMBRAR - Solo superadmin y admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']))
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="openRenameDocumentModal({{ $doc->id }}, '{{ $doc->nombre }}')"
                                        title="Renombrar archivo">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endif
                                
                                {{-- MOVER - Solo superadmin y admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']))
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="openMoveDocumentModal({{ $doc->id }}, '{{ $doc->nombre }}.{{ $doc->archivo_extension }}')"
                                        title="Mover archivo">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                @endif
                                
                                {{-- DESCARGAR - Visible para todos --}}
                                <a href="{{ route('auditoria.competencias.document.download', $doc->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                {{-- ELIMINAR - Solo superadmin y admin --}}
                                @if(in_array($userRole, ['superadmin', 'admin']))
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteElement({{ $doc->id }}, '{{ $doc->nombre }}', 'Documento')"
                                        title="Eliminar archivo">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    @if(isset($currentFolder) && $currentFolder)
    <div class="alert alert-info d-flex align-items-center mt-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        No hay archivos en esta carpeta.
    </div>
    @endif
@endif