{{----GRID DE CARPETAS O LISTADO DE SUBCARPETAS EN UNA CARPETA ACTUAL--}}
{{--ESTE CODIGO MUESTRA TODAS LAS SUBCARPETAS O CARPETAS EN FORMA DE CARDS CON ICONO DE FOLDER, 
NOMBRE DE LA CARPETA Y BOTONES DE ACCIONES (RENOMBRAR, MOVER Y ELIMNAR DISPONIBLES PARA ADMIN Y SUPERADMIN)
AL HACER CLICK EN ALGUNA DE LAS CARPETAS INGRESAS DENTRO DE ELLA --}}

{{--SI HAY AL MENOS UNA CARPETA SE MUESTRA CARPETAS DE LO CONTRARIO APARECERA VACIO EL MODULO--}}
@if($folders->count() > 0)
    <div class="row mb-4">
        @foreach($folders as $folder)
        {{--CONTENEDOR PARA SEPARAR EL GRID DE CARPETAS--}}
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
        {{--CARD QUE MUESTRA A LA CARPETA CREADA CON SU NOMBRE COLOR SELECCIONADO Y BOTONES DE ACCION--}}
            <div class="card folder-card h-100 shadow-sm" 
                data-folder-id="{{ $folder->id }}" 
                data-folder-name="{{ strtolower($folder->name) }}" 
                data-folder-date="{{ $folder->created_at }}"
                data-folder-count="{{ $folder->documents->count() }}"
                 onclick="window.location='{{ route('anexos.index', ['folder' => $folder->id]) }}'"
                 style="border-top: 4px solid {{ $folder->color ?? '#800000' }};">
                <div class="card-body text-center p-3">
                {{--EL ICONO DE LA CARPETA AL CREARLA SE PUEDE PERSONALIZAR EL COLOR CON EL PERSONALIZADOR EL CUAL APARECE POR DEFECTO
                CON EL COLOR GUINDA--}}
                    <div class="folder-icon">
                        <i class="bi bi-folder-fill" style="color: {{ $folder->color ?? '#800000' }};"></i>
                    </div>
                    <h6 class="card-title fw-bold text-truncate">{{ $folder->name }}</h6>
                    
                    
                    
                    @php $userRole = Auth::user()->role; @endphp
                    
                    {{-- BOTONES DE ACCIÓN - SOLO SUPERADMIN/ADMIN --}}
                    @if(in_array($userRole, ['superadmin', 'admin']))
                    {{--BOTON DE RENOMBRAR --}}
                    <div class="mt-2 d-flex justify-content-center gap-1" onclick="event.stopPropagation();">
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openRenameModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Renombrar carpeta">
                            <i class="bi bi-pencil"></i>
                        </button>
                        {{--BOTON DE MOVER UNA CARPETA A OTRA--}}
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="openMoveModal('{{ $folder->id }}', '{{ $folder->name }}')"
                                title="Mover carpeta">
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                        {{--BOTON DE ELIMINAR CARPETA--}}
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="deleteElement({{ $folder->id }}, '{{ addslashes($folder->name) }}', 'Carpeta')"
                                title="Eliminar carpeta">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
{{--SI NO HAY SUBCARPETAS DENTRO DE UNA CARPETA MUESTRA EL MENSAJE DE "ESTA CARPETA ESTA VACIA"--}}
@if($folders->count() == 0 && (!isset($documents) || $documents->count() == 0))
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        Esta carpeta está vacía.
    </div>
@endif