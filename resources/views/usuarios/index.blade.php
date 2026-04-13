@extends('layouts.app')

@section('title', 'Gestión de Usuarios - SAMS')

@push('styles')
<style>
    /* IMPORTA LAS FUENTES DE GOOGLE: DM SERIF DISPLAY PARA TITULOS Y DM SANS PARA TEXTO NORMAL */
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap');

    /* CONTENEDOR PRINCIPAL DE TODA LA VISTA - FONDO BLANCO Y ESPACIADO GENERAL */
    .usuarios-wrapper {
        font-family: 'DM Sans', sans-serif;
        background: #ffffff;
        min-height: 100vh;
        padding: 2.5rem 2rem;
    }

    /* ENCABEZADO SUPERIOR - PONE EL TITULO Y LOS BOTONES EN UNA MISMA LINEA */
    .usuarios-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* GRUPO QUE CONTIENE EL ICONO Y EL TITULO JUNTOS */
    .usuarios-heading {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    /* CUADRO ROJO DECORATIVO QUE ENVUELVE EL ICONO DEL ENCABEZADO */
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

    /* ICONO DENTRO DEL CUADRO ROJO EN COLOR BLANCO */
    .usuarios-icon-wrap i { color: #fff; font-size: 1.7rem; }

    /* TITULO GRANDE CON LA FUENTE ELEGANTE DM SERIF */
    .usuarios-title {
        font-family: 'DM Serif Display', serif;
        font-size: 2rem;
        color: #2a1a1a;
        margin: 0;
        letter-spacing: -0.5px;
    }

    /* SUBTITULO PEQUENO DEBAJO DEL TITULO PRINCIPAL */
    .usuarios-subtitle { font-size: 0.85rem; color: #7a6060; margin: 2px 0 0; }

    /* BOTON GRIS PARA REGISTRAR UN NUEVO USUARIO */
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

    /* AL PASAR EL MOUSE EL BOTON SE LEVANTA Y SU SOMBRA CRECE */
    .btn-registrar:hover {
        background: #737373;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px #737373;
        color: #fff;
    }

    /* CAJA DE ALERTA GENERICA - SIRVE TANTO PARA EXITO COMO PARA ERROR */
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

    /* ALERTA VERDE PARA MENSAJES DE EXITO */
    .alert-success-u { background: #d4edda; color: #155724; }

    /* ALERTA ROJA PARA MENSAJES DE ERROR */
    .alert-danger-u  { background: #f8d7da; color: #721c24; }

    /* CLASE QUE SE APLICA CUANDO LA ALERTA DEBE DESAPARECER CON ANIMACION */
    .alert-fade-out {
        opacity: 0;
        transform: translateY(-10px);
    }

    /* ANIMACION DE ENTRADA - EL ELEMENTO BAJA SUAVEMENTE DESDE ARRIBA */
    @keyframes fadeInDown {
        from { opacity:0; transform:translateY(-10px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ANIMACION DE APARICION - EL ELEMENTO CRECE DESDE UN TAMANO PEQUENO */
    @keyframes zoomIn {
        from { opacity:0; transform:scale(.9); }
        to   { opacity:1; transform:scale(1); }
    }

    /* TARJETA BLANCA QUE CONTIENE TODA LA TABLA DE USUARIOS */
    .card-usuarios {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 30px rgba(0,0,0,.07);
        overflow: hidden;
    }

    /* BARRA ENCIMA DE LA TABLA CON EL BUSCADOR Y LOS FILTROS */
    .card-toolbar {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f0e8e8;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* CAJA DE BUSQUEDA - EL ICONO DE LUPA QUEDA SUPERPUESTO DENTRO A LA IZQUIERDA */
    .search-box {
        position: relative;
        flex: 1;
        min-width: 200px;
    }

    /* CAMPO DE TEXTO DENTRO DE LA CAJA DE BUSQUEDA */
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

    /* AL HACER CLIC EN EL BUSCADOR EL BORDE CAMBIA A GRIS OSCURO */
    .search-box input:focus { border-color: #737373; background: #fff; }

    /* ICONO DE LUPA POSICIONADO DENTRO DEL CAMPO DE TEXTO */
    .search-box .bi-search {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #a08080;
        font-size: 0.95rem;
    }

    /* SELECTORES DESPLEGABLES PARA FILTRAR POR PROCESO O POR ESTADO */
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

    /* AL HACER CLIC EN EL SELECTOR EL BORDE SE PONE GRIS OSCURO */
    .filter-select:focus { border-color: #737373; }

    /* TABLA PRINCIPAL QUE LISTA TODOS LOS USUARIOS */
    .table-usuarios {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    /* FILA DE ENCABEZADOS DE LA TABLA CON FONDO GRIS CLARO */
    .table-usuarios thead th {
        background: #f8f9fa;
        color: black;
        font-weight: 600;
        padding: 0.9rem 1.25rem;
        text-align: left;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    /* CADA FILA DE USUARIO TIENE UNA LINEA SEPARADORA EN LA PARTE INFERIOR */
    .table-usuarios tbody tr {
        border-bottom: 1px solid #f3eded;
        transition: background 0.15s;
    }

    /* AL PASAR EL MOUSE SOBRE UNA FILA SE ACTIVA UN EFECTO DE FONDO */
    .table-usuarios tbody tr:hover { background: #ffff; }

    /* CADA CELDA TIENE ESPACIADO INTERNO PARA QUE EL CONTENIDO RESPIRE */
    .table-usuarios tbody td {
        padding: 0.9rem 1.25rem;
        color: #3a2a2a;
        vertical-align: middle;
    }

    /* PASTILLA QUE MUESTRA EL NOMBRE DEL PROCESO */
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

    /* PASTILLA QUE MUESTRA EL NOMBRE DEL DEPARTAMENTO */
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

    /* CIRCULO DE COLOR CON LA INICIAL DEL NOMBRE DEL USUARIO */
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

    /* CONTENEDOR QUE ALINEA EL CIRCULO CON EL NOMBRE Y CORREO */
    .user-cell { display: flex; align-items: center; }

    /* NOMBRE DEL USUARIO EN NEGRITA */
    .user-info .user-name { font-weight: 600; color: #2a1a1a; }

    /* CORREO DEL USUARIO EN LETRA PEQUENA Y COLOR GRISACEO */
    .user-info .user-email { font-size: 0.8rem; color: #9a7070; }

    /* PASTILLA GENERICA PARA MOSTRAR EL ESTADO DEL USUARIO */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 20px;
        padding: 0.3rem 0.8rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* PASTILLA VERDE PARA USUARIOS ACTIVOS */
    .status-active   { background: #e6f9ed; color: #1a7a3c; }

    /* PASTILLA GRIS PARA USUARIOS INACTIVOS */
    .status-inactive { background: #f3f3f3; color: #888; }

    /* CIRCULITO DE COLOR DENTRO DE LA PASTILLA DE ESTADO */
    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }

    /* CIRCULITO VERDE CUANDO EL USUARIO ESTA ACTIVO */
    .status-active .status-dot   { background: #28a745; }

    /* CIRCULITO GRIS CUANDO EL USUARIO ESTA INACTIVO */
    .status-inactive .status-dot { background: #aaa; }

    /* BOTON GENERICO DE ACCION QUE APARECE EN CADA FILA DE LA TABLA */
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

    /* BOTON ROJO CLARO PARA DESACTIVAR UN USUARIO */
    .btn-desactivar {
        background: #fff0f0;
        color: #800000;
        border: 1.5px solid #f5c6cb;
    }

    /* AL PASAR EL MOUSE EN DESACTIVAR EL COLOR NO CAMBIA */
    .btn-desactivar:hover {
        background: #fff0f0;
        color: #800000;
        border-color: #f5c6cb;
    }

    /* BOTON VERDE CLARO PARA ACTIVAR UN USUARIO */
    .btn-activar {
        background: #f0fff4;
        color: #1a7a3c;
        border: 1.5px solid #c3e6cb;
    }

    /* AL PASAR EL MOUSE EN ACTIVAR SE PONE VERDE SOLIDO */
    .btn-activar:hover {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }

    /* BOTON AZUL CLARO PARA EDITAR LOS DATOS DE UN USUARIO */
    .btn-editar {
        background: #f0f4ff;
        color: #1a3acc;
        border: 1.5px solid #c0ccf5;
        margin-left: 0.4rem;
    }

    /* AL PASAR EL MOUSE EN EDITAR EL COLOR NO CAMBIA */
    .btn-editar:hover {
        background: #f0f4ff;
        color: #1a3acc;
        border-color: #c0ccf5;
    }

    /* MENSAJE CENTRADO QUE APARECE CUANDO NO HAY USUARIOS EN LA TABLA */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #b08080;
    }

    /* ICONO GRANDE DEL ESTADO VACIO */
    .empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.4; display: block; }

    /* TEXTO DEL ESTADO VACIO */
    .empty-state p { margin: 0; font-size: 1rem; }

    /* PIE DE LA TARJETA - MUESTRA EL TOTAL DE USUARIOS Y LOS CONTADORES */
    .card-footer-u {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f0e8e8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #737373;
        font-size: 0.85rem;
    }

    /* CAJA DEL MODAL CON BORDES REDONDEADOS Y SOMBRA PROFUNDA */
    .modal-content {
        border-radius: 16px;
        border: none;
        font-family: 'DM Sans', sans-serif;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
    }

    /* ENCABEZADO DEL MODAL CON FONDO BLANCO Y TEXTO NEGRO */
    .modal-header {
        background: #ffffff;
        color: #000000;
        border-radius: 16px 16px 0 0;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    /* TITULO DEL MODAL CON FUENTE ELEGANTE */
    .modal-title { font-family: 'DM Serif Display', serif; font-size: 1.3rem; }

    /* FILTRO PARA PONER EL BOTON X DE CERRAR EN COLOR BLANCO */
    .btn-close-white { filter: brightness(0) invert(1); }

    /* CUERPO DEL MODAL CON ESPACIADO INTERNO */
    .modal-body { padding: 2rem 1.5rem; }

    /* CADA CAMPO DEL FORMULARIO DEL MODAL TIENE MARGEN INFERIOR */
    .modal-form-group { margin-bottom: 1.25rem; }

    /* ETIQUETA DE CADA CAMPO DEL FORMULARIO */
    .modal-label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 0.5rem;
    }

    /* ICONO DENTRO DE LA ETIQUETA EN COLOR ROJO */
    .modal-label i { color: #800000; margin-right: 0.4rem; }

    /* CAMPO DE TEXTO Y SELECTOR DEL FORMULARIO DEL MODAL */
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

    /* AL HACER CLIC EN EL CAMPO EL BORDE SE PONE GRIS OSCURO */
    .modal-input:focus, .modal-select:focus { border-color: #737373; background: #fff; }

    /* BORDE ROJO CUANDO EL CAMPO TIENE UN ERROR DE VALIDACION */
    .modal-input.is-invalid, .modal-select.is-invalid { border-color: #dc3545; }

    /* TEXTO DE ERROR DEBAJO DEL CAMPO EN COLOR ROJO */
    .field-err { color: #dc3545; font-size: 0.8rem; margin-top: 0.3rem; }

    /* BARRA DELGADA QUE INDICA QUE TAN SEGURA ES LA CONTRASENA */
    .strength-bar { height: 5px; border-radius: 3px; background: #eee; margin-top: 0.5rem; overflow: hidden; }

    /* RELLENO DE COLOR DE LA BARRA - EL ANCHO CAMBIA DINAMICAMENTE CON JAVASCRIPT */
    .strength-fill { height: 100%; width: 0; border-radius: 3px; transition: all 0.3s; }

    /* TEXTO QUE DICE SI LA CONTRASENA ES DEBIL REGULAR O FUERTE */
    .strength-label { font-size: 0.78rem; color: #888; margin-top: 0.3rem; }

    /* PIE DEL MODAL CON LOS BOTONES DE CANCELAR Y GUARDAR */
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f0e8e8; gap: 0.75rem; }

    /* BOTON GRIS PARA CANCELAR Y CERRAR EL MODAL */
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

    /* AL PASAR EL MOUSE EN CANCELAR EL BORDE CAMBIA LIGERAMENTE */
    .btn-modal-cancel:hover { border-color: #aaa; color: #fff; }

    /* BOTON ROJO OSCURO PARA CONFIRMAR Y GUARDAR */
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

    /* AL PASAR EL MOUSE EN GUARDAR APARECE UNA SOMBRA ROJA */
    .btn-modal-submit:hover { background: #800000; box-shadow: 0 4px 14px rgba(128,0,0,.3); }

    /* ============================================================
       RESPONSIVO 768PX - EN MOVIL LA TABLA SE CONVIERTE EN TARJETAS
       CADA FILA SE MUESTRA COMO UN BLOQUE CON ETIQUETAS
    ============================================================ */
    @media (max-width: 768px) {
        .usuarios-wrapper { padding: 1.5rem 1rem; }
        .usuarios-title { font-size: 1.5rem; }
        .table-usuarios thead { display: none; }
        .table-usuarios tbody tr { display: block; padding: 1rem; border-bottom: 2px solid #f0e8e8; }
        .table-usuarios tbody td { display: flex; justify-content: space-between; align-items: center; padding: 0.4rem 0; border: none; }
        .table-usuarios tbody td::before { content: attr(data-label); font-weight: 600; color: #800000; font-size: 0.8rem; }
    }

    /* BOTON GRIS PARA ABRIR EL PANEL DE GESTION DE PROCESOS */
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

    /* AL PASAR EL MOUSE EL BOTON DE PROCESOS SE LEVANTA IGUAL QUE EL DE REGISTRAR */
    .btn-gestionar-procesos:hover {
        background: #737373;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px #737373;
        color: #fff;
    }

    /* FONDO OSCURO SEMITRANSPARENTE QUE CUBRE LA PANTALLA AL ABRIR EL PANEL DE PROCESOS */
    /* EL Z-INDEX ALTO ES PARA QUE SE PONGA ENCIMA DE CUALQUIER OTRO ELEMENTO */
    #overlayProcesos {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.55);
        align-items: center;
        justify-content: center;
        z-index: 1008;
    }

    /* CAJA BLANCA DEL PANEL DE PROCESOS QUE APARECE AL CENTRO DE LA PANTALLA */
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

    /* ENCABEZADO DEL PANEL DE PROCESOS CON TITULO E ICONO */
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

    /* AREA CON SCROLL DONDE SE LISTAN TODOS LOS PROCESOS */
    .pg-body { overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1; }

    /* CAJA DE CADA PROCESO CON BORDE REDONDEADO */
    .pg-grupo {
        border: 1.5px solid #f0e8e8; border-radius: 14px;
        margin-bottom: 1rem; overflow: hidden;
    }

    /* FILA CLICKEABLE QUE MUESTRA EL NOMBRE DEL PROCESO Y SUS BOTONES */
    .pg-grupo-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .85rem 1.1rem; background: #ffffff; cursor: pointer; user-select: none;
        font-family: 'DM Serif Display', serif; color: #0d0d0d; gap: .5rem;
    }

    /* LISTA DE DEPARTAMENTOS - POR DEFECTO ESTA OCULTA */
    .pg-deptos { padding: .5rem 1.1rem .75rem; display: none; }

    /* CUANDO EL PROCESO ESTA EXPANDIDO LA LISTA SE HACE VISIBLE */
    .pg-deptos.open { display: block; }

    /* CADA DEPARTAMENTO EN SU PROPIA FILA CON BOTON DE ELIMINAR */
    .pg-depto-item {
        display: flex; align-items: center; justify-content: space-between;
        background: #faf8f8; border: 1px solid #f0e8e8; border-radius: 8px;
        padding: .5rem .85rem; margin-bottom: .35rem;
    }

    /* FORMULARIO PARA AGREGAR UN NUEVO DEPARTAMENTO - POR DEFECTO OCULTO */
    .pg-add-form {
        display: none; margin-top: .6rem; padding: .65rem;
        background: #fff; border: 1.5px dashed #f0c0c0; border-radius: 9px;
        gap: .5rem; align-items: center;
    }

    /* CUANDO SE HACE CLIC EN AGREGAR DEPTO EL FORMULARIO SE HACE VISIBLE */
    .pg-add-form.open { display: flex; }

    /* CAMPO DE TEXTO DENTRO DEL FORMULARIO DE AGREGAR DEPARTAMENTO */
    .pg-add-form input {
        flex: 1; padding: .5rem .85rem; border: 1.5px solid #e0d4d4; border-radius: 7px;
        font-family: 'DM Sans', sans-serif; font-size: .88rem; outline: none; color: #333;
    }

    /* AL HACER CLIC EN EL CAMPO EL BORDE SE PONE NEGRO */
    .pg-add-form input:focus { border-color: #000000; }

    /* FLECHITA QUE ROTA CUANDO SE EXPANDE UN PROCESO */
    .pg-chevron { transition: transform .2s; color: #a08080; font-size: .85rem; }

    /* CUANDO EL PROCESO ESTA ABIERTO LA FLECHA APUNTA HACIA ARRIBA */
    .pg-chevron.open { transform: rotate(180deg); }

    /* ============================================================
       RESPONSIVO 480PX - TELEFONOS MUY PEQUENOS COMO IPHONE SE
       TODO SE APILA EN COLUMNA PARA APROVECHAR EL ESPACIO
    ============================================================ */
    @media (max-width: 480px) {

        /* EL ENCABEZADO SE APILA EN COLUMNA EN PANTALLAS MUY PEQUENAS */
        .usuarios-header {
            flex-direction: column;
            align-items: flex-start;
        }

        /* LOS BOTONES DEL ENCABEZADO OCUPAN TODO EL ANCHO */
        .usuarios-header > div:last-child {
            width: 100%;
            flex-direction: column;
        }

        /* CADA BOTON OCUPA TODO EL ANCHO Y SE CENTRA */
        .btn-registrar,
        .btn-gestionar-procesos {
            width: 100%;
            justify-content: center;
            font-size: 0.88rem;
            padding: 0.65rem 1rem;
        }

        /* EL TITULO SE HACE UN POCO MAS PEQUENO */
        .usuarios-title {
            font-size: 1.3rem;
        }

        /* LA BARRA DE HERRAMIENTAS SE APILA EN COLUMNA */
        .card-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.65rem;
            padding: 1rem;
        }

        /* LA CAJA DE BUSQUEDA OCUPA TODO EL ANCHO */
        .search-box {
            min-width: unset;
            width: 100%;
        }

        /* CADA SELECTOR OCUPA TODO EL ANCHO */
        .filter-select {
            width: 100%;
        }

        /* LOS CONTADORES DEL PIE SE APILAN EN COLUMNA */
        .card-footer-u {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
            font-size: 0.8rem;
        }

        /* LOS BOTONES DE ACCION OCUPAN TODO EL ANCHO DE SU CELDA */
        .btn-accion {
            width: 100%;
            justify-content: center;
            margin-left: 0 !important;
            margin-top: 0.3rem;
        }

        /* LA COLUMNA DE ACCIONES SE APILA EN COLUMNA */
        .table-usuarios tbody td[data-label="Acciones"] {
            flex-direction: column;
            align-items: flex-end;
            gap: 0.3rem;
        }
    }

    /* ============================================================
       RESPONSIVO 481PX A 576PX - TELEFONOS MEDIANOS
       LOS BOTONES COMPARTEN FILA Y LOS FILTROS SE APILAN
    ============================================================ */
    @media (min-width: 481px) and (max-width: 576px) {

        /* LOS BOTONES DEL ENCABEZADO SE PONEN EN FILA CON WRAP */
        .usuarios-header > div:last-child {
            width: 100%;
            flex-wrap: wrap;
        }

        /* CADA BOTON TOMA LA MITAD DEL ANCHO DISPONIBLE */
        .btn-registrar,
        .btn-gestionar-procesos {
            flex: 1;
            justify-content: center;
            font-size: 0.88rem;
        }

        /* LA BARRA DE HERRAMIENTAS SE APILA EN COLUMNA */
        .card-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.65rem;
        }

        /* LA CAJA DE BUSQUEDA OCUPA TODO EL ANCHO */
        .search-box { width: 100%; }

        /* LOS SELECTORES COMPARTEN LA FILA INFERIOR */
        .card-toolbar > .filter-select {
            flex: 1;
        }
    }

    /* ============================================================
       RESPONSIVO 577PX A 768PX - TABLETS EN VERTICAL
       LA BUSQUEDA VA ARRIBA Y LOS FILTROS EN UNA FILA ABAJO
    ============================================================ */
    @media (min-width: 577px) and (max-width: 768px) {

        /* LA BARRA DE HERRAMIENTAS PERMITE QUE SUS HIJOS SE DISTRIBUYAN */
        .card-toolbar {
            flex-wrap: wrap;
        }

        /* LA BUSQUEDA OCUPA UNA FILA COMPLETA */
        .search-box {
            flex: 1 1 100%;
        }

        /* LOS SELECTORES COMPARTEN LA SIGUIENTE FILA */
        .filter-select {
            flex: 1;
        }

        /* EL PIE DE TARJETA PUEDE HACER SALTO DE LINEA SI NO CABE */
        .card-footer-u {
            font-size: 0.82rem;
            flex-wrap: wrap;
            gap: 0.35rem;
        }
    }

    /* ============================================================
       RESPONSIVO 769PX A 1024PX - TABLETS EN HORIZONTAL Y LAPTOPS PEQUENAS
       SE REDUCE UN POCO EL ESPACIADO Y EL TAMANO DE LAS PASTILLAS
    ============================================================ */
    @media (min-width: 769px) and (max-width: 1024px) {

        /* MENOS PADDING EN EL CONTENEDOR PRINCIPAL */
        .usuarios-wrapper {
            padding: 2rem 1.5rem;
        }

        /* CELDAS MAS COMPACTAS PARA QUE QUEPA MAS CONTENIDO */
        .table-usuarios thead th,
        .table-usuarios tbody td {
            padding: 0.75rem 1rem;
        }

        /* PASTILLAS DE PROCESO Y DEPARTAMENTO MAS PEQUENAS */
        .badge-proceso,
        .badge-depto {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
        }
    }

    /* ============================================================
       RESPONSIVO DEL PANEL DE PROCESOS EN MOVIL - MENOR A 576PX
       EL PANEL SE ADAPTA AL ANCHO DEL CELULAR Y SUS ELEMENTOS SE APILAN
    ============================================================ */
    @media (max-width: 576px) {

        /* EL PANEL OCUPA CASI TODO EL ANCHO Y MAS ALTO */
        .pg-overlay-panel {
            width: 96%;
            max-height: 92vh;
            border-radius: 14px;
        }

        /* EL ENCABEZADO DEL PANEL SE HACE MAS COMPACTO */
        .pg-header {
            padding: 1rem 1.1rem;
            font-size: 0.95rem;
        }

        /* EL CUERPO DEL PANEL TIENE MENOS PADDING */
        .pg-body {
            padding: 1rem;
        }

        /* EL ENCABEZADO DE CADA PROCESO PERMITE SALTO DE LINEA */
        .pg-grupo-header {
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.7rem 0.9rem;
        }

        /* LOS BOTONES DE AGREGAR Y ELIMINAR SE PONEN EN FILA CON WRAP */
        .pg-grupo-header > div:last-child {
            width: 100%;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.35rem;
        }

        /* EL FORMULARIO DE AGREGAR DEPARTAMENTO SE APILA EN COLUMNA */
        .pg-add-form {
            flex-direction: column;
            align-items: stretch;
        }

        /* EL CAMPO DE TEXTO OCUPA TODO EL ANCHO */
        .pg-add-form input {
            width: 100%;
        }

        /* LOS BOTONES DE AGREGAR Y CANCELAR OCUPAN TODO EL ANCHO */
        .pg-add-form button {
            width: 100%;
            padding: 0.4rem;
        }
    }

    /* ============================================================
       RESPONSIVO DEL MODAL DE REGISTRO EN MOVIL - MENOR A 576PX
       LOS BOTONES SE APILAN Y EL MODAL TIENE MENOS PADDING
    ============================================================ */
    @media (max-width: 576px) {

        /* EL CUERPO DEL MODAL TIENE MENOS PADDING EN MOVIL */
        .modal-body {
            padding: 1.25rem 1rem;
        }

        /* LOS BOTONES DEL PIE DEL MODAL SE APILAN EN COLUMNA INVERTIDA */
        .modal-footer {
            flex-direction: column-reverse;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
        }

        /* CADA BOTON DEL MODAL OCUPA TODO EL ANCHO */
        .btn-modal-cancel,
        .btn-modal-submit {
            width: 100%;
            justify-content: center;
        }
    }

    /* ============================================================
       RESPONSIVO DEL OVERLAY DE EDICION EN MOVIL - MENOR A 576PX
       LA CAJA INTERNA SE ADAPTA AL ANCHO DEL CELULAR
    ============================================================ */
    @media (max-width: 576px) {

        /* LA CAJA BLANCA INTERNA TIENE MENOS PADDING Y MAS ANCHO */
        #overlayEditarAdmin > div {
            padding: 1.5rem 1.25rem !important;
            width: 95% !important;
        }

        /* LOS BOTONES DE GUARDAR Y CANCELAR SE APILAN EN COLUMNA */
        #overlayEditarAdmin > div > form > div:last-child {
            flex-direction: column-reverse;
            gap: 0.5rem;
        }

        /* CADA BOTON OCUPA TODO EL ANCHO */
        #overlayEditarAdmin .btn-modal-cancel,
        #overlayEditarAdmin .btn-modal-submit {
            width: 100%;
            justify-content: center;
        }
    }

    /* ============================================================
       MEJORAS A LA TABLA EN MODO TARJETA - MENOR A 768PX
       SE AFINAN LOS DETALLES DE ALINEACION Y TAMANO EN MOVIL
    ============================================================ */
    @media (max-width: 768px) {

        /* EL CONTENIDO DE LA CELDA USUARIO SE ALINEA A LA DERECHA */
        .table-usuarios tbody td .user-cell {
            justify-content: flex-end;
        }

        /* EL CIRCULO DEL AVATAR SE HACE UN POCO MAS PEQUENO EN MOVIL */
        .avatar-circle {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }

        /* LAS PASTILLAS PUEDEN HACER SALTO DE LINEA Y SE ALINEAN A LA DERECHA */
        .badge-proceso,
        .badge-depto {
            white-space: normal;
            text-align: right;
            max-width: 60vw;
        }

        /* LA PASTILLA DE ESTADO SE EMPUJA A LA DERECHA */
        .status-badge {
            margin-left: auto;
        }

        /* LOS BOTONES DE ACCION SE PONEN EN FILA CON WRAP A LA DERECHA */
        .table-usuarios tbody td[data-label="Acciones"] {
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.35rem;
        }

        /* EL BOTON EDITAR PIERDE SU MARGEN IZQUIERDO EN MOVIL */
        .btn-editar {
            margin-left: 0 !important;
        }

        /* EL TEXTO DE SIN PERMISOS SE EMPUJA A LA DERECHA */
        .table-usuarios tbody td[data-label="Acciones"] span {
            margin-left: auto;
        }
    }

    /* ============================================================
       CONTENEDOR DE LA TABLA CON SCROLL HORIZONTAL SUAVE
       PERMITE QUE LA TABLA SE PUEDA DESLIZAR HORIZONTALMENTE
       EN PANTALLAS DONDE NO CABE COMPLETA
    ============================================================ */
    .table-scroll-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;      /* SCROLL SUAVE EN IOS */
        scrollbar-width: thin;                  /* BARRA DE SCROLL DELGADA EN FIREFOX */
        scrollbar-color: #e0d0d0 transparent;   /* COLORES DE LA BARRA EN FIREFOX */
    }

    /* BARRA DE SCROLL DELGADA Y DISCRETA EN CHROME Y SAFARI */
    .table-scroll-wrapper::-webkit-scrollbar {
        height: 4px;
    }
    .table-scroll-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }
    .table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #e0d0d0;
        border-radius: 4px;
    }

    /* EN DESKTOP EL NOMBRE Y CORREO DEL USUARIO NO SE CORTAN CON PUNTOS SUSPENSIVOS */
    @media (min-width: 769px) {
        .user-info .user-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        .user-info .user-email {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
    }

    /* AL IMPRIMIR SE OCULTAN LOS BOTONES Y FILTROS QUE NO SON UTILES EN PAPEL */
    @media print {
        .usuarios-header > div:last-child,
        .card-toolbar,
        .table-usuarios tbody td[data-label="Acciones"],
        .table-usuarios thead th:last-child {
            display: none !important;
        }

        .card-usuarios {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }

</style>
@endpush

@section('content')
<div class="usuarios-wrapper">

    {{-- DIVS OCULTOS QUE GUARDAN LOS MENSAJES DE SESION PARA QUE JAVASCRIPT LOS LEA --}}
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

    {{-- ENCABEZADO: TITULO DE LA VISTA Y BOTONES DE ACCION --}}
    <div class="usuarios-header">
        <div class="usuarios-heading">
            {{-- AL HACER CLIC EN EL TITULO SE VA AL DASHBOARD --}}
            <a href="{{ route('dashboard') }}" class="text-decoration-none" title="Ir al Dashboard">
                <h1 class="h3 mb-2" style="color: #7c3aed; cursor: pointer;">
                    <i class="bi bi-people-fill me-2" style="font-size: 3rem; vertical-align: middle;"></i>
                    Usuarios
                </h1>
            </a>
        </div>

        {{-- BOTONES DEL ENCABEZADO - EL DE GESTIONAR PROCESOS SOLO LO VEN LOS ADMINS --}}
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

    {{-- TARJETA PRINCIPAL CON BUSCADOR FILTROS Y TABLA --}}
    <div class="card-usuarios">

        {{-- BARRA DE HERRAMIENTAS: BUSCADOR + FILTRO DE PROCESO + FILTRO DE ESTADO --}}
        <div class="card-toolbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por nombre, correo o departamento…">
            </div>
            {{-- SELECTOR PARA FILTRAR POR PROCESO --}}
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
                <option value="Sistema de Gestión de la Calidad">SGC</option>
            </select>
            {{-- SELECTOR PARA FILTRAR POR ESTADO ACTIVO O INACTIVO --}}
            <select class="filter-select" id="filterEstado">
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
            </select>
        </div>

        {{-- TABLA ENVUELTA EN DIV CON SCROLL HORIZONTAL SUAVE --}}
        {{-- EN MOVIL MENOR A 768PX CADA FILA SE CONVIERTE EN UNA TARJETA --}}
        <div class="table-scroll-wrapper">
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
                    {{-- LOS ATRIBUTOS data-* SON USADOS POR JAVASCRIPT PARA FILTRAR SIN RECARGAR LA PAGINA --}}
                    <tr
                        data-nombre="{{ strtolower($usuario->name) }}"
                        data-email="{{ strtolower($usuario->email) }}"
                        data-depto="{{ strtolower($usuario->departamento) }}"
                        data-proceso="{{ $usuario->proceso }}"
                        data-estado="{{ $usuario->is_active ? 'activo' : 'inactivo' }}"
                    >
                        {{-- data-label ES EL TEXTO QUE SE MUESTRA COMO ETIQUETA EN MODO MOVIL --}}
                        <td data-label="Proceso">
                            <span class="badge-proceso">{{ $usuario->proceso ?? '—' }}</span>
                        </td>
                        <td data-label="Departamento">
                            <span class="badge-depto">{{ $usuario->departamento ?? '—' }}</span>
                        </td>
                        <td data-label="Usuario">
                            <div class="user-cell">
                                {{-- CIRCULO CON LA PRIMERA LETRA DEL NOMBRE --}}
                                <div class="avatar-circle">{{ strtoupper(substr($usuario->name, 0, 1)) }}</div>
                                <div class="user-info">
                                    <div class="user-name">
                                        {{ $usuario->name }}
                                        {{-- ETIQUETA NARANJA SOLO PARA USUARIOS CON ROL ADMIN --}}
                                        @if($usuario->role === 'admin')
                                            <span style="background:#fff3e0;color:#cc5500;border:1px solid #f5c6a0;border-radius:20px;padding:.15rem .6rem;font-size:.7rem;font-weight:700;margin-left:.4rem;vertical-align:middle;">Admin</span>
                                        @endif
                                    </div>
                                    <div class="user-email">{{ $usuario->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Estado">
                            {{-- MUESTRA ACTIVO EN VERDE O INACTIVO EN GRIS SEGUN EL CAMPO is_active --}}
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
                            {{-- LOS BOTONES DE ACCION SOLO LOS VE EL SUPERADMIN --}}
                            @if(auth()->user()->isSuperAdmin())
                                {{-- FORMULARIO PATCH PARA CAMBIAR EL ESTADO DEL USUARIO --}}
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
                                {{-- BOTON QUE ABRE EL OVERLAY DE EDICION CON LOS DATOS DEL USUARIO --}}
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
                    {{-- FILA QUE SE MUESTRA CUANDO NO HAY NINGUN USUARIO REGISTRADO --}}
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

        {{-- PIE DE LA TARJETA CON EL TOTAL DE USUARIOS VISIBLES Y CONTEOS POR ESTADO --}}
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

{{-- OVERLAY PARA EDITAR DATOS DE UN USUARIO: NOMBRE CORREO Y CONTRASENA --}}
{{-- SE ABRE AL HACER CLIC EN EL BOTON EDITAR DE CUALQUIER FILA --}}
{{-- SE CIERRA CON ESCAPE CON CLICK EN EL FONDO OSCURO O CON EL BOTON CANCELAR --}}
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
                {{-- EL TITULO Y SUBTITULO SE LLENAN DINAMICAMENTE CON JAVASCRIPT --}}
                <h5 style="font-family:'DM Serif Display',serif;font-size:1.3rem;margin:0;" id="editAdminTitle">Editar Usuario</h5>
                <small style="color:#888;" id="editAdminSubtitle"></small>
            </div>
        </div>

        {{-- EL ATRIBUTO ACTION SE CAMBIA CON JAVASCRIPT SEGUN EL USUARIO A EDITAR --}}
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
                    {{-- ICONO OJO PARA MOSTRAR U OCULTAR LA CONTRASENA --}}
                    <i class="bi bi-eye" id="toggleEditPwd" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                </div>
            </div>

            <div class="modal-form-group">
                <label class="modal-label"><i class="bi bi-lock-fill"></i> Confirmar Contraseña</label>
                <div style="position:relative;">
                    <input type="password" name="password_confirmation" id="editAdminPwdConf" class="modal-input" placeholder="Repite la contraseña" style="padding-right:2.5rem;">
                    {{-- ICONO OJO PARA MOSTRAR U OCULTAR LA CONFIRMACION --}}
                    <i class="bi bi-eye" id="toggleEditPwdConf" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                </div>
                {{-- ESTE TEXTO CAMBIA A VERDE O ROJO SEGUN SI LAS CONTRASENAS COINCIDEN --}}
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

{{-- MODAL DE BOOTSTRAP PARA REGISTRAR UN NUEVO USUARIO --}}
{{-- SE ABRE CON EL BOTON DEL ENCABEZADO QUE TIENE data-bs-toggle="modal" --}}
<div class="modal fade" id="modalRegistrar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
       <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #b5b2b2;">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2" style="color: #000000;"></i> Nuevo Usuario</h5>
            </div>

            <form method="POST" action="{{ route('admin.usuarios.store') }}">
                @csrf
                <div class="modal-body">

                    {{-- SI LARAVEL DEVUELVE ERRORES DE VALIDACION SE MUESTRAN AQUI EN ROJO --}}
                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-left: 4px solid #dc3545; border-radius: 8px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- CAMPO NOMBRE COMPLETO --}}
                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-person"></i> Nombre Completo</label>
                        <input type="text" name="name" class="modal-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Ej. Juan Pérez" required>
                        @error('name')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    {{-- CAMPO CORREO ELECTRONICO --}}
                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-envelope"></i> Correo Electrónico</label>
                        <input type="email" name="email" class="modal-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="ejemplo@uptex.edu.mx" required>
                        @error('email')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    {{-- SELECTOR DE PROCESO - AL ELEGIR "OTRO" APARECEN CAMPOS EXTRA PARA CREAR UNO NUEVO --}}
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
                            <option value="Sistema de Gestión de la Calidad">Sistema de Gestión de la Calidad (SGC)</option>
                            {{-- OPCIONES DINAMICAS DE PROCESOS CREADOS POR EL ADMIN --}}
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

                        {{-- CAMPOS EXTRA QUE APARECEN SOLO CUANDO SE ELIGE LA OPCION "OTRO" --}}
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

                    {{-- SELECTOR DE DEPARTAMENTO - SE FILTRA AUTOMATICAMENTE SEGUN EL PROCESO ELEGIDO --}}
                    {{-- CADA OPCION TIENE data-proceso CON LOS PROCESOS COMPATIBLES SEPARADOS POR COMA --}}
                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-building"></i> Departamento</label>
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
                            <option value="Rectoría SGC" data-proceso="Sistema de Gestión de la Calidad" {{ old('departamento')=='Rectoría SGC'?'selected':'' }}>Rectoría</option>
                            <option value="Auditoría" data-proceso="Sistema de Gestión de la Calidad" {{ old('departamento')=='Auditoría'?'selected':'' }}>Auditoría</option>
                            {{-- DEPARTAMENTOS DE PROCESOS PERSONALIZADOS --}}
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

                    {{-- CAMPO CONTRASENA CON BARRA DE SEGURIDAD QUE SE LLENA CON JAVASCRIPT --}}
                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-lock"></i> Contraseña</label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="modalPassword"
                                class="modal-input @error('password') is-invalid @enderror"
                                placeholder="Mínimo 8 caracteres" required style="padding-right:2.5rem;">
                            <i class="bi bi-eye" id="toggleModalPwd"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                        </div>
                        {{-- BARRA DE COLOR QUE INDICA QUE TAN SEGURA ES LA CONTRASENA --}}
                        <div class="strength-bar"><div class="strength-fill" id="modalStrengthFill"></div></div>
                        <div class="strength-label" id="modalStrengthText">Seguridad de la contraseña</div>
                        @error('password')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    {{-- CAMPO DE CONFIRMACION DE CONTRASENA CON RETROALIMENTACION EN TIEMPO REAL --}}
                    <div class="modal-form-group">
                        <label class="modal-label"><i class="bi bi-lock-fill"></i> Confirmar Contraseña</label>
                        <div style="position:relative;">
                            <input type="password" name="password_confirmation" id="modalPasswordConfirm"
                                class="modal-input" placeholder="Repite la contraseña" required style="padding-right:2.5rem;">
                            <i class="bi bi-eye" id="toggleModalPwdConfirm"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#a08080;"></i>
                        </div>
                        {{-- ESTE TEXTO CAMBIA A VERDE O ROJO SEGUN SI LAS CONTRASENAS COINCIDEN --}}
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

{{-- OVERLAY DE GESTION DE PROCESOS - SOLO VISIBLE PARA SUPERADMIN Y ADMIN --}}
{{-- MUESTRA LOS PROCESOS PERSONALIZADOS EN FORMA DE ACORDEON --}}
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
                    {{-- CADA PROCESO SE MUESTRA COMO UN BLOQUE EXPANDIBLE (ACORDEON) --}}
                    @foreach($pgAgrup as $pgNombre => $pgDeptos)
                    <div class="pg-grupo">
                        {{-- FILA DEL PROCESO CON NOMBRE CONTADOR Y BOTONES DE ACCION --}}
                        <div class="pg-grupo-header" onclick="pgToggle('pg-{{ $loop->index }}')">
                            <div style="display:flex;align-items:center;gap:.5rem;flex:1;">
                                <i class="bi bi-diagram-3"></i>
                                {{ $pgNombre }}
                                {{-- CONTADOR DE DEPARTAMENTOS DEL PROCESO --}}
                                <span style="background:#737373;color:#fff;border-radius:20px;padding:.1rem .5rem;font-size:.72rem;font-weight:300;">
                                    {{ $pgDeptos->count() }} depto{{ $pgDeptos->count()!=1?'s':'' }}
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:.4rem;" onclick="event.stopPropagation()">
                                {{-- BOTON PARA MOSTRAR EL FORMULARIO DE AGREGAR DEPARTAMENTO --}}
                                <button type="button"
                                    style="background:#737373;color:#fff;border:none;border-radius:7px;padding:.3rem .65rem;font-size:.78rem;cursor:pointer;"
                                    onclick="pgToggleAdd('pg-add-{{ $loop->index }}','pg-{{ $loop->index }}')">
                                    <i class="bi bi-plus-circle"></i> Agregar depto
                                </button>
                                {{-- FORMULARIO PARA ELIMINAR TODO EL PROCESO Y SUS DEPARTAMENTOS --}}
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
                                {{-- FLECHITA QUE ROTA CUANDO SE EXPANDE EL PROCESO --}}
                                <i class="bi bi-chevron-down pg-chevron" id="pgicon-{{ $loop->index }}"></i>
                            </div>
                        </div>

                        {{-- LISTA DE DEPARTAMENTOS DEL PROCESO - OCULTA POR DEFECTO --}}
                        <div class="pg-deptos" id="pg-{{ $loop->index }}">
                            @foreach($pgDeptos as $pgD)
                            <div class="pg-depto-item">
                                <span style="font-size:.88rem;color:#3a2a2a;">
                                    <i class="bi bi-building" style="color:#000000;"></i>
                                    {{ $pgD->departamento }}
                                </span>
                                {{-- FORMULARIO PARA ELIMINAR UN DEPARTAMENTO INDIVIDUAL --}}
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

                            {{-- FORMULARIO INLINE PARA AGREGAR UN NUEVO DEPARTAMENTO AL PROCESO --}}
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
                    {{-- MENSAJE CUANDO NO HAY PROCESOS PERSONALIZADOS CREADOS --}}
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
{{-- LIBRERIA SWEETALERT2 PARA MOSTRAR DIALOGOS DE CONFIRMACION BONITOS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // MUESTRA ALERTA DE EXITO CUANDO SE ACTIVO O DESACTIVO UN USUARIO
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

    // MUESTRA ALERTA DE ERROR CUANDO FALLO ACTIVAR O DESACTIVAR UN USUARIO
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

    // MUESTRA ALERTA DE EXITO CUANDO SE REALIZO UNA ACCION EN PROCESOS
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

    // MUESTRA ALERTA DE ERROR CUANDO FALLO UNA ACCION EN PROCESOS
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

    // SI LARAVEL DEVOLVIO ERRORES DE VALIDACION EL MODAL SE REABRE AUTOMATICAMENTE
    @if($errors->any())
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRegistrar')).show();
    @endif

    // REFERENCIAS A LOS ELEMENTOS DEL FORMULARIO DEL MODAL
    const modalProceso = document.getElementById('modalProceso');
    const nuevoProcesoWrap = document.getElementById('nuevoProcesoWrap');
    const modalDepartamento = document.getElementById('modalDepartamento');
    const deptoGroup = modalDepartamento.closest('.modal-form-group');

    // MUESTRA U OCULTA LOS CAMPOS DE NUEVO PROCESO SEGUN LA OPCION ELEGIDA
    function toggleNuevoProceso() {
        if (modalProceso.value === '__otro__') {
            // SI SE ELIGIO "OTRO" MUESTRA EL BLOQUE EXTRA Y OCULTA EL SELECTOR DE DEPARTAMENTOS
            nuevoProcesoWrap.style.display = 'block';
            deptoGroup.style.display = 'none';
            document.getElementById('nuevoProceso').required = true;
            document.getElementById('nuevoDepartamento').required = true;
            modalDepartamento.required = false;
        } else {
            // SI SE ELIGIO UN PROCESO NORMAL OCULTA EL BLOQUE EXTRA Y MUESTRA EL SELECTOR
            nuevoProcesoWrap.style.display = 'none';
            deptoGroup.style.display = 'block';
            document.getElementById('nuevoProceso').required = false;
            document.getElementById('nuevoDepartamento').required = false;
            modalDepartamento.required = true;
        }
    }

    modalProceso.addEventListener('change', toggleNuevoProceso);
    // EJECUTAR AL CARGAR POR SI HAY UN PROCESO YA SELECCIONADO CON old()
    toggleNuevoProceso();

    // FILTRA LOS DEPARTAMENTOS DEL SELECTOR SEGUN EL PROCESO ELEGIDO
    function filterModalDepto() {
        const sel = modalProceso.value;
        if (sel === '__otro__' || !sel) return;

        const selectedOpt = modalProceso.options[modalProceso.selectedIndex];
        const deptosJsonRaw = selectedOpt ? selectedOpt.getAttribute('data-deptos-json') : null;
        const deptosJson = deptosJsonRaw ? JSON.parse(deptosJsonRaw) : null;

        // LIMPIA LA SELECCION ACTUAL DEL DEPARTAMENTO
        modalDepartamento.value = '';

        modalDepartamento.querySelectorAll('option').forEach(opt => {
            if (!opt.value) return;

            if (deptosJson) {
                // PROCESO PERSONALIZADO: SOLO MUESTRA SUS DEPARTAMENTOS
                opt.style.display = deptosJson.includes(opt.value) ? '' : 'none';
            } else {
                // PROCESO ESTANDAR: FILTRA POR EL ATRIBUTO data-proceso DE CADA OPCION
                const allowed = (opt.getAttribute('data-proceso') || '').split(',');
                opt.style.display = (!sel || allowed.includes(sel)) ? '' : 'none';
            }
        });

        // SI SOLO HAY UN DEPARTAMENTO DISPONIBLE LO SELECCIONA AUTOMATICAMENTE
        const opcionesVisibles = Array.from(modalDepartamento.options)
            .filter(opt => opt.value && opt.style.display !== 'none');
        if (opcionesVisibles.length === 1) {
            modalDepartamento.value = opcionesVisibles[0].value;
        }
    }

    modalProceso.addEventListener('change', filterModalDepto);

    // FILTRAR AL CARGAR SI YA HAY UN PROCESO SELECCIONADO
    if (modalProceso.value && modalProceso.value !== '__otro__') {
        filterModalDepto();
    }

    // FUNCION PARA ALTERNAR ENTRE MOSTRAR Y OCULTAR UNA CONTRASENA AL HACER CLIC EN EL OJO
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input && icon) {
            icon.addEventListener('click', function() {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                // CAMBIA EL ICONO DE OJO ABIERTO A OJO CERRADO Y VICEVERSA
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }
    }

    // APLICA EL TOGGLE DE CONTRASENA A TODOS LOS CAMPOS QUE LO NECESITAN
    togglePassword('modalPassword',        'toggleModalPwd');
    togglePassword('modalPasswordConfirm', 'toggleModalPwdConfirm');
    togglePassword('editAdminPwd',         'toggleEditPwd');
    togglePassword('editAdminPwdConf',     'toggleEditPwdConf');

    // REFERENCIAS A LOS ELEMENTOS DEL MEDIDOR DE SEGURIDAD DE CONTRASENA
    const pwdInput     = document.getElementById('modalPassword');
    const pwdConfirm   = document.getElementById('modalPasswordConfirm');
    const strengthFill = document.getElementById('modalStrengthFill');
    const strengthText = document.getElementById('modalStrengthText');
    const matchText    = document.getElementById('modalMatchText');

    if (pwdInput) {
        pwdInput.addEventListener('input', function() {
            const pwd = this.value;
            let strength = 0;

            // SUMA UN PUNTO POR CADA CRITERIO QUE CUMPLE LA CONTRASENA
            if (pwd.length >= 8)             strength++; // LONGITUD MINIMA
            if (pwd.length >= 12)            strength++; // LONGITUD BUENA
            if (/[a-z]/.test(pwd))           strength++; // TIENE MINUSCULAS
            if (/[A-Z]/.test(pwd))           strength++; // TIENE MAYUSCULAS
            if (/[0-9]/.test(pwd))           strength++; // TIENE NUMEROS
            if (/[^a-zA-Z0-9]/.test(pwd))   strength++; // TIENE SIMBOLOS

            // MAPA QUE DEFINE EL ANCHO COLOR Y TEXTO DE LA BARRA SEGUN LOS PUNTOS
            const strengthMap = {
                0: { width: 5,   color: '#dc3545', text: 'Muy débil' },
                1: { width: 15,  color: '#dc3545', text: 'Débil' },
                2: { width: 35,  color: '#ffc107', text: 'Regular' },
                3: { width: 55,  color: '#ffc107', text: 'Regular' },
                4: { width: 75,  color: '#28a745', text: 'Buena' },
                5: { width: 90,  color: '#28a745', text: 'Fuerte' },
                6: { width: 100, color: '#28a745', text: 'Muy fuerte' }
            };

            const s = Math.min(strength, 6);
            // ACTUALIZA LA BARRA DE COLOR Y EL TEXTO SEGUN LOS PUNTOS OBTENIDOS
            strengthFill.style.width           = strengthMap[s].width + '%';
            strengthFill.style.backgroundColor = strengthMap[s].color;
            strengthText.textContent           = strengthMap[s].text;
            strengthText.style.color           = strengthMap[s].color;

            if (pwdConfirm) checkPasswordMatch();
        });
    }

    if (pwdConfirm) {
        pwdConfirm.addEventListener('input', checkPasswordMatch);
    }

    // COMPARA LAS DOS CONTRASENAS Y MUESTRA UN MENSAJE EN VERDE O EN ROJO
    function checkPasswordMatch() {
        if (!pwdConfirm || !pwdInput) return;

        if (!pwdConfirm.value) {
            matchText.textContent  = 'Las contraseñas deben coincidir';
            matchText.style.color  = '#888';
        } else if (pwdInput.value === pwdConfirm.value) {
            matchText.textContent  = '✓ Las contraseñas coinciden';
            matchText.style.color  = '#28a745';
        } else {
            matchText.textContent  = '✗ Las contraseñas no coinciden';
            matchText.style.color  = '#dc3545';
        }
    }

    // REFERENCIAS PARA LOS FILTROS DE LA TABLA
    const searchInput   = document.getElementById('searchInput');
    const filterProceso = document.getElementById('filterProceso');
    const filterEstado  = document.getElementById('filterEstado');
    const tbody         = document.getElementById('tbodyUsuarios');
    const countLabel    = document.getElementById('countLabel');

    // RECORRE TODAS LAS FILAS Y OCULTA LAS QUE NO COINCIDEN CON LOS FILTROS ACTIVOS
    function aplicarFiltros() {
        if (!tbody) return;

        const busqueda = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const proceso  = filterProceso ? filterProceso.value : '';
        const estado   = filterEstado  ? filterEstado.value  : '';
        let visibles   = 0;

        tbody.querySelectorAll('tr[data-nombre]').forEach(row => {
            const nombre = row.dataset.nombre || '';
            const email  = row.dataset.email  || '';
            const depto  = row.dataset.depto  || '';

            // VERIFICA SI LA FILA COINCIDE CON EL TEXTO BUSCADO EN NOMBRE CORREO O DEPTO
            const coincideBusqueda = !busqueda ||
                nombre.includes(busqueda) ||
                email.includes(busqueda)  ||
                depto.includes(busqueda);

            // VERIFICA SI LA FILA COINCIDE CON EL PROCESO SELECCIONADO EN EL FILTRO
            const coincideProceso = !proceso || row.dataset.proceso === proceso;

            // VERIFICA SI LA FILA COINCIDE CON EL ESTADO SELECCIONADO EN EL FILTRO
            const coincideEstado  = !estado  || row.dataset.estado  === estado;

            const visible = coincideBusqueda && coincideProceso && coincideEstado;
            row.style.display = visible ? '' : 'none';
            if (visible) visibles++;
        });

        // ACTUALIZA EL CONTADOR DEL PIE DE LA TARJETA
        if (countLabel) {
            countLabel.innerHTML = `Mostrando <strong>${visibles}</strong> usuario(s)`;
        }

        // SI NO HAY RESULTADOS AGREGA UNA FILA DE MENSAJE
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
            // SI YA HAY RESULTADOS ELIMINA LA FILA DE MENSAJE
            emptyRow.remove();
        }
    }

    // ESCUCHA CAMBIOS EN EL BUSCADOR Y LOS SELECTORES PARA FILTRAR EN TIEMPO REAL
    if (searchInput)   searchInput.addEventListener('input', aplicarFiltros);
    if (filterProceso) filterProceso.addEventListener('change', aplicarFiltros);
    if (filterEstado)  filterEstado.addEventListener('change', aplicarFiltros);

    // ESCUCHA CLICKS EN LA TABLA - SI FUE EN UN BOTON EDITAR ABRE EL OVERLAY CON LOS DATOS
    const tbodyUsuarios = document.getElementById('tbodyUsuarios');
    if (tbodyUsuarios) {
        tbodyUsuarios.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-editar');
            if (!btn) return;

            // LLENA EL FORMULARIO DEL OVERLAY CON LOS DATOS DEL BOTON CLICKEADO
            document.getElementById('editAdminTitle').textContent    = 'Editar Usuario';
            document.getElementById('editAdminSubtitle').textContent = btn.dataset.nombre;
            document.getElementById('editAdminNombre').value         = btn.dataset.nombre;
            document.getElementById('editAdminEmail').value          = btn.dataset.email;
            document.getElementById('editAdminPwd').value            = '';
            document.getElementById('editAdminPwdConf').value        = '';
            document.getElementById('editMatchText').textContent     = 'Las contraseñas deben coincidir';
            document.getElementById('editMatchText').style.color     = '#888';
            // CAMBIA LA URL DEL FORMULARIO AL ENDPOINT CORRECTO DEL USUARIO
            document.getElementById('formEditarAdmin').action        = btn.dataset.url;

            // MUESTRA EL OVERLAY Y BLOQUEA EL SCROLL DEL FONDO
            document.getElementById('overlayEditarAdmin').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }

    // COMPARA LAS CONTRASENAS EN EL OVERLAY DE EDICION Y MUESTRA RETROALIMENTACION
    const editPwdConf = document.getElementById('editAdminPwdConf');
    if (editPwdConf) {
        editPwdConf.addEventListener('input', function() {
            const pwd  = document.getElementById('editAdminPwd').value;
            const conf = this.value;
            const el   = document.getElementById('editMatchText');

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

    // AL PRESIONAR ESCAPE SE CIERRAN LOS OVERLAYS SI ESTAN ABIERTOS
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarOverlayEditar();
            cerrarOverlayProcesos();
        }
    });

});

// CIERRA EL OVERLAY DE EDICION Y DEVUELVE EL SCROLL AL FONDO
function cerrarOverlayEditar() {
    const el = document.getElementById('overlayEditarAdmin');
    if (el) {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// CIERRA EL OVERLAY DE PROCESOS Y DEVUELVE EL SCROLL AL FONDO
function cerrarOverlayProcesos() {
    const el = document.getElementById('overlayProcesos');
    if (el) {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ABRE EL OVERLAY DE PROCESOS Y BLOQUEA EL SCROLL DEL FONDO
function abrirOverlayProcesos() {
    const el = document.getElementById('overlayProcesos');
    if (el) {
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

// MUESTRA UN DIALOGO DE CONFIRMACION ANTES DE ACTIVAR O DESACTIVAR UN USUARIO
// SI EL USUARIO CONFIRMA SE ENVIA EL FORMULARIO
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
        if (result.isConfirmed) form.submit();
    });

    return false;
}

// MUESTRA UN DIALOGO DE CONFIRMACION ANTES DE ELIMINAR UN DEPARTAMENTO
// BUSCA EL FORMULARIO POR SU ID Y LO ENVIA SI SE CONFIRMA
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
        if (result.isConfirmed) form.submit();
    });

    return false;
}

// MUESTRA UN DIALOGO DE CONFIRMACION ANTES DE ELIMINAR UN PROCESO COMPLETO
// ADVERTENCIA: ESTO ELIMINA EL PROCESO Y TODOS SUS DEPARTAMENTOS
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
        if (result.isConfirmed) form.submit();
    });

    return false;
}

// EXPANDE O COLAPSA LA LISTA DE DEPARTAMENTOS DE UN PROCESO EN EL ACORDEON
// SOLO UN PROCESO PUEDE ESTAR ABIERTO A LA VEZ - CIERRA LOS DEMAS
function pgToggle(grupoId) {
    const lista = document.getElementById(grupoId);
    const icon  = document.getElementById('pgicon-' + grupoId.replace('pg-', ''));
    if (!lista) return;

    const isOpen = lista.classList.contains('open');

    // CIERRA TODOS LOS GRUPOS Y REGRESA TODAS LAS FLECHAS A SU POSICION ORIGINAL
    document.querySelectorAll('.pg-deptos').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.pg-chevron').forEach(el => el.classList.remove('open'));

    // SI ESTABA CERRADO LO ABRE Y ROTA LA FLECHITA
    if (!isOpen) {
        lista.classList.add('open');
        if (icon) icon.classList.add('open');
    }
}

// MUESTRA U OCULTA EL FORMULARIO DE AGREGAR DEPARTAMENTO DENTRO DE UN PROCESO
// SI EL PROCESO ESTA CERRADO LO ABRE PRIMERO ANTES DE MOSTRAR EL FORMULARIO
function pgToggleAdd(formId, grupoId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const isOpen = form.classList.contains('open');

    // EXPANDE EL GRUPO SI EL FORMULARIO SE VA A ABRIR Y EL GRUPO ESTA CERRADO
    if (grupoId && !isOpen) {
        const lista = document.getElementById(grupoId);
        if (lista && !lista.classList.contains('open')) {
            pgToggle(grupoId);
        }
    }

    // ALTERNA LA VISIBILIDAD DEL FORMULARIO
    form.classList.toggle('open');

    if (form.classList.contains('open')) {
        // AL ABRIR MUEVE EL CURSOR AL CAMPO DE TEXTO AUTOMATICAMENTE
        const inp = form.querySelector('input[name="departamento"]');
        if (inp) setTimeout(() => inp.focus(), 50);
    } else {
        // AL CERRAR LIMPIA EL CAMPO DE TEXTO
        const inp = form.querySelector('input[name="departamento"]');
        if (inp) inp.value = '';
    }
}
</script>
@endpush