
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documental.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2" style="color: #800000;"></i>
                        Subir Archivo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar archivo</label>
                        <input class="form-control" type="file" name="file" required id="fileInput">
                        <small class="text-muted">Máximo 100MB</small>
                    </div>
                    <div class="progress mb-3" id="uploadProgress" style="display: none;">
                        <div class="progress-bar" role="progressbar" style="width: 0%; background-color: #800000;">0%</div>
                    </div>
                    <div id="uploadMessage" class="alert" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;" id="uploadButton">
                        <i class="bi bi-cloud-upload me-1"></i> Subir Archivo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Script para mostrar progreso de carga (opcional)
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.querySelector('#uploadFileModal form');
    const uploadButton = document.getElementById('uploadButton');
    const progress = document.getElementById('uploadProgress');
    const progressBar = progress.querySelector('.progress-bar');
    const message = document.getElementById('uploadMessage');
    
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            const file = fileInput.files[0];
            if (file && file.size > 100 * 1024 * 1024) { // 100MB
                e.preventDefault();
                message.className = 'alert alert-danger';
                message.textContent = 'El archivo no debe exceder los 100MB';
                message.style.display = 'block';
                return;
            }
            
            // Mostrar progreso (simulado)
            uploadButton.disabled = true;
            progress.style.display = 'block';
            let width = 0;
            const interval = setInterval(function() {
                if (width >= 90) {
                    clearInterval(interval);
                } else {
                    width += 10;
                    progressBar.style.width = width + '%';
                    progressBar.textContent = width + '%';
                }
            }, 300);
        });
    }
});
</script>