<div class="modal fade" id="editAdminDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST" id="editAdminDocumentForm" enctype="multipart/form-data">
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
                            <label class="form-label fw-bold">Responsable</label>
                            <input type="text" class="form-control" id="edit_admin_responsable" name="responsable">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Proceso <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_admin_proceso" name="proceso" required>
                                <option value="">Seleccione un proceso</option>
                                @foreach($procesosDepartamentos as $proceso => $deptos)
                                    <option value="{{ $proceso }}">{{ $proceso }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Departamento <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_admin_departamento" name="departamento" required>
                                <option value="">Primero seleccione un proceso</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Clave del formato <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_admin_clave_formato" name="clave_formato" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_admin_codigo_procedimiento" name="codigo_procedimiento" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Versión del procedimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_admin_version_procedimiento" name="version_procedimiento" required>
                        </div>

                        {{-- SECCIÓN ARCHIVO --}}
                        <div class="col-12 mt-2">
                            <hr>
                            <label class="form-label fw-bold">
                                <i class="bi bi-paperclip me-1" style="color: #800000;"></i>
                                Archivo actual
                            </label>
                            <div class="d-flex align-items-center gap-2 p-2 rounded border bg-light mb-3">
                                <i class="bi bi-file-earmark-text fs-4" style="color: #800000;" id="edit_admin_file_icon"></i>
                                <span id="edit_admin_current_file_name" class="text-truncate"></span>
                            </div>
                            <label class="form-label fw-bold">
                                Reemplazar archivo
                                <small class="text-muted fw-normal">(opcional — dejar vacío para mantener el actual)</small>
                            </label>
                            <input class="form-control" type="file" name="new_file"
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                   id="edit_admin_new_file">
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
document.addEventListener('DOMContentLoaded', function () {

    // Mapa dinámico desde el servidor (estándar + usuarios registrados + custom)
    const departamentosPorProceso = @json($procesosDepartamentos);

    const procesoSelect      = document.getElementById('edit_admin_proceso');
    const departamentoSelect = document.getElementById('edit_admin_departamento');

    /**
     * Pobla el select de departamento según el proceso elegido.
     * Si el departamento guardado no está en la lista (p.ej. dato legacy),
     * lo agrega como opción para no perder la información.
     */
    function populateDepartamentos(procesoValue, selectedDep) {
        departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';

        if (!procesoValue) {
            departamentoSelect.disabled = true;
            return;
        }

        const deptos = departamentosPorProceso[procesoValue] || [];
        departamentoSelect.disabled = false;

        // Si el depto guardado no está en la lista, agregarlo primero para no perderlo
        const todosDeptos = [...deptos];
        if (selectedDep && !todosDeptos.includes(selectedDep)) {
            todosDeptos.unshift(selectedDep);
        }

        todosDeptos.forEach(dep => {
            const option = document.createElement('option');
            option.value       = dep;
            option.textContent = dep;
            if (dep === selectedDep) option.selected = true;
            departamentoSelect.appendChild(option);
        });

        // Si solo hay 1 departamento y no viene selectedDep, preseleccionarlo
        if (todosDeptos.length === 1 && !selectedDep) {
            departamentoSelect.value = todosDeptos[0];
        }
    }

    if (procesoSelect) {
        procesoSelect.addEventListener('change', function () {
            populateDepartamentos(this.value, null);
        });
    }

    // Exponer para que editAdminDocument() pueda usarla
    window.populateAdminDepartamentos = populateDepartamentos;

    // Resetear al cerrar
    const modal = document.getElementById('editAdminDocumentModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            procesoSelect.value = '';
            departamentoSelect.innerHTML = '<option value="">Primero seleccione un proceso</option>';
            departamentoSelect.disabled = true;
            document.getElementById('edit_admin_new_file').value = '';
        });
    }
});

function editAdminDocument(id) {
    fetch(`/documental/document/${id}/data`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_admin_responsable').value             = data.responsable             || '';
            document.getElementById('edit_admin_clave_formato').value           = data.clave_formato           || '';
            document.getElementById('edit_admin_codigo_procedimiento').value    = data.codigo_procedimiento    || '';
            document.getElementById('edit_admin_version_procedimiento').value   = data.version_procedimiento   || '';

            // Proceso: seleccionar la opción correcta
            const procesoSelect = document.getElementById('edit_admin_proceso');
            procesoSelect.value = data.proceso || '';

            // Departamento: poblar lista y preseleccionar el guardado
            if (window.populateAdminDepartamentos) {
                window.populateAdminDepartamentos(data.proceso, data.departamento);
            }

            // Archivo actual: nombre e ícono
            document.getElementById('edit_admin_current_file_name').textContent = data.original_name || 'Sin archivo';

            const ext     = (data.extension || '').toLowerCase();
            const iconEl  = document.getElementById('edit_admin_file_icon');
            const iconMap = {
                'pdf':  'bi-file-pdf',
                'doc':  'bi-file-word',  'docx': 'bi-file-word',
                'xls':  'bi-file-excel', 'xlsx': 'bi-file-excel',
                'jpg':  'bi-file-image', 'jpeg': 'bi-file-image',
                'png':  'bi-file-image', 'gif':  'bi-file-image',
            };
            iconEl.className   = 'bi ' + (iconMap[ext] || 'bi-file-earmark-text') + ' fs-4';
            iconEl.style.color = '#800000';

            // Limpiar input de nuevo archivo
            document.getElementById('edit_admin_new_file').value = '';

            document.getElementById('editAdminDocumentForm').action = `/documental/document/${id}`;
            new bootstrap.Modal(document.getElementById('editAdminDocumentModal')).show();
        });
}
</script>