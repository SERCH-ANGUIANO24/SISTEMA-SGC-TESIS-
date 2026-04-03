<?php

namespace App\Services;

use App\Models\{Notificacion, User};
use Illuminate\Support\Facades\View;

class NotificacionService
{
    public function __construct(
        private BrevoService $brevo
    ) {}

    // ─────────────────────────────────────────────────────────────
    // ENVIAR A UN USUARIO ESPECÍFICO
    // ─────────────────────────────────────────────────────────────
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
        if ($email) {
            $user = User::find($userId);
            $html = View::make('notificaciones.emails.notificacion', [
                'notificacion' => $notif,
                'usuario'      => $user,
            ])->render();

            try {
                $sent = $this->brevo->sendTo(
                    $user->email,
                    $user->name,
                    '[SGC] ' . $titulo,
                    $html
                );
                if ($sent) $notif->update(['enviada_email' => true]);
            } catch (\Exception $e) {
                \Log::warning('Brevo email falló para usuario ' . $userId . ': ' . $e->getMessage());
            }

        }

        return $notif;
    }

    // ─────────────────────────────────────────────────────────────
    // ENVIAR A TODOS LOS USUARIOS (cuando doc es aprobado)
    // ─────────────────────────────────────────────────────────────
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
        $usuarios   = User::where('is_active', true)->get();
        $recipients = [];

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
                $this->brevo->send($recipients, '[SGC] ' . $titulo, $html);
            } catch (\Exception $e) {
                \Log::warning('Brevo envío masivo falló: ' . $e->getMessage());
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ENVIAR A UN ROL (ej. todos los admins)
    // ─────────────────────────────────────────────────────────────
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
        $usuarios = User::where('role', $rol)->where('activo', true)->get();
        foreach ($usuarios as $u) {
            $this->enviar(
                $u->id, $titulo, $mensaje,
                $tipo, $icono, $url, $email, $docId, $tipoEvento
            );
        }
    }
}
