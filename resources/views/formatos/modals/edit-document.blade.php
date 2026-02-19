<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="editDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                        Editar Formato
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Formato</label>
                        <input type="text" class="form-control" id="edit_document_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Departamento</label>
                        <input type="text" class="form-control" id="edit_departamento" name="departamento">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Documento</label>
                        <select class="form-select" id="edit_tipo_documento" name="tipo_documento">
                            <option value="PDF">PDF</option>
                            <option value="Excel">Excel</option>
                            <option value="Word">Word</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha del Documento</label>
                        <input type="date" class="form-control" id="edit_fecha_documento" name="fecha_documento">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Script para cargar datos en el modal de edición
document.addEventListener('DOMContentLoaded', function() {
    // Esta función será llamada desde el botón de editar
    window.editDocument = function(id) {
        fetch(`/formatos/document/${id}/data`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_document_name').value = data.name;
                document.getElementById('edit_departamento').value = data.departamento || '';
                document.getElementById('edit_tipo_documento').value = data.tipo_documento || 'PDF';
                document.getElementById('edit_fecha_documento').value = data.fecha_documento || '';
                
                document.getElementById('editDocumentForm').action = `/formatos/document/${id}`;
                new bootstrap.Modal(document.getElementById('editDocumentModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del formato');
            });
    };
});
</script>