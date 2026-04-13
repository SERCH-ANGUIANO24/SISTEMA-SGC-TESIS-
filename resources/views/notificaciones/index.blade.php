@extends('layouts.app')
@section('title', 'Mis Notificaciones')

@section('content')
<div class='container-fluid py-4'>

  {{-- ── ENCABEZADO: TÍTULO DE LA PÁGINA Y BOTONES DE ACCIÓN MASIVA ── --}}
  {{-- EL TÍTULO ES UN ENLACE AL DASHBOARD PRINCIPAL --}}
  <div class='d-flex justify-content-between align-items-center mb-4'>
    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Regresar al Dashboard">
      <h1 class='h3' style='color:#ea580c;'>
        <i class="bi bi-bell me-2" style="font-size: 3rem; vertical-align: middle;"></i>
        Notificaciones
      </h1>
    </a>
    <div class='d-flex gap-2'>
      {{-- BOTÓN PARA MARCAR TODAS LAS NOTIFICACIONES COMO LEÍDAS DE UN SOLO CLIC --}}
      <button id='btnLeerTodas' class='btn btn-sm btn-outline-secondary'>
        <i class='bi bi-check2-all'></i> Marcar todas como leídas
      </button>
      {{-- BOTÓN PARA ELIMINAR TODAS LAS NOTIFICACIONES QUE YA FUERON LEÍDAS --}}
      <button id='btnLimpiar' class='btn btn-sm'>
        <i class='bi bi-trash'></i> Limpiar leídas
      </button>
    </div>
  </div>

  {{-- ── FILTROS: PERMITE FILTRAR LAS NOTIFICACIONES POR TIPO Y ESTADO ── --}}
  <div class='d-flex gap-3 mb-4 flex-wrap'>

    {{-- ← CAMBIO 1: Select actualizado --}}
    {{-- FILTRO POR TIPO: MUESTRA SOLO NOTIFICACIONES DE AUDITORÍAS O GESTIÓN DOCUMENTAL --}}
    <select id='filtroTipo' class='form-select' style='width:220px;'>
      <option value=''>Todos los tipos</option>
      <option value='auditoria'>Auditorías</option>
      <option value='documental'>Gestión Documental</option>
    </select>

    {{-- FILTRO POR ESTADO: MUESTRA TODAS, SOLO SIN LEER O SOLO LAS YA LEÍDAS --}}
    <select id='filtroEstado' class='form-select' style='width:180px;'>
      <option value=''>Todas</option>
      <option value='no-leida'>Sin leer</option>
      <option value='leida'>Leídas</option>
    </select>
  </div>

  {{-- ── LISTA DE NOTIFICACIONES ── --}}
  {{-- CADA TARJETA REPRESENTA UNA NOTIFICACIÓN DEL USUARIO --}}
  {{-- LAS NO LEÍDAS TIENEN BORDE IZQUIERDO GRIS Y LAS LEÍDAS TIENEN OPACIDAD REDUCIDA --}}
  <div id='listaNotificaciones'>
    @forelse($notificaciones as $notif)

    {{-- ← CAMBIO 2: data-tipo-evento agregado --}}
    {{-- data-tipo Y data-tipo-evento SE USAN PARA EL FILTRADO EN JAVASCRIPT --}}
    <div class='notif-card card mb-2 {{ $notif->leida ? "opacity-75" : "border-start border-3" }}'
        style='{{ !$notif->leida ? "border-left-color: #737373 !important;" : "" }}'
         data-id='{{ $notif->id }}'
         data-tipo='{{ $notif->tipo }}'
         data-tipo-evento='{{ $notif->tipo_evento ?? "" }}'>
      <div class='card-body d-flex align-items-start gap-3 py-3'>

        {{-- ÍCONO CIRCULAR DE LA NOTIFICACIÓN CON COLOR SEGÚN EL TIPO --}}
        {{-- AZUL=info | VERDE=exito | AMARILLO=advertencia | ROJO=error --}}
        <div class='rounded-circle d-flex align-items-center justify-content-center flex-shrink-0'
             style='width:42px;height:42px;min-width:42px;
             background:{{ ["info"=>"#0d6efd","exito"=>"#198754","advertencia"=>"#ffc107","error"=>"#dc3545"][$notif->tipo] ?? "#6c757d" }};
             color:#fff;'>
          <i class='bi {{ $notif->icono }}'></i>
        </div>

        {{-- CONTENIDO DE LA NOTIFICACIÓN: TÍTULO, MENSAJE Y BOTONES DE ACCIÓN --}}
        <div class='flex-grow-1'>
          <div class='d-flex justify-content-between align-items-start'>

            {{-- TÍTULO EN NEGRITA SI NO FUE LEÍDA, O EN GRIS SI YA FUE LEÍDA --}}
            <h6 class='mb-1 {{ $notif->leida ? "text-muted" : "fw-bold" }}'>
              {{ $notif->titulo }}
            </h6>

            {{-- TIEMPO RELATIVO DESDE QUE SE CREÓ LA NOTIFICACIÓN (EJ: "hace 2 horas") --}}
            <small class='text-muted ms-2 text-nowrap'>
              {{ $notif->created_at->locale('es')->diffForHumans() }}
            </small>
          </div>

          {{-- MENSAJE BREVE DE LA NOTIFICACIÓN --}}
          <p class='mb-1 text-muted small'>{{ $notif->mensaje }}</p>

          {{-- BOTONES DE ACCIÓN DE CADA NOTIFICACIÓN --}}
          <div class='d-flex gap-2 mt-2 flex-wrap'>

            {{-- BOTÓN VER DOCUMENTO: SOLO SE MUESTRA SI LA NOTIFICACIÓN TIENE URL ASOCIADA --}}
            @if($notif->url)
            <a href='{{ $notif->url }}' class='btn btn-sm btn-outline-secondary py-0'>
              <i class='bi bi-eye'></i> Ver documento
            </a>
            @endif

            {{-- BOTÓN MARCAR COMO LEÍDA: SOLO SE MUESTRA SI LA NOTIFICACIÓN AÚN NO FUE LEÍDA --}}
            @if(!$notif->leida)
            <button class='btn btn-sm btn-outline-success py-0 btn-marcar-leida'
                    data-id='{{ $notif->id }}'>
              <i class='bi bi-check'></i> Marcar como leída
            </button>
            @endif

            {{-- BOTÓN ELIMINAR: DISPONIBLE PARA TODAS LAS NOTIFICACIONES --}}
            <button class='btn btn-sm btn-outline-danger py-0 btn-eliminar'
                    data-id='{{ $notif->id }}'>
              <i class='bi bi-x'></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    @empty
    {{-- MENSAJE CUANDO EL USUARIO NO TIENE NINGUNA NOTIFICACIÓN --}}
    <div class='text-center py-5 text-muted'>
      <i class='bi bi-bell-slash' style='font-size:3rem;'></i>
      <p class='mt-2'>No tienes notificaciones</p>
    </div>
    @endforelse
  </div>

  {{-- PAGINACIÓN DE LAS NOTIFICACIONES --}}
  <div class='mt-3'>{{ $notificaciones->links('pagination::bootstrap-5') }}</div>

</div>

{{-- ── ESTILOS CSS DE LA PÁGINA DE NOTIFICACIONES ── --}}
{{-- INCLUYE ESTILOS DE BOTONES Y REGLAS RESPONSIVAS PARA TABLETS Y MÓVILES --}}
@push('styles')
<style>
  /* Botones internos - Estilo basado en tus ejemplos */
  
  /* ESTILO BASE DEL BOTÓN VER DOCUMENTO: BORDE GRIS Y TEXTO NEGRO SIN FONDO */
  .notif-card .btn.btn-outline-secondary {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  /* EFECTO HOVER Y FOCUS DEL BOTÓN VER DOCUMENTO */
  .notif-card .btn.btn-outline-secondary:hover,
  .notif-card .btn.btn-outline-secondary:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  
  /* ESTILO BASE DEL BOTÓN MARCAR COMO LEÍDA: BORDE GRIS Y TEXTO NEGRO SIN FONDO */
  .notif-card .btn.btn-outline-success {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  /* EFECTO HOVER Y FOCUS DEL BOTÓN MARCAR COMO LEÍDA */
  .notif-card .btn.btn-outline-success:hover,
  .notif-card .btn.btn-outline-success:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  
  /* ESTILO BASE DEL BOTÓN ELIMINAR (X): BORDE GRIS Y TEXTO NEGRO SIN FONDO */
  .notif-card .btn.btn-outline-danger {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  /* EFECTO HOVER Y FOCUS DEL BOTÓN ELIMINAR */
  .notif-card .btn.btn-outline-danger:hover,
  .notif-card .btn.btn-outline-danger:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }

  /* Botones superiores */
  
  /* ESTILO BASE DEL BOTÓN MARCAR TODAS COMO LEÍDAS */
  button#btnLeerTodas.btn {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  /* EFECTO HOVER Y FOCUS DEL BOTÓN MARCAR TODAS COMO LEÍDAS */
  button#btnLeerTodas.btn:hover,
  button#btnLeerTodas.btn:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  
  /* ESTILO BASE DEL BOTÓN LIMPIAR LEÍDAS */
  button#btnLimpiar.btn {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  /* EFECTO HOVER Y FOCUS DEL BOTÓN LIMPIAR LEÍDAS */
  button#btnLimpiar.btn:hover,
  button#btnLimpiar.btn:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }

  /* =====================================================
     ESTILOS RESPONSIVOS - NOTIFICACIONES
     (MISMO PATRÓN QUE ANEXOS, GESTIÓN, LISTA MAESTRA)
  ===================================================== */

  /* TABLETS (769px a 992px): REDUCE TAMAÑOS DE FUENTE, PADDING Y ÍCONO */
  @media (min-width: 769px) and (max-width: 992px) {
    .notif-card .card-body {
      padding: 0.75rem !important;
      gap: 0.75rem !important;
    }
    .notif-card h6 {
      font-size: 0.85rem !important;
    }
    .notif-card p {
      font-size: 0.75rem !important;
    }
    .notif-card .btn-sm {
      padding: 0.2rem 0.4rem !important;
      font-size: 0.7rem !important;
    }
    .notif-card .rounded-circle {
      width: 36px !important;
      height: 36px !important;
      min-width: 36px !important;
    }
    .notif-card .rounded-circle i {
      font-size: 0.9rem !important;
    }
    .pagination .page-link {
      padding: 0.2rem 0.5rem !important;
      font-size: 0.7rem !important;
    }
  }

  /* MÓVILES (768px y menos): APILA ELEMENTOS Y REDUCE TAMAÑOS DRÁSTICAMENTE */
  @media (max-width: 768px) {
    .container-fluid {
      padding-left: 12px !important;
      padding-right: 12px !important;
    }
    .h3 {
      font-size: 1.5rem !important;
    }
    .h3 i {
      font-size: 2rem !important;
    }
    
    /* ENCABEZADO - APILA TÍTULO Y BOTONES EN COLUMNA EN MÓVIL */
    .d-flex.justify-content-between.align-items-center {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 0.75rem !important;
    }
    .d-flex.justify-content-between.align-items-center .d-flex.gap-2 {
      width: 100% !important;
    }
    button#btnLeerTodas.btn,
    button#btnLimpiar.btn {
      flex: 1 !important;
      font-size: 0.7rem !important;
      padding: 0.3rem 0.5rem !important;
    }
    
    /* SELECTS DE FILTROS - OCUPAN TODO EL ANCHO EN MÓVIL */
    select.form-select {
      width: 100% !important;
    }
    .d-flex.gap-3.mb-4.flex-wrap {
      flex-direction: column !important;
      gap: 0.5rem !important;
    }
    
    /* TARJETAS DE NOTIFICACIONES - SE APILAN EN COLUMNA EN MÓVIL */
    .notif-card .card-body {
      padding: 0.75rem !important;
      gap: 0.75rem !important;
      flex-direction: column !important;
      align-items: flex-start !important;
    }
    .notif-card .rounded-circle {
      width: 36px !important;
      height: 36px !important;
      min-width: 36px !important;
    }
    .notif-card .rounded-circle i {
      font-size: 0.9rem !important;
    }
    .notif-card h6 {
      font-size: 0.85rem !important;
    }
    .notif-card p {
      font-size: 0.75rem !important;
    }
    .notif-card .d-flex.justify-content-between.align-items-start {
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 0.25rem !important;
    }
    .notif-card small {
      font-size: 0.65rem !important;
    }
    .notif-card .btn-sm {
      padding: 0.15rem 0.3rem !important;
      font-size: 0.65rem !important;
    }
    .notif-card .btn-sm i {
      font-size: 0.65rem !important;
    }
    
    /* PAGINACIÓN CENTRADA EN MÓVIL */
    .mt-3 {
      text-align: center !important;
    }
    .pagination {
      flex-wrap: wrap !important;
      justify-content: center !important;
      gap: 2px !important;
    }
    .pagination .page-link {
      padding: 0.15rem 0.4rem !important;
      font-size: 0.65rem !important;
    }
  }

  /* MÓVILES MUY PEQUEÑOS (480px y menos): REDUCE AÚN MÁS LOS TAMAÑOS */
  @media (max-width: 480px) {
    .notif-card .rounded-circle {
      width: 30px !important;
      height: 30px !important;
      min-width: 30px !important;
    }
    .notif-card .rounded-circle i {
      font-size: 0.8rem !important;
    }
    .notif-card h6 {
      font-size: 0.8rem !important;
    }
    .notif-card p {
      font-size: 0.7rem !important;
    }
    .notif-card .btn-sm {
      padding: 0.1rem 0.25rem !important;
      font-size: 0.6rem !important;
    }
    .notif-card .btn-sm i {
      font-size: 0.6rem !important;
    }
    button#btnLeerTodas.btn,
    button#btnLimpiar.btn {
      font-size: 0.65rem !important;
      padding: 0.25rem 0.4rem !important;
    }
    .pagination .page-link {
      padding: 0.1rem 0.35rem !important;
      font-size: 0.6rem !important;
    }
  }
</style>
@endpush

{{-- ── SCRIPTS DE LA PÁGINA DE NOTIFICACIONES ── --}}
{{-- MANEJA: FILTRADO, MARCAR COMO LEÍDA, ELIMINAR, MARCAR TODAS Y LIMPIAR --}}
@push('scripts')
<script>
// TOKEN CSRF PARA LAS PETICIONES FETCH AL SERVIDOR
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// ESCUCHA LOS CAMBIOS EN LOS SELECTORES DE FILTRO Y APLICA EL FILTRADO
document.getElementById('filtroTipo').addEventListener('change', filtrar);
document.getElementById('filtroEstado').addEventListener('change', filtrar);

{{-- ← CAMBIO 3: función filtrar() actualizada --}}
// FUNCIÓN QUE FILTRA LAS NOTIFICACIONES VISIBLES SEGÚN TIPO Y ESTADO
// LOS TIPOS SE IDENTIFICAN POR EL data-tipo-evento DE CADA TARJETA
function filtrar(){
    const filtro = document.getElementById('filtroTipo').value;
    const estado = document.getElementById('filtroEstado').value;

    // LISTA DE EVENTOS QUE PERTENECEN AL MÓDULO DE AUDITORÍAS
    const eventosAuditoria  = [
        'nueva_auditoria', 'nuevo_informe', 'solicitud_mejora',
        'recordatorio_solicitud', 'solicitud_en_proceso',
        'solicitud_cerrada', 'solicitud_no_atendida',
        'solicitud_vencida', 'solicitud_vencida_usuario'
    ];

    // LISTA DE EVENTOS QUE PERTENECEN AL MÓDULO DE GESTIÓN DOCUMENTAL
    const eventosDocumental = [
        'subida', 'aprobado', 'rechazado', 'publicado'
    ];

    // RECORRE CADA TARJETA Y LA MUESTRA U OCULTA SEGÚN LOS FILTROS ACTIVOS
    document.querySelectorAll('.notif-card').forEach(c => {
        const evento = c.dataset.tipoEvento ?? '';

        // VERIFICA SI EL EVENTO PERTENECE AL TIPO SELECCIONADO
        let matchTipo = true;
        if (filtro === 'auditoria')  matchTipo = eventosAuditoria.includes(evento);
        if (filtro === 'documental') matchTipo = eventosDocumental.includes(evento);

        // VERIFICA SI EL ESTADO DE LA NOTIFICACIÓN COINCIDE CON EL FILTRO
        // LAS NO LEÍDAS TIENEN LA CLASE border-3, LAS LEÍDAS NO
        const matchEstado = !estado || (
            estado === 'leida'
                ? !c.classList.contains('border-3')
                :  c.classList.contains('border-3')
        );

        // MUESTRA LA TARJETA SOLO SI CUMPLE AMBOS FILTROS
        c.style.display = matchTipo && matchEstado ? '' : 'none';
    });
}

// MARCAR UNA NOTIFICACIÓN COMO LEÍDA AL HACER CLIC EN SU BOTÓN
// ENVÍA UNA PETICIÓN POST Y QUITA EL BORDE DE NO LEÍDA DE LA TARJETA
document.addEventListener('click', async e => {
  const btn = e.target.closest('.btn-marcar-leida');
  if(!btn) return;
  const id = btn.dataset.id;
  await fetch(`/notificaciones/${id}/leer`, {method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
  btn.closest('.notif-card').classList.remove('border-start','border-danger','border-3');
  btn.remove();
});

// ELIMINAR UNA NOTIFICACIÓN INDIVIDUAL AL HACER CLIC EN SU BOTÓN X
// ENVÍA UNA PETICIÓN DELETE Y REMUEVE LA TARJETA DEL DOM
document.addEventListener('click', async e => {
  const btn = e.target.closest('.btn-eliminar');
  if(!btn) return;
  const id = btn.dataset.id;
  await fetch(`/notificaciones/${id}`, {method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  btn.closest('.notif-card').remove();
});

// MARCAR TODAS LAS NOTIFICACIONES COMO LEÍDAS Y RECARGAR LA PÁGINA
document.getElementById('btnLeerTodas').addEventListener('click', async () => {
  await fetch('/notificaciones/leer-todas', {method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
  location.reload();
});

// ELIMINAR TODAS LAS NOTIFICACIONES YA LEÍDAS Y RECARGAR LA PÁGINA
document.getElementById('btnLimpiar').addEventListener('click', async () => {
  await fetch('/notificaciones/limpiar', {method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  location.reload();
});
</script>
@endpush
@endsection