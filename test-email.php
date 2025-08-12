<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Notifications\VerifyEmailNotification;

$user = User::find(2487);
if ($user) {
    echo "Usuario encontrado: {$user->nombre} {$user->apellido_paterno}\n";
    echo "Email: {$user->email}\n";
    echo "Token actual: {$user->email_verification_token}\n\n";
    
    try {
        $token = $user->email_verification_token ?: \Str::random(60);
        if (!$user->email_verification_token) {
            $user->email_verification_token = $token;
            $user->save();
        }
        
        echo "Enviando notificación...\n";
        $user->notify(new VerifyEmailNotification($token));
        echo "✓ Notificación enviada exitosamente\n";
        echo "URL de verificación: " . url("/email/verify/{$user->id}/{$token}") . "\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    }
} else {
    echo "Usuario no encontrado\n";
}