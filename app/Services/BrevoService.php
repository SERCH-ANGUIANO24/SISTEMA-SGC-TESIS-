<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// SERVICIO PARA ENVIAR CORREOS USANDO LA API DE BREVO (ANTES SENDINBLUE)
class BrevoService
{
    private string $apiKey;    // CLAVE DE AUTENTICACIÓN DE LA API DE BREVO
    private string $fromEmail; // CORREO DEL REMITENTE
    private string $fromName;  // NOMBRE DEL REMITENTE
    private string $endpoint = 'https://api.brevo.com/v3/smtp/email'; // URL DE LA API

    // CONSTRUCTOR: CARGA LAS CREDENCIALES DESDE EL ARCHIVO DE CONFIGURACIÓN (config/services.php)
    public function __construct()
    {
        $this->apiKey    = config('services.brevo.key');
        $this->fromEmail = config('services.brevo.from_email');
        $this->fromName  = config('services.brevo.from_name');
    }

    /**
     * Envía un correo a uno o varios destinatarios.
     * $recipients = [['email' => '...', 'name' => '...'], ...]
     */
    // ENVÍA UN CORREO A UNO O VARIOS DESTINATARIOS
    // $recipients: ARREGLO DE DESTINATARIOS CON email Y name
    // $subject:    ASUNTO DEL CORREO
    // $htmlBody:   CONTENIDO DEL CORREO EN FORMATO HTML
    // RETORNA true SI EL ENVÍO FUE EXITOSO, false SI HUBO UN ERROR
    public function send(array $recipients, string $subject, string $htmlBody): bool
    {
        // FORMATEA EL ARREGLO DE DESTINATARIOS AL FORMATO QUE ESPERA LA API
        // SI NO VIENE EL name, USA EL email COMO NOMBRE
        $to = array_map(fn($r) => [
            'email' => $r['email'],
            'name'  => $r['name'] ?? $r['email'],
        ], $recipients);

        // HACE LA PETICIÓN POST A LA API DE BREVO CON EL CORREO A ENVIAR
        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
            'sender'      => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to'          => $to,
            'subject'     => $subject,
            'htmlContent' => $htmlBody,
        ]);

        // SI LA API RESPONDIÓ CON ERROR, LO REGISTRA EN EL LOG Y RETORNA false
        if ($response->failed()) {
            Log::error('Brevo API error: ' . $response->body());
            return false;
        }

        // RETORNA true SI EL CORREO SE ENVIÓ CORRECTAMENTE
        return true;
    }

    /** Shortcut para un solo destinatario */
    // ATAJO PARA ENVIAR UN CORREO A UN SOLO DESTINATARIO
    // EN VEZ DE PASAR UN ARREGLO, SE PASA email Y name DIRECTAMENTE
    public function sendTo(string $email, string $name, string $subject, string $html): bool
    {
        return $this->send([['email' => $email, 'name' => $name]], $subject, $html);
    }
}