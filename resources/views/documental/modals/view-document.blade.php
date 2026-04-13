@foreach($documents as $doc)
    @php
        // OBTIENE LA EXTENSIÓN DEL ARCHIVO
        $extension = strtolower($doc->extension ?? pathinfo($doc->original_name, PATHINFO_EXTENSION));
    @endphp
    
    {{-- SOLO CREAR MODAL SI LA EXTENSIÓN ES VISUALIZABLE (PDF, IMÁGENES, TXT) --}}
    @if(in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt']))
    <div class="modal fade" id="viewDocumentModal{{ $doc->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2" style="color: #000000;"></i>
                        {{ $doc->name }}.{{ $extension }} {{-- MUESTRA EL NOMBRE COMPLETO DEL DOCUMENTO --}}
                    </h5>

                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    {{-- SI ES IMAGEN (JPG, JPEG, PNG, GIF) - MOSTRAR CON ETIQUETA IMG --}}
                    @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <img src="{{ route('documental.ver.archivo', $doc->id) }}" 
                                 class="img-fluid" 
                                 alt="{{ $doc->name }}"
                                 style="max-height: 100%; object-fit: contain;">
                        </div>
                    {{-- SI ES PDF - MOSTRAR EN IFRAME --}}
                    @elseif(in_array($extension, ['pdf']))
                        <iframe src="{{ route('documental.ver.archivo', $doc->id) }}" 
                                style="width: 100%; height: 100%; border: none;"></iframe>
                    {{-- SI ES TXT - MOSTRAR EN IFRAME --}}
                    @elseif(in_array($extension, ['txt']))
                        <iframe src="{{ route('documental.ver.archivo', $doc->id) }}" 
                                style="width: 100%; height: 100%; border: none;"></iframe>
                    {{-- PARA OTROS TIPOS - MOSTRAR MENSAJE Y BOTÓN DE DESCARGA --}}
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center h-100">
                            <i class="bi bi-file-earmark" style="font-size: 4rem; color: #800000;"></i>
                            <p class="mt-3">Vista previa no disponible para este tipo de archivo</p>
                            <a href="{{ route('documental.document.download', $doc->id) }}" class="btn text-white mt-2" style="background-color: #800000;">
                                <i class="bi bi-download me-1"></i> Descargar para ver
                            </a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('documental.document.download', $doc->id) }}" 
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

{{-- FUNCIÓN JAVASCRIPT PARA ABRIR EL MODAL DE VISUALIZACIÓN --}}
<script>
// RECIBE EL ID DEL DOCUMENTO Y MUESTRA SU MODAL CORRESPONDIENTE
function viewDocumentExternal(id) {
    const modalElement = document.getElementById(`viewDocumentModal${id}`);
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show(); // ABRE EL MODAL
    }
}
</script>