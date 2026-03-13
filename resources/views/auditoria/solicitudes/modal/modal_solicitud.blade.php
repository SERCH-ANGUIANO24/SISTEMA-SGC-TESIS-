<!-- MODAL PARA REGISTRAR/EDITAR SOLICITUD DE MEJORA -->
<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-labelledby="modalNuevaSolicitudLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaSolicitudLabel">
                    <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                    Registrar Nueva Solicitud de Mejora
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSolicitud" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="solicitud_id" name="solicitud_id">
                    
                    <!-- DATOS DE LA SOLICITUD -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #800000;">DATOS DE LA SOLICITUD</h6>
                        </div>
                        
                        <!-- No. de Identificación (antes Folio) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. de Identificación *</label>
                            <input type="text" class="form-control" id="folio_solicitud" name="folio_solicitud" placeholder="Ej: F001">
                            <div class="invalid-feedback" id="error-folio_solicitud">El número de identificación es obligatorio.</div>
                        </div>
                        
                        <!-- Fecha de Solicitud -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Solicitud *</label>
                            <input type="date" class="form-control" id="fecha_solicitud" name="fecha_solicitud">
                            <div class="invalid-feedback" id="error-fecha_solicitud">La fecha de solicitud es obligatoria.</div>
                        </div>
                        
                        <!-- Responsable de la Acción -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Responsable de la Acción *</label>
                            <input type="text" class="form-control" id="responsable_accion" name="responsable_accion" placeholder="Nombre del responsable">
                            <div class="invalid-feedback" id="error-responsable_accion">El responsable de la acción es obligatorio.</div>
                        </div>
                        
                        <!-- Periodo de Aplicación (antes Fecha de Aplicación) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo de Aplicación *</label>
                            <input type="month" class="form-control" id="fecha_aplicacion" name="fecha_aplicacion">
                            <div class="invalid-feedback" id="error-fecha_aplicacion">El periodo de aplicación es obligatorio.</div>
                        </div>
                        
                        <!-- Periodo de Verificación (antes Fecha de Verificación) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo de Verificación</label>
                            <input type="month" class="form-control" id="fecha_verificacion" name="fecha_verificacion">
                        </div>
                        
                        <!-- Estatus -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estatus *</label>
                            <select class="form-select" id="estatus" name="estatus">
                                <option value="">Seleccione un estatus</option>
                                <option value="No Atendida">No Atendida</option>
                                <option value="En Proceso">En Proceso</option>
                                <option value="Cerrado">Cerrado</option>
                            </select>
                            <div class="invalid-feedback" id="error-estatus">El estatus es obligatorio.</div>
                        </div>
                        
                        <!-- Actividades de Verificación (AHORA OBLIGATORIO) -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Actividades de Verificación *</label>
                            <textarea class="form-control" id="actividades_verificacion" name="actividades_verificacion" rows="3" placeholder="Describa las actividades de verificación..."></textarea>
                            <div class="invalid-feedback" id="error-actividades_verificacion">Las actividades de verificación son obligatorias.</div>
                        </div>
                    </div>
                    
                    <!-- ARCHIVO ADJUNTO -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #800000;">DOCUMENTO ADJUNTO *</h6>
                            <div class="border rounded p-4 bg-light" id="dropZone">
                                <div class="text-center mb-3">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #800000;"></i>
                                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <input type="file" class="form-control" id="archivo" name="archivo" style="width: auto;" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.txt">
                                </div>
                                <div id="nombreArchivoActual" class="text-center mt-2 text-muted" style="display: none;">
                                    Archivo actual: <span id="nombreArchivo"></span>
                                </div>
                                <div class="invalid-feedback text-center" id="error-archivo" style="display: none;">El archivo adjunto es obligatorio.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn text-white" style="background-color: #800000; border: none;" id="btnGuardar">
                        <i class="bi bi-check-circle me-1"></i> Guardar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSolicitud');
    const dropZone = document.getElementById('dropZone');
    const archivoInput = document.getElementById('archivo');
    const solicitudId = document.getElementById('solicitud_id');
    const btnGuardar = document.getElementById('btnGuardar');
    
    // Quitar el required de HTML5 para evitar el cuadro chiquito
    const camposRequeridos = document.querySelectorAll('#formSolicitud [required]');
    camposRequeridos.forEach(campo => campo.removeAttribute('required'));
    
    // Drag and drop
    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '#e9ecef';
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
            
            const files = e.dataTransfer.files;
            if (archivoInput && files.length > 0) {
                archivoInput.files = files;
                archivoInput.classList.remove('is-invalid');
                document.getElementById('error-archivo').style.display = 'none';
            }
        });
    }
    
    // Validar archivo al cambiar
    if (archivoInput) {
        archivoInput.addEventListener('change', function() {
            if (archivoInput.files && archivoInput.files.length > 0) {
                archivoInput.classList.remove('is-invalid');
                document.getElementById('error-archivo').style.display = 'none';
            }
        });
    }
    
    // Eventos para quitar la clase invalid
    const folioInput = document.getElementById('folio_solicitud');
    folioInput.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-folio_solicitud').style.display = 'none';
    });
    
    const fechaSolInput = document.getElementById('fecha_solicitud');
    fechaSolInput.addEventListener('change', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-fecha_solicitud').style.display = 'none';
    });
    
    const responsableInput = document.getElementById('responsable_accion');
    responsableInput.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-responsable_accion').style.display = 'none';
    });
    
    const fechaApliInput = document.getElementById('fecha_aplicacion');
    fechaApliInput.addEventListener('change', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-fecha_aplicacion').style.display = 'none';
    });
    
    const estatusSelect = document.getElementById('estatus');
    estatusSelect.addEventListener('change', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-estatus').style.display = 'none';
    });
    
    const actividadesInput = document.getElementById('actividades_verificacion');
    actividadesInput.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-actividades_verificacion').style.display = 'none';
    });
    
    // Función para validar formulario (solo al guardar)
    function validarFormulario() {
        let isValid = true;
        
        // Validar folio_solicitud
        const folio = document.getElementById('folio_solicitud');
        if (!folio.value.trim()) {
            folio.classList.add('is-invalid');
            document.getElementById('error-folio_solicitud').style.display = 'block';
            isValid = false;
        }
        
        // Validar fecha_solicitud
        const fechaSol = document.getElementById('fecha_solicitud');
        if (!fechaSol.value) {
            fechaSol.classList.add('is-invalid');
            document.getElementById('error-fecha_solicitud').style.display = 'block';
            isValid = false;
        }
        
        // Validar responsable_accion
        const responsable = document.getElementById('responsable_accion');
        if (!responsable.value.trim()) {
            responsable.classList.add('is-invalid');
            document.getElementById('error-responsable_accion').style.display = 'block';
            isValid = false;
        }
        
        // Validar fecha_aplicacion
        const fechaApli = document.getElementById('fecha_aplicacion');
        if (!fechaApli.value) {
            fechaApli.classList.add('is-invalid');
            document.getElementById('error-fecha_aplicacion').style.display = 'block';
            isValid = false;
        }
        
        // Validar estatus
        const estatus = document.getElementById('estatus');
        if (!estatus.value) {
            estatus.classList.add('is-invalid');
            document.getElementById('error-estatus').style.display = 'block';
            isValid = false;
        }
        
        // Validar actividades_verificacion
        const actividades = document.getElementById('actividades_verificacion');
        if (!actividades.value.trim()) {
            actividades.classList.add('is-invalid');
            document.getElementById('error-actividades_verificacion').style.display = 'block';
            isValid = false;
        }
        
        // Validar archivo (solo en creación)
        if (!solicitudId.value && (!archivoInput.files || archivoInput.files.length === 0)) {
            archivoInput.classList.add('is-invalid');
            document.getElementById('error-archivo').style.display = 'block';
            isValid = false;
        }
        
        return isValid;
    }
    
    // Evento del botón guardar
    btnGuardar.addEventListener('click', function() {
        if (validarFormulario()) {
            guardarSolicitud();
        }
    });
    
    // Resetear validaciones al abrir el modal
    const modal = document.getElementById('modalNuevaSolicitud');
    modal.addEventListener('show.bs.modal', function() {
        const invalidInputs = document.querySelectorAll('#formSolicitud .is-invalid');
        invalidInputs.forEach(input => input.classList.remove('is-invalid'));
        
        const errorMessages = document.querySelectorAll('#formSolicitud .invalid-feedback');
        errorMessages.forEach(msg => msg.style.display = 'none');
        
        if (!solicitudId.value) {
            archivoInput.value = '';
            document.getElementById('nombreArchivoActual').style.display = 'none';
        }
    });
    
    // Función guardarSolicitud se llama desde el scope global (definida en index)
    window.guardarSolicitud = function() {
        const id = document.getElementById('solicitud_id').value;
        const url = id ? 
            `{{ url('auditoria/solicitudes') }}/${id}` : 
            '{{ route('auditoria.solicitudes.store') }}';
        
        const formData = new FormData(document.getElementById('formSolicitud'));
        
        if (id) {
            formData.append('_method', 'PUT');
        }

        const submitBtn = document.getElementById('btnGuardar');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalElement = document.getElementById('modalNuevaSolicitud');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
                
                if (window.cargarSolicitudes) window.cargarSolicitudes();
                
                const container = document.getElementById('mensajeExitoContainer');
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mb-3';
                alertDiv.setAttribute('role', 'alert');
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i> ${data.message || 'Solicitud guardada correctamente'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                container.innerHTML = '';
                container.appendChild(alertDiv);
                
                setTimeout(() => alertDiv.remove(), 5000);
                
                document.getElementById('formSolicitud').reset();
                document.getElementById('solicitud_id').value = '';
                document.getElementById('nombreArchivoActual').style.display = 'none';
                document.getElementById('modalNuevaSolicitudLabel').textContent = 'Registrar Nueva Solicitud de Mejora';
            } else {
                if (data.errors) {
                    for (const campo in data.errors) {
                        const errorDiv = document.getElementById(`error-${campo}`);
                        if (errorDiv) {
                            errorDiv.textContent = data.errors[campo][0];
                            errorDiv.style.display = 'block';
                            const input = document.getElementById(campo);
                            if (input) input.classList.add('is-invalid');
                        }
                    }
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la solicitud. Por favor, intente de nuevo.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    };
});
</script>