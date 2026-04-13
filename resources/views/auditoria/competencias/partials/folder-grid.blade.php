{{-- COMPONENTE QUE MUESTRA LAS CARPETAS EN FORMATO DE CUADRÍCULA (GRID) --}}
{{-- SOLO SE RENDERIZA SI HAY AL MENOS UNA CARPETA EN LA UBICACIÓN ACTUAL --}}
@if($folders->count() > 0)
    <div class="row mb-4">

        {{-- RECORRE CADA CARPETA Y LA MUESTRA COMO UNA TARJETA CLICKEABLE --}}
        @foreach($folders as $folder)
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">

            {{-- TARJETA DE CARPETA: AL HACER CLIC NAVEGA DENTRO DE ELLA --}}
            {{-- EL BORDE SUPERIOR USA EL COLOR PERSONALIZADO DE LA CARPETA --}}
            {{-- LOS ATRIBUTOS data-* SE USAN PARA FILTRAR Y ORDENAR DESDE EL FRONTEND --}}
            <div class="card folder-card h-100 shadow-sm" 
                data-folder-id="{{ $folder->id }}" 
                data-folder-name="{{ strtolower($folder->nombre) }}" 
                data-folder-date="{{ $folder->created_at }}"
                data-folder-count="{{ $folder->total_items_count }}"
                 onclick="window.location='{{ route('auditoria.competencias.index', ['folder' => $folder->id]) }}'"
                 style="border-top: 4px solid {{ $folder->color ?? '#800000' }};">
                <div class="card-body text-center p-3">

                    {{-- ÍCONO DE CARPETA CON EL COLOR PERSONALIZADO ASIGNADO AL CREARLA --}}
                    <div class="folder-icon">
                        <i class="bi bi-folder-fill" style="color: {{ $folder->color ?? '#800000' }};"></i>
                    </div>

                    {{-- NOMBRE DE LA CARPETA: SE TRUNCA SI ES MUY LARGO PARA NO ROMPER EL DISEÑO --}}
                    <h6 class="card-title fw-bold text-truncate">{{ $folder->nombre }}</h6>
                    
                    
                    {{-- BOTONES DE ACCIÓN - Solo superadmin y admin --}}
                    {{-- event.stopPropagation() EVITA QUE EL CLIC EN LOS BOTONES ABRA LA CARPETA --}}
                    @can('auditoria-access')
                    <div class="mt-2 d-flex justify-content-center gap-1" onclick="event.stopPropagation();">

                        {{-- BOTÓN RENOMBRAR: ABRE EL MODAL PARA CAMBIAR EL NOMBRE DE LA CARPETA --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openRenameModal({{ $folder->id }}, '{{ $folder->nombre }}')"
                                title="Renombrar carpeta">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        {{-- BOTÓN MOVER: ABRE EL MODAL PARA MOVER LA CARPETA A OTRA UBICACIÓN --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openMoveModal({{ $folder->id }}, '{{ $folder->nombre }}')"
                                title="Mover carpeta">
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                        
                        {{-- BOTÓN ELIMINAR: LLAMA A LA FUNCIÓN QUE CONFIRMA Y EJECUTA EL SOFT DELETE --}}
                        {{-- addslashes() ESCAPA LAS COMILLAS EN EL NOMBRE PARA EVITAR ERRORES EN JS --}}
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="deleteElement({{ $folder->id }}, '{{ addslashes($folder->nombre) }}', 'Carpeta')"
                                title="Eliminar carpeta">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- MENSAJE DE CARPETA VACÍA: SE MUESTRA SOLO SI NO HAY NI CARPETAS NI DOCUMENTOS EN LA UBICACIÓN ACTUAL --}}
@if($folders->count() == 0 && (!isset($documents) || $documents->count() == 0))
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        Esta carpeta está vacía.
    </div>
@endif

{{-- MODAL RENOMBRAR CARPETA --}}
{{-- SE ACTIVA DESDE EL BOTÓN RENOMBRAR Y ENVÍA EL FORMULARIO AL CONTROLADOR VÍA PUT --}}
<div class="modal fade" id="renameFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="renameFolderForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2" style="color: #000000;"></i>
                        Renombrar Carpeta
                    </h5>
                </div>
                <div class="modal-body">
                    {{-- CAMPO DE TEXTO DONDE EL USUARIO ESCRIBE EL NUEVO NOMBRE DE LA CARPETA --}}
                    <div class="mb-3">
                        <label for="newFolderName" class="form-label fw-bold">Nuevo nombre</label>
                        <input type="text" class="form-control" id="newFolderName" name="nombre" required autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Renombrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL MOVER CARPETA --}}
{{-- SE ACTIVA DESDE EL BOTÓN MOVER Y PERMITE SELECCIONAR LA CARPETA DESTINO --}}
{{-- EL SELECT SE LLENA DINÁMICAMENTE CON EL ÁRBOL DE CARPETAS VÍA AJAX --}}
<div class="modal fade" id="moveFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="moveFolderForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #000000;"></i>
                        Mover Carpeta
                    </h5>

                </div>
                <div class="modal-body">
                    {{-- MUESTRA EL NOMBRE DE LA CARPETA QUE SE VA A MOVER --}}
                    {{-- SE RELLENA DINÁMICAMENTE DESDE JAVASCRIPT AL ABRIR EL MODAL --}}
                    <p class="mb-3">
                        <span class="fw-bold">Carpeta a mover:</span><br>
                        <span id="moveFolderName" style="color: #737373; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        {{-- SELECT CON LAS CARPETAS DISPONIBLES COMO DESTINO --}}
                        {{-- LA OPCIÓN "RAÍZ PRINCIPAL" MUEVE LA CARPETA AL NIVEL MÁS ALTO --}}
                        <label for="folderDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="folderDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Selecciona la carpeta donde deseas mover.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-arrow-right me-1"></i> Mover aquí
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>