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

                    {{-- Aviso doc de usuario --}}
                    <div id="edit_aviso_usuario" class="alert alert-info d-flex align-items-center mb-3" style="display:none!important;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Los campos de información están bloqueados. Cambia el <strong class="ms-1">Estatus</strong> y, si lo marcas como <strong class="ms-1">Válido</strong>, completa los campos de formato.
                    </div>

                    {{-- Aviso movida a Formatos --}}
                    <div id="edit_aviso_formatos" class="alert alert-success d-flex align-items-center mb-3" style="display:none;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Al guardar como <strong class="ms-1">Válido</strong> con los campos completos, el documento se enviará automáticamente al módulo de <strong class="ms-1">Formatos</strong>.
                    </div>

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
                            <select class="form-select" id="edit_estatus" name="estatus" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Valido">Válido</option>
                                <option value="No Valido">No Válido</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de creación</label>
                            <input type="datetime-local" class="form-control" id="edit_fecha" name="fecha"
                                   readonly style="background-color: #e9ecef; cursor: not-allowed;">
                            <small class="text-muted">La fecha se asigna automáticamente al crear el documento</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3"></textarea>
                            <small class="text-muted">Las observaciones se borrarán automáticamente cuando el estatus sea "Válido"</small>
                        </div>
                    </div>

                    {{-- ── SECCIÓN CAMPOS DE FORMATO (solo visible cuando estatus = Válido y doc de usuario) ── --}}
                    <div id="edit_seccion_formato" style="display:none;">
                        <hr>
                        <p class="fw-bold mb-3" style="color: #800000;">
                            <i class="bi bi-file-earmark-check me-1"></i>
                            Información del formato <span class="text-danger">*</span>
                            <small class="text-muted fw-normal ms-1" style="font-size:0.8rem;">— Requerido para enviar al módulo de Formatos</small>
                        </p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Clave del formato <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_clave_formato"
                                       name="clave_formato" placeholder="Ej: FO-SGC-001">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo_procedimiento"
                                       name="codigo_procedimiento" placeholder="Ej: PR-001">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Versión del formato <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_version_procedimiento"
                                       name="version_procedimiento" placeholder="Ej: V1">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" id="edit_btn_guardar" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const estatusSelect      = document.getElementById('edit_estatus');
    const observacionesField = document.getElementById('edit_observaciones');
    const seccionFormato     = document.getElementById('edit_seccion_formato');
    const avisoFormatos      = document.getElementById('edit_aviso_formatos');
    const btnGuardar         = document.getElementById('edit_btn_guardar');

    // ── Controla observaciones según estatus ──
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

    // ── Controla sección de campos de formato según estatus y si es doc de usuario ──
    function toggleSeccionFormato() {
        const esValido     = estatusSelect.value === 'Valido';
        const esDeUsuario  = seccionFormato.dataset.modoUsuario === '1';

        if (esValido && esDeUsuario) {
            seccionFormato.style.display = '';
            avisoFormatos.style.display  = 'flex';
            btnGuardar.innerHTML = '<i class="bi bi-send me-1"></i> Guardar y enviar a Lista maestra';
        } else {
            seccionFormato.style.display = 'none';
            avisoFormatos.style.display  = 'none';
            btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar cambios';
        }

        toggleObservaciones();
    }

    if (estatusSelect) {
        estatusSelect.addEventListener('change', toggleSeccionFormato);
        toggleSeccionFormato();
    }
});

// ── Campos bloqueados cuando doc es de usuario (excepto estatus) ──
const camposInfoUsuario = [
    'edit_document_name',
    'edit_responsable',
    'edit_proceso',
    'edit_departamento',
];

function setModoUsuario(esDeUsuario) {
    const aviso          = document.getElementById('edit_aviso_usuario');
    const seccionFormato = document.getElementById('edit_seccion_formato');

    // Marcar si es doc de usuario para que toggleSeccionFormato lo sepa
    seccionFormato.dataset.modoUsuario = esDeUsuario ? '1' : '0';

    camposInfoUsuario.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (esDeUsuario) {
            el.setAttribute('readonly', true);
            el.style.backgroundColor = '#e9ecef';
            el.style.cursor = 'not-allowed';
        } else {
            el.removeAttribute('readonly');
            el.style.backgroundColor = '';
            el.style.cursor = '';
        }
    });

    if (aviso) aviso.style.display = esDeUsuario ? 'flex' : 'none';

    // Relanzar toggle para aplicar sección formato correctamente
    const estatusSelect = document.getElementById('edit_estatus');
    if (estatusSelect) estatusSelect.dispatchEvent(new Event('change'));
}
</script>