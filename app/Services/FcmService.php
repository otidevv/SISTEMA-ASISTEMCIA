<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    protected $serviceAccount;
    protected $projectId;

    public function __construct()
    {
        $path = storage_path('app/firebase-service-account.json');
        
        if (file_exists($path)) {
            $this->serviceAccount = json_decode(file_get_contents($path), true);
            $this->projectId = $this->serviceAccount['project_id'] ?? null;
        } else {
            Log::warning('FCM: No se encontró el archivo firebase-service-account.json en storage/app/');
        }
    }

    /**
     * Enviar una notificación individual
     */
    public function sendNotification($token, $title, $body, $data = [], $image = null)
    {
        if (!$this->projectId || !$token) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => array_map('strval', $data), // FCM v1 requiere que los valores sean strings
        ];

        if ($image) {
            $message['notification']['image'] = $image;
        }

        // Configuración específica para Android (Máxima prioridad y visibilidad)
        $message['android'] = [
            'priority' => 'high',
            'notification' => [
                'channel_id' => 'high_importance_channel',
                'sound' => 'default',
                'priority' => 'max', // Importancia MAX para Heads-up
                'visibility' => 'public',
                'vibrate_timings' => ['0s', '0.1s', '0.1s', '0.1s'],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                'message' => $message
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('FCM Error: ' . $response->body());
        return false;
    }

    /**
     * Obtener el Access Token de Google usando el Service Account
     */
    protected function getAccessToken()
    {
        return Cache::remember('fcm_access_token', 3500, function () {
            if (!$this->serviceAccount) {
                return null;
            }

            $now = time();
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claimSet = json_encode([
                'iss' => $this->serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64Header = $this->base64UrlEncode($header);
            $base64ClaimSet = $this->base64UrlEncode($claimSet);
            $signatureInput = $base64Header . "." . $base64ClaimSet;

            $signature = '';
            openssl_sign($signatureInput, $signature, $this->serviceAccount['private_key'], 'sha256WithRSAEncryption');
            $base64Signature = $this->base64UrlEncode($signature);

            $jwt = $signatureInput . "." . $base64Signature;

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $response->json()['access_token'] ?? null;
        });
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
