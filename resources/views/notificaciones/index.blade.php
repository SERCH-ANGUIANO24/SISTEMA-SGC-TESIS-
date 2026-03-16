{{-- resources/views/notificaciones/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Mis Notificaciones')

@section('content')
<div class='container-fluid py-4'>

  {{-- ENCABEZADO --}}
  <div class='d-flex justify-content-between align-items-center mb-4'>
    <h1 class='h3' style='color:#ea580c;'>
      <i class='bi bi-bell me-2'></i> Mis Notificaciones
      @if($totalNoLeidas > 0)
        <span class='badge bg-danger ms-2'>{{ $totalNoLeidas }} sin leer</span>
      @endif
    </h1>
    <div class='d-flex gap-2'>
      <button id='btnLeerTodas' class='btn btn-sm btn-outline-secondary'>
        <i class='bi bi-check2-all'></i> Marcar todas como leídas
      </button>
      <button id='btnLimpiar' class='btn btn-sm btn-outline-danger'>
        <i class='bi bi-trash'></i> Limpiar leídas
      </button>
    </div>
  </div>

  {{-- FILTROS --}}
  <div class='d-flex gap-3 mb-4 flex-wrap'>
    <select id='filtroTipo' class='form-select' style='width:180px;'>
      <option value=''>Todos los tipos</option>
      <option value='info'>Info</option>
      <option value='exito'>Éxito</option>
      <option value='advertencia'>Advertencia</option>
      <option value='error'>Error / Rechazo</option>
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
    <div class='notif-card card mb-2 {{ $notif->leida ? "opacity-75" : "border-start border-danger border-3" }}'
         data-id='{{ $notif->id }}' data-tipo='{{ $notif->tipo }}'>
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

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// Filtros
document.getElementById('filtroTipo').addEventListener('change', filtrar);
document.getElementById('filtroEstado').addEventListener('change', filtrar);
function filtrar(){
  const tipo   = document.getElementById('filtroTipo').value;
  const estado = document.getElementById('filtroEstado').value;
  document.querySelectorAll('.notif-card').forEach(c => {
    const matchTipo   = !tipo   || c.dataset.tipo === tipo;
    const matchEstado = !estado || (estado==='leida' ? !c.classList.contains('border-danger') : c.classList.contains('border-danger'));
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
