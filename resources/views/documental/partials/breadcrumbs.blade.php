<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('documental.index') }}" class="text-decoration-none">
                <i class="bi bi-house-door me-1"></i>Raíz
            </a>
        </li>
        
        @foreach($breadcrumbs as $crumb)
            <li class="breadcrumb-item">
                <a href="{{ route('documental.index', ['folder' => $crumb['id']]) }}" 
                   class="text-decoration-none">
                    {{ $crumb['name'] }}
                </a>
            </li>
        @endforeach
        
        @if($currentFolder)
            <li class="breadcrumb-item active" aria-current="page">
                {{ $currentFolder->name }}
            </li>
        @endif
    </ol>
</nav>