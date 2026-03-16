<!-- MODAL HISTÓRICO DE SOLICITUDES -->
<div class="modal fade" id="modalHistorico" tabindex="-1" aria-labelledby="modalHistoricoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #800000;">
                <h5 class="modal-title text-white" id="modalHistoricoLabel">
                    <i class="bi bi-bar-chart-line-fill me-2"></i> Histórico de Solicitudes de Mejora
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- RESUMEN -->
                <div class="row mb-4" id="resumenHistorico">
                    <div class="col-12 text-center">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>

                <!-- GRÁFICAS DE PASTEL -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header text-white fw-bold text-center" style="background-color:#800000;">
                                <i class="bi bi-pie-chart me-1"></i> Todas las Solicitudes
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="graficaHistorica" style="max-height:250px;"></canvas>
                                <div id="leyendaHistorica" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold text-center text-white" style="background-color:#c0392b;">
                                <i class="bi bi-x-circle me-1"></i> No Conformidades
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="graficaHistoricaNC" style="max-height:250px;"></canvas>
                                <div id="leyendaHistoricaNC" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header fw-bold text-center text-white" style="background-color:#1a5276;">
                                <i class="bi bi-arrow-up-circle me-1"></i> Oportunidades de Mejora
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <canvas id="graficaHistoricaOM" style="max-height:250px;"></canvas>
                                <div id="leyendaHistoricaOM" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA POR AÑO -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header fw-bold text-center" style="background-color:#f8f9fa;color:#800000;">
                                <i class="bi bi-table me-1"></i> Detalle por Año
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" style="font-size:0.88rem;">
                                        <thead style="background-color:#f8f9fa;">
                                            <tr>
                                                <th class="text-center">Año</th>
                                                <th class="text-center" style="color:#fd7e14;">No Atendida</th>
                                                <th class="text-center" style="color:#ffc107;">En Proceso</th>
                                                <th class="text-center" style="color:#dc3545;">Cerrado</th>
                                                <th class="text-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaHistorico"></tbody>
                                    </table>
                                </div>
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
    var chartHistorico   = null;
    var chartHistoricoNC = null;
    var chartHistoricoOM = null;

    window.abrirModalHistorico = function() {
        var modal = new bootstrap.Modal(document.getElementById('modalHistorico'));
        modal.show();
        cargarHistorico();
    };

    function renderPastel(canvasId, leyendaId, chartInstance, labels, valores, colores) {
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
            leyendaHTML += '<div class="d-flex align-items-center justify-content-between mb-2">' +
                '<div class="d-flex align-items-center gap-2">' +
                '<span style="width:14px;height:14px;border-radius:3px;background:' + colores[i] + ';display:inline-block;flex-shrink:0;"></span>' +
                '<span style="font-size:0.85rem;">' + label + '</span></div>' +
                '<span class="fw-bold" style="font-size:0.85rem;">' + valores[i] +
                ' <span class="text-muted fw-normal">(' + pct + '%)</span></span></div>';
        });
        document.getElementById(leyendaId).innerHTML = leyendaHTML;
        return chart;
    }

    function cargarHistorico() {
        var url = '{{ route("auditoria.solicitudes.historico") }}';
        document.getElementById('resumenHistorico').innerHTML =
            '<div class="col-12 text-center"><div class="spinner-border text-secondary" role="status"></div></div>';

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('resumenHistorico').innerHTML =
                '<div class="col-12"><div class="alert text-center fw-bold" style="background-color:#fdf3f3;border:1px solid #e8c0c0;color:#800000;">' +
                '<i class="bi bi-clipboard-data me-2"></i>' +
                'Total histórico: <span style="font-size:1.2rem;">' + data.total + '</span> solicitudes' +
                ' &nbsp;|&nbsp; <span style="color:#fd7e14;">No Atendidas: ' + data.totales['No Atendida'] + '</span>' +
                ' &nbsp;|&nbsp; <span style="color:#e6a817;">En Proceso: ' + data.totales['En Proceso'] + '</span>' +
                ' &nbsp;|&nbsp; <span style="color:#dc3545;">Cerradas: ' + data.totales['Cerrado'] + '</span>' +
                '</div></div>';

            chartHistorico = renderPastel(
                'graficaHistorica', 'leyendaHistorica', chartHistorico,
                ['No Atendida', 'En Proceso', 'Cerrado'],
                [data.totales['No Atendida'], data.totales['En Proceso'], data.totales['Cerrado']],
                ['#fd7e14', '#ffc107', '#dc3545']
            );

            chartHistoricoNC = renderPastel(
                'graficaHistoricaNC', 'leyendaHistoricaNC', chartHistoricoNC,
                ['No Atendida', 'En Proceso', 'Cerrado'],
                [data.totales_nc['No Atendida'], data.totales_nc['En Proceso'], data.totales_nc['Cerrado']],
                ['#fd7e14', '#ffc107', '#dc3545']
            );

            chartHistoricoOM = renderPastel(
                'graficaHistoricaOM', 'leyendaHistoricaOM', chartHistoricoOM,
                ['No Atendida', 'En Proceso', 'Cerrado'],
                [data.totales_om['No Atendida'], data.totales_om['En Proceso'], data.totales_om['Cerrado']],
                ['#fd7e14', '#ffc107', '#1a5276']
            );

            var tbodyHTML = '';
            if (data.por_anio && data.por_anio.length > 0) {
                data.por_anio.forEach(function(fila) {
                    var totalFila = (fila['No Atendida'] || 0) + (fila['En Proceso'] || 0) + (fila['Cerrado'] || 0);
                    tbodyHTML += '<tr>' +
                        '<td class="text-center fw-bold">' + fila.anio + '</td>' +
                        '<td class="text-center" style="color:#fd7e14;font-weight:600;">' + (fila['No Atendida'] || 0) + '</td>' +
                        '<td class="text-center" style="color:#e6a817;font-weight:600;">' + (fila['En Proceso'] || 0) + '</td>' +
                        '<td class="text-center" style="color:#dc3545;font-weight:600;">' + (fila['Cerrado'] || 0) + '</td>' +
                        '<td class="text-center fw-bold">' + totalFila + '</td>' +
                        '</tr>';
                });
            } else {
                tbodyHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin datos</td></tr>';
            }
            document.getElementById('tablaHistorico').innerHTML = tbodyHTML;
        })
        .catch(function(err) {
            console.error(err);
            document.getElementById('resumenHistorico').innerHTML =
                '<div class="col-12 text-center text-danger">Error al cargar los datos.</div>';
        });
    }
})();
</script>