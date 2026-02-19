<div class="modal fade" id="moveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" id="moveDocumentForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle me-2" style="color: #800000;"></i>
                        Mover Formato
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <span class="fw-bold">Formato a mover:</span><br>
                        <span id="moveDocumentName" style="color: #800000; font-size: 1.1rem;"></span>
                    </p>
                    <div class="mb-3">
                        <label for="documentDestination" class="form-label fw-bold">Seleccionar destino</label>
                        <select class="form-select" id="documentDestination" name="destination_id">
                            <option value="">📁 Raíz principal</option>
                        </select>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.moveDocument = function(id, name) {
        document.getElementById('moveDocumentName').textContent = name;
        document.getElementById('moveDocumentForm').action = `/formatos/document/${id}/move`;
        
        const select = document.getElementById('documentDestination');
        select.innerHTML = '<option value="">📁 Cargando...</option>';
        select.disabled = true;
        
        const currentFolder = {{ $currentFolder->id ?? 'null' }};
        
        fetch(`/formatos/folders/tree?current_folder=${currentFolder}`)
            .then(response => response.json())
            .then(folders => {
                select.innerHTML = '<option value="">📁 Raíz principal</option>';
                select.disabled = false;
                folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    option.textContent = '📁 ' + folder.full_path;
                    select.appendChild(option);
                });
            })
            .catch(() => {
                select.innerHTML = '<option value="">❌ Error al cargar carpetas</option>';
                select.disabled = false;
            });
        
        new bootstrap.Modal(document.getElementById('moveDocumentModal')).show();
    };
});
</script>