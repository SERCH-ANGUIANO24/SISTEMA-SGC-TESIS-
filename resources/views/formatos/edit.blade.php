{{-- resources/views/formatos/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar formato</h1>

    <form action="{{ route('formatos.update', $formato) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="proceso" class="form-label">Proceso *</label>
            <select class="form-control" id="proceso" name="proceso" required>
                <option value="">Seleccione un proceso</option>
                <option value="PLANEACION" {{ $formato->proceso == 'PLANEACION' ? 'selected' : '' }}>Planeación</option>
                <option value="PREINSCRIPCION" {{ $formato->proceso == 'PREINSCRIPCION' ? 'selected' : '' }}>Preinscripción</option>
                <option value="INSCRIPCION" {{ $formato->proceso == 'INSCRIPCION' ? 'selected' : '' }}>Inscripción</option>
                <option value="TITULACION" {{ $formato->proceso == 'TITULACION' ? 'selected' : '' }}>Titulación</option>
                <option value="ENSEÑANZA APRENDIZAJE" {{ $formato->proceso == 'ENSEÑANZA APRENDIZAJE' ? 'selected' : '' }}>Enseñanza Aprendizaje</option>
                <option value="CONTRATACION O CONTROL DE PERSONAL" {{ $formato->proceso == 'CONTRATACION O CONTROL DE PERSONAL' ? 'selected' : '' }}>Contratación o Control de Personal</option>
                <option value="VINCULACION" {{ $formato->proceso == 'VINCULACION' ? 'selected' : '' }}>Vinculación</option>
                <option value="TI" {{ $formato->proceso == 'TI' ? 'selected' : '' }}>TI</option>
                <option value="GESTION DE RECURSOS" {{ $formato->proceso == 'GESTION DE RECURSOS' ? 'selected' : '' }}>Gestión de Recursos</option>
                <option value="LABORATORIOS Y TALLERES" {{ $formato->proceso == 'LABORATORIOS Y TALLERES' ? 'selected' : '' }}>Laboratorios y Talleres</option>
                <option value="CENTRO DE INFORMACION" {{ $formato->proceso == 'CENTRO DE INFORMACION' ? 'selected' : '' }}>Centro de Información</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="departamento" class="form-label">Departamento *</label>
            <select class="form-control" id="departamento" name="departamento" required>
                <option value="">Seleccione un departamento</option>
                @if($formato->proceso)
                    @php
                        $departamentos = [
                            'PLANEACION' => ['Rectoría', 'Dirección Académica', 'Dirección de Administración', 'Finanzas'],
                            'PREINSCRIPCION' => ['Servicios Escolares'],
                            'INSCRIPCION' => ['Servicios Escolares'],
                            'TITULACION' => ['Servicios Escolares'],
                            'ENSEÑANZA APRENDIZAJE' => ['Dirección Académica'],
                            'CONTRATACION O CONTROL DE PERSONAL' => ['Recursos Humanos'],
                            'VINCULACION' => ['Vinculación'],
                            'TI' => ['Sistemas Computacionales'],
                            'GESTION DE RECURSOS' => ['Recursos Financieros', 'Almacén'],
                            'LABORATORIOS Y TALLERES' => ['Encargado/a de Laboratorios'],
                            'CENTRO DE INFORMACION' => ['Biblioteca']
                        ];
                    @endphp
                    @foreach($departamentos[$formato->proceso] ?? [] as $dep)
                        <option value="{{ $dep }}" {{ $formato->departamento == $dep ? 'selected' : '' }}>{{ $dep }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="mb-3">
            <label for="clave_formato" class="form-label">Clave del formato *</label>
            <input type="text" class="form-control" id="clave_formato" name="clave_formato" value="{{ old('clave_formato', $formato->clave_formato) }}" required>
        </div>

        <div class="mb-3">
            <label for="codigo_procedimiento" class="form-label">Código de procedimiento *</label>
            <input type="text" class="form-control" id="codigo_procedimiento" name="codigo_procedimiento" value="{{ old('codigo_procedimiento', $formato->codigo_procedimiento) }}" required>
        </div>

        <div class="mb-3">
            <label for="version_procedimiento" class="form-label">Versión del procedimiento *</label>
            <input type="text" class="form-control" id="version_procedimiento" name="version_procedimiento" value="{{ old('version_procedimiento', $formato->version_procedimiento) }}" required>
        </div>

        <div class="mb-3">
            <label for="archivo" class="form-label">Archivo (dejar vacío si no desea cambiarlo)</label>
            <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
            <small>Archivo actual: <a href="{{ $formato->archivo_url }}" target="_blank">{{ $formato->archivo_nombre }}</a></small>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar formato</button>
        <a href="{{ route('formatos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const procesoSelect = document.getElementById('proceso');
    const departamentoSelect = document.getElementById('departamento');

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

    // Precargar departamentos según el proceso actual (por si cambia)
    function cargarDepartamentos(proceso, selectedDep = null) {
        departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
        if (proceso && departamentosPorProceso[proceso]) {
            departamentosPorProceso[proceso].forEach(dep => {
                const option = document.createElement('option');
                option.value = dep;
                option.textContent = dep;
                if (selectedDep && dep === selectedDep) {
                    option.selected = true;
                }
                departamentoSelect.appendChild(option);
            });
        }
    }

    // Al cambiar proceso, actualizar departamentos
    procesoSelect.addEventListener('change', function() {
        cargarDepartamentos(this.value);
    });

    // Inicializar con el valor actual (por si la página carga con proceso fijo)
    const procesoActual = procesoSelect.value;
    const departamentoActual = '{{ $formato->departamento }}';
    if (procesoActual) {
        cargarDepartamentos(procesoActual, departamentoActual);
    }
});
</script>
@endsection