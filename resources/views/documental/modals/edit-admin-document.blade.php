<div class="modal fade" id="editAdminDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- FORMULARIO DINÁMICO - LA ACCIÓN SE COMPLETA CON JAVASCRIPT SEGÚN EL DOCUMENTO A EDITAR --}}
        <form action="" method="POST" id="editAdminDocumentForm" enctype="multipart/form-data">
            @csrf {{-- TOKEN DE SEGURIDAD --}}
            @method('PUT') {{-- SIMULA EL MÉTODO HTTP PUT PARA ACTUALIZAR --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                        Renombrar Documento
                    </h5>

                </div>
                <div class="modal-body">

                    {{-- CAMPO PARA INGRESAR EL NUEVO NOMBRE DEL DOCUMENTO --}}
                    <div class="mb-3">
                        <label for="edit_admin_name" class="form-label fw-bold">Nuevo nombre</label>
                        <input type="text" class="form-control" id="edit_admin_name" name="name"
                               required autofocus>
                        {{-- MENSAJE INFORMATIVO - LA EXTENSIÓN NO CAMBIA --}}
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

{{-- JAVASCRIPT PARA MANEJAR LA EDICIÓN DE DOCUMENTOS --}}
<script>
{{-- FUNCIÓN QUE SE EJECUTA AL HACER CLIC EN EDITAR DOCUMENTO --}}
// RECIBE EL ID DEL DOCUMENTO A RENOMBRAR
function editAdminDocument(id) {
    // OBTIENE LOS DATOS ACTUALES DEL DOCUMENTO DESDE LA API
    fetch(`/documental/document/${id}/data`)
        .then(response => response.json())
        .then(data => {
            // CARGA EL NOMBRE ACTUAL EN EL INPUT DEL MODAL
            document.getElementById('edit_admin_name').value = data.name || '';
            // CONFIGURA LA ACCIÓN DEL FORMULARIO CON EL ID DEL DOCUMENTO
            document.getElementById('editAdminDocumentForm').action = `/documental/document/${id}`;
            // ABRE EL MODAL
            new bootstrap.Modal(document.getElementById('editAdminDocumentModal')).show();
        });
}

{{-- CUANDO LA PÁGINA TERMINA DE CARGARSE --}}
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editAdminDocumentModal');
    if (modal) {
        {{-- CUANDO EL MODAL SE CIERRA, LIMPIA EL CAMPO DE NOMBRE --}}
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('edit_admin_name').value = '';
        });
    }
});
</script>