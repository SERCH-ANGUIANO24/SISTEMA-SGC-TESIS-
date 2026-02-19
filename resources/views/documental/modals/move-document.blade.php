
<div class="modal fade" id="moveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="moveDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #800000;"></i>
                        Mover Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <span class="fw-bold">Documento a mover:</span><br>
                        <span id="moveDocumentName" style="color: #800000; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="documentDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="documentDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                        </select>
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Selecciona la carpeta donde deseas mover el documento.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-arrow-right me-1"></i> Mover aquí
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>