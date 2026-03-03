{{-- resources/views/formatos/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Subir nuevo formato</h1>

    <form action="{{ route('formatos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="proceso" class="form-label">Proceso *</label>
            <select class="form-control" id="proceso" name="proceso" required>
                <option value="">Seleccione un proceso</option>
                <option value="PLANEACION">Planeación</option>
                <option value="PREINSCRIPCION">Preinscripción</option>
                <option value="INSCRIPCION">Inscripción</option>
                <option value="TITULACION">Titulación</option>
                <option value="ENSEÑANZA APRENDIZAJE">Enseñanza Aprendizaje</option>
                <option value="CONTRATACION O CONTROL DE PERSONAL">Contratación o Control de Personal</option>
                <option value="VINCULACION">Vinculación</option>
                <option value="TI">TI</option>
                <option value="GESTION DE RECURSOS">Gestión de Recursos</option>
                <option value="LABORATORIOS Y TALLERES">Laboratorios y Talleres</option>
                <option value="CENTRO DE INFORMACION">Centro de Información</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="departamento" class="form-label">Departamento *</label>
            <select class="form-control" id="departamento" name="departamento" required>
                <option value="">Primero seleccione un proceso</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="clave_formato" class="form-label">Clave del formato *</label>
            <input type="text" class="form-control" id="clave_formato" name="clave_formato" required>
        </div>

        <div class="mb-3">
            <label for="codigo_procedimiento" class="form-label">Código de procedimiento *</label>
            <input type="text" class="form-control" id="codigo_procedimiento" name="codigo_procedimiento" required>
        </div>

        <div class="mb-3">
            <label for="version_procedimiento" class="form-label">Versión del procedimiento *</label>
            <input type="text" class="form-control" id="version_procedimiento" name="version_procedimiento" required>
        </div>

        <div class="mb-3">
            <label for="archivo" class="form-label">Archivo *</label>
            <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
        </div>

        <button type="submit" class="btn btn-primary">Subir formato</button>
        <a href="{{ route('formatos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const procesoSelect = document.getElementById('proceso');
    const departamentoSelect = document.getElementById('departamento');

    // Mapeo de procesos a departamentos
    const departamentosPorProceso = {
        'PLANEACION': ['Rectoría', 'Dirección Académica', 'Dirección de Administración', 'Finanzas'],
        'PREINSCRIPCION': ['Servicios Escolares'],
        'INSCRIPCION': ['Servicios Escolares'],
        'TITULACION': ['Servicios Escolares'],
        'ENSEÑANZA APRENDIZAJE': ['Dirección Académica'],
        'CONTRATACION O CONTROL DE PERSONAL': ['Recursos Humanos'],
        'VINCULACION': ['Vinculación'],
        'TI': ['Sistemas Computacionales'],
        'GESTION DE RECURSOS': ['Recursos Financieros', 'Almacén'],
        'LABORATORIOS Y TALLERES': ['Encargado/a de Laboratorios'],
        'CENTRO DE INFORMACION': ['Biblioteca']
    };

    procesoSelect.addEventListener('change', function() {
        const proceso = this.value;
        departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';

        if (proceso && departamentosPorProceso[proceso]) {
            departamentosPorProceso[proceso].forEach(dep => {
                const option = document.createElement('option');
                option.value = dep;
                option.textContent = dep;
                departamentoSelect.appendChild(option);
            });
        }
    });
});
</script>
@endsection