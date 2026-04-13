@if(count($breadcrumbs) > 0)
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3">
        {{-- ENLACE SIEMPRE VISIBLE A LA RAÍZ  --}}
        <li class="breadcrumb-item">
            <a href="{{ route('documental.index') }}" class="text-decoration-none">
                <i class="bi bi-house-door"></i> Raíz
            </a>
        </li>
        {{-- RECORRE TODAS LAS CARPETAS EN LA RUTA ACTUAL --}}
        @foreach($breadcrumbs as $index => $folder)
            {{-- SI ES LA ÚLTIMA CARPETA (DONDE ESTAMOS ACTUALMENTE) --}}
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-folder" style="color: #000000;"></i> {{ $folder['name'] }}
                </li>
            @else
                {{-- CARPETAS ANTERIORES - SON ENLACES CLICKEABLES --}}
                <li class="breadcrumb-item">
                    <a href="{{ route('documental.index', ['folder' => $folder['id']]) }}" class="text-decoration-none">
                        <i class="bi bi-folder" style="color: #000000;"></i> {{ $folder['name'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif