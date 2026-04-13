@extends('layouts.app')

@section('title', 'Detalle de Actividad - Historial de Versiones')

@section('content')
<div class="container-fluid">

    {{-- ── ENCABEZADO: TÍTULO DE LA PÁGINA Y BOTÓN PARA VOLVER AL HISTORIAL ── --}}
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    {{-- TÍTULO DE LA SECCIÓN CON ÍCONO DE RELOJ --}}
                    <h1 class="h3 mb-2 text-dark">
                        <i class="bi bi-clock-history me-2" style="color: #737373;"></i>
                        Detalle de Actividad
                    </h1>
                    {{-- DESCRIPCIÓN BREVE DE LO QUE MUESTRA ESTA VISTA --}}
                    <p class="text-muted mb-0">
                        Información completa de la acción realizada
                    </p>
                </div>
                <div>
                    {{-- BOTÓN PARA REGRESAR AL LISTADO GENERAL DEL HISTORIAL --}}
                    <a href="{{ route('historial-versiones.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al historial
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── CONTENIDO PRINCIPAL: INFORMACIÓN DE LA ACTIVIDAD Y DATOS TÉCNICOS ── --}}
    <div class="row">

        {{-- ── COLUMNA IZQUIERDA (8/12): DETALLES COMPLETOS DE LA ACTIVIDAD ── --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información de la Actividad
                    </h5>
                </div>
                <div class="card-body">

                    {{-- ── TABLA DE DOS COLUMNAS CON LOS DATOS PRINCIPALES DE LA ACTIVIDAD ── --}}
                    <div class="row mb-4">

                        {{-- COLUMNA IZQUIERDA DE LA TABLA: MÓDULO, ACCIÓN, IMPORTANCIA, FECHA E IP --}}
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Módulo:</th>
                                    <td>
                                        {{-- BADGE DEL MÓDULO CON SU COLOR E ÍCONO ESPECÍFICO --}}
                                        <span class="badge" style="background: {{ $actividad->color_modulo }}20; color: {{ $actividad->color_modulo }}; padding: 8px 12px;">
                                            <i class="{{ $actividad->icono_modulo }} me-1"></i>
                                            {{ ucfirst(strtolower($actividad->modulo)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Acción:</th>
                                    <td>
                                        {{-- BADGE DE LA ACCIÓN CON SU COLOR E ÍCONO SEGÚN EL TIPO --}}
                                        <span class="badge" style="background: {{ $actividad->color_accion }}; color: white; padding: 8px 12px;">
                                            <i class="{{ $actividad->icono_accion }} me-1"></i>
                                            {{ str_replace('MOVIR', 'MOVER', $actividad->accion) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Importancia:</th>
                                    {{-- BADGE DE IMPORTANCIA GENERADO DINÁMICAMENTE DESDE EL MODELO --}}
                                    <td>{!! $actividad->badge_importancia !!}</td>
                                </tr>
                                <tr>
                                    <th>Fecha:</th>
                                    {{-- FECHA FORMATEADA CALCULADA EN EL MODELO --}}
                                    <td>{{ $actividad->fecha_formateada }}</td>
                                </tr>
                                <tr>
                                    <th>IP:</th>
                                    {{-- DIRECCIÓN IP DESDE DONDE SE REALIZÓ LA ACCIÓN --}}
                                    <td><code>{{ $actividad->ip_address ?? 'No registrada' }}</code></td>
                                </tr>
                            </table>
                        </div>

                        {{-- COLUMNA DERECHA DE LA TABLA: DATOS DEL USUARIO QUE REALIZÓ LA ACCIÓN --}}
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">Usuario:</th>
                                    <td>
                                        {{-- NOMBRE DEL USUARIO QUE REALIZÓ LA ACCIÓN --}}
                                        <strong>{{ $actividad->usuario_nombre }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    {{-- CORREO ELECTRÓNICO DEL USUARIO --}}
                                    <td>{{ $actividad->usuario_email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Rol:</th>
                                    {{-- ROL DEL USUARIO EN EL SISTEMA CON PRIMERA LETRA EN MAYÚSCULA --}}
                                    <td>{{ ucfirst($actividad->usuario_rol) }}</td>
                                </tr>
                                <tr>
                                    <th>ID Usuario:</th>
                                    {{-- ID DEL USUARIO O "Sistema" SI FUE UNA ACCIÓN AUTOMÁTICA --}}
                                    <td>{{ $actividad->usuario_id ?? 'Sistema' }}</td>
                                </tr>
                                <tr>
                                    <th>Navegador:</th>
                                    {{-- USER AGENT DEL NAVEGADOR TRUNCADO A 50 CARACTERES --}}
                                    <td>
                                        <small class="text-muted">{{ Str::limit($actividad->user_agent, 50) }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- ── DESCRIPCIÓN COMPLETA DE LA ACTIVIDAD ── --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Descripción:</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $actividad->descripcion }}
                        </div>
                    </div>

                    {{-- ── TABLA DE CAMBIOS REALIZADOS (SOLO SE MUESTRA SI HAY DATOS ANTERIORES O NUEVOS) ── --}}
                    {{-- COMPARA LOS VALORES ANTES Y DESPUÉS DE LA ACCIÓN CAMPO POR CAMPO --}}
                    @if($actividad->datos_anteriores || $actividad->datos_nuevos)
                    <div>
                        <h6 class="fw-bold mb-3">Cambios Realizados:</h6>
                        
                        {{-- OBTIENE EL ARRAY DE CAMBIOS CALCULADO DESDE EL MODELO --}}
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
                                        {{-- RECORRE CADA CAMPO QUE FUE MODIFICADO Y MUESTRA SUS VALORES --}}
                                        @foreach($cambios as $campo => $valores)
                                        <tr>
                                            {{-- NOMBRE DEL CAMPO CON GUIONES BAJOS REEMPLAZADOS POR ESPACIOS --}}
                                            <td><strong>{{ ucfirst(str_replace('_', ' ', $campo)) }}</strong></td>

                                            {{-- VALOR ANTERIOR: MUESTRA (vacío) SI ES NULO, JSON SI ES ARRAY U OBJETO --}}
                                            <td>
                                                @if(is_null($valores['anterior']))
                                                    <span class="text-muted">(vacío)</span>
                                                @elseif(is_array($valores['anterior']) || is_object($valores['anterior']))
                                                    <pre class="mb-0"><code>{{ json_encode($valores['anterior'], JSON_PRETTY_PRINT) }}</code></pre>
                                                @else
                                                    {{ $valores['anterior'] }}
                                                @endif
                                            </td>

                                            {{-- VALOR NUEVO: MUESTRA (vacío) SI ES NULO, JSON SI ES ARRAY U OBJETO --}}
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
                            {{-- MENSAJE CUANDO NO HAY CAMBIOS DETALLADOS DISPONIBLES --}}
                            <p class="text-muted">No hay cambios detallados disponibles</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── COLUMNA DERECHA (4/12): REGISTRO AFECTADO E INFORMACIÓN TÉCNICA ── --}}
        <div class="col-md-4">

            {{-- TARJETA: INFORMACIÓN DEL REGISTRO AFECTADO POR LA ACCIÓN --}}
            <!-- Información del registro afectado -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-database me-2"></i>
                        Registro Afectado
                    </h5>
                </div>
                <div class="card-body">
                    {{-- SI HAY DATOS DEL REGISTRO, LOS MUESTRA; SI NO, MUESTRA MENSAJE INFORMATIVO --}}
                    @if($actividad->tabla_afectada || $actividad->registro_id)
                        {{-- NOMBRE DE LA TABLA EN LA BASE DE DATOS QUE FUE AFECTADA --}}
                        <p>
                            <strong>Tabla:</strong> 
                            <span class="badge bg-secondary">{{ $actividad->tabla_afectada ?? 'N/A' }}</span>
                        </p>
                        {{-- ID DEL REGISTRO ESPECÍFICO QUE FUE CREADO, EDITADO O ELIMINADO --}}
                        <p>
                            <strong>ID Registro:</strong> 
                            <span class="badge bg-info">{{ $actividad->registro_id ?? 'N/A' }}</span>
                        </p>
                        {{-- NOMBRE LEGIBLE DEL ELEMENTO AFECTADO --}}
                        <p>
                            <strong>Elemento:</strong> 
                            <span>{{ $actividad->elemento_nombre ?? 'N/A' }}</span>
                        </p>
                    @else
                        {{-- MENSAJE CUANDO NO SE TIENE INFORMACIÓN DEL REGISTRO AFECTADO --}}
                        <p class="text-muted text-center py-3">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No hay información del registro afectado
                        </p>
                    @endif
                </div>
            </div>

            {{-- TARJETA: INFORMACIÓN TÉCNICA DEL REGISTRO DE ACTIVIDAD --}}
            <!-- Información técnica -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Información Técnica
                    </h5>
                </div>
                <div class="card-body">
                    {{-- ID ÚNICO DEL REGISTRO EN LA TABLA DE HISTORIAL --}}
                    <p>
                        <strong>ID Actividad:</strong>
                        <span class="badge bg-dark">{{ $actividad->id }}</span>
                    </p>
                    {{-- FECHA Y HORA EXACTA EN QUE SE CREÓ EL REGISTRO EN LA BASE DE DATOS --}}
                    <p>
                        <strong>Created_at:</strong>
                        <br>
                        <small>{{ $actividad->created_at->format('Y-m-d H:i:s') }}</small>
                    </p>
                    {{-- FECHA Y HORA DE LA ÚLTIMA ACTUALIZACIÓN DEL REGISTRO --}}
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