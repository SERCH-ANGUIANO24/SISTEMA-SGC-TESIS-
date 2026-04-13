<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SAMS - Sistema de Gestión de la Calidad')</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Auth CSS solo para páginas de autenticación -->
    @if(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*'))
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    @endif
    
    <!-- Fix para texto visible -->
    <link href="{{ asset('css/fix-text.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #800000 0%, #800000 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-bg: #f8f9fa;
            --card-bg: #9b2226;
            --text-light: #ffffff;
            --text-muted: #ffffff;
            --border-color: #ffffff;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;

            /* ── TEMA POR USUARIO ── */
            --theme-color: {{ auth()->check() ? (auth()->user()->theme_color ?? '#800000') : '#800000' }};
        }

        body {
            background: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: var(--theme-color) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            transition: background 0.3s;
        }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; color: white !important; }
        .navbar-nav .nav-link { color: rgba(255,255,255,0.9) !important; margin: 0 0.5rem; transition: all 0.3s ease; }
        .navbar-nav .nav-link:hover { color: white !important; transform: translateY(-2px); }

        .card-custom { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,.15); transition: transform 0.3s ease; }
        .card-custom:hover { transform: translateY(-5px); }
        .btn-primary-custom { background: var(--primary-gradient); border: none; border-radius: 8px; padding: 0.75rem 1.5rem; font-weight: 600; transition: all 0.3s ease; }
        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,.4); }
        .main-content { min-height: calc(100vh - 80px); padding-top: 2rem; }
        .sidebar { background: var(--card-bg); border-right: 1px solid var(--border-color); height: calc(100vh - 80px); position: fixed; width: 250px; padding: 1rem; }
        .sidebar-link { color: var(--text-light); text-decoration: none; display: block; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 0.5rem; transition: all 0.3s ease; }
        .sidebar-link:hover, .sidebar-link.active { background: var(--primary-gradient); color: white; text-decoration: none; }
        .content-with-sidebar { margin-left: 250px; padding: 2rem; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .content-with-sidebar { margin-left: 0; padding: 1rem; }
        }

        .alert-custom { border-radius: 8px; border: none; margin-bottom: 1.5rem; }
        .alert-success-custom { background: linear-gradient(135deg, #48bb78, #38a169); color: white; }
        .alert-danger-custom { background: linear-gradient(135deg, #f56565, #e53e3e); color: white; }

        /* Los estilos originales del navbar se mantienen, pero los responsivos los sobreescriben */
        .welcome-container { position: absolute; left: 50%; transform: translateX(-50%); }
        .welcome-text { white-space: nowrap; font-size: 1.25rem; }
        .left-section, .right-section { width: 200px; }

        @media (max-width: 992px) {
            .welcome-text { font-size: 1.1rem; }
            .left-section, .right-section { width: 150px; }
        }
        @media (max-width: 768px) {
            .welcome-container { position: static; transform: none; text-align: center; margin: 0.5rem 0; }
            .welcome-text { white-space: normal; font-size: 1rem; }
            .left-section, .right-section { width: auto; }
        }

        .footer {
            background: var(--theme-color) !important;
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            margin-top: 4rem;
            transition: background 0.3s;
        }

        /* ── ENCABEZADOS DE MODALES — BLANCOS ── */
        .modal-header {
            background: #ffffff !important;
            color: #212529 !important;
            border-bottom: 1px solid #dee2e6 !important;
            transition: none !important;
        }
        .modal-header .btn-close { filter: none !important; }
        .modal-title { color: #212529 !important; }

        /* ── BOTONES PRIMARIOS (tema) ── */
        .btn-primary, .btn-tema, button.btn-primary, a.btn-primary,
        input[type="submit"].btn-primary, .modal .btn-primary,
        .modal-footer .btn-primary, .card .btn-primary,
        .form-group .btn-primary, [class*="btn-primary"] {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            color: #fff !important;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active,
        .btn-primary.active, .btn-tema:hover, button.btn-primary:hover,
        .modal .btn-primary:hover, .modal-footer .btn-primary:hover {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            filter: brightness(0.88);
            color: #fff !important;
        }
        .btn-primary {
            --bs-btn-bg: var(--theme-color) !important;
            --bs-btn-border-color: var(--theme-color) !important;
            --bs-btn-hover-bg: var(--theme-color) !important;
            --bs-btn-active-bg: var(--theme-color) !important;
        }

        /* ── BOTONES PERSONALIZADOS (tema) ── */
        .btn-modal-submit, .btn-registrar, .btn-gestionar-procesos,
        .usuarios-icon-wrap {
            background: var(--theme-color) !important;
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            transition: background 0.3s, background-color 0.3s, border-color 0.3s !important;
        }
        .btn-modal-submit:hover, .btn-registrar:hover, .btn-gestionar-procesos:hover {
            background: var(--theme-color) !important;
            background-color: var(--theme-color) !important;
            filter: brightness(0.88);
            box-shadow: 0 4px 14px rgba(0,0,0,0.25) !important;
        }

        /* ── AVATAR-CIRCLE — SIEMPRE GRIS (no cambia con el tema) ── */
        .avatar-circle {
            background: linear-gradient(135deg, #737373, #737373) !important;
            background-color: #737373 !important;
        }

        /* ── BTN-MODAL-CANCEL — SIEMPRE GRIS ── */
        .btn-modal-cancel {
            background: #6c757d !important;
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        .btn-modal-cancel:hover {
            background: #5a6268 !important;
            background-color: #5a6268 !important;
            border-color: #545b62 !important;
            color: #fff !important;
        }

        .btn-registrar, .btn-gestionar-procesos {
            box-shadow: 0 4px 14px color-mix(in srgb, var(--theme-color) 50%, transparent) !important;
        }

        /* ── BTN-SECONDARY (tema, excluye filtros) ── */
        .btn-secondary:not(.dropdown-toggle):not(.btn-light):not(.btn-clear-search) {
            --bs-btn-bg: var(--theme-color);
            --bs-btn-border-color: var(--theme-color);
            --bs-btn-hover-bg: var(--theme-color);
            --bs-btn-hover-border-color: var(--theme-color);
            --bs-btn-active-bg: var(--theme-color);
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            color: #fff !important;
        }

        /* ── CANCELAR MODAL — SIEMPRE GRIS ── */
        .modal-footer .btn-secondary,
        button[data-bs-dismiss="modal"],
        .btn-secondary[data-bs-dismiss="modal"] {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        .modal-footer .btn-secondary:hover,
        button[data-bs-dismiss="modal"]:hover {
            background-color: #5a6268 !important;
            border-color: #545b62 !important;
        }

        /* ── OVERLAY PROCESOS — colores fijos ── */
        #overlayProcesos .btn-modal-cancel {
            background: #6c757d !important;
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        #overlayProcesos button[style*="737373"],
        #overlayProcesos button[style*="737373"] {
            background: #737373 !important;
            background-color: #737373 !important;
            color: #fff !important;
        }
        #overlayProcesos button[style*="dc3545"] {
            background: #dc3545 !important;
            background-color: #dc3545 !important;
            color: #fff !important;
        }
        #overlayProcesos button[style*="6c757d"] {
            background: #6c757d !important;
            background-color: #6c757d !important;
            color: #fff !important;
        }

        /* ── BTN-LIGHT (filtros) — siempre blancos ── */
        .btn-light, .btn-light.border, .btn-light.dropdown-toggle {
            background-color: #ffffff !important;
            border-color: #dee2e6 !important;
            color: #495057 !important;
        }
        .btn-light:hover, .btn-light.dropdown-toggle:hover {
            background-color: #f8f9fa !important;
            border-color: #adb5bd !important;
            color: #212529 !important;
        }

        /* ── ESTADÍSTICAS E HISTÓRICO — colores fijos ── */
        #btnEstadisticas {
            background-color: #0dcaf0 !important;
            border-color: #0dcaf0 !important;
            color: #fff !important;
        }
        #btnHistorico {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }
        #modalEstadisticas .modal-header {
            background-color: #0dcaf0 !important;
            background: #0dcaf0 !important;
            color: #fff !important;
        }
        #modalEstadisticas .modal-title,
        #modalEstadisticas .modal-header * { color: #fff !important; }
        #modalEstadisticas .btn-close { filter: invert(1) !important; }
        #modalHistorico .modal-header {
            background-color: #0d6efd !important;
            background: #0d6efd !important;
            color: #fff !important;
        }
        #modalHistorico .modal-title,
        #modalHistorico .modal-header * { color: #fff !important; }
        #modalHistorico .btn-close { filter: invert(1) !important; }
        #modalGraficas .modal-header,
        #modalEstadisticasSolicitudes .modal-header {
            background-color: #0dcaf0 !important;
            background: #0dcaf0 !important;
            color: #fff !important;
        }
        #modalGraficas .modal-title,
        #modalEstadisticasSolicitudes .modal-title,
        #modalGraficas .modal-header *,
        #modalEstadisticasSolicitudes .modal-header * { color: #fff !important; }
        #modalGraficas .btn-close,
        #modalEstadisticasSolicitudes .btn-close { filter: invert(1) !important; }
        #modalHistoricoSolicitudes .modal-header,
        #modalHistoricoGlobal .modal-header {
            background-color: #0d6efd !important;
            background: #0d6efd !important;
            color: #fff !important;
        }
        #modalHistoricoSolicitudes .modal-title,
        #modalHistoricoGlobal .modal-title,
        #modalHistoricoSolicitudes .modal-header *,
        #modalHistoricoGlobal .modal-header * { color: #fff !important; }
        #modalHistoricoSolicitudes .btn-close,
        #modalHistoricoGlobal .btn-close { filter: invert(1) !important; }

        /* ── NOTIFICACIONES ── */
        .notif-card .btn, .notif-card .btn-outline-secondary,
        .notif-card .btn-outline-success, .notif-card .btn-outline-danger,
        #btnLeerTodas, #btnLimpiar {
            border-color: var(--theme-color) !important;
            color: var(--theme-color) !important;
            background-color: transparent !important;
        }
        .notif-card .btn:hover, .notif-card .btn-outline-secondary:hover,
        .notif-card .btn-outline-success:hover, .notif-card .btn-outline-danger:hover,
        #btnLeerTodas:hover, #btnLimpiar:hover {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            color: #fff !important;
        }
        .notif-card .btn:hover i, #btnLeerTodas:hover i, #btnLimpiar:hover i { color: #fff !important; }

        /* ── CARD HEADERS ── */
        .card-header-tema { background: var(--theme-color) !important; color: #fff !important; transition: background 0.3s; }

        /* ── SWATCHES ── */
        .theme-swatch { width:26px; height:26px; border-radius:50%; cursor:pointer; border:2.5px solid transparent; display:inline-block; transition:transform 0.1s; flex-shrink:0; }
        .theme-swatch:hover { transform: scale(1.15); }
        .theme-swatch.active { border-color: #1f2937; }

        /* ── PREVIEW STRIP ── */
        .theme-preview-wrap { border-radius:6px; overflow:hidden; border:1px solid #dee2e6; }
        .theme-preview-header { padding:8px 14px; color:#fff; font-size:13px; font-weight:600; }
        .theme-preview-body { background:#f8f9fa; padding:10px 14px; display:flex; gap:8px; align-items:center; }
        .theme-preview-footer { padding:6px 14px; color:#fff; font-size:11px; text-align:center; }
        .theme-preview-btn { padding:4px 14px; border:none; border-radius:4px; color:#fff; font-size:12px; font-weight:600; cursor:default; }

        /* ── HISTORIAL ── */
        #filtrosForm .btn-outline-secondary, #btnBorrarTodo {
            border-color: #6c757d !important;
            color: #6c757d !important;
            background-color: #ffffff !important;
        }
        #filtrosForm .btn-outline-secondary:hover, #btnBorrarTodo:hover {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #ffffff !important;
        }
        a.btn-outline-primary.btn-sm {
            border-color: #0d6efd !important;
            color: #0d6efd !important;
            background-color: transparent !important;
        }
        a.btn-outline-primary.btn-sm:hover {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #ffffff !important;
        }
        .btn-outline-success.restaurar-btn {
            border-color: #198754 !important;
            color: #198754 !important;
            background-color: transparent !important;
        }
        .btn-outline-success.restaurar-btn:hover {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
        }

        .no-tema,
        .no-tema:hover,
        .no-tema:focus,
        .no-tema:active {
            background-color: inherit !important;
            border-color: inherit !important;
            color: inherit !important;
        }

        /* ===== MODAL VISUALIZAR ARCHIVO - BOTON CERRAR NO CAMBIA ===== */
        .modal:not(#modalTema):not(#modalEstadisticas):not(#modalHistorico):not(#modalGraficas):not(#modalHistoricoSolicitudes):not(#modalHistoricoGlobal):not(#modalEstadisticasSolicitudes) .modal-footer .btn-secondary,
        .modal-footer .btn-secondary[data-bs-dismiss="modal"] {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        
        .modal:not(#modalTema):not(#modalEstadisticas):not(#modalHistorico):not(#modalGraficas):not(#modalHistoricoSolicitudes):not(#modalHistoricoGlobal):not(#modalEstadisticasSolicitudes) .modal-footer .btn-secondary:hover,
        .modal-footer .btn-secondary[data-bs-dismiss="modal"]:hover {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
            filter: brightness(0.98);
        }

        /* ===== BOTON DESCARGAR - RESPETA TEMA DEL USUARIO ===== */
        .modal .modal-footer a.btn[download],
        .modal .modal-footer a.btn[href*="download"] {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            color: #fff !important;
            transition: all 0.3s ease !important;
        }

        .modal .modal-footer a.btn[download]:hover,
        .modal .modal-footer a.btn[href*="download"]:hover {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            filter: brightness(0.88) !important;
            color: #fff !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
        }

        .modal-body a.btn[download],
        .modal-body a.btn[href*="download"] {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            color: #fff !important;
            transition: all 0.3s ease !important;
        }

        .modal-body a.btn[download]:hover,
        .modal-body a.btn[href*="download"]:hover {
            background-color: var(--theme-color) !important;
            border-color: var(--theme-color) !important;
            filter: brightness(0.88) !important;
            transform: translateY(-1px);
        }
        
        /* ===== X BLANCA EN MODALES DE ESTADÍSTICAS E HISTÓRICO ===== */
        #modalEstadisticas .btn-close,
        #modalHistorico .btn-close {
            background-color: transparent !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") !important;
            filter: none !important;
            opacity: 0.9 !important;
            box-shadow: none !important;
            border: none !important;
        }

        #modalEstadisticas .btn-close:hover,
        #modalHistorico .btn-close:hover {
            background-color: rgba(255,255,255,0.15) !important;
            opacity: 1 !important;
            border-radius: 4px !important;
        }

        #modalHistorico .modal-footer .btn-secondary,
        #modalHistorico .modal-footer button[data-bs-dismiss="modal"] {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }

        #modalHistorico .modal-footer .btn-secondary:hover,
        #modalHistorico .modal-footer button[data-bs-dismiss="modal"]:hover {
            background-color: #5a6268 !important;
            border-color: #545b62 !important;
            color: #fff !important;
        }

        #editDocumentModal .btn-close {
            display: none !important;
        }

        /* =====================================================
           ESTILOS RESPONSIVOS - CORRECCIÓN DEFINITIVA PARA TABLET
        ===================================================== */

        /* Tablets (769px a 992px) - VERSIÓN MODERNA Y EQUILIBRADA */ /*Pendiente */
        @media (min-width: 800px) and (max-width: 1280px) {  /* Pendiente */
            /* Navbar más compacto */
            .navbar-custom {
                padding: 0.4rem 0 !important;
            }
            .navbar-custom .container {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 0.5rem !important;
            }
            
            /* Logo más proporcionado */
            .navbar-brand img {
                height: 32px !important;
                width: auto !important;
            }
            .left-section {
                width: auto !important;
                flex: 0 0 auto !important;
            }
            
            /* Texto central - elegante y legible */
            .welcome-container {
                position: static !important;
                transform: none !important;
                flex: 1 !important;
                text-align: center !important;
                margin: 0 !important;
                padding: 0 0.5rem !important;
            }
            .welcome-text {
                font-size: 0.7rem !important;
                font-weight: 500 !important;
                white-space: nowrap !important;
                display: inline-block !important;
                letter-spacing: -0.2px !important;
            }
            
            /* Sección derecha - compacta pero legible */
            .right-section {
                width: auto !important;
                flex: 0 0 auto !important;
                gap: 0.5rem !important;
            }
            
            /* Fecha */
            .right-section .d-flex.align-items-center {
                font-size: 0.65rem !important;
                white-space: nowrap !important;
                gap: 0.25rem !important;
            }
            .right-section .d-flex.align-items-center i {
                font-size: 0.7rem !important;
            }
            
            /* Usuario */
            .right-section .navbar-nav {
                margin: 0 !important;
            }
            .right-section .nav-link {
                font-size: 0.65rem !important;
                padding: 0.2rem 0.35rem !important;
                white-space: nowrap !important;
            }
            .right-section .nav-link i {
                font-size: 0.7rem !important;
                margin-right: 0.25rem !important;
            }
            
            /* Dropdown menu */
            .dropdown-menu {
                font-size: 0.7rem !important;
                min-width: 130px !important;
                padding: 0.3rem 0 !important;
            }
            .dropdown-item {
                padding: 0.25rem 0.6rem !important;
                font-size: 0.65rem !important;
            }
            .dropdown-item svg {
                width: 11px !important;
                height: 11px !important;
            }
            
            /* Footer */
            .footer {
                padding: 0.75rem 0 !important;
                margin-top: 2rem !important;
            }
            .footer p {
                font-size: 0.6rem !important;
            }
            
            /* Contenido principal */
            .main-content {
                padding-top: 1rem !important;
            }
            
            /* Tarjetas y contenedores */
            .card {
                margin-bottom: 1rem !important;
            }
            
            /* Tablas responsivas */
            .table-responsive {
                overflow-x: auto !important;
            }
        }

        /* Móviles (768px y menos) */
        @media (max-width: 768px) {
            .navbar-custom {
                padding: 0.5rem 0 !important;
            }
            .navbar-custom .container {
                flex-direction: column !important;
                align-items: center !important;
                gap: 0.35rem !important;
            }
            .navbar-brand img {
                height: 32px !important;
            }
            .left-section {
                width: auto !important;
            }
            .welcome-container {
                position: static !important;
                transform: none !important;
                text-align: center !important;
                margin: 0.15rem 0 !important;
            }
            .welcome-text {
                font-size: 0.7rem !important;
                white-space: normal !important;
            }
            .right-section {
                width: 100% !important;
                justify-content: center !important;
                gap: 0.5rem !important;
            }
            .right-section .d-flex.align-items-center {
                font-size: 0.6rem !important;
                gap: 0.25rem !important;
            }
            .right-section .d-flex.align-items-center i {
                font-size: 0.7rem !important;
            }
            .right-section .navbar-nav {
                margin: 0 !important;
            }
            .right-section .nav-link {
                font-size: 0.7rem !important;
                padding: 0.2rem 0.35rem !important;
                white-space: nowrap !important;
            }
            .right-section .nav-link i {
                font-size: 0.7rem !important;
            }
            .dropdown-menu {
                min-width: 140px !important;
                font-size: 0.7rem !important;
                padding: 0.25rem 0 !important;
            }
            .dropdown-item {
                padding: 0.25rem 0.6rem !important;
                font-size: 0.7rem !important;
            }
            .dropdown-item svg {
                width: 11px !important;
                height: 11px !important;
            }
            .dropdown-divider {
                margin: 0.25rem 0 !important;
            }
            .footer {
                padding: 0.75rem 0 !important;
                margin-top: 1.5rem !important;
            }
            .footer p {
                font-size: 0.6rem !important;
            }
            .main-content {
                padding-top: 0.75rem !important;
            }
            .alert-custom {
                margin-bottom: 0.75rem !important;
                padding: 0.6rem 0.8rem !important;
                font-size: 0.75rem !important;
            }
            .modal-dialog {
                margin: 0.5rem !important;
            }
            #modalTema .modal-dialog {
                max-width: 95% !important;
            }
            #modalTema .modal-header {
                padding: 10px 12px !important;
            }
            #modalTema .modal-body {
                padding: 0.75rem !important;
            }
            .theme-swatch {
                width: 20px !important;
                height: 20px !important;
            }
        }

        /* Móviles muy pequeños (480px y menos) */
        @media (max-width: 480px) {
            .navbar-brand img {
                height: 28px !important;
            }
            .welcome-text {
                font-size: 0.65rem !important;
            }
            .right-section .d-flex.align-items-center {
                font-size: 0.55rem !important;
            }
            .right-section .d-flex.align-items-center i {
                font-size: 0.6rem !important;
            }
            .right-section .nav-link {
                font-size: 0.65rem !important;
                padding: 0.15rem 0.3rem !important;
            }
            .dropdown-menu {
                min-width: 130px !important;
            }
            .dropdown-item {
                font-size: 0.65rem !important;
                padding: 0.2rem 0.5rem !important;
            }
            .footer p {
                font-size: 0.55rem !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container position-relative">
        <div class="left-section">
            <a class="navbar-brand">
                <img src="https://lh3.googleusercontent.com/proxy/iBImmZjJODGa39TgtflRih-vmGJwiTPpBotgG80_ckaAxtEWogKYQLf1ACpY-Nqr_-QnZM01aRtgtNef_Gk-m6An8VR-9ovpNw" alt="UPTEX Logo" style="height: 50px; width: auto;">
            </a>
        </div>

        @auth
        <div class="welcome-container">
            <span class="navbar-text fw-bold text-white welcome-text">Sistema de Gestión de la Calidad</span>
        </div>
        @endauth

        @auth
        <div class="right-section d-flex align-items-center gap-3">
            <div class="d-flex align-items-center text-white">
                <i class="bi bi-calendar3 me-2"></i>
                <span id="fecha-actual"></span>
            </div>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalTema">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/>
                                    <line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/>
                                    <line x1="2" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22" y2="12"/>
                                </svg>
                                Tema
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        @endauth

        @guest
        <div class="welcome-container">
            <span class="navbar-text fw-bold text-white welcome-text">Sistema de Gestión de Calidad</span>
        </div>
        @endguest
    </div>
</nav>

    <main class="main-content">
        @if (session('success'))
            <div class="container">
                <div class="alert alert-success-custom alert-custom alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="container">
                <div class="alert alert-danger-custom alert-custom alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="footer">
        <div class="row">
            <div class="col-12 text-center">
                <p class="text-white mb-0">&copy; {{ date('Y') }} SAMS Infinity. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    @auth
    <div class="modal fade" id="modalTema" tabindex="-1" aria-labelledby="modalTemaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
            <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none">
                <div class="modal-header" style="padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.2);background:var(--theme-color) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/>
                            <line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/>
                            <line x1="2" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22" y2="12"/>
                        </svg>
                        <h5 class="modal-title mb-0 fw-semibold" id="modalTemaLabel" style="font-size:15px;color:#fff !important;">
                            Personalizar tema
                        </h5>
                    </div>
                </div>
                <div class="modal-body" style="padding:18px;background:#fff">
                    <p style="font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Colores rápidos</p>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="themeSwatches">
                        @foreach(['#800000','#7b1c1c','#1a3a5c','#1d6e3d','#3d1a5c','#185FA5','#854F0B','#0F6E56','#993556','#2c2c2a'] as $preset)
                            <div class="theme-swatch {{ (auth()->user()->theme_color ?? '#800000') === $preset ? 'active' : '' }}"
                                 style="background:{{ $preset }}" data-color="{{ $preset }}" title="{{ $preset }}"></div>
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <label for="themePickerInput" style="font-size:13px;color:#4b5563;flex:1">Color personalizado</label>
                        <input type="color" id="themePickerInput" value="{{ auth()->user()->theme_color ?? '#800000' }}"
                               style="width:40px;height:32px;border:none;border-radius:6px;padding:2px;cursor:pointer">
                    </div>
                    <p style="font-size:10px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Vista previa</p>
                    <div class="theme-preview-wrap">
                        <div class="theme-preview-header" id="prevHeader">Sistema de Gestión de la Calidad</div>
                        <div class="theme-preview-body">
                            <div class="theme-preview-btn" id="prevBtn">Guardar cambios</div>
                            <div style="padding:4px 12px;border-radius:4px;background:#6b7280;color:#fff;font-size:12px">Cancelar</div>
                        </div>
                        <div class="theme-preview-footer" id="prevFooter">&copy; {{ date('Y') }} SAMS Infinity. Todos los derechos reservados.</div>
                    </div>
                </div>
                <div class="modal-footer" style="padding:10px 18px;border-top:1px solid #e5e7eb;background:#fff">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-sm fw-semibold text-white" id="btnGuardarTema" style="background:var(--theme-color);border:none">Aplicar tema</button>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.Laravel = { csrfToken: '{{ csrf_token() }}' };
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => { const bsAlert = new bootstrap.Alert(alert); bsAlert.close(); });
            }, 5000);
        });
        function actualizarFecha() {
            const fechaElement = document.getElementById('fecha-actual');
            if (fechaElement) {
                const hoy = new Date();
                const dia = String(hoy.getDate()).padStart(2, '0');
                const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                const anio = hoy.getFullYear();
                fechaElement.textContent = dia + '/' + mes + '/' + anio;
            }
        }
        @auth
        setInterval(actualizarFecha, 1000);
        actualizarFecha();
        @endauth
    </script>

    @auth
    <script>
    (function () {
        const ROUTE = "{{ route('tema.update') }}";
        const CSRF  = "{{ csrf_token() }}";
        let tempColor = "{{ auth()->user()->theme_color ?? '#800000' }}";

        function applyTheme(color) {
            if (window.location.href.indexOf('historial-versiones') !== -1) {
                return;
            }
                    
            document.documentElement.style.setProperty('--theme-color', color);

            document.querySelectorAll(
                '.btn-primary, .btn-tema, .btn-modal-submit, .btn-registrar, .btn-gestionar-procesos, .usuarios-icon-wrap'
            ).forEach(function(el) {
                if (el.disabled) return;
                if (el.classList.contains('no-tema')) return;
                el.style.setProperty('background-color', color, 'important');
                el.style.setProperty('border-color', color, 'important');
                el.style.setProperty('background', color, 'important');
            });

            document.querySelectorAll('.btn-secondary').forEach(function(el) {
                if (el.disabled) return;
                if (el.classList.contains('no-tema')) return; 
                if (el.classList.contains('dropdown-toggle')) return;
                if (el.classList.contains('btn-light')) return;
                if (el.classList.contains('btn-clear-search')) return;
                if (el.hasAttribute('data-bs-dismiss')) return;
                if (el.closest('#overlayProcesos')) return;
                if (el.closest('#modalEstadisticas')) return;
                if (el.closest('#modalHistorico')) return;
                if (el.closest('#modalGraficas')) return;
                if (el.closest('#modalHistoricoSolicitudes')) return;
                el.style.setProperty('background-color', color, 'important');
                el.style.setProperty('border-color', color, 'important');
            });

            document.querySelectorAll('button[style*="background"], a[style*="background"]').forEach(function(el) {
                if (el.disabled) return;
                if (el.classList.contains('no-tema')) return;
                if (el.classList.contains('dropdown-toggle')) return;
                if (el.classList.contains('btn-light')) return;
                if (el.classList.contains('btn-clear-search')) return;
                if (el.hasAttribute('data-bs-dismiss')) return;
                if (el.closest('#overlayProcesos')) return;
                if (el.closest('#modalEstadisticas')) return;
                if (el.closest('#modalHistorico')) return;
                if (el.closest('#modalGraficas')) return;
                if (el.closest('#modalHistoricoSolicitudes')) return;
                if (el.id === 'btnEstadisticas' || el.id === 'btnHistorico') return;
                var bg = el.style.backgroundColor;
                if (!bg || bg === 'transparent') return;
                if (bg === 'rgb(255, 255, 255)') return;
                if (bg === 'rgb(169, 169, 169)') return;
                if (bg === 'rgb(13, 202, 240)') return;
                if (bg === 'rgb(13, 110, 253)') return;
                if (el.style.background && el.style.background.indexOf('rgba') !== -1) return;
                el.style.setProperty('background-color', color, 'important');
                el.style.setProperty('background', color, 'important');
            });

            document.querySelectorAll('.btn-modal-cancel, .modal-footer .btn-secondary').forEach(function(el) {
                el.style.setProperty('background-color', '#6c757d', 'important');
                el.style.setProperty('background', '#6c757d', 'important');
                el.style.setProperty('border-color', '#6c757d', 'important');
            });

            document.querySelectorAll('#modalEstadisticas .btn-close, #modalHistorico .btn-close, #modalEstadisticas button[data-bs-dismiss="modal"], #modalHistorico button[data-bs-dismiss="modal"]').forEach(function(el) {
                el.style.setProperty('background-color', 'transparent', 'important');
                el.style.setProperty('background', 'transparent', 'important');
                el.style.setProperty('border-color', 'transparent', 'important');
            });

            document.querySelectorAll('.avatar-circle').forEach(function(el) {
                el.style.setProperty('background', 'linear-gradient(135deg, #737373, #737373)', 'important');
                el.style.setProperty('background-color', '#737373', 'important');
            });

            var elEst = document.getElementById('btnEstadisticas');
            if (elEst) { elEst.style.setProperty('background-color','#0dcaf0','important'); elEst.style.setProperty('background','#0dcaf0','important'); }
            var elHist = document.getElementById('btnHistorico');
            if (elHist) { elHist.style.setProperty('background-color','#0d6efd','important'); elHist.style.setProperty('background','#0d6efd','important'); }

            document.querySelectorAll('.modal-header').forEach(function(el) {
                var modal = el.closest('.modal');
                if (!modal) return;
                if (modal.id === 'modalTema') return;
                if (modal.id === 'modalEstadisticas' || modal.id === 'modalHistorico') return;
                if (modal.id === 'modalGraficas' || modal.id === 'modalHistoricoSolicitudes') return;
                if (modal.id === 'modalHistoricoGlobal' || modal.id === 'modalEstadisticasSolicitudes') return;
                el.style.setProperty('background-color', '#ffffff', 'important');
                el.style.setProperty('background', '#ffffff', 'important');
                el.style.setProperty('color', '#212529', 'important');
            });

            document.querySelectorAll('.pagination .page-link').forEach(function(el) {
                el.style.setProperty('background-color', '#ffffff', 'important');
                el.style.setProperty('border-color', '#dee2e6', 'important');
                el.style.setProperty('color', '#6c757d', 'important');
            });

            document.querySelectorAll('#filtrosForm .btn-outline-secondary, #btnBorrarTodo').forEach(function(el) {
                if (el.disabled) return;
                el.style.setProperty('background-color', '#ffffff', 'important');
                el.style.setProperty('border-color', color, 'important');
                el.style.setProperty('color', color, 'important');
            });
            
            document.querySelectorAll('#modalEstadisticas .btn-close, #modalHistorico .btn-close').forEach(function(el) {
                el.style.setProperty('filter', 'invert(1)', 'important');
                el.style.setProperty('opacity', '1', 'important');
                el.style.setProperty('background-color', 'transparent', 'important');
            });
            
            function fixCloseButtons() {
                const modales = ['modalEstadisticas', 'modalHistorico'];
                modales.forEach(id => {
                    const modal = document.getElementById(id);
                    if (!modal) return;
                    const btnClose = modal.querySelector('.btn-close');
                    if (btnClose && !btnClose.hasAttribute('data-fixed')) {
                        btnClose.setAttribute('data-fixed', 'true');
                        btnClose.style.cssText = `
                            background: transparent !important;
                            background-color: transparent !important;
                            border: none !important;
                            font-size: 20px !important;
                            font-weight: bold !important;
                            line-height: 1 !important;
                            color: white !important;
                            opacity: 0.9 !important;
                            padding: 0 8px !important;
                            margin: 0 !important;
                            box-shadow: none !important;
                            outline: none !important;
                            text-shadow: none !important;
                        `;
                        btnClose.textContent = '✕';
                        btnClose.innerHTML = '✕';
                    }
                });
            }

            document.getElementById('modalEstadisticas')?.addEventListener('shown.bs.modal', fixCloseButtons);
            document.getElementById('modalHistorico')?.addEventListener('shown.bs.modal', fixCloseButtons);
            setTimeout(fixCloseButtons, 500);
            
            var modalHistoricoFooterBtn = document.querySelector('#modalHistorico .modal-footer .btn-secondary');
            if (modalHistoricoFooterBtn) {
                modalHistoricoFooterBtn.style.setProperty('background-color', '#6c757d', 'important');
                modalHistoricoFooterBtn.style.setProperty('border-color', '#6c757d', 'important');
                modalHistoricoFooterBtn.style.setProperty('background', '#6c757d', 'important');
            }

            // Excluir botones de filtro de lista maestra del tema
            document.querySelectorAll('.filtro-lista-maestra').forEach(function(el) {
                el.style.removeProperty('background-color');
                el.style.removeProperty('background');
                el.style.removeProperty('border-color');
                el.style.setProperty('background-color', '#ffffff', 'important');
            });
        }

        function updatePreview(color) {
            document.getElementById('prevHeader').style.background = color;
            document.getElementById('prevFooter').style.background = color;
            document.getElementById('prevBtn').style.background    = color;
            document.getElementById('btnGuardarTema').style.background = color;
        }

        document.querySelectorAll('.theme-swatch').forEach(function(sw) {
            sw.addEventListener('click', function () {
                tempColor = this.dataset.color;
                document.getElementById('themePickerInput').value = tempColor;
                document.querySelectorAll('.theme-swatch').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                updatePreview(tempColor);
            });
        });

        document.getElementById('themePickerInput').addEventListener('input', function () {
            tempColor = this.value;
            document.querySelectorAll('.theme-swatch').forEach(s => s.classList.remove('active'));
            updatePreview(tempColor);
        });

        document.getElementById('modalTema').addEventListener('show.bs.modal', function () {
            updatePreview(tempColor);
        });

        document.getElementById('btnGuardarTema').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Guardando...';
            fetch(ROUTE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ theme_color: tempColor }),
            })
            .then(r => r.json())
            .then(function(data) {
                if (data.success) {
                    applyTheme(tempColor);
                    bootstrap.Modal.getInstance(document.getElementById('modalTema')).hide();
                } else { alert('Error al guardar el tema.'); }
            })
            .catch(function() { alert('Error de conexión.'); })
            .finally(function() {
                btn.disabled = false;
                btn.textContent = 'Aplicar tema';
                btn.style.background = tempColor;
            });
        });

        applyTheme(tempColor);
    })();
    </script>
    @endauth

    @stack('scripts')
</body>
</html>