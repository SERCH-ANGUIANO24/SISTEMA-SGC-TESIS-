{{-- TABLA DE DOCUMENTOS DEL EXPLORADOR DE COMPETENCIAS --}}
{{-- MUESTRA TODOS LOS DOCUMENTOS DE LA CARPETA ACTUAL CON SUS DATOS Y ACCIONES DISPONIBLES --}}
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

            {{-- RECORRE CADA DOCUMENTO DE LA CARPETA ACTUAL Y LO MUESTRA EN UNA FILA --}}
            {{-- LOS ATRIBUTOS data-* SE USAN PARA FILTRAR Y ORDENAR LOS DOCUMENTOS DESDE EL FRONTEND --}}
            @foreach($documents as $doc)
            <tr class="document-row" 
                data-document-name="{{ $doc->nombre }}"
                data-document-date="{{ $doc->created_at }}"
                data-document-size="{{ $doc->archivo_tamano }}">

                {{-- COLUMNA DE NOMBRE: MUESTRA EL ÍCONO DEL ARCHIVO Y SU NOMBRE CON EXTENSIÓN --}}
                <td>
                    <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                    {{ $doc->nombre }}.{{ $doc->archivo_extension }}
                </td>

                {{-- COLUMNA DE RESPONSABLE: MUESTRA EL RESPONSABLE O UN GUIÓN SI NO TIENE --}}
                <td>{{ $doc->responsable ?? '-' }}</td>

                {{-- COLUMNA DE FECHA DE EMISIÓN: FORMATEA LA FECHA EN dd/mm/YYYY O MUESTRA GUIÓN --}}
                <td>{{ $doc->fecha_emision ? $doc->fecha_emision->format('d/m/Y') : '-' }}</td>

                {{-- COLUMNA DE FECHA DE VENCIMIENTO: SI YA VENCIÓ SE MUESTRA EN ROJO --}}
                <td>
                    @if($doc->fecha_vencimiento)
                        <span class="{{ now() > $doc->fecha_vencimiento ? 'text-danger' : '' }}">
                            {{ $doc->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                    @else
                        -
                    @endif
                </td>

                {{-- COLUMNA DE ESTADO: CALCULA SI EL DOCUMENTO ESTÁ ACTIVO O VENCIDO --}}
                {{-- SI LA FECHA DE VENCIMIENTO YA PASÓ, SE MUESTRA "VENCIDO" EN ROJO --}}
                {{-- SI AÚN NO VENCE O NO TIENE FECHA, SE MUESTRA "ACTIVO" EN VERDE --}}
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

                {{-- COLUMNA DE TAMAÑO: MUESTRA EL TAMAÑO DEL ARCHIVO EN FORMATO LEGIBLE (KB, MB, ETC.) --}}
                <td>{{ $doc->formatted_size }}</td>

                {{-- COLUMNA DE ACCIONES: BOTONES PARA INTERACTUAR CON EL DOCUMENTO --}}
                {{-- event.stopPropagation() EVITA QUE EL CLIC SE PROPAGUE A LA FILA Y CAUSE CONFLICTOS --}}
                <td class="text-center" style="white-space: nowrap;">

                    {{-- DETERMINA SI EL ARCHIVO ES PREVISUALIZABLE DIRECTAMENTE EN EL NAVEGADOR --}}
                    {{-- LOS FORMATOS DE OFFICE (doc, docx, xls, xlsx, ppt, pptx) NO SON PREVISUALIZABLES --}}
                    @php
                        $extension = strtolower($doc->archivo_extension ?? '');
                        $esVisible = !in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
                    @endphp
                    
                    {{-- BOTÓN VER: SOLO SE MUESTRA SI EL ARCHIVO PUEDE PREVISUALIZARSE EN EL NAVEGADOR --}}
                    {{-- AL HACER CLIC ABRE UN MODAL ESPECÍFICO PARA ESTE DOCUMENTO --}}
                    @if($esVisible)
                        <button class="btn btn-sm btn-outline-info" 
                                onclick="event.stopPropagation(); var modal = new bootstrap.Modal(document.getElementById('viewDocumentModal{{ $doc->id }}')); modal.show();"
                                title="Ver">
                            <i class="bi bi-eye"></i>
                        </button>
                    @endif
                    
                    {{-- BOTÓN DESCARGAR: DISPONIBLE PARA TODOS LOS FORMATOS DE ARCHIVO --}}
                    <a href="{{ route('auditoria.competencias.document.download', $doc->id) }}" 
                       class="btn btn-sm btn-outline-success" 
                       onclick="event.stopPropagation();"
                       title="Descargar">
                        <i class="bi bi-download"></i>
                    </a>
                    
                    {{-- BOTÓN RENOMBRAR: ABRE EL MODAL PARA CAMBIAR EL NOMBRE DEL DOCUMENTO --}}
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="event.stopPropagation(); openRenameModal({{ $doc->id }}, '{{ $doc->nombre }}', 'Documento')"
                            title="Renombrar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    
                    {{-- BOTÓN MOVER: ABRE EL MODAL PARA MOVER EL DOCUMENTO A OTRA CARPETA --}}
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="event.stopPropagation(); openMoveModal({{ $doc->id }}, '{{ $doc->nombre }}.{{ $doc->archivo_extension }}', 'Documento')"
                            title="Mover">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    
                    {{-- BOTÓN ELIMINAR: LLAMA A LA FUNCIÓN QUE CONFIRMA Y EJECUTA EL SOFT DELETE --}}
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