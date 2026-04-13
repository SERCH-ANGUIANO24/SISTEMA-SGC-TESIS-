{{-- ============================================================ --}}
{{-- ARCHIVO: MODAL_ACCIONES.BLADE.PHP                           --}}
{{-- MÓDULO: SOLICITUDES DE MEJORA                               --}}
{{-- CONTIENE 3 MODALES (VENTANAS EMERGENTES) PARA GESTIONAR     --}}
{{-- LOS DOCUMENTOS DENTRO DEL MÓDULO DE SOLICITUDES DE MEJORA.  --}}
{{-- ============================================================ --}}


{{-- ============================================================ --}}
{{-- MODAL 1: RENOMBRAR DOCUMENTO                                 --}}
{{-- MUESTRA UN CAMPO DE TEXTO PARA ESCRIBIR EL NUEVO NOMBRE     --}}
{{-- DE UN DOCUMENTO EN EL MÓDULO DE SOLICITUDES DE MEJORA.      --}}
{{-- LA EXTENSIÓN DEL ARCHIVO SE CONSERVA AUTOMÁTICAMENTE.       --}}
{{-- ============================================================ --}}
<div class="modal fade" id="renameDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="renameDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2" style="color: #800000;"></i>
                        Renombrar Documento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newDocumentName" class="form-label fw-bold">Nuevo nombre</label>
                        <input type="text" class="form-control" id="newDocumentName" name="name" required autofocus>
                        <div class="form-text">La extensión del archivo se mantendrá automáticamente.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">
                        <i class="bi bi-check-circle me-1"></i> Renombrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


{{-- ============================================================ --}}
{{-- MODAL 2: MOVER DOCUMENTO                                     --}}
{{-- MUESTRA EL NOMBRE DEL DOCUMENTO Y UN SELECTOR DE CARPETA    --}}
{{-- PARA ELEGIR A DÓNDE MOVER EL DOCUMENTO DENTRO DEL MÓDULO   --}}
{{-- DE SOLICITUDES DE MEJORA.                                   --}}
{{-- LAS OPCIONES DEL SELECTOR SE CARGAN DINÁMICAMENTE.          --}}
{{-- ============================================================ --}}
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
                            <option value="">📁 Sin categoría</option>
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


{{-- ============================================================ --}}
{{-- MODAL 3: VER CALENDARIO (DETALLE DE FECHAS)                  --}}
{{-- MUESTRA INFORMACIÓN DETALLADA DE LAS FECHAS RELACIONADAS    --}}
{{-- CON UNA SOLICITUD DE MEJORA (FECHAS DE INICIO, CIERRE,      --}}
{{-- SEGUIMIENTO, ETC.). EL CONTENIDO SE CARGA DINÁMICAMENTE.    --}}
{{-- ============================================================ --}}
<div class="modal fade" id="calendarioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-week me-2" style="color: #800000;"></i>
                    Detalle de Fechas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="calendarioContent">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>