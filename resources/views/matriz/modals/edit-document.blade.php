<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="editDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                        Renombrar Matriz
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuevo nombre de la Matriz</label>
                        <input type="text" class="form-control" id="edit_document_name" name="name" required>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Solo puedes cambiar el nombre del archivo. La extensión se mantendrá automáticamente.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Renombrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>