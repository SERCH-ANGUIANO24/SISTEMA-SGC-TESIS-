<div class="modal fade" id="editAdminDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="editAdminDocumentForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                        Renombrar Documento
                    </h5>

                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="edit_admin_name" class="form-label fw-bold">Nuevo nombre</label>
                        <input type="text" class="form-control" id="edit_admin_name" name="name"
                               required autofocus>
                        <small class="text-muted">La extensión del archivo se mantendrá automáticamente.</small>
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

<script>
function editAdminDocument(id) {
    fetch(`/documental/document/${id}/data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_admin_name').value = data.name || '';
            document.getElementById('editAdminDocumentForm').action = `/documental/document/${id}`;
            new bootstrap.Modal(document.getElementById('editAdminDocumentModal')).show();
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editAdminDocumentModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('edit_admin_name').value = '';
        });
    }
});
</script>