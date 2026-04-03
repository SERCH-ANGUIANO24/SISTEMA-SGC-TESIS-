<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoService
{
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;
    private string $endpoint = 'https://api.brevo.com/v3/smtp/email';

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
    public function send(array $recipients, string $subject, string $htmlBody): bool
    {
        $to = array_map(fn($r) => [
            'email' => $r['email'],
            'name'  => $r['name'] ?? $r['email'],
        ], $recipients);

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
            'sender'      => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to'          => $to,
            'subject'     => $subject,
            'htmlContent' => $htmlBody,
        ]);

        if ($response->failed()) {
            Log::error('Brevo API error: ' . $response->body());
            return false;
        }

        return true;
    }

    /** Shortcut para un solo destinatario */
    public function sendTo(string $email, string $name, string $subject, string $html): bool
    {
        return $this->send([['email' => $email, 'name' => $name]], $subject, $html);
    }
}
