@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="d-flex justify-items-center justify-content-between">
        {{-- Versión móvil --}}
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination pagination-sm">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" style="background-color: #f8f9fa; color: #adb5bd; border-color: #dee2e6;">@lang('pagination.previous')</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="background-color: #737373; color: white; border-color: #737373;">@lang('pagination.previous')</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="background-color: #737373; color: white; border-color: #737373;">@lang('pagination.next')</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" style="background-color: #f8f9fa; color: #adb5bd; border-color: #dee2e6;">@lang('pagination.next')</span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Versión escritorio --}}
        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
            {{-- Información de resultados --}}
            <div>
                <p class="small mb-0" style="color: #737373; font-weight: 500;">
                    <i class="bi bi-table me-1"></i>
                    Mostrando 
                    <strong style="color: #000000;">{{ $paginator->firstItem() }}</strong> 
                    a 
                    <strong style="color: #000000;">{{ $paginator->lastItem() }}</strong> 
                    de 
                    <strong style="color: #000000;">{{ $paginator->total() }}</strong> 
                    resultados
                </p>
            </div>

            {{-- Botones de paginación --}}
            <div>
                <ul class="pagination pagination-sm mb-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true" style="background-color: #f8f9fa; color: #adb5bd; border-color: #dee2e6;">
                                <i class="bi bi-chevron-left"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" style="background-color: #ffffff; color: #6c757d; border-color: #dee2e6; transition: all 0.2s ease;" 
                               onmouseover="this.style.backgroundColor='#6c757d'; this.style.color='white'; this.style.borderColor='#6c757d';"
                               onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6';">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link" style="background-color: #f8f9fa; color: #6c757d; border-color: #dee2e6;">{{ $element }}</span>
                            </li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link" style="background-color: #6c757d; color: white; border-color: #6c757d; font-weight: 600;">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}" style="background-color: #ffffff; color: #6c757d; border-color: #dee2e6; transition: all 0.2s ease;" 
                                           onmouseover="this.style.backgroundColor='#6c757d'; this.style.color='white'; this.style.borderColor='#6c757d';"
                                           onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6';">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" style="background-color: #ffffff; color: #6c757d; border-color: #dee2e6; transition: all 0.2s ease;"
                               onmouseover="this.style.backgroundColor='#6c757d'; this.style.color='white'; this.style.borderColor='#6c757d';"
                               onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#6c757d'; this.style.borderColor='#dee2e6';">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true" style="background-color: #f8f9fa; color: #adb5bd; border-color: #dee2e6;">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif