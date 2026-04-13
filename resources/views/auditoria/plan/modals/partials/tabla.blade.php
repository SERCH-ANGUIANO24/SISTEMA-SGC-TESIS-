{{-- PARTIAL DE TABLA DEL MÓDULO PLAN DE AUDITORÍA --}}
{{-- MUESTRA LA LISTA DE AUDITORÍAS REGISTRADAS CON SUS COLUMNAS Y ACCIONES --}}

<!-- Tabla de Auditorías -->
<div class="row mb-4">
    <div class="col-12">
        {{-- CONTENEDOR RESPONSIVO: PERMITE SCROLL HORIZONTAL EN PANTALLAS PEQUEÑAS --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                {{-- ENCABEZADOS DE LA TABLA --}}
                <thead class="table-light">
                    <tr>
                        <th>Nombre de Auditoría</th>   {{-- NOMBRE DE LA AUDITORÍA --}}
                        <th>Tipo de Auditoría</th>      {{-- INTERNA O EXTERNA --}}
                        <th>Auditor Líder</th>          {{-- RESPONSABLE PRINCIPAL DE LA AUDITORÍA --}}
                        <th>Fecha de Auditoría</th>     {{-- FECHA EN QUE SE REALIZÓ O SE REALIZARÁ --}}
                        <th>Año</th>                    {{-- AÑO DE LA AUDITORÍA --}}
                        <th>Plan de Auditoría</th>      {{-- ARCHIVO O DOCUMENTO DEL PLAN --}}
                        <th>Auditores</th>              {{-- AUDITORES ASIGNADOS A LA AUDITORÍA --}}
                        <th class="text-end">Acciones</th> {{-- BOTONES DE EDITAR, ELIMINAR, ETC. --}}
                    </tr>
                </thead>

                {{-- CUERPO DE LA TABLA --}}
                {{-- EL ID tablaBody PERMITE QUE JAVASCRIPT INSERTE O ACTUALICE LAS FILAS DINÁMICAMENTE --}}
                <tbody id="tablaBody">
                    {{-- MENSAJE POR DEFECTO CUANDO NO HAY AUDITORÍAS REGISTRADAS --}}
                    <tr>
                        <td colspan="8" class="text-center">No hay auditorías registradas</td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>
</div>