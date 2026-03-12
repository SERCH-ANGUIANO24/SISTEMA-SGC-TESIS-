<!-- FILTROS EN ORDEN HORIZONTAL -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Buscar solicitudes -->
            <div class="d-flex align-items-center position-relative" style="width: 700px;">
                <div class="position-relative flex-grow-1">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                    <input type="text" class="form-control ps-5" style="width: 100%; height: 42px; font-size: 1rem; border-radius: 4px 0 0 4px; border-right: none;" placeholder="Buscar solicitudes..." id="buscadorArchivos">
                </div>
                <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" 
                        style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border-left: none;"
                        id="limpiarBusqueda"
                        onclick="limpiarBuscador()"
                        title="Limpiar búsqueda">
                    <i class="bi bi-x-lg" style="font-size: 1.4rem; font-weight: bold;"></i>
                </button>
            </div>

            <!-- Ordenar por -->
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnOrdenar" style="height: 42px; background-color: white;">
                    <i class="bi bi-arrow-up-short"></i> <span id="ordenarTexto">Ordenar por</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('nombre-asc', 'Nombre (A-Z)')">Nombre (A-Z)</a></li>
                    <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('nombre-desc', 'Nombre (Z-A)')">Nombre (Z-A)</a></li>
                    <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-asc', 'Fecha (más antiguo)')">Fecha (más antiguo)</a></li>
                    <li><a class="dropdown-item" href="#" onclick="seleccionarOrden('fecha-desc', 'Fecha (más reciente)')">Fecha (más reciente)</a></li>
                </ul>
            </div>

            <!-- Filtrar por Año -->
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnAnio" style="height: 42px; background-color: white;">
                    <i class="bi bi-calendar"></i> <span id="anioTexto">Filtrar por Año</span>
                </button>
                <ul class="dropdown-menu" id="menuAnios">
                    <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('', 'Filtrar por Año')">Todos los años</a></li>
                    @foreach($anios ?? [] as $anio)
                        <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('{{ $anio }}', 'Año {{ $anio }}')">{{ $anio }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Filtrar por Estatus -->
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnEstatus" style="height: 42px; background-color: white;">
                    <i class="bi bi-rectangle-fill"></i> <span id="estatusTexto">Estatus</span>
                </button>
                <ul class="dropdown-menu" id="menuEstatus">
                    <li><a class="dropdown-item" href="#" onclick="seleccionarEstatus('', 'Todos los estatus')">Todos los estatus</a></li>
                    <li><a class="dropdown-item" href="#" onclick="seleccionarEstatus('No Atendida', 'No Atendida')">No Atendida</a></li>
                    <li><a class="dropdown-item" href="#" onclick="seleccionarEstatus('En Proceso', 'En Proceso')">En Proceso</a></li>
                    <li><a class="dropdown-item" href="#" onclick="seleccionarEstatus('Cerrado', 'Cerrado')">Cerrado</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function limpiarBuscador() {
    const buscador = document.getElementById('buscadorArchivos');
    if (buscador) {
        buscador.value = '';
        buscador.dispatchEvent(new Event('keyup'));
    }
}

function seleccionarOrden(valor, texto) {
    document.getElementById('ordenarTexto').innerText = texto;
    if (window.seleccionarOrden) {
        window.seleccionarOrden(valor, texto);
    }
}

function seleccionarAnio(valor, texto) {
    document.getElementById('anioTexto').innerText = texto;
    if (window.seleccionarAnio) {
        window.seleccionarAnio(valor, texto);
    }
}

function seleccionarEstatus(valor, texto) {
    document.getElementById('estatusTexto').innerText = texto;
    if (window.seleccionarEstatus) {
        window.seleccionarEstatus(valor, texto);
    }
}
</script>