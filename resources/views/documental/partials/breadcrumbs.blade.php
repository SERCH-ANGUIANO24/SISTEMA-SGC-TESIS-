<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('documental.index') }}" class="text-muted text-decoration-none">
                <i class="bi bi-folder me-1"></i>Raíz
            </a>
        </li>
        
        @foreach($breadcrumbs as $crumb)
            <li class="breadcrumb-item">
                <a href="{{ route('documental.index', ['folder' => $crumb['id']]) }}" 
                   class="text-muted text-decoration-none">
                    <i class="bi bi-folder me-1"></i>{{ $crumb['name'] }}
                </a>
            </li>
        @endforeach
    </ol>
</nav>