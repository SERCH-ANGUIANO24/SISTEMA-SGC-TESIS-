<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        {{-- FORMULARIO QUE ENVÍA LOS DATOS AL CONTROLADOR PARA GUARDAR LA CARPETA --}}
        <form action="{{ route('documental.folder.store') }}" method="POST">
            @csrf {{-- TOKEN DE SEGURIDAD - PROTEGE CONTRA ATAQUES CSRF --}}
            {{-- CAMPO OCULTO QUE INDICA DENTRO DE QUÉ CARPETA SE CREARÁ LA NUEVA CARPETA --}}
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-folder-plus me-1" style="color: #000000;"></i>
                        Agregar Carpeta
                    </h5>

                </div>
                <div class="modal-body">
                    {{-- CAMPO PARA EL NOMBRE DE LA CARPETA - OBLIGATORIO --}}
                    <div class="mb-3">
                        <label class="form-label">Nombre de Carpeta</label>
                        <input type="text" class="form-control" name="name" required autofocus>
                    </div>
                    {{-- SELECTOR DE COLOR PARA PERSONALIZAR LA CARPETA - VALOR POR DEFECTO ROJO --}}
                    <div class="mb-3">
                        <label class="form-label">Color Visual</label>
                        <input type="color" class="form-control form-control-color" name="color" value="#800000" style="width: 100%; height: 40px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #800000;">Crear Carpeta</button>
                </div>
            </div>
        </form>
    </div>
</div>