<!-- MODAL PARA REGISTRAR/EDITAR AUDITORÍA -->
<div class="modal fade" id="modalNuevaAuditoria" tabindex="-1" aria-labelledby="modalNuevaAuditoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
           <div class="modal-header">
                <h5 class="modal-title" id="modalNuevaAuditoriaLabel">
                    <i class="bi bi-pencil-square me-2" style="color: #800000;"></i>
                    Registrar Nueva Auditoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAuditoria" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="auditoria_id" name="auditoria_id">
                    
                    <!-- DATOS DE LA AUDITORÍA -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #800000;">DATOS DE LA AUDITORÍA</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre de Auditoría *</label>
                            <input type="text" class="form-control" id="nombre_auditoria" name="nombre_auditoria" placeholder="Ej: Auditoría Anual 2026" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Auditoría *</label>
                            <select class="form-control" id="tipo_auditoria" name="tipo_auditoria" required>
                                <option value="Interna">Interna</option>
                                <option value="Externa">Externa</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditor Líder *</label>
                            <input type="text" class="form-control" id="auditor_lider" name="auditor_lider" placeholder="Nombre del auditor líder" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Auditoría *</label>
                            <input type="date" class="form-control" id="fecha_auditoria" name="fecha_auditoria" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Año *</label>
                            <input type="number" class="form-control" id="anio" name="anio" min="2000" max="2100" placeholder="Ej: 2026" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auditores</label>
                            <input type="text" class="form-control" id="auditores" name="auditores" placeholder="Auditor1-Auditor2-Auditor3">
                        </div>
                    </div>
                    
                    <!-- PLAN DE AUDITORÍA (ARCHIVO) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #800000;">PLAN DE AUDITORÍA</h6>
                            <div class="border rounded p-4 bg-light">
                                <div class="text-center mb-3">
                                    <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #800000;"></i>
                                    <p class="mt-2 mb-1"><strong>Arrastra tu archivo aquí o haz clic para seleccionar</strong></p>
                                    <p class="text-muted small">Imágenes, PDF, Word, Excel, CSV y más - Max. 20 MB</p>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <input type="file" class="form-control" id="archivo_plan" name="archivo_plan" style="width: auto;" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.txt" required>
                                </div>
                                <div id="nombreArchivoActual" class="text-center mt-2 text-muted" style="display: none;">
                                    Archivo actual: <span id="nombreArchivo"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000; border: none;">
                    <i class="bi bi-check-circle me-1"></i> Guardar Auditoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>