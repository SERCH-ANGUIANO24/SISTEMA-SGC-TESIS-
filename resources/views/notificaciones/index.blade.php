@extends('layouts.app')
@section('title', 'Mis Notificaciones')

@section('content')
<div class='container-fluid py-4'>

  {{-- ENCABEZADO --}}
  <div class='d-flex justify-content-between align-items-center mb-4'>
    <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Regresar al Dashboard">
      <h1 class='h3' style='color:#ea580c;'>
              <i class="bi bi-bell me-2" style="font-size: 3rem; vertical-align: middle;"></i>
              Notificaciones
        </h1>
    </a>
    <div class='d-flex gap-2'>
      <button id='btnLeerTodas' class='btn btn-sm btn-outline-secondary'>
        <i class='bi bi-check2-all'></i> Marcar todas como leídas
      </button>
      <button id='btnLimpiar' class='btn btn-sm'>
        <i class='bi bi-trash'></i> Limpiar leídas
      </button>
    </div>
  </div>

  {{-- FILTROS --}}
  <div class='d-flex gap-3 mb-4 flex-wrap'>

    {{-- ← CAMBIO 1: Select actualizado --}}
    <select id='filtroTipo' class='form-select' style='width:220px;'>
      <option value=''>Todos los tipos</option>
      <option value='auditoria'>Auditorías</option>
      <option value='documental'>Gestión Documental</option>
    </select>

    <select id='filtroEstado' class='form-select' style='width:180px;'>
      <option value=''>Todas</option>
      <option value='no-leida'>Sin leer</option>
      <option value='leida'>Leídas</option>
    </select>
  </div>

  {{-- LISTA --}}
  <div id='listaNotificaciones'>
    @forelse($notificaciones as $notif)

    {{-- ← CAMBIO 2: data-tipo-evento agregado --}}
    <div class='notif-card card mb-2 {{ $notif->leida ? "opacity-75" : "border-start border-3" }}'
        style='{{ !$notif->leida ? "border-left-color: #737373 !important;" : "" }}'
         data-id='{{ $notif->id }}'
         data-tipo='{{ $notif->tipo }}'
         data-tipo-evento='{{ $notif->tipo_evento ?? "" }}'>
      <div class='card-body d-flex align-items-start gap-3 py-3'>

        {{-- ICONO --}}
        <div class='rounded-circle d-flex align-items-center justify-content-center flex-shrink-0'
             style='width:42px;height:42px;min-width:42px;
             background:{{ ["info"=>"#0d6efd","exito"=>"#198754","advertencia"=>"#ffc107","error"=>"#dc3545"][$notif->tipo] ?? "#6c757d" }};
             color:#fff;'>
          <i class='bi {{ $notif->icono }}'></i>
        </div>

        {{-- CONTENIDO --}}
        <div class='flex-grow-1'>
          <div class='d-flex justify-content-between align-items-start'>
            <h6 class='mb-1 {{ $notif->leida ? "text-muted" : "fw-bold" }}'>
              {{ $notif->titulo }}
            </h6>
            <small class='text-muted ms-2 text-nowrap'>
              {{ $notif->created_at->diffForHumans() }}
            </small>
          </div>
          <p class='mb-1 text-muted small'>{{ $notif->mensaje }}</p>
          <div class='d-flex gap-2 mt-2 flex-wrap'>
            @if($notif->url)
            <a href='{{ $notif->url }}' class='btn btn-sm btn-outline-secondary py-0'>
              <i class='bi bi-eye'></i> Ver documento
            </a>
            @endif
            @if(!$notif->leida)
            <button class='btn btn-sm btn-outline-success py-0 btn-marcar-leida'
                    data-id='{{ $notif->id }}'>
              <i class='bi bi-check'></i> Marcar como leída
            </button>
            @endif
            <button class='btn btn-sm btn-outline-danger py-0 btn-eliminar'
                    data-id='{{ $notif->id }}'>
              <i class='bi bi-x'></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class='text-center py-5 text-muted'>
      <i class='bi bi-bell-slash' style='font-size:3rem;'></i>
      <p class='mt-2'>No tienes notificaciones</p>
    </div>
    @endforelse
  </div>

  {{-- PAGINACIÓN --}}
  <div class='mt-3'>{{ $notificaciones->links('pagination::bootstrap-5') }}</div>

</div>

@push('styles')
<style>
  /* Botones internos - Estilo basado en tus ejemplos */
  
  /* Botón VER DOCUMENTO */
  .notif-card .btn.btn-outline-secondary {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  .notif-card .btn.btn-outline-secondary:hover,
  .notif-card .btn.btn-outline-secondary:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  
  /* Botón MARCAR COMO LEÍDA */
  .notif-card .btn.btn-outline-success {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  .notif-card .btn.btn-outline-success:hover,
  .notif-card .btn.btn-outline-success:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  
  /* Botón ELIMINAR (X) */
  .notif-card .btn.btn-outline-danger {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  .notif-card .btn.btn-outline-danger:hover,
  .notif-card .btn.btn-outline-danger:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }

  /* Botones superiores */
  
  /* Botón MARCAR TODAS COMO LEÍDAS */
  button#btnLeerTodas.btn {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  button#btnLeerTodas.btn:hover,
  button#btnLeerTodas.btn:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  
  /* Botón LIMPIAR LEÍDAS */
  button#btnLimpiar.btn {
    border: 1px solid #737373 !important;
    color: #000000 !important;
    background-color: transparent !important;
  }
  
  button#btnLimpiar.btn:hover,
  button#btnLimpiar.btn:focus {
    border-color: #000000 !important;
    color: #000000 !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
</style>
@endpush

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// Filtros
document.getElementById('filtroTipo').addEventListener('change', filtrar);
document.getElementById('filtroEstado').addEventListener('change', filtrar);

{{-- ← CAMBIO 3: función filtrar() actualizada --}}
function filtrar(){
    const filtro = document.getElementById('filtroTipo').value;
    const estado = document.getElementById('filtroEstado').value;

    const eventosAuditoria  = [
        'nueva_auditoria', 'nuevo_informe', 'solicitud_mejora',
        'recordatorio_solicitud', 'solicitud_en_proceso',
        'solicitud_cerrada', 'solicitud_no_atendida',
        'solicitud_vencida', 'solicitud_vencida_usuario'
    ];
    const eventosDocumental = [
        'subida', 'aprobado', 'rechazado', 'publicado'
    ];

    document.querySelectorAll('.notif-card').forEach(c => {
        const evento = c.dataset.tipoEvento ?? '';

        let matchTipo = true;
        if (filtro === 'auditoria')  matchTipo = eventosAuditoria.includes(evento);
        if (filtro === 'documental') matchTipo = eventosDocumental.includes(evento);

        const matchEstado = !estado || (
            estado === 'leida'
                ? !c.classList.contains('border-primary')
                :  c.classList.contains('border-primary')
        );

        c.style.display = matchTipo && matchEstado ? '' : 'none';
    });
}

// Marcar una como leída
document.addEventListener('click', async e => {
  const btn = e.target.closest('.btn-marcar-leida');
  if(!btn) return;
  const id = btn.dataset.id;
  await fetch(`/notificaciones/${id}/leer`, {method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
  btn.closest('.notif-card').classList.remove('border-start','border-danger','border-3');
  btn.remove();
});

// Eliminar una
document.addEventListener('click', async e => {
  const btn = e.target.closest('.btn-eliminar');
  if(!btn) return;
  const id = btn.dataset.id;
  await fetch(`/notificaciones/${id}`, {method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  btn.closest('.notif-card').remove();
});

// Marcar todas como leídas
document.getElementById('btnLeerTodas').addEventListener('click', async () => {
  await fetch('/notificaciones/leer-todas', {method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
  location.reload();
});

// Limpiar leídas
document.getElementById('btnLimpiar').addEventListener('click', async () => {
  await fetch('/notificaciones/limpiar', {method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF}});
  location.reload();
});
</script>
@endpush
@endsection