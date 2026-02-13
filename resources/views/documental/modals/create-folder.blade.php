
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documental.folder.store') }}" method="POST">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-folder-plus me-2" style="color: #800000;"></i>
                        Agregar Carpeta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de Carpeta</label>
                        <input type="text" class="form-control" name="name" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Color Visual</label>
                        <input type="color" class="form-control form-control-color" name="color" value="#800000" style="width: 100%; height: 40px;">
                        <small class="text-muted">Selecciona un color para la carpeta</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Crear Carpeta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>