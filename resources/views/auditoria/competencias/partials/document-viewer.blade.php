<div class="container-fluid p-0">
    @php
        $extension = strtolower($extension ?? '');
    @endphp

    @if(in_array($extension, ['csv']))
        <div class="d-flex flex-column justify-content-center align-items-center h-100" style="min-height: 60vh;">
            <i class="bi bi-file-earmark-spreadsheet" style="font-size: 5rem; color: #800000;"></i>
            <h5 class="mt-4 mb-3">Vista previa no disponible</h5>
            <p class="text-muted text-center mb-4">
                Los archivos CSV no se pueden visualizar en el navegador.
            </p>
            <div class="alert alert-info d-flex align-items-center" role="alert" style="max-width: 500px;">
                <i class="bi bi-info-circle-fill me-2"></i>
                <span>Puedes descargar el archivo para ver su contenido.</span>
            </div>
            <a href="{{ route('auditoria.competencias.document.download', $docId ?? '') }}" class="btn text-white mt-3" style="background-color: #800000;">
                <i class="bi bi-download me-1"></i> Descargar CSV
            </a>
        </div>
    @elseif(in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
        <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}"
                style="width: 100%; height: 80vh; border: none;">
        </iframe>
    @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg']))
        <div class="d-flex justify-content-center align-items-center h-100">
            <img src="{{ $fileUrl }}" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
        </div>
    @else
        <iframe src="{{ $fileUrl }}"
                style="width: 100%; height: 80vh; border: none;">
        </iframe>
    @endif
</div>