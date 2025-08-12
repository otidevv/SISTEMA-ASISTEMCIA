<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email? : El email destino para la prueba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el envío de correos electrónicos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testEmail = $this->argument('email') ?? 'apenam@unamad.edu.pe';
        
        $this->info('===========================================');
        $this->info('PRUEBA DE CONFIGURACIÓN DE CORREO');
        $this->info('===========================================');
        
        // Mostrar configuración actual
        $this->info('Configuración SMTP:');
        $this->line('Host: ' . config('mail.mailers.smtp.host'));
        $this->line('Puerto: ' . config('mail.mailers.smtp.port'));
        $this->line('Encriptación: ' . config('mail.mailers.smtp.encryption'));
        $this->line('Usuario: ' . config('mail.mailers.smtp.username'));
        $this->line('From: ' . config('mail.from.address'));
        $this->line('-------------------------------------------');
        
        // Opción 1: Prueba simple con Mail::raw
        $this->info('Prueba 1: Enviando correo simple...');
        try {
            Mail::raw('Este es un correo de prueba desde el sistema CEPRE UNAMAD. Si recibes este mensaje, la configuración SMTP está funcionando correctamente.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('Prueba de Correo - CEPRE UNAMAD');
            });
            $this->info('✓ Correo simple enviado exitosamente a ' . $testEmail);
        } catch (\Exception $e) {
            $this->error('✗ Error enviando correo simple:');
            $this->error($e->getMessage());
            $this->line('');
            $this->warn('Posibles causas:');
            $this->line('1. Credenciales incorrectas en .env');
            $this->line('2. Contraseña de aplicación no configurada en Gmail');
            $this->line('3. Acceso de aplicaciones menos seguras deshabilitado');
            $this->line('4. Puerto bloqueado por firewall');
            return 1;
        }
        
        $this->line('-------------------------------------------');
        
        // Opción 2: Prueba con notificación real
        if ($this->confirm('¿Desea probar el envío de una notificación de verificación real?')) {
            $this->info('Prueba 2: Enviando notificación de verificación...');
            
            // Buscar o crear un usuario de prueba
            $user = User::where('email', $testEmail)->first();
            
            if (!$user) {
                $this->warn('No se encontró un usuario con el email ' . $testEmail);
                $user = User::where('id', 2484)->first(); // El usuario que mencionaste
                if ($user) {
                    $this->info('Usando usuario: ' . $user->nombre . ' ' . $user->apellido_paterno . ' (' . $user->email . ')');
                } else {
                    $this->error('No se pudo encontrar un usuario para la prueba');
                    return 1;
                }
            }
            
            try {
                $token = \Str::random(60);
                $user->email_verification_token = $token;
                $user->save();
                
                $user->notify(new VerifyEmailNotification($token));
                
                $this->info('✓ Notificación enviada exitosamente');
                $this->info('Token de verificación: ' . $token);
                $this->info('URL de verificación: ' . url("/email/verify/{$user->id}/{$token}"));
                
            } catch (\Exception $e) {
                $this->error('✗ Error enviando notificación:');
                $this->error($e->getMessage());
                $this->line('');
                $this->line('Stack trace:');
                $this->line($e->getTraceAsString());
            }
        }
        
        $this->line('===========================================');
        $this->info('Prueba completada');
        
        return 0;
    }
}