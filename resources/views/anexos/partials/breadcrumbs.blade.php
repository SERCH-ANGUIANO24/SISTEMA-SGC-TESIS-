@if($breadcrumbs->count() > 0 || $currentFolder)
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-light p-3 rounded-3">
        <li class="breadcrumb-item">
            <a href="{{ route('anexos.index') }}" class="text-decoration-none">
                <i class="bi bi-house-door"></i> Raíz
            </a>
        </li>
        @foreach($breadcrumbs as $folder)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-folder" style="color: {{ $folder->color }}"></i> {{ $folder->name }}
                </li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ route('anexos.index', ['folder' => $folder->id]) }}" class="text-decoration-none">
                        <i class="bi bi-folder" style="color: {{ $folder->color }}"></i> {{ $folder->name }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif