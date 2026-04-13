@extends('layouts.app')

@section('title', $aviso->titulo)  

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex flex-column">
                    <!-- ENLACE PARA VOLVER AL LISTADO DE AVISOS -->
                    <a href="{{ route('avisos.index') }}" class="text-decoration-none" title="Volver a Avisos">
                        <h1 class="h3 mb-2" style="color: #4f46e5; cursor: pointer;">
                            <i class="bi bi-arrow-left-circle me-2" style="font-size: 2rem; vertical-align: middle;"></i>
                            <i class="bi bi-megaphone me-2" style="font-size: 2rem; vertical-align: middle;"></i>
                            Detalle del Aviso
                        </h1>
                    </a>
                </div>
                /** BOTONES DE ACCIÓN **/
                // BOTÓN PARA EDITAR (AMARILLO) Y BOTÓN PARA ELIMINAR (ROJO CON CONFIRMACIÓN)
                <div>
                    <a href="{{ route('avisos.edit', $aviso->id) }}" class="btn btn-warning me-2">
                        <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $aviso->id }})">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    /** SECCIÓN: TÍTULO Y ESTADO DEL AVISO **/
                    <div class="mb-4">
                        <h2 class="h4">{{ $aviso->titulo }}</h2>
                        <div class="d-flex gap-3 mt-2">
                            <!-- ISACTIVE() - MUESTRA EL ESTADO DEL AVISO: ACTIVO, PROGRAMADO O EXPIRADO -->
                            <span class="badge {{ $aviso->isActive() ? 'bg-success' : (now() < $aviso->fecha_inicio ? 'bg-warning' : 'bg-secondary') }}">
                                {{ $aviso->isActive() ? 'Activo' : (now() < $aviso->fecha_inicio ? 'Programado' : 'Expirado') }}
                            </span>
                            <!-- FECHA DE CREACIÓN -->
                            <span><i class="bi bi-calendar me-1"></i>Publicado: {{ $aviso->created_at->format('d/m/Y H:i') }}</span>
                            <!-- CONTADOR DE VISITAS -->
                            <span><i class="bi bi-eye me-1"></i>{{ $aviso->visitas }} visitas</span>
                        </div>
                    </div>

                    /** SECCIÓN: DESCRIPCIÓN **/
                    // SOLO SE MUESTRA SI EL AVISO TIENE DESCRIPCIÓN
                    @if($aviso->descripcion)
                        <div class="mb-4">
                            <h5 class="fw-bold">Descripción</h5>
                            <div class="p-3 bg-light rounded">
                                {{ $aviso->descripcion }}
                            </div>
                        </div>
                    @endif

                    /** SECCIÓN: FECHAS DE INICIO Y FIN **/
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <i class="bi bi-calendar-check me-2 text-success"></i>
                                <strong>Fecha de inicio:</strong><br>
                                {{ $aviso->fecha_inicio->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <i class="bi bi-calendar-x me-2 text-danger"></i>
                                <strong>Fecha de fin:</strong><br>
                                {{ $aviso->fecha_fin->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    /** SECCIÓN: INFORMACIÓN ADICIONAL **/
                    // QUIÉN CREÓ EL AVISO Y CUÁNDO SE ACTUALIZÓ POR ÚLTIMA VEZ
                    <div class="text-muted small">
                        <hr>
                        <p>Creado por: {{ $aviso->creador->name }}</p>
                        <p>Última actualización: {{ $aviso->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="col-md-4">
                    /** SECCIÓN: ARCHIVO ADJUNTO **/
                    // SI EL AVISO TIENE ARCHIVO, MUESTRA TARJETA CON ÍCONO, NOMBRE, TAMAÑO Y BOTÓN DE DESCARGA
                    @if($aviso->archivo_nombre)
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <!-- GETICONOARCHIVO() - MUESTRA ÍCONO SEGÚN EL TIPO DE ARCHIVO -->
                                <i class="{{ $aviso->getIconoArchivo() }}" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Archivo adjunto</h5>
                                <p class="mb-2">{{ $aviso->archivo_nombre }}</p>
                                <p class="small text-muted">
                                    Tamaño: {{ number_format($aviso->tamano_archivo / 1024 / 1024, 2) }} MB
                                </p>
                                <!-- BOTÓN PARA DESCARGAR EL ARCHIVO -->
                                <a href="{{ route('avisos.download', $aviso->id) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-download me-2"></i>Descargar archivo
                                </a>
                            </div>
                        </div>
                    @else
                        // SI NO HAY ARCHIVO, MUESTRA MENSAJE INFORMATIVO
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark" style="font-size: 3rem; color: #6c757d;"></i>
                                <p class="mt-3 text-muted">Este aviso no tiene archivo adjunto</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

/** FORMULARIO OCULTO PARA ELIMINAR **/
// ESTE FORMULARIO NO SE VE. SE ENVÍA CUANDO EL USUARIO CONFIRMA LA ELIMINACIÓN
<form id="delete-form" action="{{ route('avisos.destroy', $aviso->id) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')  // SIMULA EL MÉTODO HTTP DELETE
</form>

/** BLOQUE DE JAVASCRIPT - CONFIRMACIÓN DE ELIMINACIÓN **/
// USA SWEETALERT (UNA LIBRERÍA DE MODALES BONITOS) PARA PREGUNTAR ANTES DE ELIMINAR
@push('scripts')
<script>
function confirmDelete(id) {
    // MUESTRA UN CUADRO DE DIÁLOGO CON OPCIÓN DE CONFIRMAR O CANCELAR
    Swal.fire({
        title: '¿Eliminar aviso?',
        text: 'Esta acción no se puede deshacer',  // ADVERTENCIA CLARA
        icon: 'warning',  // ÍCONO DE ADVERTENCIA (TRIÁNGULO AMARILLO)
        showCancelButton: true,  // MUESTRA BOTÓN DE CANCELAR
        confirmButtonColor: '#d33',  // BOTÓN ROJO PARA ELIMINAR
        cancelButtonColor: '#3085d6',  // BOTÓN AZUL PARA CANCELAR
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        // SI EL USUARIO CONFIRMA (HACE CLIC EN "SÍ, ELIMINAR")
        if (result.isConfirmed) {
            // ENVÍA EL FORMULARIO OCULTO QUE ELIMINA EL AVISO
            document.getElementById('delete-form').submit();
        }
    });
}
</script>
@endpush
@endsection