@extends('layouts.app')

@section('title', 'Gestión de Usuarios - SAMS')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap');

    .usuarios-wrapper {
        font-family: 'DM Sans', sans-serif;
        background: #ffffff;
        min-height: 100vh;
        padding: 2.5rem 2rem;
    }

    .usuarios-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .usuarios-heading {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .usuarios-icon-wrap {
        background: #800000;
        border-radius: 14px;
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 16px rgba(128,0,0,.25);
    }

    .usuarios-icon-wrap i { color: #fff; font-size: 1.7rem; }

    .usuarios-title {
        font-family: 'DM Serif Display', serif;
        font-size: 2rem;
        color: #2a1a1a;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .usuarios-subtitle { font-size: 0.85rem; color: #7a6060; margin: 2px 0 0; }

    .btn-registrar {
        background: #737373;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0.7rem 1.5rem;
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 14px #737373;
        text-decoration: none;
    }

    .btn-registrar:hover {
        background: #737373;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px #737373;
        color: #fff;
    }

    .alert-usuarios {
        border-radius: 10px;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
        font-weight: 500;
        animation: fadeInDown 0.4s ease;
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .alert-success-u { background: #d4edda; color: #155724; }
    .alert-danger-u  { background: #f8d7da; color: #721c24; }

    .alert-fade-out {
        opacity: 0;
        transform: translateY(-10px);
    }

    @keyframes fadeInDown {
        from { opacity:0; transform:translateY(-10px); }
        to   { opacity:1; transform:translateY(0); }
    }

    @keyframes zoomIn {
        from { opacity:0; transform:scale(.9); }
        to   { opacity:1; transform:scale(1); }
    }

    .card-usuarios {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 30px rgba(0,0,0,.07);
        overflow: hidden;
    }

    .card-toolbar {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f0e8e8;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
    }

    .search-box input {
        width: 100%;
        padding: 0.6rem 1rem 0.6rem 2.5rem;
        border: 1.5px solid #e8dede;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        color: #333;
        transition: border-color 0.2s;
        outline: none;
        background: #faf8f8;
    }

    .search-box input:focus { border-color: #737373; background: #fff; }

    .search-box .bi-search {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #a08080;
        font-size: 0.95rem;
    }

    .filter-select {
        padding: 0.6rem 0.9rem;
        border: 1.5px solid #e8dede;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        color: #555;
        background: #faf8f8;
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .filter-select:focus { border-color: #737373; }

    .table-usuarios {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .table-usuarios thead th {
        background: #f8f9fa;
        color: black;
        font-weight: 600;
        padding: 0.9rem 1.25rem;
        text-align: left;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .table-usuarios tbody tr {
        border-bottom: 1px solid #f3eded;
        transition: background 0.15s;
    }

    .table-usuarios tbody tr:hover { background: #ffff; }

    .table-usuarios tbody td {
        padding: 0.9rem 1.25rem;
        color: #3a2a2a;
        vertical-align: middle;
    }

    .badge-proceso {
        background: #fff;
        color: #000000;
        border-radius: 20px;
        padding: 0.3rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }

    .badge-depto {
        background: #fff;
        color: #000;
        border-radius: 20px;
        padding: 0.3rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
        white-space: nowrap;
    }

    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #737373, #737373);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        margin-right: 0.6rem;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(128,0,0,.2);
    }

    .user-cell { display: flex; align-items: center; }
    .user-info .user-name { font-weight: 600; color: #2a1a1a; }
    .user-info .user-email { font-size: 0.8rem; color: #9a7070; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 20px;
        padding: 0.3rem 0.8rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-active   { background: #e6f9ed; color: #1a7a3c; }
    .status-inactive { background: #f3f3f3; color: #888; }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-active .status-dot   { background: #28a745; }
    .status-inactive .status-dot { background: #aaa; }

    .btn-accion {
        border: none;
        border-radius: 7px;
        padding: 0.45rem 0.9rem;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s;
    }

    .btn-desactivar {
        background: #fff0f0;
        color: #800000;
        border: 1.5px solid #f5c6cb;
    }

    .btn-desactivar:hover {
        background: #fff0f0;
        color: #800000;
        border-color: #f5c6cb;
    }

    .btn-activar {
        background: #f0fff4;
        color: #1a7a3c;
        border: 1.5px solid #c3e6cb;
    }

    .btn-activar:hover {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }

    .btn-editar {
        background: #f0f4ff;
        color: #1a3acc;
        border: 1.5px solid #c0ccf5;
        margin-left: 0.4rem;
    }

    .btn-editar:hover {
        background: #f0f4ff;
        color: #1a3acc;
        border-color: #c0ccf5;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #b08080;
    }

    .empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.4; display: block; }
    .empty-state p { margin: 0; font-size: 1rem; }

    .card-footer-u {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f0e8e8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #737373;
        font-size: 0.85rem;
    }

    /* Modal registrar */
    .modal-content {
        border-radius: 16px;
        border: none;
        font-family: 'DM Sans', sans-serif;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
    }

    .modal-header {
        background: #ffffff;
        color: #000000;
        border-radius: 16px 16px 0 0;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    .modal-title { font-family: 'DM Serif Display', serif; font-size: 1.3rem; }
    .btn-close-white { filter: brightness(0) invert(1); }
    .modal-body { padding: 2rem 1.5rem; }
    .modal-form-group { margin-bottom: 1.25rem; }

    .modal-label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 0.5rem;
    }

    .modal-label i { color: #800000; margin-right: 0.4rem; }

    .modal-input, .modal-select {
        width: 100%;
        padding: 0.7rem 1rem;
        border: 1.5px solid #e0d4d4;
        border-radius: 9px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.95rem;
        color: #333;
        background: #faf8f8;
        transition: border-color 0.2s;
        outline: none;
    }

    .modal-input:focus, .modal-select:focus { border-color: #737373; background: #fff; }
    .modal-input.is-invalid, .modal-select.is-invalid { border-color: #dc3545; }
    .field-err { color: #dc3545; font-size: 0.8rem; margin-top: 0.3rem; }

    .strength-bar { height: 5px; border-radius: 3px; background: #eee; margin-top: 0.5rem; overflow: hidden; }
    .strength-fill { height: 100%; width: 0; border-radius: 3px; transition: all 0.3s; }
    .strength-label { font-size: 0.78rem; color: #888; margin-top: 0.3rem; }

    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f0e8e8; gap: 0.75rem; }

    .btn-modal-cancel {
        border: 1.5px solid #ddd;
        background: #737373;
        color: #fff;
        border-radius: 9px;
        padding: 0.65rem 1.25rem;
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover { border-color: #aaa; color: #fff; }

    .btn-modal-submit {
        background: #800000;
        color: #fff;
        border: none;
        border-radius: 9px;
        padding: 0.65rem 1.5rem;
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s, box-shadow 0.2s;
    }

    .btn-modal-submit:hover { background: #800000; box-shadow: 0 4px 14px rgba(128,0,0,.3); }

    @media (max-width: 768px) {
        .usuarios-wrapper { padding: 1.5rem 1rem; }
        .usuarios-title { font-size: 1.5rem; }
        .table-usuarios thead { display: none; }
        .table-usuarios tbody tr { display: block; padding: 1rem; border-bottom: 2px solid #f0e8e8; }
        .table-usuarios tbody td { display: flex; justify-content: space-between; align-items: center; padding: 0.4rem 0; border: none; }
        .table-usuarios tbody td::before { content: attr(data-label); font-weight: 600; color: #800000; font-size: 0.8rem; }
    }

    /* Gestión de Procesos */
    .btn-gestionar-procesos {
        background: #737373;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0.7rem 1.5rem;
        font-family: 'DM Sans', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 14px #737373;
        text-decoration: none;
    }
    .btn-gestionar-procesos:hover {
        background: #737373;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px #737373;
        color: #fff;
    }
    
    /* OVERLAY DE PROCESOS - Z-INDEX CORREGIDO */
    #overlayProcesos {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.55);
        align-items: center;
        justify-content: center;
        z-index: 1008;
    }
    
    .pg-overlay-panel {
        background: #fff;
        border-radius: 18px;
        width: 92%;
        max-width: 660px;
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        animation: zoomIn .18s ease;
        overflow: hidden;
        position: relative;
        z-index: 9999;
    }
    
    .pg-header {
        background: #ffffff; 
        color: #000000; 
        padding: 1.25rem 1.5rem;
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        flex-shrink: 0;
        font-family: 'DM Serif Display', serif;
        border-bottom: 1px solid #b5b2b2
    }
    .pg-body { overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1; }
    .pg-grupo {
        border: 1.5px solid #f0e8e8; border-radius: 14px;
        margin-bottom: 1rem; overflow: hidden;
    }
    .pg-grupo-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .85rem 1.1rem; background: #ffffff; cursor: pointer; user-select: none;
        font-family: 'DM Serif Display', serif; color: #0d0d0d; gap: .5rem;
    }
    .pg-deptos { padding: .5rem 1.1rem .75rem; display: none; }
    .pg-deptos.open { display: block; }
    .pg-depto-item {
        display: flex; align-items: center; justify-content: space-between;
        background: #faf8f8; border: 1px solid #f0e8e8; border-radius: 8px;
        padding: .5rem .85rem; margin-bottom: .35rem;
    }
    .pg-add-form {
        display: none; margin-top: .6rem; padding: .65rem;
        background: #fff; border: 1.5px dashed #f0c0c0; border-radius: 9px;
        gap: .5rem; align-items: center;
    }
    .pg-add-form.open { display: flex; }
    .pg-add-form input {
        flex: 1; padding: .5rem .85rem; border: 1.5px solid #e0d4d4; border-radius: 7px;
        font-family: 'DM Sans', sans-serif; font-size: .88rem; outline: none; color: #333;
    }
    .pg-add-form input:focus { border-color: #000000; }
    .pg-chevron { transition: transform .2s; color: #a08080; font-size: .85rem; }
    .pg-chevron.open { transform: rotate(180deg); }
    
</style>
@endpush

@section('content')
<div class="usuarios-wrapper">

    {{-- SOLO DATOS OCULTOS PARA SWEETALERT (activar/desactivar y procesos) --}}
    @if(session('estado_success'))
        <div id="estado-success" data-message="{{ session('estado_success') }}" style="display: none;"></div>
    @endif
    @if(session('estado_error'))
        <div id="estado-error" data-message="{{ session('estado_error') }}" style="display: none;"></div>
    @endif
    @if(session('proceso_success'))
        <div id="proceso-success" data-message="{{ session('proceso_success') }}" style="display: none;"></div>
    @endif
    @if(session('proceso_error'))
        <div id="proceso-error" data-message="{{ session('proceso_error') }}" style="display: none;"></div>
    @endif

    {{-- NO HAY MENSAJES DE BOOTSTRAP AQUÍ --}}

    <div class="usuarios-header">
        <div class="usuarios-heading">
            <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                <h1 class="h3 mb-2" style="color: #7c3aed; cursor: pointer;">
                    <i class="bi bi-people-fill me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                    Usuarios
                </h1>
            </a>
        </div>

        <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
            <button class="btn-gestionar-procesos" onclick="abrirOverlayProcesos()">
                <i class="bi bi-diagram-3-fill"></i> Gestionar Procesos
            </button>
            @endif
            <button class="btn-registrar" data-bs-toggle="modal" data-bs-target="#modalRegistrar">
                <i class="bi bi-person-plus-fill"></i> Registrar Usuario
            </button>
        </div>
    </div>

    <div class="card-usuarios">
        <div class="card-toolbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por nombre, correo o departamento…">
            </div>
            <select class="filter-select" id="filterProceso">
                <option value="">Todos los procesos</option>
                <option value="Planeación">Planeación</option>
                <option value="Preinscripción">Preinscripción</option>
                <option value="Inscripción">Inscripción</option>
                <option value="Reinscripción">Reinscripción</option>
                <option value="Titulación">Titulación</option>
                <option value="Enseñanza/Aprendizaje">Enseñanza/Aprendizaje</option>
                <option value="Contratación o Control de Personal">Contratación de Personal</option>
                <option value="Vinculación">Vinculación</option>
                <option value="TI">TI</option>
                <option value="Gestión de Recursos">Gestión de Recursos</option>
                <option value="Laboratorios y Talleres">Laboratorios y Talleres</option>
                <option value="Centro de Información">Centro de Información</option>
            </select>
            <select class="filter-select" id="filterEstado">
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
            </select>
        </div>

        <div style="overflow-x:auto;">
            <table class="table-usuarios">
                <thead>
                    <tr>
                        <th>Proceso</th>
                        <th>Departamento</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyUsuarios">
                    @forelse($usuarios as $usuario)
                    <tr
                        data-nombre="{{ strtolower($usuario->name) }}"
                        data-email="{{ strtolower($usuario->email) }}"
                        data-depto="{{ strtolower($usuario->departamento) }}"
                        data-proceso="{{ $usuario->proceso }}"
                        data-estado="{{ $usuario->is_active ? 'activo' : 'inactivo' }}"
                    >
                        <td data-label="Proceso">
                            <span class="badge-proceso">{{ $usuario->proceso ?? '—' }}</span>
                        </td>
                        <td data-label="Departamento">
                            <span class="badge-depto">{{ $usuario->departamento ?? '—' }}</span>
                        </td>
                        <td data-label="Usuario">
                            <div class="user-cell">
                                <div class="avatar-circle">{{ strtoupper(substr($usuario->name, 0, 1)) }}</div>
                                <div class="user-info">
                                    <div class="user-name">
                                        {{ $usuario->name }}
                                        @if($usuario->role === 'admin')
                                            <span style="background:#fff3e0;color:#cc5500;border:1px solid #f5c6a0;border-radius:20px;padding:.15rem .6rem;font-size:.7rem;font-weight:700;margin-left:.4rem;vertical-align:middle;">Admin</span>
                                        @endif
                                    </div>
                                    <div class="user-email">{{ $usuario->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Estado">
                            @if($usuario->is_active)
                                <span class="status-badge status-active">
                                    <span class="status-dot"></span> Activo
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    <span class="status-dot"></span> Inactivo
                                </span>
                            @endif
                        </td>
                        <td data-label="Acciones">
                            @if(auth()->user()->isSuperAdmin())
                                <form
                                    method="POST"
                                    action="{{ route('admin.usuarios.estado', $usuario->id) }}"
                                    onsubmit="return confirmarAccion(event, '{{ addslashes($usuario->name) }}', '{{ $usuario->is_active ? 'desactivar' : 'activar' }}')"
                                    style="display:inline;"
                                >
                                    @csrf
                                    @method('PATCH')
                                    @if($usuario->is_active)
                                        <button type="submit" class="btn-accion btn-desactivar">
                                            <i class="bi bi-person-x"></i> Desactivar
                                        </button>
                                    @else
                                        <button type="submit" class="btn-accion btn-activar">
                                            <i class="bi bi-person-check"></i> Activar
                                        </button>
                                    @endif
                                </form>
                                <button type="button" class="btn-accion btn-editar"
                                    data-id="{{ $usuario->id }}"
                                    data-nombre="{{ $usuario->name }}"
                                    data-email="{{ $usuario->email }}"
                                    data-role="{{ $usuario->role }}"
                                    data-url="{{ route('admin.usuarios.updateAdmin', $usuario->id) }}">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            @else
                                <span style="color:#aaa;font-size:.82rem;font-style:italic;">Sin permisos</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <p>No hay usuarios registrados aún.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer-u">
            <span id="countLabel">
                Mostrando <strong>{{ $usuarios->count() }}</strong> usuario(s)
            </span>
            <span>
                Activos: <strong>{{ $usuarios->where('is_active', true)->count() }}</strong> &nbsp;|&nbsp;
                Inactivos: <strong>{{ $usuarios->where('is_active', false)->count() }}</strong>
            </span>
        </div>
    </div>

</div>

{{-- OVERLAY EDITAR --}}
<div id="overlayEditarAdmin" onclick="if(event.target===this)cerrarOverlayEditar()" style="
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.55); z-index:9999;
    align-items:center; justify-content:center;
">
    <div style="
        background:#fff; border-radius:16px; padding:2.5rem 2rem;
        max-width:480px; width:90%; text-align:left;
        box-shadow:0 20px 60px rgba(0,0,0,.3);
        animation:zoomIn .18s ease;
    ">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
            <div style="width:48px;height:48px;border-radius:50%;background:#fff;color:#1a3acc;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                <i class="bi bi-pencil"></i>
            </div>
            <div>
                <h5 style="font-family:'DM Serif Display',serif;font-size:1.3rem;margin:0;" id="editAdminTitle">Editar Usuario</h5>
                <small style="color:#888;" id="editAdminSubtitle"></small>
            </div>
        </div>

        <form method="POST" id="formEditarAdmin" action="">
            @csrf
            @method('PATCH')

            <div class="modal-form-group">
                <label class="modal-label"><i class="bi bi-person"></i> Nombre</label>
                <input type="text" name="name" id="editAdminNombre" class="modal-input" required>
            </div>

            <div class="modal-form-group">
                <label class="modal-label"><i class="bi bi-envelope"></i> Correo Electrónico</label>
                <input type="email" name="email" id="editAdminEmail" class="modal-input" required>
            </div>

            <div class="modal-form-group">
                <label class="modal-label"><i class="bi bi-lock"></i> Nueva Contraseña <small style="color:#aaa;font-weight:400;">(dejar vacío para no cambiar)</small></label>
                <div style="position:relative;">
                    <input type="password" name="password" id="editAdminPwd" class="modal-input" placeholder="Mínimo 8 caracteres" style="padding-right:2.5rem;">
                    <i class="bi bi-eye" id="toggleEditPwd" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                </div>
            </div>

            <div class="modal-form-group">
                <label class="modal-label"><i class="bi bi-lock-fill"></i> Confirmar Contraseña</label>
                <div style="position:relative;">
                    <input type="password" name="password_confirmation" id="editAdminPwdConf" class="modal-input" placeholder="Repite la contraseña" style="padding-right:2.5rem;">
                    <i class="bi bi-eye" id="toggleEditPwdConf" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                </div>
                <div id="editMatchText" style="font-size:.78rem;color:#888;margin-top:.3rem;">Las contraseñas deben coincidir</div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:1rem;margin-top:1.5rem;">
                <button type="button" class="btn-modal-cancel" onclick="cerrarOverlayEditar()">Cancelar</button>
                <button type="submit" class="btn-modal-submit">
                    <i class="bi bi-check-lg"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: Registrar Usuario --}}
<div class="modal fade" id="modalRegistrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
       <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #b5b2b2;">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2" style="color: #000000;"></i> Nuevo Usuario</h5>
            </div>

            <form method="POST" action="{{ route('admin.usuarios.store') }}">
                @csrf
                <div class="modal-body">

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-left: 4px solid #dc3545; border-radius: 8px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-person"></i> Nombre Completo</label>
                        <input type="text" name="name" class="modal-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Ej. Juan Pérez" required>
                        @error('name')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-envelope"></i> Correo Electrónico</label>
                        <input type="email" name="email" class="modal-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="ejemplo@uptex.edu.mx" required>
                        @error('email')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-gear"></i> Proceso</label>
                        <select name="proceso" id="modalProceso" class="modal-select @error('proceso') is-invalid @enderror" required>
                            <option value="">Selecciona un proceso</option>
                            <option value="Planeación">Planeación</option>
                            <option value="Preinscripción">Preinscripción</option>
                            <option value="Inscripción">Inscripción</option>
                            <option value="Reinscripción">Reinscripción</option>
                            <option value="Titulación">Titulación</option>
                            <option value="Enseñanza/Aprendizaje">Enseñanza/Aprendizaje</option>
                            <option value="Contratación o Control de Personal">Contratación o Control de Personal</option>
                            <option value="Vinculación">Vinculación</option>
                            <option value="TI">TI</option>
                            <option value="Gestión de Recursos">Gestión de Recursos</option>
                            <option value="Laboratorios y Talleres">Laboratorios y Talleres</option>
                            <option value="Centro de Información">Centro de Información</option>
                            @isset($procesosCustom)
                                @php $pgrouped = $procesosCustom->groupBy('proceso'); @endphp
                                @if($pgrouped->count())
                                    <option disabled style="color:#aaa;font-size:.8rem;">── Procesos personalizados ──</option>
                                    @foreach($pgrouped as $pnombre => $pdeptos)
                                        <option value="{{ $pnombre }}"
                                            data-deptos-json="{{ json_encode($pdeptos->pluck('departamento')->values()) }}">
                                            {{ $pnombre }}
                                        </option>
                                    @endforeach
                                @endif
                            @endisset
                            <option value="__otro__">➕ Otro (nuevo proceso)</option>
                        </select>
                        @error('proceso')<div class="field-err">{{ $message }}</div>@enderror

                        <div id="nuevoProcesoWrap" style="display:none; margin-top:.75rem;">
                            <div class="modal-form-group" style="margin-bottom:.75rem;">
                                <label class="modal-label"><i class="bi bi-plus-circle"></i> Nombre del nuevo proceso</label>
                                <input type="text" name="nuevo_proceso" id="nuevoProceso" class="modal-input">
                            </div>
                            <div class="modal-form-group" style="margin-bottom:0;">
                                <label class="modal-label"><i class="bi bi-building-add"></i> Departamento</label>
                                <input type="text" name="nuevo_departamento" id="nuevoDepartamento" class="modal-input">
                            </div>
                        </div>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-building"></i> Departamento</label>
                        {{-- CAMBIO 1: Se agregaron data-proceso a cada option y procesosCustom --}}
                        <select name="departamento" id="modalDepartamento" class="modal-select @error('departamento') is-invalid @enderror" required>
                            <option value="">Selecciona un departamento</option>
                            <option value="Rectoría" data-proceso="Planeación" {{ old('departamento')=='Rectoría'?'selected':'' }}>Rectoría</option>
                            <option value="Dirección Académica" data-proceso="Planeación,Enseñanza/Aprendizaje" {{ old('departamento')=='Dirección Académica'?'selected':'' }}>Dirección Académica</option>
                            <option value="Dirección de Administración y Finanzas" data-proceso="Planeación" {{ old('departamento')=='Dirección de Administración y Finanzas'?'selected':'' }}>Dirección de Administración y Finanzas</option>
                            <option value="Servicios Escolares" data-proceso="Preinscripción,Inscripción,Reinscripción,Titulación" {{ old('departamento')=='Servicios Escolares'?'selected':'' }}>Servicios Escolares</option>
                            <option value="Recursos Humanos" data-proceso="Contratación o Control de Personal" {{ old('departamento')=='Recursos Humanos'?'selected':'' }}>Recursos Humanos</option>
                            <option value="Vinculación" data-proceso="Vinculación" {{ old('departamento')=='Vinculación'?'selected':'' }}>Vinculación</option>
                            <option value="Sistemas Computacionales" data-proceso="TI" {{ old('departamento')=='Sistemas Computacionales'?'selected':'' }}>Sistemas Computacionales</option>
                            <option value="Recursos Financieros" data-proceso="Gestión de Recursos" {{ old('departamento')=='Recursos Financieros'?'selected':'' }}>Recursos Financieros</option>
                            <option value="Almacén" data-proceso="Gestión de Recursos" {{ old('departamento')=='Almacén'?'selected':'' }}>Almacén</option>
                            <option value="Encargado/a de Laboratorios" data-proceso="Laboratorios y Talleres" {{ old('departamento')=='Encargado/a de Laboratorios'?'selected':'' }}>Encargado/a de Laboratorios</option>
                            <option value="Biblioteca" data-proceso="Centro de Información" {{ old('departamento')=='Biblioteca'?'selected':'' }}>Biblioteca</option>
                            @isset($procesosCustom)
                                @foreach($procesosCustom as $pc)
                                    <option value="{{ $pc->departamento }}"
                                        data-proceso="{{ $pc->proceso }}"
                                        {{ old('departamento')==$pc->departamento?'selected':'' }}>
                                        {{ $pc->departamento }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                        @error('departamento')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-lock"></i> Contraseña</label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="modalPassword"
                                class="modal-input @error('password') is-invalid @enderror"
                                placeholder="Mínimo 8 caracteres" required style="padding-right:2.5rem;">
                            <i class="bi bi-eye" id="toggleModalPwd"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                        </div>
                        <div class="strength-bar"><div class="strength-fill" id="modalStrengthFill"></div></div>
                        <div class="strength-label" id="modalStrengthText">Seguridad de la contraseña</div>
                        @error('password')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-lock-fill"></i> Confirmar Contraseña</label>
                        <div style="position:relative;">
                            <input type="password" name="password_confirmation" id="modalPasswordConfirm"
                                class="modal-input" placeholder="Repite la contraseña" required style="padding-right:2.5rem;">
                            <i class="bi bi-eye" id="toggleModalPwdConfirm"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                        </div>
                        <div class="strength-label" id="modalMatchText">Las contraseñas deben coincidir</div>
                    </div>

                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-modal-submit">
                        <i class="bi bi-person-plus"></i> Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- OVERLAY: GESTIÓN DE PROCESOS --}}
@if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
<div id="overlayProcesos"
    onclick="if(event.target===this)cerrarOverlayProcesos()"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);align-items:center;justify-content:center;">
    <div class="pg-overlay-panel">
        <div class="pg-header">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <i class="bi bi-diagram-3-fill" style="font-size:1.3rem;"></i>
                <div>
                    <div style="font-size:1.2rem;">Gestión de Procesos</div>
                    <small style="opacity:.8;font-size:.8rem;">Procesos personalizados</small>
                </div>
            </div>
        </div>
        <div class="pg-body">
            @isset($procesosCustom)
                @php $pgAgrup = $procesosCustom->groupBy('proceso'); @endphp
                @if($pgAgrup->count())
                    <p style="font-size:.82rem;color:#000;margin-bottom:1rem;">
                        <i class="bi bi-info-circle"></i> Haz clic en un proceso para ver sus departamentos.
                    </p>
                    @foreach($pgAgrup as $pgNombre => $pgDeptos)
                    <div class="pg-grupo">
                        <div class="pg-grupo-header" onclick="pgToggle('pg-{{ $loop->index }}')">
                            <div style="display:flex;align-items:center;gap:.5rem;flex:1;">
                                <i class="bi bi-diagram-3"></i>
                                {{ $pgNombre }}
                                <span style="background:#737373;color:#fff;border-radius:20px;padding:.1rem .5rem;font-size:.72rem;font-weight:300;">
                                    {{ $pgDeptos->count() }} depto{{ $pgDeptos->count()!=1?'s':'' }}
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:.4rem;" onclick="event.stopPropagation()">
                                <button type="button"
                                    style="background:#737373;color:#fff;border:none;border-radius:7px;padding:.3rem .65rem;font-size:.78rem;cursor:pointer;"
                                    onclick="pgToggleAdd('pg-add-{{ $loop->index }}','pg-{{ $loop->index }}')">
                                    <i class="bi bi-plus-circle"></i> Agregar depto
                                </button>
                                <form method="POST" action="{{ route('admin.procesos.destroyProceso') }}"
                                    onsubmit="return confirmarEliminarProceso(event, '{{ addslashes($pgNombre) }}')" 
                                    style="margin:0;" id="form-delete-proceso-{{ $loop->index }}">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="proceso" value="{{ $pgNombre }}">
                                    <button type="submit"
                                        style="background:#dc3545;color:#fff;border:none;border-radius:7px;padding:.3rem .65rem;font-size:.78rem;cursor:pointer;">
                                        <i class="bi bi-trash3"></i> Eliminar
                                    </button>
                                </form>
                                <i class="bi bi-chevron-down pg-chevron" id="pgicon-{{ $loop->index }}"></i>
                            </div>
                        </div>

                        <div class="pg-deptos" id="pg-{{ $loop->index }}">
                            @foreach($pgDeptos as $pgD)
                            <div class="pg-depto-item">
                                <span style="font-size:.88rem;color:#3a2a2a;">
                                    <i class="bi bi-building" style="color:#000000;"></i>
                                    {{ $pgD->departamento }}
                                </span>
                                <form method="POST" action="{{ route('admin.procesos.destroy', $pgD->id) }}"
                                    onsubmit="return confirmarEliminarDepartamento(event, '{{ addslashes($pgD->departamento) }}', '{{ $pgD->id }}')" 
                                    style="margin:0;" id="form-delete-depto-{{ $pgD->id }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#dc3545; cursor:pointer;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @endforeach

                            <form method="POST" action="{{ route('admin.procesos.addDepartamento') }}"
                                class="pg-add-form" id="pg-add-{{ $loop->index }}">
                                @csrf
                                <input type="hidden" name="proceso" value="{{ $pgNombre }}">
                                <input type="text" name="departamento" placeholder="Nuevo departamento…" required>
                                <button type="submit" style="background:#dc3545;color:#fff;border:none;border-radius:7px;padding:.3rem .65rem;">
                                    Agregar
                                </button>
                                <button type="button" style="background:#6c757d;color:#fff;border:none;border-radius:7px;padding:.3rem .65rem;"
                                    onclick="pgToggleAdd('pg-add-{{ $loop->index }}',null)">Cancelar</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:3rem 1rem;color:#666;">
                        <i class="bi bi-diagram-3" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.5;"></i>
                        <p>No hay procesos personalizados.</p>
                    </div>
                @endif
            @endisset
        </div>
        <div style="padding:1rem 1.5rem;border-top:1px solid #f0e8e8;display:flex;justify-content:flex-end;">
            <button onclick="cerrarOverlayProcesos()" class="btn-modal-cancel">Cerrar</button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // SWEETALERT - Para mensajes de activar/desactivar
    const estadoSuccess = document.getElementById('estado-success');
    if (estadoSuccess && estadoSuccess.getAttribute('data-message')) {
        Swal.fire({
            title: '¡Éxito!',
            text: estadoSuccess.getAttribute('data-message'),
            icon: 'success',
            iconHtml: '<i class="bi bi-check-circle-fill" style="font-size: 4rem; color: #28a745;"></i>',
            width: '600px',
            padding: '2rem',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    const estadoError = document.getElementById('estado-error');
    if (estadoError && estadoError.getAttribute('data-message')) {
        Swal.fire({
            title: '¡Error!',
            text: estadoError.getAttribute('data-message'),
            icon: 'error',
            iconHtml: '<i class="bi bi-exclamation-triangle-fill" style="font-size: 4rem; color: #dc3545;"></i>',
            width: '600px',
            padding: '2rem',
            confirmButtonColor: '#800000',
            confirmButtonText: 'Aceptar',
            timer: 3000,
            timerProgressBar: true
        });
    }

    // SWEETALERT - Para mensajes de procesos
    const procesoSuccess = document.getElementById('proceso-success');
    if (procesoSuccess && procesoSuccess.getAttribute('data-message')) {
        Swal.fire({
            title: '¡Éxito!',
            text: procesoSuccess.getAttribute('data-message'),
            icon: 'success',
            iconHtml: '<i class="bi bi-check-circle-fill" style="font-size: 4rem; color: #28a745;"></i>',
            width: '600px',
            padding: '2rem',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    const procesoError = document.getElementById('proceso-error');
    if (procesoError && procesoError.getAttribute('data-message')) {
        Swal.fire({
            title: '¡Error!',
            text: procesoError.getAttribute('data-message'),
            icon: 'error',
            iconHtml: '<i class="bi bi-exclamation-triangle-fill" style="font-size: 4rem; color: #dc3545;"></i>',
            width: '600px',
            padding: '2rem',
            confirmButtonColor: '#800000',
            confirmButtonText: 'Aceptar',
            timer: 3000,
            timerProgressBar: true
        });
    }

    @if($errors->any())
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRegistrar')).show();
    @endif

    // Mostrar/ocultar campo de nuevo proceso
    const modalProceso = document.getElementById('modalProceso');
    const nuevoProcesoWrap = document.getElementById('nuevoProcesoWrap');
    const modalDepartamento = document.getElementById('modalDepartamento');
    const deptoGroup = modalDepartamento.closest('.modal-form-group');

    function toggleNuevoProceso() {
        if (modalProceso.value === '__otro__') {
            nuevoProcesoWrap.style.display = 'block';
            deptoGroup.style.display = 'none';
            document.getElementById('nuevoProceso').required = true;
            document.getElementById('nuevoDepartamento').required = true;
            modalDepartamento.required = false;
        } else {
            nuevoProcesoWrap.style.display = 'none';
            deptoGroup.style.display = 'block';
            document.getElementById('nuevoProceso').required = false;
            document.getElementById('nuevoDepartamento').required = false;
            modalDepartamento.required = true;
        }
    }

    modalProceso.addEventListener('change', toggleNuevoProceso);
    toggleNuevoProceso();

    // CAMBIO 2: Filtrar departamentos según proceso seleccionado usando data-proceso
    function filterModalDepto() {
        const sel = modalProceso.value;
        if (sel === '__otro__' || !sel) return;
        
        const selectedOpt = modalProceso.options[modalProceso.selectedIndex];
        const deptosJsonRaw = selectedOpt ? selectedOpt.getAttribute('data-deptos-json') : null;
        const deptosJson = deptosJsonRaw ? JSON.parse(deptosJsonRaw) : null;

        // Limpiar selección actual
        modalDepartamento.value = '';
        
        modalDepartamento.querySelectorAll('option').forEach(opt => {
            if (!opt.value) return;
            
            if (deptosJson) {
                // Si es un proceso personalizado, mostrar solo sus departamentos
                opt.style.display = deptosJson.includes(opt.value) ? '' : 'none';
            } else {
                // Si es un proceso estándar, filtrar por data-proceso
                const allowed = (opt.getAttribute('data-proceso') || '').split(',');
                opt.style.display = (!sel || allowed.includes(sel)) ? '' : 'none';
            }
        });

        // Si solo hay un departamento disponible, seleccionarlo automáticamente
        const opcionesVisibles = Array.from(modalDepartamento.options).filter(opt => opt.value && opt.style.display !== 'none');
        if (opcionesVisibles.length === 1) {
            modalDepartamento.value = opcionesVisibles[0].value;
        }
    }

    modalProceso.addEventListener('change', filterModalDepto);
    
    // Ejecutar filtro al cargar si hay un proceso seleccionado
    if (modalProceso.value && modalProceso.value !== '__otro__') {
        filterModalDepto();
    }

    // Toggle contraseñas
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input && icon) {
            icon.addEventListener('click', function() {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }
    }

    togglePassword('modalPassword', 'toggleModalPwd');
    togglePassword('modalPasswordConfirm', 'toggleModalPwdConfirm');
    togglePassword('editAdminPwd', 'toggleEditPwd');
    togglePassword('editAdminPwdConf', 'toggleEditPwdConf');

    // Medidor de seguridad de contraseña
    const pwdInput = document.getElementById('modalPassword');
    const pwdConfirm = document.getElementById('modalPasswordConfirm');
    const strengthFill = document.getElementById('modalStrengthFill');
    const strengthText = document.getElementById('modalStrengthText');
    const matchText = document.getElementById('modalMatchText');

    if (pwdInput) {
        pwdInput.addEventListener('input', function() {
            const pwd = this.value;
            let strength = 0;
            
            if (pwd.length >= 8) strength++;
            if (pwd.length >= 12) strength++;
            if (/[a-z]/.test(pwd)) strength++;
            if (/[A-Z]/.test(pwd)) strength++;
            if (/[0-9]/.test(pwd)) strength++;
            if (/[^a-zA-Z0-9]/.test(pwd)) strength++;

            const strengthMap = {
                0: { width: 5, color: '#dc3545', text: 'Muy débil' },
                1: { width: 15, color: '#dc3545', text: 'Débil' },
                2: { width: 35, color: '#ffc107', text: 'Regular' },
                3: { width: 55, color: '#ffc107', text: 'Regular' },
                4: { width: 75, color: '#28a745', text: 'Buena' },
                5: { width: 90, color: '#28a745', text: 'Fuerte' },
                6: { width: 100, color: '#28a745', text: 'Muy fuerte' }
            };

            const s = Math.min(strength, 6);
            strengthFill.style.width = strengthMap[s].width + '%';
            strengthFill.style.backgroundColor = strengthMap[s].color;
            strengthText.textContent = strengthMap[s].text;
            strengthText.style.color = strengthMap[s].color;
            
            if (pwdConfirm) checkPasswordMatch();
        });
    }

    if (pwdConfirm) {
        pwdConfirm.addEventListener('input', checkPasswordMatch);
    }

    function checkPasswordMatch() {
        if (!pwdConfirm || !pwdInput) return;
        
        if (!pwdConfirm.value) {
            matchText.textContent = 'Las contraseñas deben coincidir';
            matchText.style.color = '#888';
        } else if (pwdInput.value === pwdConfirm.value) {
            matchText.textContent = '✓ Las contraseñas coinciden';
            matchText.style.color = '#28a745';
        } else {
            matchText.textContent = '✗ Las contraseñas no coinciden';
            matchText.style.color = '#dc3545';
        }
    }

    // Filtros de búsqueda
    const searchInput = document.getElementById('searchInput');
    const filterProceso = document.getElementById('filterProceso');
    const filterEstado = document.getElementById('filterEstado');
    const tbody = document.getElementById('tbodyUsuarios');
    const countLabel = document.getElementById('countLabel');

    function aplicarFiltros() {
        if (!tbody) return;
        
        const busqueda = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const proceso = filterProceso ? filterProceso.value : '';
        const estado = filterEstado ? filterEstado.value : '';
        let visibles = 0;

        tbody.querySelectorAll('tr[data-nombre]').forEach(row => {
            const nombre = row.dataset.nombre || '';
            const email = row.dataset.email || '';
            const depto = row.dataset.depto || '';
            
            const coincideBusqueda = !busqueda || 
                nombre.includes(busqueda) || 
                email.includes(busqueda) || 
                depto.includes(busqueda);
            
            const coincideProceso = !proceso || row.dataset.proceso === proceso;
            const coincideEstado = !estado || row.dataset.estado === estado;
            
            const visible = coincideBusqueda && coincideProceso && coincideEstado;
            row.style.display = visible ? '' : 'none';
            if (visible) visibles++;
        });

        if (countLabel) {
            countLabel.innerHTML = `Mostrando <strong>${visibles}</strong> usuario(s)`;
        }

        const emptyRow = document.getElementById('noResultsRow');
        if (visibles === 0) {
            if (!emptyRow && tbody) {
                const newEmptyRow = document.createElement('tr');
                newEmptyRow.id = 'noResultsRow';
                newEmptyRow.innerHTML = `<td colspan="5"><div class="empty-state">
                    <i class="bi bi-search"></i><p>No se encontraron usuarios.</p></div>`;
                tbody.appendChild(newEmptyRow);
            }
        } else if (emptyRow) {
            emptyRow.remove();
        }
    }

    if (searchInput) searchInput.addEventListener('input', aplicarFiltros);
    if (filterProceso) filterProceso.addEventListener('change', aplicarFiltros);
    if (filterEstado) filterEstado.addEventListener('change', aplicarFiltros);

    // Editar usuario
    const tbodyUsuarios = document.getElementById('tbodyUsuarios');
    if (tbodyUsuarios) {
        tbodyUsuarios.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-editar');
            if (!btn) return;

            document.getElementById('editAdminTitle').textContent = 'Editar Usuario';
            document.getElementById('editAdminSubtitle').textContent = btn.dataset.nombre;
            document.getElementById('editAdminNombre').value = btn.dataset.nombre;
            document.getElementById('editAdminEmail').value = btn.dataset.email;
            document.getElementById('editAdminPwd').value = '';
            document.getElementById('editAdminPwdConf').value = '';
            document.getElementById('editMatchText').textContent = 'Las contraseñas deben coincidir';
            document.getElementById('editMatchText').style.color = '#888';
            document.getElementById('formEditarAdmin').action = btn.dataset.url;

            document.getElementById('overlayEditarAdmin').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }

    const editPwdConf = document.getElementById('editAdminPwdConf');
    if (editPwdConf) {
        editPwdConf.addEventListener('input', function() {
            const pwd = document.getElementById('editAdminPwd').value;
            const conf = this.value;
            const el = document.getElementById('editMatchText');
            
            if (!conf) {
                el.textContent = 'Las contraseñas deben coincidir';
                el.style.color = '#888';
            } else if (pwd === conf) {
                el.textContent = '✓ Las contraseñas coinciden';
                el.style.color = '#28a745';
            } else {
                el.textContent = '✗ Las contraseñas no coinciden';
                el.style.color = '#dc3545';
            }
        });
    }

    // Cerrar overlays con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarOverlayEditar();
            cerrarOverlayProcesos();
        }
    });

});

// Función para cerrar overlay de editar
function cerrarOverlayEditar() {
    const el = document.getElementById('overlayEditarAdmin');
    if (el) {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Función para cerrar overlay de procesos
function cerrarOverlayProcesos() {
    const el = document.getElementById('overlayProcesos');
    if (el) {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Función para abrir overlay de procesos
function abrirOverlayProcesos() {
    const el = document.getElementById('overlayProcesos');
    if (el) {
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

// SWEETALERT - Confirmación para activar/desactivar usuario
function confirmarAccion(event, nombre, accion) {
    event.preventDefault();
    const form = event.target;
    
    const config = accion === 'desactivar' 
        ? {
            title: '¿Desactivar usuario?',
            text: `El usuario "${nombre}" no podrá iniciar sesión.`,
            icon: 'warning',
            iconHtml: '<i class="bi bi-exclamation-triangle-fill" style="font-size: 4rem; color: #dc3545;"></i>',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, desactivar'
        }
        : {
            title: '¿Activar usuario?',
            text: `El usuario "${nombre}" podrá iniciar sesión nuevamente.`,
            icon: 'question',
            iconHtml: '<i class="bi bi-question-circle-fill" style="font-size: 4rem; color: #28a745;"></i>',
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Sí, activar'
        };

    Swal.fire({
        title: config.title,
        text: config.text,
        icon: config.icon,
        iconHtml: config.iconHtml,
        width: '650px',
        padding: '3rem',
        showCancelButton: true,
        confirmButtonColor: config.confirmButtonColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    
    return false;
}

// SWEETALERT - Confirmación para eliminar departamento
function confirmarEliminarDepartamento(event, nombre, id) {
    event.preventDefault();
    const form = document.getElementById('form-delete-depto-' + id);
    
    Swal.fire({
        title: '¿Eliminar departamento?',
        text: `El departamento "${nombre}" será eliminado permanentemente.`,
        icon: 'warning',
        iconHtml: '<i class="bi bi-exclamation-triangle-fill" style="font-size: 4rem; color: #dc3545;"></i>',
        width: '650px',
        padding: '3rem',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    
    return false;
}

// SWEETALERT - Confirmación para eliminar proceso completo
function confirmarEliminarProceso(event, nombre) {
    event.preventDefault();
    const form = event.target;
    
    Swal.fire({
        title: '¿Eliminar proceso?',
        text: `El proceso "${nombre}" y TODOS sus departamentos serán eliminados permanentemente.`,
        icon: 'warning',
        iconHtml: '<i class="bi bi-exclamation-triangle-fill" style="font-size: 4rem; color: #dc3545;"></i>',
        width: '650px',
        padding: '3rem',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    
    return false;
}

// Funciones para gestión de procesos
function pgToggle(grupoId) {
    const lista = document.getElementById(grupoId);
    const icon = document.getElementById('pgicon-' + grupoId.replace('pg-',''));
    if (!lista) return;
    
    const isOpen = lista.classList.contains('open');
    document.querySelectorAll('.pg-deptos').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.pg-chevron').forEach(el => el.classList.remove('open'));
    
    if (!isOpen) {
        lista.classList.add('open');
        if (icon) icon.classList.add('open');
    }
}

function pgToggleAdd(formId, grupoId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const isOpen = form.classList.contains('open');
    
    if (grupoId && !isOpen) {
        const lista = document.getElementById(grupoId);
        if (lista && !lista.classList.contains('open')) {
            pgToggle(grupoId);
        }
    }
    
    form.classList.toggle('open');
    
    if (form.classList.contains('open')) {
        const inp = form.querySelector('input[name="departamento"]');
        if (inp) setTimeout(() => inp.focus(), 50);
    } else {
        const inp = form.querySelector('input[name="departamento"]');
        if (inp) inp.value = '';
    }
}
</script>
@endpush