{{-- SECCIÓN DE BREADCRUMBS (MIGAS DE PAN) PARA LA NAVEGACIÓN DEL EXPLORADOR DE COMPETENCIAS --}}
{{-- SOLO SE MUESTRA SI HAY CARPETAS EN EL BREADCRUMB O SI HAY UNA CARPETA ACTUAL SELECCIONADA --}}
@if($breadcrumbs->count() > 0 || $currentFolder)
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-light p-3 rounded-3">

        {{-- PRIMER ELEMENTO FIJO: SIEMPRE MUESTRA "RAÍZ" CON ENLACE AL INICIO DEL EXPLORADOR --}}
        <li class="breadcrumb-item">
            <a href="{{ route('auditoria.competencias.index') }}" class="text-decoration-none">
                <i class="bi bi-house-door"></i> Raíz
            </a>
        </li>

        {{-- RECORRE CADA CARPETA DEL BREADCRUMB PARA CONSTRUIR LA RUTA DE NAVEGACIÓN --}}
        @foreach($breadcrumbs as $folder)

            {{-- SI ES LA ÚLTIMA CARPETA DEL BREADCRUMB, SE MUESTRA COMO ELEMENTO ACTIVO (SIN ENLACE) --}}
            {{-- ESTO INDICA AL USUARIO EN QUÉ CARPETA SE ENCUENTRA ACTUALMENTE --}}
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-folder" style="color: {{ $folder->color }}"></i> {{ $folder->nombre }}
                </li>

            {{-- SI NO ES LA ÚLTIMA CARPETA, SE MUESTRA CON ENLACE PARA PODER NAVEGAR HACIA ELLA --}}
            @else
                <li class="breadcrumb-item">
                    <a href="{{ route('auditoria.competencias.index', ['folder' => $folder->id]) }}" class="text-decoration-none">
                        <i class="bi bi-folder" style="color: {{ $folder->color }}"></i> {{ $folder->nombre }}
                    </a>
                </li>
            @endif

        @endforeach
    </ol>
</nav>
@endif