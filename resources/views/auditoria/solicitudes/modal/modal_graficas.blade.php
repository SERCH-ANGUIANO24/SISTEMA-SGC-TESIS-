<!-- MODAL GRÁFICAS DE SOLICITUDES -->
<div class="modal fade" id="modalGraficas" tabindex="-1" aria-labelledby="modalGraficasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #0dcaf0;">
                <h5 class="modal-title text-white" id="modalGraficasLabel">
                    <i class="bi bi-pie-chart-fill me-2"></i> Estadísticas de Solicitudes de Mejora
                </h5>

            </div>
            <div class="modal-body">

                <!-- FILTROS -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filtrar por Año</label>
                        <select class="form-select" id="graficaFiltroAnio">
                            <option value="">Todos los años</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filtrar por Proceso</label>
                        <select class="form-select" id="graficaFiltroProceso">
                            <option value="">Todos los procesos</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn w-30 text-white" style="background-color:#0dcaf0;;" onclick="cargarGraficas()">
                            <i class="bi bi-funnel me-1"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- RESUMEN TOTAL -->
                <div class="row mb-4" id="resumenGraficas">
                    <div class="col-12 text-center">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>

                <!-- INDICADORES NC/OM POR PROCESO -->
                <div class="row mb-4" id="indicadoresNcOmGrafica" style="display:none;">
                    <div class="col-12">
                        <div class="rounded p-3 d-flex gap-3 justify-content-center align-items-center flex-wrap"
                             style="background:#fdf3f3;border:1px solid #e8c0c0;">
                            <div class="text-center px-4">
                                <div style="font-size:0.78rem;color:#800000;font-weight:600;">NO CONFORMIDADES DEL PROCESO</div>
                                <div id="graficaValorNC" style="font-size:2.8rem;font-weight:700;color:#800000;line-height:1.1;">—</div>
                                <div style="font-size:0.72rem;color:#999;" id="graficaTextoAnioNC">según informes</div>
                            </div>
                            <div style="width:1px;min-height:70px;background:#e8c0c0;"></div>
                            <div class="text-center px-4">
                                <div style="font-size:0.78rem;color:#1a5276;font-weight:600;">OPORTUNIDADES DE MEJORA DEL PROCESO</div>
                                <div id="graficaValorOM" style="font-size:2.8rem;font-weight:700;color:#1a5276;line-height:1.1;">—</div>
                                <div style="font-size:0.72rem;color:#999;" id="graficaTextoAnioOM">según informes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRÁFICAS -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header text-white fw-bold text-center" style="background-color:#737373;">
                                <i class="bi bi-pie-chart me-1"></i> Todas las Solicitudes
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="graficaTodas" style="max-height:250px;"></canvas>
                                <div id="leyendaTodas" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold text-center text-white" style="background-color:#dc3545;">
                                <i class="bi bi-x-circle me-1"></i> No Conformidades
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="graficaNC" style="max-height:250px;"></canvas>
                                <div id="leyendaNC" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold text-center text-white" style="background-color:#28a745;">
                                <i class="bi bi-arrow-up-circle me-1"></i> Oportunidades de Mejora
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="graficaOM" style="max-height:250px;"></canvas>
                                <div id="leyendaOM" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var chartTodas = null;
    var chartNC    = null;
    var chartOM    = null;

    window.abrirModalGraficas = function() {
        var modal = new bootstrap.Modal(document.getElementById('modalGraficas'));
        modal.show();
        window.cargarGraficas();
    };

    window.cargarGraficas = function() {
        var anio    = document.getElementById('graficaFiltroAnio').value;
        var proceso = document.getElementById('graficaFiltroProceso').value;

        var url = '{{ route("auditoria.solicitudes.graficas") }}';
        var params = new URLSearchParams();
        if (anio)    params.append('anio', anio);
        if (proceso) params.append('proceso', proceso);
        if (params.toString()) url += '?' + params.toString();

        document.getElementById('resumenGraficas').innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-secondary" role="status"></div></div>';
        document.getElementById('indicadoresNcOmGrafica').style.display = 'none';

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var selAnio       = document.getElementById('graficaFiltroAnio');
            var selProceso    = document.getElementById('graficaFiltroProceso');
            var anioActual    = selAnio.value;
            var procesoActual = selProceso.value;

            if (selAnio.options.length <= 1 && data.anios) {
                data.anios.forEach(function(a) {
                    var opt = document.createElement('option');
                    opt.value = a; opt.textContent = a;
                    selAnio.appendChild(opt);
                });
                selAnio.value = anioActual;
            }
            if (selProceso.options.length <= 1 && data.procesos) {
                data.procesos.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p; opt.textContent = p;
                    selProceso.appendChild(opt);
                });
                selProceso.value = procesoActual;
            }

            document.getElementById('resumenGraficas').innerHTML =
                '<div class="col-12"><div class="alert text-center fw-bold" style="background-color:#fff;border:1px solid #bab2b2;color:#000000;">' +
                '<i class="bi bi-clipboard-data me-2"></i>Total de solicitudes: <span style="font-size:1.2rem;">' + data.total + '</span></div></div>';

            var procesoSel = document.getElementById('graficaFiltroProceso').value;
            var anioSel    = document.getElementById('graficaFiltroAnio').value;
            if (procesoSel) {
                window.cargarNcOmGrafica(procesoSel, anioSel);
            }

            chartTodas = renderizarPastel('graficaTodas', 'leyendaTodas', chartTodas,
                ['No Atendida', 'En Proceso', 'Cerrado'],
                [data.todas_por_estatus['No Atendida'], data.todas_por_estatus['En Proceso'], data.todas_por_estatus['Cerrado']],
                ['#fd7e14', '#ffc107', '#dc3545']);

            chartNC = renderizarPastel('graficaNC', 'leyendaNC', chartNC,
                ['No Atendida', 'En Proceso', 'Cerrado'],
                [data.nc_por_estatus['No Atendida'], data.nc_por_estatus['En Proceso'], data.nc_por_estatus['Cerrado']],
                ['#fd7e14', '#ffc107', '#dc3545']);

            chartOM = renderizarPastel('graficaOM', 'leyendaOM', chartOM,
                ['No Atendida', 'En Proceso', 'Cerrado'],
                [data.om_por_estatus['No Atendida'], data.om_por_estatus['En Proceso'], data.om_por_estatus['Cerrado']],
                ['#fd7e14', '#ffc107', '#dc3545']);
        })
        .catch(function(err) {
            console.error(err);
            document.getElementById('resumenGraficas').innerHTML = '<div class="col-12 text-center text-danger">Error al cargar los datos.</div>';
        });
    };

    window.cargarNcOmGrafica = function(proceso, anio) {
        var url = '{{ route("auditoria.solicitudes.ncOmPorProcesoAnio") }}';
        var params = new URLSearchParams();
        params.append('proceso', proceso);
        if (anio) params.append('anio', anio);
        url += '?' + params.toString();

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('graficaValorNC').textContent = data.nc !== undefined ? data.nc : 0;
            document.getElementById('graficaValorOM').textContent = data.om !== undefined ? data.om : 0;
            var textoAnio = anio ? 'año ' + anio : 'todos los años';
            document.getElementById('graficaTextoAnioNC').textContent = 'según informes (' + textoAnio + ')';
            document.getElementById('graficaTextoAnioOM').textContent = 'según informes (' + textoAnio + ')';
            document.getElementById('indicadoresNcOmGrafica').style.display = 'block';
        })
        .catch(function() {
            document.getElementById('indicadoresNcOmGrafica').style.display = 'none';
        });
    };

    function renderizarPastel(canvasId, leyendaId, chartInstance, labels, valores, colores) {
        if (chartInstance) chartInstance.destroy();

        var total = valores.reduce(function(a, b) { return a + b; }, 0);

        if (total === 0) {
            document.getElementById(canvasId).style.display = 'none';
            document.getElementById(leyendaId).innerHTML = '<p class="text-center text-muted small">Sin datos para mostrar</p>';
            return null;
        }

        document.getElementById(canvasId).style.display = '';
        var ctx = document.getElementById(canvasId).getContext('2d');

        var chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{ data: valores, backgroundColor: colores, borderColor: '#ffffff', borderWidth: 2 }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var val = context.parsed;
                                var pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        var leyendaHTML = '';
        labels.forEach(function(label, i) {
            var pct = total > 0 ? ((valores[i] / total) * 100).toFixed(1) : 0;
            leyendaHTML += '<div class="d-flex align-items-center justify-content-between mb-1">' +
                '<div class="d-flex align-items-center gap-2">' +
                '<span style="width:14px;height:14px;border-radius:3px;background:' + colores[i] + ';display:inline-block;flex-shrink:0;"></span>' +
                '<span style="font-size:0.82rem;">' + label + '</span></div>' +
                '<span class="fw-bold" style="font-size:0.85rem;">' + valores[i] + ' <span class="text-muted fw-normal">(' + pct + '%)</span></span></div>';
        });
        document.getElementById(leyendaId).innerHTML = leyendaHTML;

        return chart;
    }
})();
</script>