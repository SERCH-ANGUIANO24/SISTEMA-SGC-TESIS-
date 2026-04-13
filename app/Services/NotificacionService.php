<?php

namespace App\Services;

use App\Models\{Notificacion, User};
use Illuminate\Support\Facades\View;

// SERVICIO PARA GESTIONAR EL ENVÍO DE NOTIFICACIONES EN LA PLATAFORMA Y POR CORREO
class NotificacionService
{
    // INYECTA EL SERVICIO DE BREVO PARA EL ENVÍO DE CORREOS
    public function __construct(
        private BrevoService $brevo
    ) {}

    // ─────────────────────────────────────────────────────────────
    // ENVIAR A UN USUARIO ESPECÍFICO
    // ─────────────────────────────────────────────────────────────

    // CREA UNA NOTIFICACIÓN EN LA PLATAFORMA Y OPCIONALMENTE ENVÍA UN CORREO
    // $userId:     ID DEL USUARIO QUE RECIBIRÁ LA NOTIFICACIÓN
    // $titulo:     TÍTULO DE LA NOTIFICACIÓN
    // $mensaje:    CUERPO DE LA NOTIFICACIÓN
    // $tipo:       TIPO VISUAL (info, success, warning, etc.)
    // $icono:      ÍCONO DE BOOTSTRAP ICONS A MOSTRAR
    // $url:        ENLACE OPCIONAL AL QUE REDIRIGE LA NOTIFICACIÓN
    // $email:      SI ES true, TAMBIÉN SE ENVÍA POR CORREO
    // $docId:      ID DEL DOCUMENTO RELACIONADO (OPCIONAL)
    // $tipoEvento: TIPO DE EVENTO QUE GENERÓ LA NOTIFICACIÓN (OPCIONAL)
    // RETORNA EL OBJETO Notificacion CREADO
    public function enviar(
        int     $userId,
        string  $titulo,
        string  $mensaje,
        string  $tipo        = 'info',
        string  $icono       = 'bi-bell',
        ?string $url         = null,
        bool    $email       = true,
        ?string $docId       = null,
        ?string $tipoEvento  = null
    ): Notificacion {

        // 1. Guardar en base de datos (notif. en plataforma)
        // GUARDA LA NOTIFICACIÓN EN LA BASE DE DATOS CON leida Y enviada_email EN false
        $notif = Notificacion::create([
            'user_id'       => $userId,
            'tipo'          => $tipo,
            'titulo'        => $titulo,
            'mensaje'       => $mensaje,
            'icono'         => $icono,
            'url'           => $url,
            'leida'         => false,
            'enviada_email' => false,
            'documento_id'  => $docId,
            'tipo_evento'   => $tipoEvento,
        ]);

        // 2. Enviar correo con Brevo
        // SI $email ES true, BUSCA AL USUARIO Y LE ENVÍA EL CORREO CON LA VISTA HTML
        if ($email) {
            $user = User::find($userId);

            // RENDERIZA LA VISTA DEL CORREO PASANDO LA NOTIFICACIÓN Y EL USUARIO
            $html = View::make('notificaciones.emails.notificacion', [
                'notificacion' => $notif,
                'usuario'      => $user,
            ])->render();

            try {
                // ENVÍA EL CORREO CON EL PREFIJO [SGC] EN EL ASUNTO
                $sent = $this->brevo->sendTo(
                    $user->email,
                    $user->name,
                    '[SGC] ' . $titulo,
                    $html
                );
                // SI EL ENVÍO FUE EXITOSO, MARCA enviada_email COMO true EN LA BASE DE DATOS
                if ($sent) $notif->update(['enviada_email' => true]);
            } catch (\Exception $e) {
                // SI BREVO FALLA, REGISTRA EL ERROR EN EL LOG SIN DETENER LA EJECUCIÓN
                \Log::warning('Brevo email falló para usuario ' . $userId . ': ' . $e->getMessage());
            }

        }

        return $notif;
    }

    // ─────────────────────────────────────────────────────────────
    // ENVIAR A TODOS LOS USUARIOS (cuando doc es aprobado)
    // ─────────────────────────────────────────────────────────────

    // CREA UNA NOTIFICACIÓN PARA CADA USUARIO ACTIVO Y HACE UN ENVÍO MASIVO DE CORREO EN UNA SOLA LLAMADA
    // LOS PARÁMETROS SON LOS MISMOS QUE enviar() PERO SIN $userId (SE APLICA A TODOS)
    public function enviarATodos(
        string  $titulo,
        string  $mensaje,
        string  $tipo       = 'info',
        string  $icono      = 'bi-bell',
        ?string $url        = null,
        bool    $email      = true,
        ?string $docId      = null,
        ?string $tipoEvento = null
    ): void {
        // OBTIENE TODOS LOS USUARIOS ACTIVOS DEL SISTEMA
        $usuarios   = User::where('is_active', true)->get();
        $recipients = [];

        // CREA UNA NOTIFICACIÓN EN LA BASE DE DATOS POR CADA USUARIO
        // Y ACUMULA SU CORREO EN $recipients PARA EL ENVÍO MASIVO
        foreach ($usuarios as $u) {
            Notificacion::create([
                'user_id'       => $u->id,
                'tipo'          => $tipo,
                'titulo'        => $titulo,
                'mensaje'       => $mensaje,
                'icono'         => $icono,
                'url'           => $url,
                'leida'         => false,
                'enviada_email' => $email,
                'documento_id'  => $docId,
                'tipo_evento'   => $tipoEvento,
            ]);
            if ($email) $recipients[] = ['email' => $u->email, 'name' => $u->name];
        }

        // Envío masivo en UNA sola llamada a Brevo
        // SI HAY DESTINATARIOS, RENDERIZA EL HTML Y ENVÍA UN SOLO CORREO A TODOS (MÁS EFICIENTE)
        if ($email && count($recipients) > 0) {
            $html = View::make('notificaciones.emails.notificacion', [
                'notificacion' => (object)[
                    'titulo'  => $titulo, 'mensaje' => $mensaje,
                    'tipo'    => $tipo,   'url'     => $url,
                    'observaciones' => null,
                ],
                'usuario' => (object)['name' => 'Estimado usuario'],
            ])->render();

            try {
                // ENVÍA EL CORREO MASIVO A TODOS LOS DESTINATARIOS
                $this->brevo->send($recipients, '[SGC] ' . $titulo, $html);
            } catch (\Exception $e) {
                // SI BREVO FALLA, REGISTRA EL ERROR EN EL LOG SIN DETENER LA EJECUCIÓN
                \Log::warning('Brevo envío masivo falló: ' . $e->getMessage());
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ENVIAR A UN ROL (ej. todos los admins)
    // ─────────────────────────────────────────────────────────────

    // ENVÍA UNA NOTIFICACIÓN A TODOS LOS USUARIOS QUE TENGAN UN ROL ESPECÍFICO
    // $rol: NOMBRE DEL ROL (EJEMPLO: 'admin', 'superadmin', 'auditor_lider')
    // REUTILIZA enviar() POR CADA USUARIO QUE COINCIDA CON EL ROL
    public function enviarARol(
        string  $rol,
        string  $titulo,
        string  $mensaje,
        string  $tipo       = 'info',
        string  $icono      = 'bi-bell',
        ?string $url        = null,
        bool    $email      = true,
        ?string $docId      = null,
        ?string $tipoEvento = null
    ): void {
        // BUSCA TODOS LOS USUARIOS ACTIVOS CON EL ROL INDICADO
        $usuarios = User::where('role', $rol)->where('activo', true)->get();

        // LLAMA A enviar() INDIVIDUALMENTE POR CADA USUARIO ENCONTRADO
        foreach ($usuarios as $u) {
            $this->enviar(
                $u->id, $titulo, $mensaje,
                $tipo, $icono, $url, $email, $docId, $tipoEvento
            );
        }
    }
}