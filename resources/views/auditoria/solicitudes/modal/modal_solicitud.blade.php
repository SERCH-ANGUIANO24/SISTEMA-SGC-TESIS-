<!-- MODAL PARA REGISTRAR/EDITAR SOLICITUD DE MEJORA -->
<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-labelledby="modalNuevaSolicitudLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaSolicitudLabel">
                    <i class="bi bi-file-earmark-plus me-2" style="color: #000000;"></i>
                    Registrar Nueva Solicitud de Mejora
                </h5>
            </div>
            <form id="formSolicitud" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="solicitud_id" name="solicitud_id">

                    <!-- DATOS DE LA SOLICITUD -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #000000;">DATOS DE LA SOLICITUD</h6>
                        </div>

                        <!-- Informe Relacionado -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Informe Relacionado <span class="text-danger">*</span></label>
                            <select class="form-select" id="informe_id" name="informe_id" required>
                                <option value="">-- Seleccionar informe --</option>
                                @foreach($informes as $inf)
                                    <option value="{{ $inf->id }}" data-fecha="{{ $inf->fecha_informe ? $inf->fecha_informe->format('Y-m-d') : '' }}">
                                        {{ $inf->nombre_informe }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error-informe_id">Debes seleccionar un informe relacionado.</div>
                        </div>

                        <!-- Fecha del Informe -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha del Informe <small class="text-muted fw-normal"><i class="bi bi-lock-fill"></i> Se llena automáticamente</small></label>
                            <input type="text" class="form-control" id="fecha_informe_display" readonly
                                   placeholder="Selecciona un informe"
                                   style="background-color:#f8f9fa; cursor:not-allowed; border-color:#ced4da;">
                            <input type="hidden" id="fecha_informe" name="fecha_informe">
                        </div>

                        <!-- No. de Identificación -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. de Identificación</label>
                            <input type="text" class="form-control" id="folio_solicitud" name="folio_solicitud" placeholder="Ej: F001">
                            <div class="invalid-feedback" id="error-folio_solicitud"></div>
                        </div>

                        <!-- Fecha de Solicitud -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Solicitud</label>
                            <input type="date" class="form-control" id="fecha_solicitud" name="fecha_solicitud">
                            <div class="invalid-feedback" id="error-fecha_solicitud"></div>
                        </div>

                        <!-- Responsable de la Acción -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Responsable de la Acción</label>
                            <input type="text" class="form-control" id="responsable_accion" name="responsable_accion" placeholder="Nombre del responsable">
                            <div class="invalid-feedback" id="error-responsable_accion"></div>
                        </div>

                        <!-- Periodo de Aplicación -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo de Aplicación</label>
                            <input type="month" class="form-control" id="fecha_aplicacion" name="fecha_aplicacion">
                            <div class="invalid-feedback" id="error-fecha_aplicacion"></div>
                        </div>

                        <!-- Periodo de Verificación -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periodo de Verificación</label>
                            <input type="month" class="form-control" id="fecha_verificacion" name="fecha_verificacion">
                        </div>

                        <!-- Estatus -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estatus</label>
                            <select class="form-select" id="estatus" name="estatus">
                                <option value="">Seleccione un estatus</option>
                                <option value="No Atendida">No Atendida</option>
                                <option value="En Proceso">En Proceso</option>
                                <option value="Cerrado">Cerrado</option>
                            </select>
                            <div class="invalid-feedback" id="error-estatus"></div>
                        </div>

                        <!-- Procesos Auditados -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Procesos Auditados <span class="text-danger">*</span></label>
                            <select class="form-select" id="procesos_auditados" name="procesos_auditados" required>
                                <option value="">-- Seleccionar proceso --</option>
                                @foreach($todosLosProcesos as $proceso)
                                    <option value="{{ $proceso }}">{{ $proceso }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="error-procesos_auditados">Debes seleccionar un proceso auditado.</div>
                        </div>

                        <!-- Indicadores NC/OM del proceso (se muestra al seleccionar proceso + informe) -->
                        <div class="col-12 mb-3" id="indicadoresNcOm" style="display:none;">
                            <div class="rounded p-3 d-flex gap-3 justify-content-center" style="background:#fff;border:1px solid #bab2b2;">
                                <div class="text-center">
                                    <div style="font-size:0.78rem;color:#dc3545;font-weight:600;">NO CONFORMIDADES</div>
                                    <div id="valorNC" style="font-size:2rem;font-weight:700;color:#dc3545;line-height:1.1;">0</div>
                                    <div style="font-size:0.72rem;color:#737373;">en este proceso</div>
                                </div>
                                <div style="width:1px;background:#e8c0c0;"></div>
                                <div class="text-center">
                                    <div style="font-size:0.78rem;color:#28a745;font-weight:600;">OPORTUNIDADES DE MEJORA</div>
                                    <div id="valorOM" style="font-size:2rem;font-weight:700;color:#28a745;line-height:1.1;">0</div>
                                    <div style="font-size:0.72rem;color:#737373;">en este proceso</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tipo de Solicitud -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Solicitud <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_solicitud" name="tipo_solicitud" required>
                                <option value="">-- Seleccionar tipo --</option>
                                <option value="No Conformidad">No Conformidad</option>
                                <option value="Oportunidad de Mejora">Oportunidad de Mejora</option>
                            </select>
                            <div class="invalid-feedback" id="error-tipo_solicitud">Debes seleccionar un tipo de solicitud.</div>
                        </div>

                        <!-- Actividades de Verificación -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Actividades de Verificación</label>
                            <textarea class="form-control" id="actividades_verificacion" name="actividades_verificacion" rows="3" placeholder="Describa las actividades de verificación..."></textarea>
                            <div class="invalid-feedback" id="error-actividades_verificacion"></div>
                        </div>
                    </div>

                    <!-- ARCHIVO ADJUNTO -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #000000;">DOCUMENTO ADJUNTO</h6>
                            <div class="border rounded p-4 bg-light" id="dropZone">
                                <div class="text-center mb-3">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #000000;"></i>
                                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <input type="file" class="form-control" id="archivo" name="archivo" style="width: auto;" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.txt">
                                </div>
                                <div id="nombreArchivoActual" class="text-center mt-2 text-muted" style="display: none;">
                                    Archivo actual: <span id="nombreArchivo"></span>
                                </div>
                                <div class="invalid-feedback text-center" id="error-archivo" style="display: none;"></div>
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
    const dropZone      = document.getElementById('dropZone');
    const archivoInput  = document.getElementById('archivo');
    const solicitudId   = document.getElementById('solicitud_id');
    const btnGuardar    = document.getElementById('btnGuardar');
    const informeSelect = document.getElementById('informe_id');
    const procesoSelect = document.getElementById('procesos_auditados');
    const modal         = document.getElementById('modalNuevaSolicitud');

    document.querySelectorAll('#formSolicitud [required]').forEach(c => c.removeAttribute('required'));

    // ===== FUNCIÓN PARA CAMBIAR TÍTULO DEL MODAL =====
    function setModalTitle(tipo) {
        const titleElement = document.getElementById('modalNuevaSolicitudLabel');
        if (tipo === 'editar') {
            titleElement.innerHTML = '<i class="bi bi-pencil-square me-2" style="color: #000000;"></i> Editar Solicitud de Mejora';
        } else {
            titleElement.innerHTML = '<i class="bi bi-file-earmark-plus me-2" style="color: #000000;"></i> Registrar Nueva Solicitud de Mejora';
        }
    }

    // ===== FUNCIÓN PARA LIMPIAR EL FORMULARIO =====
    function limpiarFormulario() {
        document.getElementById('formSolicitud').reset();
        solicitudId.value = '';
        document.getElementById('nombreArchivoActual').style.display = 'none';
        document.getElementById('fecha_informe_display').value = '';
        document.getElementById('fecha_informe').value = '';
        document.getElementById('procesos_auditados').value = '';
        document.getElementById('tipo_solicitud').value = '';
        document.getElementById('indicadoresNcOm').style.display = 'none';
        archivoInput.value = '';

        // Limpiar errores
        document.querySelectorAll('#formSolicitud .is-invalid').forEach(i => i.classList.remove('is-invalid'));
        document.querySelectorAll('#formSolicitud .invalid-feedback').forEach(m => m.style.display = 'none');
    }

    // ===== EVENTO AL ABRIR EL MODAL =====
    modal.addEventListener('show.bs.modal', function() {
        if (!solicitudId.value) {
            setModalTitle('nuevo');
            limpiarFormulario();
        } else {
            setModalTitle('editar');
        }
    });

    // ===== EVENTO AL CERRAR EL MODAL =====
    modal.addEventListener('hidden.bs.modal', function() {
        limpiarFormulario();
        setModalTitle('nuevo');
    });

    // ── Función para cargar NC/OM según informe + proceso ──
    function cargarNcOm() {
        const informeId = informeSelect.value;
        const proceso   = procesoSelect.value;

        if (!informeId || !proceso) {
            document.getElementById('indicadoresNcOm').style.display = 'none';
            return;
        }

        fetch(`{{ route('auditoria.solicitudes.ncOmPorProceso') }}?informe_id=${informeId}&proceso=${encodeURIComponent(proceso)}`, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('valorNC').textContent = data.nc ?? 0;
            document.getElementById('valorOM').textContent = data.om ?? 0;
            document.getElementById('indicadoresNcOm').style.display = 'block';
        })
        .catch(() => {
            document.getElementById('indicadoresNcOm').style.display = 'none';
        });
    }

    // Al seleccionar informe → llenar fecha + cargar NC/OM
    informeSelect.addEventListener('change', function() {
        const selected     = this.options[this.selectedIndex];
        const fecha        = selected.dataset.fecha || '';
        const hiddenFecha  = document.getElementById('fecha_informe');
        const displayFecha = document.getElementById('fecha_informe_display');

        if (fecha) {
            const partes = fecha.split('-');
            displayFecha.value = partes[2] + '/' + partes[1] + '/' + partes[0];
            hiddenFecha.value  = fecha;
        } else {
            displayFecha.value = '';
            hiddenFecha.value  = '';
        }

        // Limpiar error de informe al seleccionar
        this.classList.remove('is-invalid');
        document.getElementById('error-informe_id').style.display = 'none';

        cargarNcOm();
    });

    // Al seleccionar proceso → cargar NC/OM + limpiar error
    procesoSelect.addEventListener('change', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-procesos_auditados').style.display = 'none';
        cargarNcOm();
    });

    // Al seleccionar tipo solicitud → limpiar error
    document.getElementById('tipo_solicitud').addEventListener('change', function() {
        this.classList.remove('is-invalid');
        document.getElementById('error-tipo_solicitud').style.display = 'none';
    });

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

    if (archivoInput) {
        archivoInput.addEventListener('change', function() {
            if (archivoInput.files && archivoInput.files.length > 0) {
                archivoInput.classList.remove('is-invalid');
                document.getElementById('error-archivo').style.display = 'none';
            }
        });
    }

    // ===== VALIDACIÓN DEL FORMULARIO =====
    function validarFormulario() {
        let valido = true;

        // Validar Informe Relacionado obligatorio
        const informeId    = document.getElementById('informe_id');
        const errorInforme = document.getElementById('error-informe_id');
        if (!informeId.value) {
            informeId.classList.add('is-invalid');
            errorInforme.style.display = 'block';
            valido = false;
        } else {
            informeId.classList.remove('is-invalid');
            errorInforme.style.display = 'none';
        }

        // Validar Procesos Auditados obligatorio
        const proceso       = document.getElementById('procesos_auditados');
        const errorProceso  = document.getElementById('error-procesos_auditados');
        if (!proceso.value) {
            proceso.classList.add('is-invalid');
            errorProceso.style.display = 'block';
            valido = false;
        } else {
            proceso.classList.remove('is-invalid');
            errorProceso.style.display = 'none';
        }

        // Validar Tipo de Solicitud obligatorio
        const tipoSolicitud = document.getElementById('tipo_solicitud');
        const errorTipo     = document.getElementById('error-tipo_solicitud');
        if (!tipoSolicitud.value) {
            tipoSolicitud.classList.add('is-invalid');
            errorTipo.style.display = 'block';
            valido = false;
        } else {
            tipoSolicitud.classList.remove('is-invalid');
            errorTipo.style.display = 'none';
        }

        return valido;
    }

    btnGuardar.addEventListener('click', function() {
        if (validarFormulario()) guardarSolicitud();
    });

    // Función global para cargar informe al editar
    window.cargarInformeEnModal = function(informeId, fechaInformeRaw) {
        const select = document.getElementById('informe_id');
        select.value = informeId || '';

        const displayFecha = document.getElementById('fecha_informe_display');
        const hiddenFecha  = document.getElementById('fecha_informe');

        if (fechaInformeRaw) {
            const partes = fechaInformeRaw.substring(0, 10).split('-');
            displayFecha.value = partes[2] + '/' + partes[1] + '/' + partes[0];
            hiddenFecha.value  = fechaInformeRaw.substring(0, 10);
        } else {
            const selected = select.options[select.selectedIndex];
            const fecha    = selected ? (selected.dataset.fecha || '') : '';
            if (fecha) {
                const partes = fecha.split('-');
                displayFecha.value = partes[2] + '/' + partes[1] + '/' + partes[0];
                hiddenFecha.value  = fecha;
            } else {
                displayFecha.value = '';
                hiddenFecha.value  = '';
            }
        }

        cargarNcOm();
    };

    window.guardarSolicitud = function() {
        const id  = document.getElementById('solicitud_id').value;
        const url = id ?
            `{{ url('auditoria/solicitudes') }}/${id}` :
            '{{ route('auditoria.solicitudes.store') }}';

        const formData = new FormData(document.getElementById('formSolicitud'));
        if (id) formData.append('_method', 'PUT');

        const submitBtn    = document.getElementById('btnGuardar');
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
                const modalElement  = document.getElementById('modalNuevaSolicitud');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) modalInstance.hide();

                if (window.cargarSolicitudes) window.cargarSolicitudes();

                const container = document.getElementById('mensajeExitoContainer');
                const alertDiv  = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mb-3';
                alertDiv.setAttribute('role', 'alert');
                alertDiv.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i> ${data.message || 'Solicitud guardada correctamente'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                container.innerHTML = '';
                container.appendChild(alertDiv);
                setTimeout(() => alertDiv.remove(), 5000);

                limpiarFormulario();
                setModalTitle('nuevo');

            } else {
                if (data.errors) {
                    for (const campo in data.errors) {
                        const errorDiv = document.getElementById(`error-${campo}`);
                        if (errorDiv) {
                            errorDiv.textContent   = data.errors[campo][0];
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
            submitBtn.disabled  = false;
            submitBtn.innerHTML = originalText;
        });
    };
});

// ===== FUNCIÓN EDITAR SOLICITUD =====
function editarSolicitud(id) {
    const solicitud = solicitudesData.find(s => s.id === id);
    if (solicitud) {
        document.getElementById('solicitud_id').value        = solicitud.id;
        document.getElementById('folio_solicitud').value     = solicitud.folio_solicitud || '';
        document.getElementById('responsable_accion').value  = solicitud.responsable_accion || '';
        document.getElementById('actividades_verificacion').value = solicitud.actividades_verificacion || '';

        // ===== ESTATUS =====
        const estatusValue  = solicitud.estatus ? solicitud.estatus.trim() : '';
        const estatusSelect = document.getElementById('estatus');
        let optionExists = false;
        for (let i = 0; i < estatusSelect.options.length; i++) {
            if (estatusSelect.options[i].value === estatusValue) {
                optionExists = true;
                break;
            }
        }
        estatusSelect.value = (optionExists && estatusValue !== '') ? estatusValue : '';

        // ===== PROCESOS AUDITADOS =====
        const selectProcesos = document.getElementById('procesos_auditados');
        if (selectProcesos) selectProcesos.value = solicitud.procesos_auditados || '';

        // ===== TIPO DE SOLICITUD =====
        const selectTipo = document.getElementById('tipo_solicitud');
        if (selectTipo) selectTipo.value = solicitud.tipo_solicitud || '';

        // ===== FECHAS =====
        if (solicitud.fecha_solicitud) {
            const fecha = new Date(solicitud.fecha_solicitud);
            const año   = fecha.getFullYear();
            const mes   = String(fecha.getMonth() + 1).padStart(2, '0');
            const dia   = String(fecha.getDate()).padStart(2, '0');
            document.getElementById('fecha_solicitud').value = `${año}-${mes}-${dia}`;
        } else {
            document.getElementById('fecha_solicitud').value = '';
        }

        if (solicitud.fecha_aplicacion) {
            const fecha = new Date(solicitud.fecha_aplicacion);
            const año   = fecha.getFullYear();
            const mes   = String(fecha.getMonth() + 1).padStart(2, '0');
            document.getElementById('fecha_aplicacion').value = `${año}-${mes}`;
        } else {
            document.getElementById('fecha_aplicacion').value = '';
        }

        if (solicitud.fecha_verificacion) {
            const fecha = new Date(solicitud.fecha_verificacion);
            const año   = fecha.getFullYear();
            const mes   = String(fecha.getMonth() + 1).padStart(2, '0');
            document.getElementById('fecha_verificacion').value = `${año}-${mes}`;
        } else {
            document.getElementById('fecha_verificacion').value = '';
        }

        // ===== INFORME RELACIONADO Y FECHA DEL INFORME =====
        if (window.cargarInformeEnModal) {
            window.cargarInformeEnModal(
                solicitud.informe_id || '',
                solicitud.fecha_informe || ''
            );
        }

        // ===== ARCHIVO =====
        const nombreArchivoActual = document.getElementById('nombreArchivoActual');
        const nombreArchivo       = document.getElementById('nombreArchivo');
        if (solicitud.archivo_nombre) {
            if (nombreArchivoActual) nombreArchivoActual.style.display = 'block';
            if (nombreArchivo) nombreArchivo.textContent = solicitud.archivo_nombre;
        } else {
            if (nombreArchivoActual) nombreArchivoActual.style.display = 'none';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalNuevaSolicitud'));
        modal.show();
    }
}
</script>