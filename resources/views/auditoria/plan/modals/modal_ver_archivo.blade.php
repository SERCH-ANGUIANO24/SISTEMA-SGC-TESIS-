{{-- MODAL PARA VISUALIZAR UN ARCHIVO DIRECTAMENTE EN LA PANTALLA --}}
{{-- EL ARCHIVO SE CARGA DENTRO DE UN IFRAME — EL SRC SE ASIGNA DINÁMICAMENTE CON JAVASCRIPT --}}

<!-- MODAL PARA VER ARCHIVOS -->
<div class="modal fade" id="modalVerArchivo" tabindex="-1" aria-labelledby="modalVerArchivoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                {{-- TÍTULO DEL MODAL — SE PUEDE ACTUALIZAR CON JAVASCRIPT PARA MOSTRAR EL NOMBRE DEL ARCHIVO --}}
                <h5 class="modal-title" id="modalVerArchivoLabel">Visualizador de Archivo</h5>
                {{-- BOTÓN PARA CERRAR EL MODAL --}}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- SIN PADDING PARA QUE EL IFRAME OCUPE TODO EL ESPACIO DISPONIBLE --}}
            <div class="modal-body p-0">
                {{-- IFRAME QUE MUESTRA EL ARCHIVO — OCUPA EL 100% DEL ANCHO Y EL 80% DE LA ALTURA DE PANTALLA --}}
                {{-- EL ATRIBUTO src SE ASIGNA DESDE JAVASCRIPT CUANDO EL USUARIO HACE CLIC EN "VER" --}}
                <iframe id="visorArchivo" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>