<div class="modal fade" id="moveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- FORMULARIO DINÁMICO - LA ACCIÓN SE COMPLETA CON JAVASCRIPT SEGÚN EL DOCUMENTO A MOVER --}}
        <form action="" method="POST" id="moveDocumentForm">
            @csrf {{-- TOKEN DE SEGURIDAD - PROTEGE CONTRA ATAQUES CSRF --}}
            @method('PUT') {{-- SIMULA EL MÉTODO HTTP PUT PARA ACTUALIZAR LA UBICACIÓN --}}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #000000;"></i>
                        Mover Documento
                    </h5>
                </div>
                <div class="modal-body">
                    {{-- MUESTRA EL NOMBRE DEL DOCUMENTO QUE SE VA A MOVER --}}
                    <p class="mb-3">
                        <span class="fw-bold">Documento a mover:</span><br>
                        <span id="moveDocumentName" style="color: #737373; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="documentDestination" class="form-label fw-bold">Seleccionar destino</label>
                        {{-- SELECTOR DE CARPETA DESTINO - SE LLENA DINÁMICAMENTE CON JAVASCRIPT --}}
                        <select class="form-select" id="documentDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                            {{-- LAS OPCIONES DE CARPETAS SE CARGAN VIA JAVASCRIPT --}}
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