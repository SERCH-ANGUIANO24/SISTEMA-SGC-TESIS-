
{{-- BARRA DE FILTROS HORIZONTAL PARA BUSCAR Y ORDENAR           --}}
{{-- LAS SOLICITUDES DE MEJORA EN LA TABLA PRINCIPAL.            --}}
{{-- ============================================================ --}}

<!-- FILTROS EN ORDEN HORIZONTAL -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 flex-wrap">

            {{-- ================================================ --}}
            {{-- FILTRO 1: BUSCADOR DE SOLICITUDES                --}}
            {{-- CAMPO DE TEXTO PARA BUSCAR POR PALABRAS CLAVE.   --}}
            {{-- EL BOTÓN "X" DE LA DERECHA LIMPIA EL BUSCADOR    --}}
            {{-- Y DISPARA EL EVENTO PARA ACTUALIZAR LA TABLA.    --}}
            {{-- ================================================ --}}
            <div class="d-flex align-items-center position-relative" style="width: 700px;">
                <div class="position-relative flex-grow-1">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 1rem;"></i>
                    <input type="text" class="form-control ps-5" style="width: 100%; height: 42px; font-size: 1rem; border-radius: 4px 0 0 4px; border-right: none;" placeholder="Buscar solicitudes..." id="buscadorArchivos">
                </div>
                <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center btn-clear-search" 
                        style="width: 42px; height: 42px; border-radius: 0 4px 4px 0; background-color: white; border-left: none;"
                        id="limpiarBusqueda"
                        onclick="limpiarBuscador()"
                        title="Limpiar búsqueda">
                    <i class="bi bi-x-lg" style="font-size: 1.4rem; font-weight: bold;"></i>
                </button>
            </div>

            {{-- ================================================ --}}
            {{-- FILTRO 2: ORDENAR POR                            --}}
            {{-- DROPDOWN PARA ORDENAR LA TABLA POR:              --}}
            {{--   · NOMBRE A-Z / Z-A                             --}}
            {{--   · FECHA MÁS ANTIGUO / MÁS RECIENTE            --}}
            {{-- ================================================ --}}
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

            {{-- ================================================ --}}
            {{-- FILTRO 3: FILTRAR POR AÑO                        --}}
            {{-- DROPDOWN QUE CARGA DINÁMICAMENTE LOS AÑOS        --}}
            {{-- DISPONIBLES DESDE LA VARIABLE $anios DE BLADE.   --}}
            {{-- TAMBIÉN TIENE LA OPCIÓN "TODOS LOS AÑOS".        --}}
            {{-- ================================================ --}}
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

            {{-- ================================================ --}}
            {{-- FILTRO 4: FILTRAR POR ESTATUS                    --}}
            {{-- DROPDOWN PARA FILTRAR POR ESTADO DE LA SOLICITUD: --}}
            {{--   · TODOS LOS ESTATUS                            --}}
            {{--   · NO ATENDIDA                                  --}}
            {{--   · EN PROCESO                                   --}}
            {{--   · CERRADO                                      --}}
            {{-- ================================================ --}}
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

            {{-- ================================================ --}}
            {{-- BOTÓN: VER ESTADÍSTICAS                          --}}
            {{-- ABRE EL MODAL DE GRÁFICAS (MODAL_GRAFICAS).      --}}
            {{-- VISIBLE PARA TODOS LOS USUARIOS.                 --}}
            {{-- ================================================ --}}
            <button class="btn" type="button" onclick="abrirModalGraficas()"
                style="height: 42px; background-color: #0dcaf0; color: white; border: none; border-radius: 4px;">
                <i class="bi bi-pie-chart-fill me-1"></i> Estadísticas
            </button>

            {{-- ================================================ --}}
            {{-- BOTÓN: VER HISTÓRICO                             --}}
            {{-- ABRE EL MODAL DE HISTÓRICO (MODAL_HISTORICO).    --}}
            {{-- VISIBLE PARA TODOS LOS USUARIOS.                 --}}
            {{-- ================================================ --}}
            <button class="btn" type="button" onclick="abrirModalHistorico()"
                style="height: 42px; background-color: #0d6efd; color: white; border: none; border-radius: 4px;">
                <i class="bi bi-bar-chart-line-fill me-1"></i> Histórico
            </button>

        </div>
    </div>
</div>

<style>
    /* Estilos específicos para el botón de limpiar búsqueda */
    .btn-clear-search {
        border-color: #ced4da !important;
        background-color: white !important;
        transition: all 0.2s ease;
    }

    .btn-clear-search:hover {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
    }

    .btn-clear-search:hover i {
        color: white !important;
    }

    /* Asegurar que el input tenga el mismo borde gris */
    #buscadorArchivos {
        border-color: #ced4da !important;
    }

    /* Opcional: si quieres que el borde del input sea consistente en focus */
    #buscadorArchivos:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        z-index: 2;
    }
</style>

<script>
// ============================================================
// FUNCIÓN: limpiarBuscador
// VACÍA EL CAMPO DE BÚSQUEDA Y DISPARA EL EVENTO "keyup"
// PARA QUE LA TABLA SE ACTUALICE AUTOMÁTICAMENTE.
// ============================================================
function limpiarBuscador() {
    const buscador = document.getElementById('buscadorArchivos');
    if (buscador) {
        buscador.value = '';
        buscador.dispatchEvent(new Event('keyup'));
    }
}

// ============================================================
// FUNCIÓN: seleccionarOrden
// ACTUALIZA EL TEXTO DEL BOTÓN "ORDENAR POR" Y LLAMA
// A LA FUNCIÓN GLOBAL DEL MISMO NOMBRE PARA APLICAR EL ORDEN.
// ============================================================
function seleccionarOrden(valor, texto) {
    document.getElementById('ordenarTexto').innerText = texto;
    if (window.seleccionarOrden) {
        window.seleccionarOrden(valor, texto);
    }
}

// ============================================================
// FUNCIÓN: seleccionarAnio
// ACTUALIZA EL TEXTO DEL BOTÓN "FILTRAR POR AÑO" Y LLAMA
// A LA FUNCIÓN GLOBAL DEL MISMO NOMBRE PARA APLICAR EL FILTRO.
// ============================================================
function seleccionarAnio(valor, texto) {
    document.getElementById('anioTexto').innerText = texto;
    if (window.seleccionarAnio) {
        window.seleccionarAnio(valor, texto);
    }
}

// ============================================================
// FUNCIÓN: seleccionarEstatus
// ACTUALIZA EL TEXTO DEL BOTÓN "ESTATUS" Y LLAMA
// A LA FUNCIÓN GLOBAL DEL MISMO NOMBRE PARA APLICAR EL FILTRO.
// ============================================================
function seleccionarEstatus(valor, texto) {
    document.getElementById('estatusTexto').innerText = texto;
    if (window.seleccionarEstatus) {
        window.seleccionarEstatus(valor, texto);
    }
}
</script>