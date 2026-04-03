<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('documental.upload') }}" method="POST" enctype="multipart/form-data" id="uploadFileForm">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2" style="color: #000000;"></i>
                        Subir Archivo
                    </h5>
                </div>
                <div class="modal-body">

                    {{-- CAMPO DE ARCHIVO --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar archivo <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="file" required>
                    </div>

                    {{-- TIPO DE DOCUMENTO --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de documento <span class="text-danger">*</span></label>
                        <select class="form-select" name="tipo_documento" id="upload_tipo_documento" required>
                            <option value="">— Selecciona el tipo —</option>
                            <option value="Formato">Formato</option>
                            <option value="Procedimiento">Procedimiento</option>
                        </select>
                        <small class="text-muted">Indica si el archivo es un Formato o un Procedimiento.</small>
                    </div>

                    @if(in_array(Auth::user()->role, ['superadmin', 'admin']))

                    {{-- SECCIÓN FORMATO --}}
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

                    {{-- SECCIÓN PROCEDIMIENTO --}}
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

<script>
document.addEventListener('DOMContentLoaded', function () {

    const tipoSelect = document.getElementById('upload_tipo_documento');

    if (tipoSelect) {
        tipoSelect.addEventListener('change', function () {
            const tipo = this.value;
            const secFormato       = document.getElementById('upload_seccion_formato');
            const secProcedimiento = document.getElementById('upload_seccion_procedimiento');

            // Ocultar ambas secciones y deshabilitar todos sus campos
            if (secFormato)       secFormato.style.display       = 'none';
            if (secProcedimiento) secProcedimiento.style.display = 'none';

            ['upload_clave_formato','upload_codigo_formato','upload_version_formato',
             'upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) { el.disabled = true; el.value = ''; }
            });

            // Mostrar y habilitar la sección correcta
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

    // Reset al cerrar modal
    const modal = document.getElementById('uploadFileModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            if (tipoSelect) tipoSelect.value = '';

            const sf = document.getElementById('upload_seccion_formato');
            const sp = document.getElementById('upload_seccion_procedimiento');
            if (sf) sf.style.display = 'none';
            if (sp) sp.style.display = 'none';

            ['upload_clave_formato','upload_codigo_formato','upload_version_formato',
             'upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) { el.disabled = true; el.value = ''; }
            });
        });
    }
});

function submitUploadForm() {
    const tipoEl = document.getElementById('upload_tipo_documento');
    const tipo   = tipoEl ? tipoEl.value : '';

    // Limpiar errores previos
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

    // Validar archivo obligatorio
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

    // Validar tipo de documento obligatorio
    if (!tipo) {
        tipoEl.classList.add('is-invalid');
        const msg = document.createElement('div');
        msg.id = 'tipo_documento_error';
        msg.className = 'invalid-feedback';
        msg.textContent = 'Por favor selecciona el tipo de documento.';
        tipoEl.parentNode.appendChild(msg);
        hayError = true;
    }

    // Si es Formato: clave y versión obligatorios
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

    // Si es Procedimiento: código y versión obligatorios
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

    if (hayError) return;

    // ── Solución al problema de names duplicados ──
    // En lugar de habilitar todos, copiamos los valores al campo correcto
    // según el tipo seleccionado, usando inputs hidden
    if (tipo === 'Formato') {
        // Deshabilitar sección procedimiento para que no interfiera
        ['upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = true;
        });
        // Habilitar sección formato
        ['upload_clave_formato','upload_codigo_formato','upload_version_formato'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = false;
        });
    } else if (tipo === 'Procedimiento') {
        // Deshabilitar sección formato para que no interfiera
        ['upload_clave_formato','upload_codigo_formato','upload_version_formato'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = true;
        });
        // Habilitar sección procedimiento
        ['upload_codigo_procedimiento','upload_version_procedimiento'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.disabled = false;
        });
    }

    document.getElementById('uploadFileForm').submit();
}

</script>