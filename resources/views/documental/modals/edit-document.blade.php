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
                        {{-- Tipo de documento: visible solo si es doc de usuario, inhabilitado --}}
                        <div class="col-md-6 mb-3" id="edit_campo_tipo_documento" style="display:none;">
                            <label class="form-label fw-bold">Tipo de documento</label>
                            <input type="text" class="form-control" id="edit_tipo_documento_display"
                                   readonly style="background-color: #e9ecef; cursor: not-allowed;">
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

                    {{-- ── SECCIÓN CAMPOS DE FORMATO ── --}}
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
                                        placeholder="Ej: FO-SGC-001">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo_procedimiento"
                                     placeholder="Ej: PR-001">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Versión del formato <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_version_procedimiento"
                                     placeholder="Ej: V1">
                            </div>
                        </div>
                    </div>

                    {{-- ── SECCIÓN CAMPOS DE PROCEDIMIENTO ── --}}
                    {{-- IMPORTANTE: estos inputs NO tienen name, se asignan en el submit via JS --}}
                    <div id="edit_seccion_procedimiento" style="display:none;">
                        <hr>
                        <p class="fw-bold mb-3" style="color: #800000;">
                            <i class="bi bi-file-earmark-ruled me-1"></i>
                            Información del procedimiento <span class="text-danger">*</span>
                            <small class="text-muted fw-normal ms-1" style="font-size:0.8rem;">— Requerido para enviar al módulo de Lista Maestra</small>
                        </p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo_proc"
                                       placeholder="Ej: PR-001">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Versión del procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_version_proc"
                                       placeholder="Ej: V1">
                            </div>
                        </div>
                    </div>

                    {{-- Campos hidden que se llenan en el submit según el tipo --}}
                    <input type="hidden" id="hidden_clave_formato"        name="clave_formato">
                    <input type="hidden" id="hidden_codigo_procedimiento" name="codigo_procedimiento">
                    <input type="hidden" id="hidden_version_procedimiento" name="version_procedimiento">

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

    const estatusSelect          = document.getElementById('edit_estatus');
    const observacionesField     = document.getElementById('edit_observaciones');
    const seccionFormato         = document.getElementById('edit_seccion_formato');
    const seccionProcedimiento   = document.getElementById('edit_seccion_procedimiento');
    const avisoFormatos          = document.getElementById('edit_aviso_formatos');
    const btnGuardar             = document.getElementById('edit_btn_guardar');

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

    function toggleSeccionFormato() {
        const esValido      = estatusSelect.value === 'Valido';
        const esDeUsuario   = seccionFormato.dataset.modoUsuario === '1';
        const tipoDocumento = seccionFormato.dataset.tipoDocumento || '';

        seccionFormato.style.display       = 'none';
        seccionProcedimiento.style.display = 'none';
        avisoFormatos.style.display        = 'none';
        btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar cambios';

        if (esValido && esDeUsuario) {
            if (tipoDocumento === 'Procedimiento') {
                seccionProcedimiento.style.display = '';
                btnGuardar.innerHTML = '<i class="bi bi-send me-1"></i> Guardar y enviar a Lista maestra';
            } else {
                seccionFormato.style.display = '';
                avisoFormatos.style.display  = 'flex';
                btnGuardar.innerHTML = '<i class="bi bi-send me-1"></i> Guardar y enviar a Lista maestra';
            }
        }

        toggleObservaciones();
    }

    if (estatusSelect) {
        estatusSelect.addEventListener('change', toggleSeccionFormato);
        toggleSeccionFormato();
    }

    // ── Al hacer submit: mapear los valores al hidden correcto ──
    const form = document.getElementById('editDocumentForm');
    if (form) {
            form.addEventListener('submit', function (e) {
                const tipoDocumento = seccionFormato.dataset.tipoDocumento || '';
                const esValido      = estatusSelect.value === 'Valido';
                const esDeUsuario   = seccionFormato.dataset.modoUsuario === '1';

                // Limpiar errores previos
                ['edit_clave_formato','edit_codigo_procedimiento','edit_version_procedimiento',
                'edit_codigo_proc','edit_version_proc'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) {
                        el.classList.remove('is-invalid');
                        const err = document.getElementById(id + '_error');
                        if (err) err.remove();
                    }
                });

                let hayError = false;

                if (esValido && esDeUsuario) {
                    if (tipoDocumento === 'Procedimiento') {
                        const codigoEl  = document.getElementById('edit_codigo_proc');
                        const versionEl = document.getElementById('edit_version_proc');

                        if (codigoEl && !codigoEl.value.trim()) {
                            codigoEl.classList.add('is-invalid');
                            const msg = document.createElement('div');
                            msg.id = 'edit_codigo_proc_error';
                            msg.className = 'invalid-feedback';
                            msg.textContent = 'El código de procedimiento es obligatorio.';
                            codigoEl.parentNode.appendChild(msg);
                            hayError = true;
                        }
                        if (versionEl && !versionEl.value.trim()) {
                            versionEl.classList.add('is-invalid');
                            const msg = document.createElement('div');
                            msg.id = 'edit_version_proc_error';
                            msg.className = 'invalid-feedback';
                            msg.textContent = 'La versión del procedimiento es obligatoria.';
                            versionEl.parentNode.appendChild(msg);
                            hayError = true;
                        }
                    } else {
                        const claveEl   = document.getElementById('edit_clave_formato');
                        const codigoEl  = document.getElementById('edit_codigo_procedimiento');
                        const versionEl = document.getElementById('edit_version_procedimiento');

                        if (claveEl && !claveEl.value.trim()) {
                            claveEl.classList.add('is-invalid');
                            const msg = document.createElement('div');
                            msg.id = 'edit_clave_formato_error';
                            msg.className = 'invalid-feedback';
                            msg.textContent = 'La clave del formato es obligatoria.';
                            claveEl.parentNode.appendChild(msg);
                            hayError = true;
                        }
                        if (codigoEl && !codigoEl.value.trim()) {
                            codigoEl.classList.add('is-invalid');
                            const msg = document.createElement('div');
                            msg.id = 'edit_codigo_procedimiento_error';
                            msg.className = 'invalid-feedback';
                            msg.textContent = 'El código de procedimiento es obligatorio.';
                            codigoEl.parentNode.appendChild(msg);
                            hayError = true;
                        }
                        if (versionEl && !versionEl.value.trim()) {
                            versionEl.classList.add('is-invalid');
                            const msg = document.createElement('div');
                            msg.id = 'edit_version_procedimiento_error';
                            msg.className = 'invalid-feedback';
                            msg.textContent = 'La versión del formato es obligatoria.';
                            versionEl.parentNode.appendChild(msg);
                            hayError = true;
                        }
                    }
                }

                if (hayError) {
                    e.preventDefault();
                    return;
                }

                // Limpiar hiddens y mapear valores
                document.getElementById('hidden_clave_formato').value         = '';
                document.getElementById('hidden_codigo_procedimiento').value  = '';
                document.getElementById('hidden_version_procedimiento').value = '';

                if (esValido && esDeUsuario) {
                    if (tipoDocumento === 'Procedimiento') {
                        document.getElementById('hidden_codigo_procedimiento').value  = document.getElementById('edit_codigo_proc').value;
                        document.getElementById('hidden_version_procedimiento').value = document.getElementById('edit_version_proc').value;
                    } else {
                        document.getElementById('hidden_clave_formato').value         = document.getElementById('edit_clave_formato').value;
                        document.getElementById('hidden_codigo_procedimiento').value  = document.getElementById('edit_codigo_procedimiento').value;
                        document.getElementById('hidden_version_procedimiento').value = document.getElementById('edit_version_procedimiento').value;
                    }
                }
            });
    }
});

const camposInfoUsuario = [
    'edit_document_name',
    'edit_responsable',
    'edit_proceso',
    'edit_departamento',
];

function setModoUsuario(esDeUsuario, tipoDocumento) {
    const aviso          = document.getElementById('edit_aviso_usuario');
    const seccionFormato = document.getElementById('edit_seccion_formato');
    const campotipo      = document.getElementById('edit_campo_tipo_documento');
    const displayTipo    = document.getElementById('edit_tipo_documento_display');

    seccionFormato.dataset.modoUsuario   = esDeUsuario ? '1' : '0';
    seccionFormato.dataset.tipoDocumento = tipoDocumento || '';

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

    if (campotipo && displayTipo) {
        if (esDeUsuario && tipoDocumento) {
            campotipo.style.display = '';
            displayTipo.value       = tipoDocumento;
        } else {
            campotipo.style.display = 'none';
            displayTipo.value       = '';
        }
    }

    const estatusSelect = document.getElementById('edit_estatus');
    if (estatusSelect) estatusSelect.dispatchEvent(new Event('change'));
}
</script>