@if(!empty($breadcrumbs) || $currentFolder)
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-light p-3 rounded-3">
        <li class="breadcrumb-item">
            <a href="{{ route('matriz.index') }}" class="text-decoration-none" style="color: #800000;">
                <i class="bi bi-house-door"></i> Raíz
            </a>
        </li>
        @foreach($breadcrumbs as $folder)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page" style="color: #6c757d;">
                    <i class="bi bi-folder me-1"></i> {{ $folder['name'] }}
                </li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ route('matriz.index', ['folder' => $folder['id']]) }}" 
                       class="text-decoration-none" style="color: #800000;">
                        <i class="bi bi-folder me-1"></i> {{ $folder['name'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif