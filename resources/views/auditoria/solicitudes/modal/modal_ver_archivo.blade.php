{{-- MODAL PARA VISUALIZAR EL ARCHIVO ADJUNTO DE UNA SOLICITUD   --}}
{{-- DIRECTAMENTE EN EL NAVEGADOR SIN NECESIDAD DE DESCARGARLO.  --}}
{{-- TAMBIÉN TIENE UN BOTÓN PARA DESCARGAR EL ARCHIVO.           --}}
{{-- ============================================================ --}}

<!-- MODAL PARA VER ARCHIVOS -->
<div class="modal fade" id="modalVerArchivo" tabindex="-1" aria-labelledby="modalVerArchivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            {{-- ENCABEZADO DEL MODAL CON TÍTULO E ÍCONO --}}
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerArchivoLabel">
                    <i class="bi bi-file-earmark-text me-2" style="color: #800000;"></i>
                    Visualizador de Archivo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- ================================================ --}}
            {{-- CUERPO DEL MODAL: VISOR DE ARCHIVO               --}}
            {{-- UTILIZA UN IFRAME QUE OCUPA EL 100% DEL ANCHO    --}}
            {{-- Y EL 80% DE LA ALTURA DE LA PANTALLA.            --}}
            {{-- LA URL DEL ARCHIVO SE ASIGNA DINÁMICAMENTE       --}}
            {{-- DESDE JAVASCRIPT AL ABRIR EL MODAL.              --}}
            {{-- ================================================ --}}
            <div class="modal-body p-0">
                <iframe id="visorArchivo" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
            </div>

            {{-- ================================================ --}}
            {{-- PIE DEL MODAL: BOTONES DE ACCIÓN                 --}}
            {{--   · CERRAR   → CIERRA EL MODAL                  --}}
            {{--   · DESCARGAR → DESCARGA EL ARCHIVO AL DISPOSITIVO --}}
            {{-- EL ENLACE DE DESCARGA SE ASIGNA DINÁMICAMENTE    --}}
            {{-- DESDE JAVASCRIPT AL ABRIR EL MODAL.              --}}
            {{-- ================================================ --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="btnDescargarArchivo" class="btn text-white" style="background-color: #800000;">
                    <i class="bi bi-download me-1"></i> Descargar
                </a>
            </div>

        </div>
    </div>
</div>