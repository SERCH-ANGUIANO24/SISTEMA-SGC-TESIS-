<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="POST" id="editDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2" style="color: #000000;"></i>
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
                        <p class="fw-bold mb-3" style="color: #000000;">
                            <i class="bi bi-file-earmark-check me-1"></i>
                            Información del formato <span class="text-danger">*</span>
                            <small class="text-muted fw-normal ms-1" style="font-size:0.8rem;">— Requerido para enviar al módulo de Formatos</small>
                        </p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Clave del formato <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_clave_formato"
                                       name="clave_formato" placeholder="Ej: FO-SGC-001">
                                <div class="invalid-feedback" id="error_clave_formato">La clave del formato es obligatoria</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo_procedimiento"
                                       name="codigo_procedimiento" placeholder="Ej: PR-001">
                                <div class="invalid-feedback" id="error_codigo_procedimiento">El código de procedimiento es obligatorio</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Versión del formato <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_version_procedimiento"
                                       name="version_procedimiento" placeholder="Ej: V1">
                                <div class="invalid-feedback" id="error_version_procedimiento">La versión del formato es obligatoria</div>
                            </div>
                        </div>
                    </div>

                    {{-- ── SECCIÓN CAMPOS DE PROCEDIMIENTO ── --}}
                    {{-- IMPORTANTE: estos inputs NO tienen name, se asignan en el submit via JS --}}
                    <div id="edit_seccion_procedimiento" style="display:none;">
                        <hr>
                        <p class="fw-bold mb-3" style="color: #000000;">
                            <i class="bi bi-file-earmark-ruled me-1"></i>
                            Información del procedimiento <span class="text-danger">*</span>
                            <small class="text-muted fw-normal ms-1" style="font-size:0.8rem;">— Requerido para enviar al módulo de Lista Maestra</small>
                        </p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo_proc"
                                       placeholder="Ej: PR-001">
                                <div class="invalid-feedback" id="error_codigo_proc">El código de procedimiento es obligatorio</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Versión del procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_version_proc"
                                       placeholder="Ej: V1">
                                <div class="invalid-feedback" id="error_version_proc">La versión del procedimiento es obligatoria</div>
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

    // Función para limpiar errores de validación
    function limpiarErroresValidacion() {
        // Limpiar errores de sección formato
        ['edit_clave_formato', 'edit_codigo_procedimiento', 'edit_version_procedimiento'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('is-invalid');
            }
        });
        
        // Limpiar errores de sección procedimiento
        ['edit_codigo_proc', 'edit_version_proc'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('is-invalid');
            }
        });
    }

    // Función para validar campos obligatorios
    function validarCamposObligatorios() {
        let esValido = true;
        const tipoDocumento = seccionFormato.dataset.tipoDocumento || '';
        const esValidoStatus = estatusSelect.value === 'Valido';
        const esDeUsuario = seccionFormato.dataset.modoUsuario === '1';
        
        // Limpiar errores previos
        limpiarErroresValidacion();
        
        // Solo validar si es válido y es documento de usuario
        if (esValidoStatus && esDeUsuario) {
            if (tipoDocumento === 'Procedimiento') {
                // Validar campos de procedimiento
                const codigoProc = document.getElementById('edit_codigo_proc');
                const versionProc = document.getElementById('edit_version_proc');
                
                if (!codigoProc.value.trim()) {
                    codigoProc.classList.add('is-invalid');
                    esValido = false;
                }
                
                if (!versionProc.value.trim()) {
                    versionProc.classList.add('is-invalid');
                    esValido = false;
                }
            } else {
                // Validar campos de formato
                const claveFormato = document.getElementById('edit_clave_formato');
                const codigoProcedimiento = document.getElementById('edit_codigo_procedimiento');
                const versionProcedimiento = document.getElementById('edit_version_procedimiento');
                
                if (!claveFormato.value.trim()) {
                    claveFormato.classList.add('is-invalid');
                    esValido = false;
                }
                
                if (!codigoProcedimiento.value.trim()) {
                    codigoProcedimiento.classList.add('is-invalid');
                    esValido = false;
                }
                
                if (!versionProcedimiento.value.trim()) {
                    versionProcedimiento.classList.add('is-invalid');
                    esValido = false;
                }
            }
        }
        
        return esValido;
    }

    // ── Al hacer submit: validar y luego mapear los valores al hidden correcto ──
    const form = document.getElementById('editDocumentForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const tipoDocumento = seccionFormato.dataset.tipoDocumento || '';
            const esValido      = estatusSelect.value === 'Valido';
            const esDeUsuario   = seccionFormato.dataset.modoUsuario === '1';
            
            // Validar campos obligatorios
            if (!validarCamposObligatorios()) {
                e.preventDefault();
                // Solo mostrar visualmente los campos en rojo, sin popup
                return false;
            }

            // Limpiar hiddens primero
            document.getElementById('hidden_clave_formato').value         = '';
            document.getElementById('hidden_codigo_procedimiento').value  = '';
            document.getElementById('hidden_version_procedimiento').value = '';

            if (esValido && esDeUsuario) {
                if (tipoDocumento === 'Procedimiento') {
                    // Solo código y versión, sin clave
                    document.getElementById('hidden_codigo_procedimiento').value  = document.getElementById('edit_codigo_proc').value;
                    document.getElementById('hidden_version_procedimiento').value = document.getElementById('edit_version_proc').value;
                } else {
                    // Formato: clave + código + versión
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