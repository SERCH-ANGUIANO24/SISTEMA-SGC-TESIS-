<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        {{-- FORMULARIO DINÁMICO - LA ACCIÓN SE COMPLETA CON JAVASCRIPT --}}
        <form action="" method="POST" id="editDocumentForm">
            @csrf {{-- TOKEN DE SEGURIDAD --}}
            @method('PUT') {{-- SIMULA EL MÉTODO HTTP PUT PARA ACTUALIZAR --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2" style="color: #000000;"></i>
                        Editar Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- AVISO PARA DOCUMENTOS DE USUARIO - CAMPOS BLOQUEADOS --}}
                    <div id="edit_aviso_usuario" class="alert alert-info d-flex align-items-center mb-3" style="display:none!important;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Los campos de información están bloqueados. Cambia el <strong class="ms-1">Estatus</strong> y, si lo marcas como <strong class="ms-1">Válido</strong>, completa los campos de formato.
                    </div>

                    {{-- AVISO PARA ENVÍO AUTOMÁTICO A FORMATOS --}}
                    <div id="edit_aviso_formatos" class="alert alert-success d-flex align-items-center mb-3" style="display:none;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Al guardar como <strong class="ms-1">Válido</strong> con los campos completos, el documento se enviará automáticamente al módulo de <strong class="ms-1">Formatos</strong>.
                    </div>

                    <div class="row">
                        {{-- NOMBRE DEL DOCUMENTO - OBLIGATORIO --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del documento</label>
                            <input type="text" class="form-control" id="edit_document_name" name="name" required>
                        </div>
                        {{-- RESPONSABLE DEL DOCUMENTO --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Responsable</label>
                            <input type="text" class="form-control" id="edit_responsable" name="responsable">
                        </div>
                        {{-- PROCESO ASOCIADO --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Proceso</label>
                            <input type="text" class="form-control" id="edit_proceso" name="proceso">
                        </div>
                        {{-- DEPARTAMENTO RESPONSABLE --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Departamento</label>
                            <input type="text" class="form-control" id="edit_departamento" name="departamento">
                        </div>
                        {{-- TIPO DE DOCUMENTO - SOLO VISIBLE PARA DOCS DE USUARIO, SOLO LECTURA --}}
                        <div class="col-md-6 mb-3" id="edit_campo_tipo_documento" style="display:none;">
                            <label class="form-label fw-bold">Tipo de documento</label>
                            <input type="text" class="form-control" id="edit_tipo_documento_display"
                                   readonly style="background-color: #e9ecef; cursor: not-allowed;">
                        </div>
                        {{-- ESTATUS DEL DOCUMENTO: PENDIENTE, VÁLIDO, NO VÁLIDO --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Estatus</label>
                            <select class="form-select" id="edit_estatus" name="estatus" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Valido">Válido</option>
                                <option value="No Valido">No Válido</option>
                            </select>
                        </div>
                        {{-- FECHA DE CREACIÓN - SOLO LECTURA, SE ASIGNA AUTOMÁTICAMENTE --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de creación</label>
                            <input type="datetime-local" class="form-control" id="edit_fecha" name="fecha"
                                   readonly style="background-color: #e9ecef; cursor: not-allowed;">
                            <small class="text-muted">La fecha se asigna automáticamente al crear el documento</small>
                        </div>
                        {{-- OBSERVACIONES - SE BORRAN AUTOMÁTICAMENTE CUANDO EL ESTATUS ES VÁLIDO --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3"></textarea>
                            <small class="text-muted">Las observaciones se borrarán automáticamente cuando el estatus sea "Válido"</small>
                        </div>
                    </div>

                    {{-- SECCIÓN CAMPOS DE FORMATO - SE MUESTRA CUANDO ESTATUS ES VÁLIDO Y ES DOCUMENTO DE USUARIO --}}
                    <div id="edit_seccion_formato" style="display:none;">
                        <hr>
                        <p class="fw-bold mb-3" style="color: #000000;">
                            <i class="bi bi-file-earmark-check me-1"></i>
                            Información del formato <span class="text-danger">*</span>
                            <small class="text-muted fw-normal ms-1" style="font-size:0.8rem;">— Requerido para enviar al módulo de Formatos</small>
                        </p>
                        <div class="row">
                            {{-- CLAVE DEL FORMATO - EJEMPLO: FO-SGC-001 --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Clave del formato <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_clave_formato"
                                       name="clave_formato" placeholder="Ej: FO-SGC-001">
                                <div class="invalid-feedback" id="error_clave_formato">La clave del formato es obligatoria</div>
                            </div>
                            {{-- CÓDIGO DE PROCEDIMIENTO --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo_procedimiento"
                                       name="codigo_procedimiento" placeholder="Ej: PR-001">
                                <div class="invalid-feedback" id="error_codigo_procedimiento">El código de procedimiento es obligatorio</div>
                            </div>
                            {{-- VERSIÓN DEL FORMATO --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Versión del formato <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_version_procedimiento"
                                       name="version_procedimiento" placeholder="Ej: V1">
                                <div class="invalid-feedback" id="error_version_procedimiento">La versión del formato es obligatoria</div>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN CAMPOS DE PROCEDIMIENTO - PARA DOCUMENTOS TIPO PROCEDIMIENTO --}}
                    {{-- IMPORTANTE: ESTOS INPUTS NO TIENEN NAME, SE ASIGNAN EN EL SUBMIT VIA JS --}}
                    <div id="edit_seccion_procedimiento" style="display:none;">
                        <hr>
                        <p class="fw-bold mb-3" style="color: #000000;">
                            <i class="bi bi-file-earmark-ruled me-1"></i>
                            Información del procedimiento <span class="text-danger">*</span>
                            <small class="text-muted fw-normal ms-1" style="font-size:0.8rem;">— Requerido para enviar al módulo de Lista Maestra</small>
                        </p>
                        <div class="row">
                            {{-- CÓDIGO DE PROCEDIMIENTO --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo_proc"
                                       placeholder="Ej: PR-001">
                                <div class="invalid-feedback" id="error_codigo_proc">El código de procedimiento es obligatorio</div>
                            </div>
                            {{-- VERSIÓN DEL PROCEDIMIENTO --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Versión del procedimiento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_version_proc"
                                       placeholder="Ej: V1">
                                <div class="invalid-feedback" id="error_version_proc">La versión del procedimiento es obligatoria</div>
                            </div>
                        </div>
                    </div>

                    {{-- CAMPOS OCULTOS QUE SE LLENAN EN EL SUBMIT SEGÚN EL TIPO DE DOCUMENTO --}}
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

{{-- JAVASCRIPT PARA MANEJAR LA LÓGICA DEL MODAL DE EDICIÓN --}}
<script>
{{-- CUANDO LA PÁGINA TERMINA DE CARGARSE --}}
document.addEventListener('DOMContentLoaded', function () {

    {{-- REFERENCIAS A LOS ELEMENTOS DEL MODAL --}}
    const estatusSelect          = document.getElementById('edit_estatus');
    const observacionesField     = document.getElementById('edit_observaciones');
    const seccionFormato         = document.getElementById('edit_seccion_formato');
    const seccionProcedimiento   = document.getElementById('edit_seccion_procedimiento');
    const avisoFormatos          = document.getElementById('edit_aviso_formatos');
    const btnGuardar             = document.getElementById('edit_btn_guardar');

    {{-- FUNCIÓN QUE CONTROLA EL COMPORTAMIENTO DEL CAMPO OBSERVACIONES --}}
    // SI EL ESTATUS ES "NO VÁLIDO", EL CAMPO ES EDITABLE
    // SI EL ESTATUS ES "VÁLIDO", EL CAMPO SE VACÍA Y SE BLOQUEA
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

    {{-- FUNCIÓN QUE MUESTRA/OCULTA LAS SECCIONES DE FORMATO Y PROCEDIMIENTO --}}
    // DEPENDE DEL ESTATUS Y DEL TIPO DE DOCUMENTO
    function toggleSeccionFormato() {
        const esValido      = estatusSelect.value === 'Valido';
        const esDeUsuario   = seccionFormato.dataset.modoUsuario === '1';
        const tipoDocumento = seccionFormato.dataset.tipoDocumento || '';

        {{-- OCULTA TODO INICIALMENTE --}}
        seccionFormato.style.display       = 'none';
        seccionProcedimiento.style.display = 'none';
        avisoFormatos.style.display        = 'none';
        btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar cambios';

        {{-- SI ES VÁLIDO Y ES DOCUMENTO DE USUARIO, MUESTRA LA SECCIÓN CORRESPONDIENTE --}}
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

    {{-- ESCUCHA EL CAMBIO DE ESTATUS Y EJECUTA LA FUNCIÓN --}}
    if (estatusSelect) {
        estatusSelect.addEventListener('change', toggleSeccionFormato);
        toggleSeccionFormato();
    }

    {{-- FUNCIÓN PARA LIMPIAR LOS ERRORES DE VALIDACIÓN --}}
    function limpiarErroresValidacion() {
        {{-- LIMPIA ERRORES DE SECCIÓN FORMATO --}}
        ['edit_clave_formato', 'edit_codigo_procedimiento', 'edit_version_procedimiento'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('is-invalid');
            }
        });
        
        {{-- LIMPIA ERRORES DE SECCIÓN PROCEDIMIENTO --}}
        ['edit_codigo_proc', 'edit_version_proc'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('is-invalid');
            }
        });
    }

    {{-- FUNCIÓN PARA VALIDAR CAMPOS OBLIGATORIOS ANTES DE ENVIAR --}}
    function validarCamposObligatorios() {
        let esValido = true;
        const tipoDocumento = seccionFormato.dataset.tipoDocumento || '';
        const esValidoStatus = estatusSelect.value === 'Valido';
        const esDeUsuario = seccionFormato.dataset.modoUsuario === '1';
        
        {{-- LIMPIAR ERRORES PREVIOS --}}
        limpiarErroresValidacion();
        
        {{-- SOLO VALIDAR SI ES VÁLIDO Y ES DOCUMENTO DE USUARIO --}}
        if (esValidoStatus && esDeUsuario) {
            if (tipoDocumento === 'Procedimiento') {
                {{-- VALIDAR CAMPOS DE PROCEDIMIENTO --}}
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
                {{-- VALIDAR CAMPOS DE FORMATO --}}
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

    {{-- AL ENVIAR EL FORMULARIO: VALIDAR Y MAPEAR LOS VALORES AL HIDDEN CORRECTO --}}
    const form = document.getElementById('editDocumentForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const tipoDocumento = seccionFormato.dataset.tipoDocumento || '';
            const esValido      = estatusSelect.value === 'Valido';
            const esDeUsuario   = seccionFormato.dataset.modoUsuario === '1';
            
            {{-- VALIDAR CAMPOS OBLIGATORIOS --}}
            if (!validarCamposObligatorios()) {
                e.preventDefault();
                return false;
            }

            {{-- LIMPIAR HIDDENS ANTES DE ASIGNAR NUEVOS VALORES --}}
            document.getElementById('hidden_clave_formato').value         = '';
            document.getElementById('hidden_codigo_procedimiento').value  = '';
            document.getElementById('hidden_version_procedimiento').value = '';

            {{-- SI ES VÁLIDO Y ES DOCUMENTO DE USUARIO, ASIGNAR VALORES A HIDDENS --}}
            if (esValido && esDeUsuario) {
                if (tipoDocumento === 'Procedimiento') {
                    {{-- SOLO CÓDIGO Y VERSIÓN, SIN CLAVE --}}
                    document.getElementById('hidden_codigo_procedimiento').value  = document.getElementById('edit_codigo_proc').value;
                    document.getElementById('hidden_version_procedimiento').value = document.getElementById('edit_version_proc').value;
                } else {
                    {{-- FORMATO: CLAVE + CÓDIGO + VERSIÓN --}}
                    document.getElementById('hidden_clave_formato').value         = document.getElementById('edit_clave_formato').value;
                    document.getElementById('hidden_codigo_procedimiento').value  = document.getElementById('edit_codigo_procedimiento').value;
                    document.getElementById('hidden_version_procedimiento').value = document.getElementById('edit_version_procedimiento').value;
                }
            }
        });
    }
});

{{-- LISTA DE CAMPOS DE INFORMACIÓN DE USUARIO QUE SE BLOQUEAN EN MODO USUARIO --}}
const camposInfoUsuario = [
    'edit_document_name',
    'edit_responsable',
    'edit_proceso',
    'edit_departamento',
];

{{-- FUNCIÓN QUE CONFIGURA EL MODO USUARIO (CAMPOS BLOQUEADOS O EDITABLES) --}}
// ESDEUSUARIO: TRUE SI ES DOCUMENTO DE USUARIO, FALSE SI ES DOCUMENTO NORMAL
// TIPODOCUMENTO: 'FORMATO' O 'PROCEDIMIENTO'
function setModoUsuario(esDeUsuario, tipoDocumento) {
    const aviso          = document.getElementById('edit_aviso_usuario');
    const seccionFormato = document.getElementById('edit_seccion_formato');
    const campotipo      = document.getElementById('edit_campo_tipo_documento');
    const displayTipo    = document.getElementById('edit_tipo_documento_display');

    {{-- GUARDA LA CONFIGURACIÓN EN DATASET PARA USARLA EN OTRAS FUNCIONES --}}
    seccionFormato.dataset.modoUsuario   = esDeUsuario ? '1' : '0';
    seccionFormato.dataset.tipoDocumento = tipoDocumento || '';

    {{-- BLOQUEA O DESBLOQUEA LOS CAMPOS DE INFORMACIÓN --}}
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

    {{-- MUESTRA U OCULTA EL AVISO DE DOCUMENTO DE USUARIO --}}
    if (aviso) aviso.style.display = esDeUsuario ? 'flex' : 'none';

    {{-- MUESTRA U OCULTA EL CAMPO DE TIPO DE DOCUMENTO --}}
    if (campotipo && displayTipo) {
        if (esDeUsuario && tipoDocumento) {
            campotipo.style.display = '';
            displayTipo.value       = tipoDocumento;
        } else {
            campotipo.style.display = 'none';
            displayTipo.value       = '';
        }
    }

    {{-- DISPARA EL EVENTO CHANGE DEL ESTATUS PARA ACTUALIZAR LAS SECCIONES --}}
    const estatusSelect = document.getElementById('edit_estatus');
    if (estatusSelect) estatusSelect.dispatchEvent(new Event('change'));
}
</script>