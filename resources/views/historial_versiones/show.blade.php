@extends('layouts.app')

@section('title', 'Detalle de Actividad - Historial de Versiones')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2 text-dark">
                        <i class="bi bi-clock-history me-2" style="color: #737373;"></i>
                        Detalle de Actividad
                    </h1>
                    <p class="text-muted mb-0">
                        Información completa de la acción realizada
                    </p>
                </div>
                <div>
                    <a href="{{ route('historial-versiones.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al historial
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información principal -->
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información de la Actividad
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Módulo:</th>
                                    <td>
                                        <span class="badge" style="background: {{ $actividad->color_modulo }}20; color: {{ $actividad->color_modulo }}; padding: 8px 12px;">
                                            <i class="{{ $actividad->icono_modulo }} me-1"></i>
                                            {{ ucfirst(strtolower($actividad->modulo)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Acción:</th>
                                    <td>
                                        <span class="badge" style="background: {{ $actividad->color_accion }}; color: white; padding: 8px 12px;">
                                            <i class="{{ $actividad->icono_accion }} me-1"></i>
                                            {{ $actividad->accion }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Importancia:</th>
                                    <td>{!! $actividad->badge_importancia !!}</td>
                                </tr>
                                <tr>
                                    <th>Fecha:</th>
                                    <td>{{ $actividad->fecha_formateada }}</td>
                                </tr>
                                <tr>
                                    <th>IP:</th>
                                    <td><code>{{ $actividad->ip_address ?? 'No registrada' }}</code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Usuario:</th>
                                    <td>
                                        <strong>{{ $actividad->usuario_nombre }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $actividad->usuario_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Rol:</th>
                                    <td>{{ ucfirst($actividad->usuario_rol) }}</td>
                                </tr>
                                <tr>
                                    <th>ID Usuario:</th>
                                    <td>{{ $actividad->usuario_id ?? 'Sistema' }}</td>
                                </tr>
                                <tr>
                                    <th>Navegador:</th>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($actividad->user_agent, 50) }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Descripción:</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $actividad->descripcion }}
                        </div>
                    </div>

                    @if($actividad->datos_anteriores || $actividad->datos_nuevos)
                    <div>
                        <h6 class="fw-bold mb-3">Cambios Realizados:</h6>
                        
                        @php
                            $cambios = $actividad->getCambiosRealizados();
                        @endphp
                        
                        @if(!empty($cambios))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Campo</th>
                                            <th>Valor Anterior</th>
                                            <th>Valor Nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cambios as $campo => $valores)
                                        <tr>
                                            <td><strong>{{ ucfirst(str_replace('_', ' ', $campo)) }}</strong></td>
                                            <td>
                                                @if(is_null($valores['anterior']))
                                                    <span class="text-muted">(vacío)</span>
                                                @elseif(is_array($valores['anterior']) || is_object($valores['anterior']))
                                                    <pre class="mb-0"><code>{{ json_encode($valores['anterior'], JSON_PRETTY_PRINT) }}</code></pre>
                                                @else
                                                    {{ $valores['anterior'] }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(is_null($valores['nuevo']))
                                                    <span class="text-muted">(vacío)</span>
                                                @elseif(is_array($valores['nuevo']) || is_object($valores['nuevo']))
                                                    <pre class="mb-0"><code>{{ json_encode($valores['nuevo'], JSON_PRETTY_PRINT) }}</code></pre>
                                                @else
                                                    {{ $valores['nuevo'] }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No hay cambios detallados disponibles</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Información del registro afectado -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-database me-2"></i>
                        Registro Afectado
                    </h5>
                </div>
                <div class="card-body">
                    @if($actividad->tabla_afectada || $actividad->registro_id)
                        <p>
                            <strong>Tabla:</strong> 
                            <span class="badge bg-secondary">{{ $actividad->tabla_afectada ?? 'N/A' }}</span>
                        </p>
                        <p>
                            <strong>ID Registro:</strong> 
                            <span class="badge bg-info">{{ $actividad->registro_id ?? 'N/A' }}</span>
                        </p>
                        <p>
                            <strong>Elemento:</strong> 
                            <span>{{ $actividad->elemento_nombre ?? 'N/A' }}</span>
                        </p>
                    @else
                        <p class="text-muted text-center py-3">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No hay información del registro afectado
                        </p>
                    @endif
                </div>
            </div>

            <!-- Información técnica -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Información Técnica
                    </h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>ID Actividad:</strong>
                        <span class="badge bg-dark">{{ $actividad->id }}</span>
                    </p>
                    <p>
                        <strong>Created_at:</strong>
                        <br>
                        <small>{{ $actividad->created_at->format('Y-m-d H:i:s') }}</small>
                    </p>
                    <p>
                        <strong>Updated_at:</strong>
                        <br>
                        <small>{{ $actividad->updated_at->format('Y-m-d H:i:s') }}</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection