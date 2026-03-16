<!-- FILTROS EN ORDEN HORIZONTAL -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Buscar archivos con X cuadrada siempre visible -->
            <div class="d-flex align-items-center position-relative" style="width: 700px;">
                <div class="position-relative flex-grow-1">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                    <input type="text" class="form-control ps-5" style="width: 100%; height: 42px; font-size: 1rem; border-radius: 4px 0 0 4px; border-right: none;" placeholder="Buscar archivos" id="buscadorArchivos">
                </div>
                <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center btn-clear-search" 
                        style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border-left: none; transition: all 0.2s;"
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
                    @foreach($anios as $anio)
                        <li><a class="dropdown-item" href="#" onclick="seleccionarAnio('{{ $anio }}', 'Año {{ $anio }}')">{{ $anio }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Tipo de Auditoría -->
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" id="btnTipo" style="height: 42px; background-color: white;">
                    <i class="bi bi-building"></i> <span id="tipoTexto">Tipo de Auditoría</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Interna', 'Interna')" id="opcionInterna">Interna</a></li>
                    <li><a class="dropdown-item" href="#" onclick="seleccionarTipo('Externa', 'Externa')" id="opcionExterna">Externa</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    /* Hover para botón de limpiar búsqueda */
    .btn-clear-search:hover {
        background-color: #737373 !important;
        border-color: #737373 !important;
    }
    .btn-clear-search:hover i {
        color: white !important;
    }
</style>