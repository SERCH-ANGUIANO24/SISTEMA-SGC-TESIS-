{{-- resources/views/emails/notificacion.blade.php --}}
<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: #f5f5f5; }
    .wrap { max-width: 600px; margin: 30px auto; background: #fff;
            border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.10); }
    .header { background: #ffffff; padding: 28px 32px; } /*AQUI SE CAMBIA EL COLOR DEL ENCABEZADO (SE PUEDE CAMBIAR EN BACKGROUN:#ffffff) DEL CORREO ELECTRONICO CUANDO SE ENVIA UNA NOTIFICACION POR CORREO */ 
    .header h1 { color: #000000; font-size: 1.15rem; margin-bottom: 4px; }/* SE PUEDE CAMBIAR EL TAMAÑO Y COLOR DE LAS LETRAS DEL ENCABEZADO DEL CORREO ELECTROCNICO */
    .header p  { color: rgba(255,255,255,.75); font-size: .82rem; }
    .body { padding: 28px 32px; }
    .greeting { color: #374151; font-size: .95rem; margin-bottom: 14px; }
    .badge { display:inline-block; padding: 3px 12px; border-radius: 20px;
             font-size: .75rem; font-weight: 600; margin-bottom: 14px; }
    .info        { background:#DBEAFE; color:#1E40AF; }
    .exito       { background:#D1FAE5; color:#065F46; }
    .error       { background:#FEE2E2; color:#991B1B; }
    .advertencia { background:#FEF3C7; color:#92400E; }
    .message { color: #374151; line-height: 1.7; font-size: .9rem;
               white-space: pre-wrap; margin-bottom: 20px; }
    .obs { background: #FEF3C7; border-left: 4px solid #F59E0B;
           padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 18px 0; }
    .obs-title { font-weight: 700; color: #92400E; margin-bottom: 6px;
                 font-size: .85rem; }
    .obs-body  { color: #78350F; font-size: .85rem; line-height: 1.6; }
    .btn { display: inline-block; background: #737373; color: #fff;
           padding: 11px 28px; border-radius: 6px; text-decoration: none;
           font-size: .88rem; font-weight: 600; margin-top: 6px; }
    .footer { background: #F9FAFB; border-top: 1px solid #E5E7EB;
              padding: 16px 32px; text-align: center;
              font-size: .75rem; color: #9CA3AF; }
  </style>
</head>
<body>
<div class='wrap'>

  {{-- HEADER --}}
  <div class='header'>
    <h1>🔔 {{ $notificacion->titulo }}</h1>
    <p>Sistema de Gestión de la Calidad — UPTEX</p>
  </div>

  {{-- BODY --}}
  <div class='body'>
    <p class='greeting'>Hola, <strong>{{ $usuario->name }}</strong></p>

    <span class='badge {{ $notificacion->tipo }}'>
      {{ ucfirst($notificacion->tipo) }}
    </span>

    <p class='message'>{{ $notificacion->mensaje }}</p>

    {{-- OBSERVACIONES (solo en rechazo) --}}
    @if(isset($notificacion->observaciones) && $notificacion->observaciones)
    <div class='obs'>
      <div class='obs-title'>⚠️ Observaciones del administrador:</div>
      <div class='obs-body'>{{ $notificacion->observaciones }}</div>
    </div>
    @endif

    {{-- BOTÓN VER EN PLATAFORMA --}}
    @if($notificacion->url)
    <a href='{{ $notificacion->url }}' class='btn'>
      Ver en la plataforma →
    </a>
    @endif
  </div>

  {{-- FOOTER --}}
  <div class='footer'>
    Este mensaje fue generado automáticamente por el SGC. Por favor no respondas.<br>
    © {{ date('Y') }} SAMS Infinity. Todos los derechos reservados.
  </div>

</div>
</body></html>
