<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            @if(request()->has('folder'))
                {{-- Si hay una carpeta seleccionada, Raíz es cliqueable --}}
                <a href="{{ route('documental.index') }}" class="text-decoration-none" style="color: #800000; cursor: pointer;">
                    <i class="bi bi-folder me-1"></i>Raíz
                </a>
            @endif
        </li>
        
        @foreach($breadcrumbs as $index => $crumb)
            @if($index < count($breadcrumbs) - 1)
                {{-- Carpetas superiores (cliqueables) --}}
                <li class="breadcrumb-item">
                    <a href="{{ route('documental.index', ['folder' => $crumb['id']]) }}" 
                       class="text-decoration-none" style="color: #800000; cursor: pointer;">
                        <i class="bi bi-folder me-1"></i>{{ $crumb['name'] }}
                    </a>
                </li>
            @else
                {{-- Última carpeta del breadcrumb (no cliqueable) --}}
                <li class="breadcrumb-item active" aria-current="page">
                    <span style="color: #6c757d; cursor: default;">
                        <i class="bi bi-folder me-1"></i>{{ $crumb['name'] }}
                    </span>
                </li>
            @endif
        @endforeach
    </ol>
</nav>