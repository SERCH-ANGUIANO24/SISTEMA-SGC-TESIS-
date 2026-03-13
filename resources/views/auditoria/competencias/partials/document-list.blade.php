<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Nombre</th>
                <th>Responsable</th>
                <th>Fecha Emisión</th>
                <th>Fecha Vencimiento</th>
                <th>Estado</th>
                <th>Tamaño</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody id="documentTableBody">
            @foreach($documents as $doc)
            <tr class="document-row" 
                data-document-name="{{ $doc->nombre }}"
                data-document-date="{{ $doc->created_at }}"
                data-document-size="{{ $doc->archivo_tamano }}">
                <td>
                    <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                    {{ $doc->nombre }}.{{ $doc->archivo_extension }}
                </td>
                <td>{{ $doc->responsable ?? '-' }}</td>
                <td>{{ $doc->fecha_emision ? $doc->fecha_emision->format('d/m/Y') : '-' }}</td>
                <td>
                    @if($doc->fecha_vencimiento)
                        <span class="{{ now() > $doc->fecha_vencimiento ? 'text-danger' : '' }}">
                            {{ $doc->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @php
                        $estadoClass = 'badge bg-success';
                        $estadoText = 'Activo';
                        
                        if ($doc->fecha_vencimiento && now() > $doc->fecha_vencimiento) {
                            $estadoClass = 'badge bg-danger';
                            $estadoText = 'Vencido';
                        }
                    @endphp
                    <span class="{{ $estadoClass }}">{{ $estadoText }}</span>
                </td>
                <td>{{ $doc->formatted_size }}</td>
                <td class="text-center" style="white-space: nowrap;">
                    @php
                        $extension = strtolower($doc->archivo_extension ?? '');
                        $esVisible = !in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
                    @endphp
                    
                    @if($esVisible)
                        <button class="btn btn-sm btn-outline-info" 
                                onclick="event.stopPropagation(); var modal = new bootstrap.Modal(document.getElementById('viewDocumentModal{{ $doc->id }}')); modal.show();"
                                title="Ver">
                            <i class="bi bi-eye"></i>
                        </button>
                    @endif
                    
                    <a href="{{ route('auditoria.competencias.document.download', $doc->id) }}" 
                       class="btn btn-sm btn-outline-success" 
                       onclick="event.stopPropagation();"
                       title="Descargar">
                        <i class="bi bi-download"></i>
                    </a>
                    
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="event.stopPropagation(); openRenameModal({{ $doc->id }}, '{{ $doc->nombre }}', 'Documento')"
                            title="Renombrar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="event.stopPropagation(); openMoveModal({{ $doc->id }}, '{{ $doc->nombre }}.{{ $doc->archivo_extension }}', 'Documento')"
                            title="Mover">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="event.stopPropagation(); deleteElement({{ $doc->id }}, '{{ $doc->nombre }}', 'Documento')"
                            title="Eliminar">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>