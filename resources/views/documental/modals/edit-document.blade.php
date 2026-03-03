<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST" id="editDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                        Editar Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del documento</label>
                            <input type="text" class="form-control" id="edit_document_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Responsable</label>
                            <input type="text" class="form-control" id="edit_responsable" name="responsable">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Proceso</label>
                            <input type="text" class="form-control" id="edit_proceso" name="proceso">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Departamento</label>
                            <input type="text" class="form-control" id="edit_departamento" name="departamento">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Estatus</label>
                            <select class="form-select" id="edit_estatus" name="estatus">
                                <option value="Pendiente">Pendiente</option>
                                <option value="Valido">Válido</option>
                                <option value="No Valido">No Válido</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="datetime-local" class="form-control" id="edit_fecha" name="fecha">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3"></textarea>
                            <small class="text-muted">Las observaciones se borrarán automáticamente cuando el estatus sea "Válido"</small>
                        </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const estatusSelect = document.getElementById('edit_estatus');
    const observacionesField = document.getElementById('edit_observaciones');
    
    if (estatusSelect && observacionesField) {
        function toggleObservaciones() {
            if (estatusSelect.value === 'No Valido') {
                observacionesField.removeAttribute('readonly');
                observacionesField.style.backgroundColor = '#fff';
                observacionesField.placeholder = 'Escribe las observaciones aquí...';
            } else {
                observacionesField.setAttribute('readonly', true);
                observacionesField.style.backgroundColor = '#e9ecef';
                observacionesField.value = '';
                observacionesField.placeholder = 'Las observaciones se borran cuando el documento es válido';
            }
        }
        
        estatusSelect.addEventListener('change', toggleObservaciones);
        toggleObservaciones();
    }
});
</script>