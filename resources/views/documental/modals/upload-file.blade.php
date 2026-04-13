<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        {{-- FORMULARIO PARA SUBIR ARCHIVO - ENVÍA DATOS AL CONTROLADOR --}}
        <form action="{{ route('documental.upload') }}" method="POST" enctype="multipart/form-data" id="uploadFileForm">
            @csrf {{-- TOKEN DE SEGURIDAD - PROTEGE CONTRA ATAQUES CSRF --}}
            {{-- CAMPO OCULTO QUE INDICA EN QUÉ CARPETA SE SUBE EL ARCHIVO --}}
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2" style="color: #000000;"></i>
                        Subir Archivo
                    </h5>
                </div>
                <div class="modal-body">

                    {{-- CAMPO PARA SELECCIONAR EL ARCHIVO - OBLIGATORIO --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar archivo <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="file" required>
                    </div>

                    {{-- SELECTOR DE TIPO DE DOCUMENTO - FORMATO O PROCEDIMIENTO --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de documento <span class="text-danger">*</span></label>
                        <select class="form-select" name="tipo_documento" id="upload_tipo_documento" required>
                            <option value="">— Selecciona el tipo —</option>
                            <option value="Formato">Formato</option>
                            <option value="Procedimiento">Procedimiento</option>
                        </select>
                        <small class="text-muted">Indica si el archivo es un Formato o un Procedimiento.</small>
                    </div>

                    {{-- SECCIONES SOLO VISIBLES PARA SUPERADMIN Y ADMIN --}}
                    @if(in_array(Auth::user()->role, ['superadmin', 'admin']))

                    {{-- SECCIÓN DE CAMPOS PARA FORMATO (CLAVE, CÓDIGO, VERSIÓN) --}}
                    <div id="upload_seccion_formato" style="display:none;">
                        <hr>
                        <p class="fw-bold mb-2" style="color:#000000; font-size:0.9rem;">
                            <i class="bi bi-file-earmark-check me-1"></i>
                            Información del formato
                            <small class="text-muted fw-normal ms-1">(opcional)</small>
                        </p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Clave del formato</label>
                                <input type="text" class="form-control"
                                       id="upload_clave_formato"
                                       name="clave_formato"
                                       placeholder="Ej: FO-SGC-001"
                                       disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento</label>
                                <input type="text" class="form-control"
                                       id="upload_codigo_formato"
                                       name="codigo_procedimiento"
                                       placeholder="Ej: PR-001"
                                       disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Versión del formato</label>
                                <input type="text" class="form-control"
                                       id="upload_version_formato"
                                       name="version_procedimiento"
                                       placeholder="Ej: V1"
                                       disabled>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN DE CAMPOS PARA PROCEDIMIENTO (CÓDIGO Y VERSIÓN) --}}
                    <div id="upload_seccion_procedimiento" style="display:none;">
                        <hr>
                        <p class="fw-bold mb-2" style="color:#000000; font-size:0.9rem;">
                            <i class="bi bi-file-earmark-ruled me-1"></i>
                            Información del procedimiento
                            <small class="text-muted fw-normal ms-1">(opcional)</small>
                        </p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Código de procedimiento</label>
                                <input type="text" class="form-control"
                                       id="upload_codigo_procedimiento"
                                       name="codigo_procedimiento"
                                       placeholder="Ej: PR-001"
                                       disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Versión del procedimiento</label>
                                <input type="text" class="form-control"
                                       id="upload_version_procedimiento"
                                       name="version_procedimiento"
                                       placeholder="Ej: V1"
                                       disabled>
                            </div>
                        </div>
                    </div>

                    {{-- AVISO DE ENVÍO AUTOMÁTICO A LISTA MAESTRA --}}
                    <div class="alert alert-success d-flex align-items-center mt-2" style="font-size:0.85rem;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        El archivo se enviará automáticamente al módulo de <strong class="ms-1">Lista Maestra</strong>.
                    </div>

                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white" style="background-color: #800000;"
                            onclick="submitUploadForm()">
                        <i class="bi bi-upload me-1"></i> Subir Archivo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- JAVASCRIPT PARA MANEJAR LA LÓGICA DE SUBIDA DE ARCHIVOS --}}
<script>
{{-- CUANDO LA PÁGINA TERMINA DE CARGARSE --}}
document.addEventListener('DOMContentLoaded', function () {

    const tipoSelect = document.getElementById('upload_tipo_documento');

    if (tipoSelect) {
        {{-- CUANDO EL USUARIO CAMBIA EL TIPO DE DOCUMENTO --}}
        tipoSelect.addEventListener('change', function () {
            const tipo = this.value;
            const secFormato       = document.getElementById('upload_seccion_formato');
            const secProcedimiento = document.getElementById('upload_seccion_procedimiento');

            {{-- OCULTAR AMBAS SECCIONES Y DESHABILITAR TODOS SUS CAMPOS --}}
            if (secFormato)       secFormato.style.display       = 'none';
            if (secProcedimiento) secProcedimiento.style.display = 'none';

            ['upload_clave_formato','upload_codigo_formato','upload_version_formato',
             'upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) { el.disabled = true; el.value = ''; }
            });

            {{-- MOSTRAR Y HABILITAR LA SECCIÓN CORRECTA SEGÚN EL TIPO SELECCIONADO --}}
            if (tipo === 'Formato') {
                if (secFormato) secFormato.style.display = '';
                ['upload_clave_formato','upload_codigo_formato','upload_version_formato'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) el.disabled = false;
                });
            } else if (tipo === 'Procedimiento') {
                if (secProcedimiento) secProcedimiento.style.display = '';
                ['upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) el.disabled = false;
                });
            }
        });
    }

    {{-- RESETEA EL FORMULARIO CUANDO EL MODAL SE CIERRA --}}
    const modal = document.getElementById('uploadFileModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            {{-- RESETEA EL SELECTOR DE TIPO --}}
            if (tipoSelect) tipoSelect.value = '';

            {{-- OCULTA LAS SECCIONES ADICIONALES --}}
            const sf = document.getElementById('upload_seccion_formato');
            const sp = document.getElementById('upload_seccion_procedimiento');
            if (sf) sf.style.display = 'none';
            if (sp) sp.style.display = 'none';

            {{-- LIMPIA Y DESHABILITA TODOS LOS CAMPOS ADICIONALES --}}
            ['upload_clave_formato','upload_codigo_formato','upload_version_formato',
             'upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) { el.disabled = true; el.value = ''; }
            });
        });
    }
});

{{-- FUNCIÓN QUE VALIDA EL FORMULARIO ANTES DE ENVIAR --}}
function submitUploadForm() {
    const tipoEl = document.getElementById('upload_tipo_documento');
    const tipo   = tipoEl ? tipoEl.value : '';

    {{-- LIMPIAR ERRORES PREVIOS --}}
    ['upload_tipo_documento','upload_clave_formato','upload_version_formato',
     'upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    });
    ['tipo_documento_error','clave_formato_error','version_formato_error',
     'codigo_procedimiento_error','version_procedimiento_error','archivo_error'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    });

    let hayError = false;

    {{-- VALIDAR QUE EL ARCHIVO SEA OBLIGATORIO --}}
    const archivoInput = document.querySelector('#uploadFileForm input[type="file"]');
    if (archivoInput && !archivoInput.files.length) {
        archivoInput.classList.add('is-invalid');
        const msg = document.createElement('div');
        msg.id = 'archivo_error';
        msg.className = 'invalid-feedback';
        msg.textContent = 'Por favor selecciona un archivo.';
        archivoInput.parentNode.appendChild(msg);
        hayError = true;
    }

    {{-- VALIDAR QUE EL TIPO DE DOCUMENTO SEA OBLIGATORIO --}}
    if (!tipo) {
        tipoEl.classList.add('is-invalid');
        const msg = document.createElement('div');
        msg.id = 'tipo_documento_error';
        msg.className = 'invalid-feedback';
        msg.textContent = 'Por favor selecciona el tipo de documento.';
        tipoEl.parentNode.appendChild(msg);
        hayError = true;
    }

    {{-- SI ES FORMATO: CLAVE Y VERSIÓN SON OBLIGATORIOS --}}
    if (tipo === 'Formato') {
        const claveEl   = document.getElementById('upload_clave_formato');
        const versionEl = document.getElementById('upload_version_formato');

        if (claveEl && !claveEl.value.trim()) {
            claveEl.classList.add('is-invalid');
            const msg = document.createElement('div');
            msg.id = 'clave_formato_error';
            msg.className = 'invalid-feedback';
            msg.textContent = 'La clave del formato es obligatoria.';
            claveEl.parentNode.appendChild(msg);
            hayError = true;
        }
        if (versionEl && !versionEl.value.trim()) {
            versionEl.classList.add('is-invalid');
            const msg = document.createElement('div');
            msg.id = 'version_formato_error';
            msg.className = 'invalid-feedback';
            msg.textContent = 'La versión del formato es obligatoria.';
            versionEl.parentNode.appendChild(msg);
            hayError = true;
        }
    }

    {{-- SI ES PROCEDIMIENTO: CÓDIGO Y VERSIÓN SON OBLIGATORIOS --}}
    if (tipo === 'Procedimiento') {
        const codigoEl  = document.getElementById('upload_codigo_procedimiento');
        const versionEl = document.getElementById('upload_version_procedimiento');

        if (codigoEl && !codigoEl.value.trim()) {
            codigoEl.classList.add('is-invalid');
            const msg = document.createElement('div');
            msg.id = 'codigo_procedimiento_error';
            msg.className = 'invalid-feedback';
            msg.textContent = 'El código de procedimiento es obligatorio.';
            codigoEl.parentNode.appendChild(msg);
            hayError = true;
        }
        if (versionEl && !versionEl.value.trim()) {
            versionEl.classList.add('is-invalid');
            const msg = document.createElement('div');
            msg.id = 'version_procedimiento_error';
            msg.className = 'invalid-feedback';
            msg.textContent = 'La versión del procedimiento es obligatoria.';
            versionEl.parentNode.appendChild(msg);
            hayError = true;
        }
    }

    {{-- SI HAY ERRORES, DETENER EL ENVÍO --}}
    if (hayError) return;

    {{-- SOLUCIÓN AL PROBLEMA DE NOMBRES DUPLICADOS --}}
    // DESHABILITAR LA SECCIÓN QUE NO CORRESPONDE Y HABILITAR LA CORRECTA
    if (tipo === 'Formato') {
        {{-- DESHABILITAR SECCIÓN PROCEDIMIENTO --}}
        ['upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = true;
        });
        {{-- HABILITAR SECCIÓN FORMATO --}}
        ['upload_clave_formato','upload_codigo_formato','upload_version_formato'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = false;
        });
    } else if (tipo === 'Procedimiento') {
        {{-- DESHABILITAR SECCIÓN FORMATO --}}
        ['upload_clave_formato','upload_codigo_formato','upload_version_formato'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = true;
        });
        {{-- HABILITAR SECCIÓN PROCEDIMIENTO --}}
        ['upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = false;
        });
    }

    {{-- ENVÍA EL FORMULARIO --}}
    document.getElementById('uploadFileForm').submit();
}

</script>