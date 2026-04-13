{{-- TABLA PRINCIPAL QUE MUESTRA TODAS LAS SOLICITUDES DE MEJORA --}}
{{-- CON SUS COLUMNAS DE INFORMACIÓN Y BOTONES DE ACCIÓN.        --}}
{{-- EL CONTENIDO DEL TBODY SE CARGA DINÁMICAMENTE VÍA JAVASCRIPT.--}}
{{-- ============================================================ --}}

<!-- Tabla de Solicitudes de Mejora -->
<div class="row mb-4">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                {{-- ================================================ --}}
                {{-- ENCABEZADOS DE LA TABLA                          --}}
                {{-- COLUMNAS:                                         --}}
                {{--   1. FECHA DE SOLICITUD                          --}}
                {{--   2. NO. IDENTIFICACIÓN (FOLIO)                  --}}
                {{--   3. RESPONSABLE DE LA ACCIÓN                    --}}
                {{--   4. PROCESO AUDITADO                            --}}
                {{--   5. TIPO DE SOLICITUD (NC U OM)                 --}}
                {{--   6. PERIODO DE APLICACIÓN                       --}}
                {{--   7. ACTIVIDADES DE VERIFICACIÓN                 --}}
                {{--   8. DOCUMENTO ADJUNTO                           --}}
                {{--   9. PERIODO DE VERIFICACIÓN                     --}}
                {{--  10. ESTATUS (NO ATENDIDA / EN PROCESO / CERRADO)--}}
                {{--  11. ACCIONES (EDITAR / ELIMINAR / ETC.)         --}}
                {{-- ================================================ --}}
                <thead class="table-light">
                    <tr>
                        <th>Fecha de Solicitud</th>
                        <th>No. Identificación</th>
                        <th>Responsable de la Acción</th>
                        <th>Proceso Auditado</th>
                        <th>Tipo de Solicitud</th>
                        <th>Periodo de Aplicación</th>
                        <th>Actividades de Verificación</th>
                        <th>Documento</th>
                        <th>Periodo de Verificación</th>
                        <th>Estatus</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                {{-- ================================================ --}}
                {{-- CUERPO DE LA TABLA (TBODY)                       --}}
                {{-- LAS FILAS SE GENERAN DINÁMICAMENTE DESDE         --}}
                {{-- JAVASCRIPT AL CARGAR O FILTRAR LAS SOLICITUDES.  --}}
                {{-- SI NO HAY DATOS, MUESTRA UN MENSAJE VACÍO CON    --}}
                {{-- UN ÍCONO Y EL TEXTO "NO HAY SOLICITUDES".        --}}
                {{-- ================================================ --}}
                <tbody id="tablaBody">
                    <tr>
                        <td colspan="11" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bi bi-rectangle-fill" style="font-size: 2rem; color: #800000; opacity: 0.5;"></i>
                                <p class="mt-2 text-muted">No hay solicitudes de mejora registradas</p>
                            </div>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>
</div>