<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog {{ in_array(Auth::user()->role, ['superadmin', 'admin']) ? 'modal-lg' : '' }}">
        <form action="{{ route('documental.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2" style="color: #800000;"></i>
                        Subir Archivo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- CAMPO DE ARCHIVO - Para todos --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar archivo <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="file"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                    </div>

                    {{-- FORMULARIO EXTRA - Solo superadmin y admin --}}
                    @if(in_array(Auth::user()->role, ['superadmin', 'admin']))
                    <hr>
                    <p class="fw-bold mb-3" style="color: #800000;">
                        <i class="bi bi-file-earmark-text me-1"></i> Información del documento
                    </p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Proceso <span class="text-danger">*</span></label>
                            <select class="form-select" id="upload_proceso" name="proceso" required>
                                <option value="">Seleccione un proceso</option>
                                @foreach($procesosDepartamentos as $proceso => $deptos)
                                    <option value="{{ $proceso }}">{{ $proceso }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Departamento <span class="text-danger">*</span></label>
                            <select class="form-select" id="upload_departamento" name="departamento" required disabled>
                                <option value="">Primero seleccione un proceso</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Clave del formato <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="clave_formato" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Código de procedimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="codigo_procedimiento" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Versión del formato <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="version_procedimiento" required>
                        </div>
                    </div>
                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-upload me-1"></i> Subir Archivo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(in_array(Auth::user()->role, ['superadmin', 'admin']))
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Mapa dinámico generado desde el servidor (incluye estándar + usuarios + custom)
    const departamentosPorProceso = @json($procesosDepartamentos);

    const procesoSelect     = document.getElementById('upload_proceso');
    const departamentoSelect = document.getElementById('upload_departamento');

    if (!procesoSelect || !departamentoSelect) return;

    procesoSelect.addEventListener('change', function () {
        const proceso = this.value;
        departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';

        if (proceso && departamentosPorProceso[proceso]) {
            departamentoSelect.disabled = false;
            departamentosPorProceso[proceso].forEach(dep => {
                const option = document.createElement('option');
                option.value       = dep;
                option.textContent = dep;
                departamentoSelect.appendChild(option);
            });

            // Si solo hay 1 departamento, preseleccionarlo automáticamente
            if (departamentosPorProceso[proceso].length === 1) {
                departamentoSelect.value = departamentosPorProceso[proceso][0];
            }
        } else {
            departamentoSelect.disabled = true;
            departamentoSelect.innerHTML = '<option value="">Primero seleccione un proceso</option>';
        }
    });

    // Al cerrar el modal, resetear el formulario
    const modal = document.getElementById('uploadFileModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            procesoSelect.value = '';
            departamentoSelect.innerHTML = '<option value="">Primero seleccione un proceso</option>';
            departamentoSelect.disabled = true;
        });
    }
});
</script>
@endif