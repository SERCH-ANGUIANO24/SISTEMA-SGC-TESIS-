{{--ESTE CODIGO ES DEL MENSAJE QUE SE ENVIA POR CORREO ELECTRONICO PARA EL REESTABLEICMIENTO DE CONTRASEÑA---}}
<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  {{--ESTILOS DEL MENSAJE QUE SE ENVIA POR CORREO--}}
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: #f5f5f5; }
    .wrap { max-width: 600px; margin: 30px auto; background: #fff;
            border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,.10); }
    .header { background: #ffffff; padding: 28px 32px;
              border-bottom: 3px solid #800000; }
    .header h1 { color: #000000; font-size: 1.15rem; margin-bottom: 4px; }
    .header p  { color: #737373; font-size: .82rem; }
    .body { padding: 28px 32px; }
    .greeting { color: #374151; font-size: .95rem; margin-bottom: 14px; }
    .badge { display:inline-block; padding: 3px 12px; border-radius: 20px;
             font-size: .75rem; font-weight: 600; margin-bottom: 14px;
             background:#FEF3C7; color:#92400E; }
    .message { color: #374151; line-height: 1.7; font-size: .9rem;
               margin-bottom: 20px; }
    .alert { background: #737373; border-left: 4px solid #737373;
             padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 18px 0; }
    .alert-title { font-weight: 700; color: #ffffff; margin-bottom: 6px;
                   font-size: .85rem; }
    .alert-body  { color: #ffffff; font-size: .85rem; line-height: 1.6; }
    .btn { display: inline-block; background: #737373; color: #fff;
           padding: 11px 28px; border-radius: 6px; text-decoration: none;
           font-size: .88rem; font-weight: 600; margin-top: 6px; }
    .btn:hover { background: #600000; }
    .expire { color: #9CA3AF; font-size: .82rem; margin-top: 18px; }
    .divider { border: none; border-top: 1px solid #E5E7EB; margin: 20px 0; }
    .url-fallback { font-size: .78rem; color: #9CA3AF; margin-top: 12px;
                    word-break: break-all; }
    .footer { background: #F9FAFB; border-top: 1px solid #E5E7EB;
              padding: 16px 32px; text-align: center;
              font-size: .75rem; color: #9CA3AF; }
  </style>
</head>
<body>
<div class='wrap'>

  {{-- HEADER, APARECE AL PRINCIPIO DEL CORREO --}}
  <div class='header'>
    <h1>🔐 Restablecer Contraseña</h1>
    <p>Sistema de Gestión de la Calidad — UPTEX</p>
  </div>

  {{-- CUERPO DEL CORREO ELECTRONICO --}}
  <div class='body'>

    <p class='greeting'>Hola Estimado, <strong>{{ $notifiable->name ?? 'Usuario' }}</strong></p>

    <span class='badge'>Seguridad de cuenta</span>

    <p class='message'>
      Recibimos una solicitud para restablecer la contraseña de tu cuenta en el
      Sistema de Gestión de la Calidad (SGC) de UPTEX.<br><br>
      Haz clic en el botón de abajo para crear una nueva contraseña.
      Este enlace expirará en <strong>{{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60) }} minutos</strong>.
    </p>

    {{-- BOTÓN DE RESETEO DE CONTRASEÑA --}}
    <a href='{{ $actionUrl }}' class='btn'>
      Restablecer contraseña →
    </a>

    <p class='expire'>
      Este enlace expirará en {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60) }} minutos.
    </p>

    <hr class='divider'>

    {{-- ALERTA SI NO FUE EL USUARIO --}}
    <div class='alert'>
      <div class='alert-title'>⚠️ ¿No solicitaste este cambio?</div>
      <div class='alert-body'>
        Si no solicitaste restablecer tu contraseña, ignora este correo.
        Tu cuenta permanecerá segura y no se realizará ningún cambio.
      </div>
    </div>

    {{-- URL FALLBACK, ESTE URL LO COPIAS Y SE PEGA EN EL NAVEGADOR EN CASO DE QUE EL BOTON DE RESTABLECER CONTRASEÑA NO FUNCIONE --}}
    <p class='url-fallback'>
      Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
      {{ $actionUrl }}
    </p>

  </div>

  {{-- PIE DEL CORREO ELETRONICO --}}
  <div class='footer'>
    Este mensaje fue generado automáticamente por el SGC. Por favor no respondas.<br>
    © {{ date('Y') }} SAMS Infinity. Todos los derechos reservados.
  </div>

</div>
</body>
</html>