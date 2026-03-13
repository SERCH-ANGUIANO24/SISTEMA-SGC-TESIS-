<!-- MODAL PARA VER ARCHIVOS -->
<div class="modal fade" id="modalVerArchivo" tabindex="-1" aria-labelledby="modalVerArchivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerArchivoLabel">
                    <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                    Visualizador de Archivo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="visorArchivo" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="btnDescargarArchivo" class="btn text-white" style="background-color: #800000;">
                    <i class="bi bi-download me-1"></i> Descargar
                </a>
            </div>
        </div>
    </div>
</div>