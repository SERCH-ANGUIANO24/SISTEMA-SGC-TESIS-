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
                        <i class="bi bi-file-earmark-text me-2" style="color: #16a34a;"></i>
                        {{ $doc->name }}.{{ $extension }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh; background-color: #f8f9fa;">
                    @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <img src="{{ route('ver.imagen', $doc->id) }}" 
                                 class="img-fluid" 
                                 alt="{{ $doc->name }}"
                                 style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                    @elseif(in_array($extension, ['pdf']))
                        <iframe src="{{ route('ver.imagen', $doc->id) }}#toolbar=1" 
                                style="width: 100%; height: 100%; border: none;"
                                title="Visor PDF"></iframe>
                    @elseif(in_array($extension, ['txt']))
                        <iframe src="{{ route('ver.imagen', $doc->id) }}" 
                                style="width: 100%; height: 100%; border: none; background-color: white;"
                                title="Visor TXT"></iframe>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('formatos.document.download', $doc->id) }}" 
                       class="btn text-white" 
                       style="background-color: #16a34a;">
                        <i class="bi bi-download me-1"></i> Descargar
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach