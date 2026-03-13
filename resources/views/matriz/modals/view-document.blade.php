@foreach($documents as $doc)
    @php
        $extension = strtolower($doc->extension ?? '');
    @endphp
    
    @if(in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt']))
    <div class="modal fade" id="viewDocumentModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                        {{ $doc->name }}.{{ $extension }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <img src="{{ route('matriz.ver.archivo', $doc->id) }}" 
                                 class="img-fluid" 
                                 alt="{{ $doc->name }}"
                                 style="max-height: 100%; object-fit: contain;">
                        </div>
                    @elseif(in_array($extension, ['pdf']))
                        <iframe src="{{ route('matriz.ver.archivo', $doc->id) }}" 
                                style="width: 100%; height: 100%; border: none;"></iframe>
                    @elseif(in_array($extension, ['txt']))
                        <iframe src="{{ route('matriz.ver.archivo', $doc->id) }}" 
                                style="width: 100%; height: 100%; border: none;"></iframe>
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center h-100">
                            <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                            <p class="mt-3">Vista previa no disponible para este tipo de archivo</p>
                            <a href="{{ route('matriz.document.download', $doc->id) }}" class="btn text-white mt-2" style="background-color: #800000;">
                                <i class="bi bi-download me-1"></i> Descargar para ver
                            </a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('matriz.document.download', $doc->id) }}" 
                       class="btn text-white" 
                       style="background-color: #800000;">
                        <i class="bi bi-download me-1"></i> Descargar
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach