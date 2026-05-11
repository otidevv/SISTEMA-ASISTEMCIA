<?php

/**
 * Script para registrar fuentes personalizadas en DomPDF.
 * Ejecutar UNA VEZ después del deploy:  php register_fonts.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Dompdf\Dompdf;
use Dompdf\Options;

$fontDir = storage_path('fonts');
if (!is_dir($fontDir)) {
    mkdir($fontDir, 0755, true);
    echo "Creada carpeta: $fontDir\n";
}

// Copiar installed-fonts.json base
$installedFonts = $fontDir . '/installed-fonts.json';
if (!file_exists($installedFonts)) {
    $baseFonts = base_path('vendor/dompdf/dompdf/lib/fonts/installed-fonts.json');
    if (file_exists($baseFonts)) {
        copy($baseFonts, $installedFonts);
        echo "Copiado installed-fonts.json base\n";
    } else {
        file_put_contents($installedFonts, json_encode([]));
        echo "Creado installed-fonts.json vacío\n";
    }
}

try {
    $options = new Options();
    $options->set('fontDir', $fontDir);
    $options->set('fontCache', $fontDir);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $fontMetrics = $dompdf->getFontMetrics();

    $fonts = [
        ['family' => 'Cinzel Decorative', 'style' => 'normal', 'weight' => 'bold', 'file' => 'CinzelDecorative-Bold.ttf'],
        ['family' => 'Playfair Display',  'style' => 'normal', 'weight' => 'bold', 'file' => 'PlayfairDisplay-Bold.ttf'],
        ['family' => 'Montserrat',        'style' => 'normal', 'weight' => 'normal', 'file' => 'Montserrat-Regular.ttf'],
    ];

    foreach ($fonts as $font) {
        $path = public_path('fonts/' . $font['file']);
        if (file_exists($path)) {
            $fontMetrics->registerFont(
                ['family' => $font['family'], 'style' => $font['style'], 'weight' => $font['weight']],
                $path
            );
            echo "OK  {$font['family']} => $path\n";
        } else {
            echo "ERR {$font['family']} no encontrada en: $path\n";
        }
    }

    $fontMetrics->saveFontFamilies();
    echo "\nFuentes registradas correctamente en: $fontDir\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
