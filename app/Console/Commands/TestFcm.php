<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\GeneralAnnouncement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestFcm extends Command
{
    /**
     * El nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'test:fcm {documento? : El número de documento del usuario de prueba}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Prueba exhaustiva de notificaciones Push FCM con soporte multimedia';

    /**
     * Ejecutar el comando.
     */
    public function handle()
    {
        $this->info('====================================================');
        $this->info('🚀 DIAGNÓSTICO DE NOTIFICACIONES PUSH (FCM v1)');
        $this->info('====================================================');

        // 1. Verificar ARCHIVO JSON
        $jsonPath = storage_path('app/firebase-service-account.json');
        if (!file_exists($jsonPath)) {
            $this->error('✗ ERROR: No se encontró el archivo de credenciales en storage/app/firebase-service-account.json');
            $this->warn('Asegúrate de haber subido el archivo manualmente.');
            return 1;
        }
        $this->info('✓ Archivo de credenciales Firebase detectado.');

        // 2. Identificar Usuario
        $documento = $this->argument('documento');
        if (!$documento) {
            $documento = $this->ask('¿Cuál es el número de documento (DNI) del usuario de prueba?');
        }

        $user = User::where('numero_documento', $documento)->first();

        if (!$user) {
            $this->error("✗ ERROR: No se encontró ningún usuario con DNI: {$documento}");
            return 1;
        }

        $this->info("✓ Usuario identificado: {$user->nombre} {$user->apellido_paterno}");

        // 3. Verificar FCM Token
        if (!$user->fcm_token) {
            $this->error('✗ ERROR: El usuario NO tiene un FCM Token registrado.');
            $this->warn('El usuario debe haber iniciado sesión al menos una vez en la NUEVA APK para registrar su token.');
            return 1;
        }
        $this->info('✓ Token FCM detectado correctamente.');

        // 4. Elegir Tipo de Prueba
        $tipoPrueba = $this->choice(
            '¿Qué tipo de notificación multimedia deseas probar?',
            ['Texto Simple', 'Con Imagen (URL)', 'Con Video (MP4)', 'Con Documento (PDF)'],
            0
        );

        $titulo = "Prueba de " . $tipoPrueba;
        $mensaje = "Esta es una notificación de prueba del sistema CEPRE UNAMAD (" . now()->format('H:i:s') . ")";
        $imagen = null;
        $archivo = null;
        $tipoArchivo = null;

        switch ($tipoPrueba) {
            case 'Con Imagen (URL)':
                $imagen = 'https://cepre.unamad.edu.pe/logo.png'; // URL de ejemplo o ruta local
                break;
            case 'Con Video (MP4)':
                $archivo = 'anuncios/documentos/test_video.mp4';
                $tipoArchivo = 'mp4';
                $this->warn("Asegúrate de que exista: storage/app/public/{$archivo}");
                break;
            case 'Con Documento (PDF)':
                $archivo = 'anuncios/documentos/test_demo.pdf';
                $tipoArchivo = 'pdf';
                $this->warn("Asegúrate de que exista: storage/app/public/{$archivo}");
                break;
        }

        $this->line('----------------------------------------------------');
        $this->info("Enviando payload a FCM...");

        try {
            $user->notifyNow(new GeneralAnnouncement(
                $titulo,
                $mensaje,
                $imagen,
                $archivo,
                $tipoArchivo
            ));

            $this->info('✅ ¡SOLICITUD ENVIADA EXITOSAMENTE!');
            $this->line('');
            $this->comment('Revisa tu celular en unos segundos.');
            $this->line('Payload enviado:');
            $this->line("- Título: {$titulo}");
            $this->line("- Archivo: " . ($archivo ?? 'Ninguno'));
            $this->line("- Tipo: " . ($tipoArchivo ?? 'N/A'));

        } catch (\Exception $e) {
            $this->error('✗ Fallo al enviar la notificación:');
            $this->error($e->getMessage());
            Log::error('FCM Test Command Error: ' . $e->getMessage());
        }

        $this->info('====================================================');
        
        return 0;
    }
}
