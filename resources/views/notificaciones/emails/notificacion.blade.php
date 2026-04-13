{{-- resources/views/emails/notificacion.blade.php --}}
{{-- PLANTILLA DE CORREO ELECTRÓNICO PARA EL ENVÍO DE NOTIFICACIONES DEL SISTEMA --}}
{{-- SE USA CUANDO EL SISTEMA ENVÍA ALERTAS POR EMAIL A LOS USUARIOS --}}
<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>

  {{-- ESTILOS CSS DEL CORREO ELECTRÓNICO --}}
  {{-- TODOS LOS ESTILOS SON INLINE PARA MÁXIMA COMPATIBILIDAD CON CLIENTES DE CORREO --}}
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    /* FONDO GRIS CLARO DEL CORREO Y FUENTE ARIAL */
    body { font-family: Arial, sans-serif; background: #f5f5f5; }

    /* CONTENEDOR PRINCIPAL DEL CORREO CON ANCHO MÁXIMO, BORDES REDONDEADOS Y SOMBRA */
    .wrap { max-width: 600px; margin: 30px auto; background: #fff;
            border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.10); }

    /* ENCABEZADO DEL CORREO */
    /*AQUI SE CAMBIA EL COLOR DEL ENCABEZADO (SE PUEDE CAMBIAR EN BACKGROUN:#ffffff) DEL CORREO ELECTRONICO CUANDO SE ENVIA UNA NOTIFICACION POR CORREO */ 
    .header { background: #ffffff; padding: 28px 32px; }

    /* TÍTULO DEL ENCABEZADO DEL CORREO */
    /* SE PUEDE CAMBIAR EL TAMAÑO Y COLOR DE LAS LETRAS DEL ENCABEZADO DEL CORREO ELECTROCNICO */
    .header h1 { color: #000000; font-size: 1.15rem; margin-bottom: 4px; }

    /* SUBTÍTULO DEL ENCABEZADO (NOMBRE DEL SISTEMA) */
    .header p  { color: rgba(255,255,255,.75); font-size: .82rem; }

    /* CONTENIDO PRINCIPAL DEL CORREO CON PADDING INTERNO */
    .body { padding: 28px 32px; }

    /* TEXTO DE SALUDO AL USUARIO */
    .greeting { color: #374151; font-size: .95rem; margin-bottom: 14px; }

    /* ESTILOS BASE DEL BADGE (ETIQUETA) DE TIPO DE NOTIFICACIÓN */
    .badge { display:inline-block; padding: 3px 12px; border-radius: 20px;
             font-size: .75rem; font-weight: 600; margin-bottom: 14px; }

    /* COLORES DE LOS BADGES SEGÚN EL TIPO DE NOTIFICACIÓN */
    .info        { background:#DBEAFE; color:#1E40AF; }   /* AZUL = INFORMATIVO */
    .exito       { background:#D1FAE5; color:#065F46; }   /* VERDE = ÉXITO */
    .error       { background:#FEE2E2; color:#991B1B; }   /* ROJO = ERROR */
    .advertencia { background:#FEF3C7; color:#92400E; }   /* AMARILLO = ADVERTENCIA */

    /* TEXTO DEL MENSAJE PRINCIPAL CON SALTOS DE LÍNEA CONSERVADOS */
    .message { color: #374151; line-height: 1.7; font-size: .9rem;
               white-space: pre-wrap; margin-bottom: 20px; }

    /* CAJA DE OBSERVACIONES CON BORDE IZQUIERDO AMARILLO (SOLO SE MUESTRA EN RECHAZOS) */
    .obs { background: #FEF3C7; border-left: 4px solid #F59E0B;
           padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 18px 0; }

    /* TÍTULO DE LA SECCIÓN DE OBSERVACIONES */
    .obs-title { font-weight: 700; color: #92400E; margin-bottom: 6px;
                 font-size: .85rem; }

    /* TEXTO DEL CONTENIDO DE LAS OBSERVACIONES */
    .obs-body  { color: #78350F; font-size: .85rem; line-height: 1.6; }

    /* BOTÓN DE ENLACE A LA PLATAFORMA (COLOR GRIS CON TEXTO BLANCO) */
    .btn { display: inline-block; background: #737373; color: #fff;
           padding: 11px 28px; border-radius: 6px; text-decoration: none;
           font-size: .88rem; font-weight: 600; margin-top: 6px; }

    /* PIE DE PÁGINA CON FONDO GRIS CLARO Y TEXTO PEQUEÑO */
    .footer { background: #F9FAFB; border-top: 1px solid #E5E7EB;
              padding: 16px 32px; text-align: center;
              font-size: .75rem; color: #9CA3AF; }
  </style>
</head>
<body>
<div class='wrap'>

  {{-- HEADER: MUESTRA EL TÍTULO DE LA NOTIFICACIÓN Y EL NOMBRE DEL SISTEMA --}}
  <div class='header'>
    <h1>🔔 {{ $notificacion->titulo }}</h1>
    <p>Sistema de Gestión de la Calidad — UPTEX</p>
  </div>

  {{-- BODY: CONTENIDO PRINCIPAL DEL CORREO --}}
  <div class='body'>

    {{-- SALUDO PERSONALIZADO CON EL NOMBRE DEL USUARIO DESTINATARIO --}}
    <p class='greeting'>Hola, <strong>{{ $usuario->name }}</strong></p>

    {{-- BADGE DEL TIPO DE NOTIFICACIÓN (info, exito, error, advertencia) --}}
    {{-- EL ESTILO CSS SE APLICA SEGÚN EL VALOR DE $notificacion->tipo --}}
    <span class='badge {{ $notificacion->tipo }}'>
      {{ ucfirst($notificacion->tipo) }}
    </span>

    {{-- MENSAJE PRINCIPAL DE LA NOTIFICACIÓN --}}
    {{-- white-space: pre-wrap CONSERVA LOS SALTOS DE LÍNEA DEL MENSAJE --}}
    <p class='message'>{{ $notificacion->mensaje }}</p>

    {{-- OBSERVACIONES (solo en rechazo) --}}
    {{-- SECCIÓN OPCIONAL: SOLO SE MUESTRA SI LA NOTIFICACIÓN TIENE OBSERVACIONES --}}
    {{-- SE USA PRINCIPALMENTE CUANDO UN ADMINISTRADOR RECHAZA UNA SOLICITUD --}}
    @if(isset($notificacion->observaciones) && $notificacion->observaciones)
    <div class='obs'>
      <div class='obs-title'>⚠️ Observaciones del administrador:</div>
      <div class='obs-body'>{{ $notificacion->observaciones }}</div>
    </div>
    @endif

    {{-- BOTÓN VER EN PLATAFORMA --}}
    {{-- SOLO SE MUESTRA SI LA NOTIFICACIÓN TIENE UNA URL ASOCIADA --}}
    {{-- AL HACER CLIC LLEVA AL USUARIO DIRECTAMENTE AL MÓDULO RELACIONADO --}}
    @if($notificacion->url)
    <a href='{{ $notificacion->url }}' class='btn'>
      Ver en la plataforma →
    </a>
    @endif
  </div>

  {{-- FOOTER: PIE DE PÁGINA CON AVISO LEGAL Y AÑO ACTUAL --}}
  {{-- date('Y') MUESTRA EL AÑO ACTUAL AUTOMÁTICAMENTE --}}
  <div class='footer'>
    Este mensaje fue generado automáticamente por el SGC. Por favor no respondas.<br>
    © {{ date('Y') }} SAMS Infinity. Todos los derechos reservados.
  </div>

</div>
</body></html>