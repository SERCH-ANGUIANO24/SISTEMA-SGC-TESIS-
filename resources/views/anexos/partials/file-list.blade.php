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
                            data-file-name="{{ strtolower($doc->name) }}"
                            data-file-size="{{ $doc->size }}"
                            data-file-date="{{ $doc->created_at }}"
                            data-file-extension="{{ strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION)) }}">
                            <td>
                                <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                                {{ $doc->name }}.{{ pathinfo($doc->original_name, PATHINFO_EXTENSION) }}
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
                                    $ext = strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION));
                                @endphp
                                
                                @if(!in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewDocumentModal{{ $doc->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                                
                                {{-- BOTÓN RENOMBRAR --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="openRenameDocumentModal('{{ $doc->id }}', '{{ $doc->name }}')"
                                        title="Renombrar archivo">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                {{-- BOTÓN MOVER --}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="openMoveDocumentModal('{{ $doc->id }}', '{{ $doc->name }}.{{ pathinfo($doc->original_name, PATHINFO_EXTENSION) }}')"
                                        title="Mover archivo">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                
                                <a href="{{ route('anexos.document.download', $doc->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                <form action="{{ route('anexos.document.destroy', $doc->id) }}" method="POST" class="d-inline">
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
        No hay archivos en esta carpeta.
    </div>
    @endif
@endif