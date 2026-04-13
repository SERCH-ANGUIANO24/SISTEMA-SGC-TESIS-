{{-- COMPONENTE QUE MUESTRA LA LISTA DE DOCUMENTOS DENTRO DE UNA CARPETA DEL EXPLORADOR --}}
{{-- SOLO SE RENDERIZA SI HAY AL MENOS UN DOCUMENTO EN LA CARPETA ACTUAL --}}
@if($documents->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold" style="color: #000000;">
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

                        {{-- RECORRE CADA DOCUMENTO Y LO MUESTRA EN UNA FILA DE LA TABLA --}}
                        {{-- LOS ATRIBUTOS data-* SE USAN PARA FILTRAR Y ORDENAR DESDE EL FRONTEND --}}
                        @foreach($documents as $doc)
                        <tr class="file-row" 
                            data-file-id="{{ $doc->id }}"
                            data-file-name="{{ strtolower($doc->nombre) }}"
                            data-file-size="{{ $doc->archivo_tamano }}"
                            data-file-date="{{ $doc->created_at }}"
                            data-file-extension="{{ strtolower($doc->archivo_extension ?? '') }}">

                            {{-- COLUMNA DE NOMBRE: MUESTRA EL ÍCONO Y EL NOMBRE DEL ARCHIVO CON SU EXTENSIÓN --}}
                            <td>
                                <i class="bi bi-file-earmark-text me-2" style="color: #000000;"></i>
                                {{ $doc->nombre }}.{{ $doc->archivo_extension }}
                            </td>

                            {{-- COLUMNA DE TAMAÑO: MUESTRA EL PESO DEL ARCHIVO EN FORMATO LEGIBLE (KB, MB, ETC.) --}}
                            <td>{{ $doc->formatted_size }}</td>

                            {{-- COLUMNA DE FECHA: MUESTRA LA FECHA Y HORA EN QUE SE SUBIÓ EL ARCHIVO --}}
                            <td>{{ $doc->created_at->format('d/m/Y h:i A') }}</td>

                            {{-- COLUMNA DE ACCIONES: BOTONES PARA INTERACTUAR CON EL DOCUMENTO --}}
                            <td class="text-end">
                                @php
                                    $ext = strtolower($doc->archivo_extension ?? '');
                                    // Extensiones que NO se pueden ver
                                    $noViewable = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv'];
                                @endphp
                                
                                {{-- BOTÓN VER: VISIBLE PARA TODOS LOS USUARIOS --}}
                                {{-- SOLO SE MUESTRA SI EL ARCHIVO PUEDE PREVISUALIZARSE EN EL NAVEGADOR --}}
                                {{-- LOS FORMATOS DE OFFICE Y CSV NO SON PREVISUALIZABLES --}}
                                @if(!in_array($ext, $noViewable))
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewDocumentModal{{ $doc->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                                
                                {{-- BOTÓN RENOMBRAR: SOLO VISIBLE PARA SUPERADMIN Y ADMIN --}}
                                {{-- ABRE UN MODAL PARA CAMBIAR EL NOMBRE DEL DOCUMENTO --}}
                                @can('auditoria-access')
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="openRenameDocumentModal({{ $doc->id }}, '{{ $doc->nombre }}')"
                                        title="Renombrar archivo">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endcan
                                
                                {{-- BOTÓN MOVER: SOLO VISIBLE PARA SUPERADMIN Y ADMIN --}}
                                {{-- ABRE UN MODAL PARA MOVER EL DOCUMENTO A OTRA CARPETA --}}
                                @can('auditoria-access')
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="openMoveDocumentModal({{ $doc->id }}, '{{ $doc->nombre }}.{{ $doc->archivo_extension }}')"
                                        title="Mover archivo">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                                @endcan
                                
                                {{-- BOTÓN DESCARGAR: VISIBLE PARA TODOS LOS USUARIOS --}}
                                {{-- DESCARGA EL ARCHIVO CON SU NOMBRE ORIGINAL --}}
                                <a href="{{ route('auditoria.competencias.document.download', $doc->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                {{-- BOTÓN ELIMINAR: SOLO VISIBLE PARA SUPERADMIN, ADMIN Y AUDITOR_LIDER --}}
                                {{-- LLAMA A LA FUNCIÓN QUE CONFIRMA Y EJECUTA EL SOFT DELETE --}}
                                @can('auditoria-access')
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteElement({{ $doc->id }}, '{{ $doc->nombre }}', 'Documento')"
                                        title="Eliminar archivo">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{{-- SI NO HAY DOCUMENTOS Y SE ESTÁ DENTRO DE UNA CARPETA, SE MUESTRA UN MENSAJE INFORMATIVO --}}
@else
    @if(isset($currentFolder) && $currentFolder)
    <div class="alert alert-info d-flex align-items-center mt-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        No hay archivos en esta carpeta.
    </div>
    @endif
@endif