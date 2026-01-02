<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CarnetTemplate;

class CarnetTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear plantilla por defecto con las coordenadas actuales
        CarnetTemplate::create([
            'nombre' => 'Plantilla Postulante Default 2026',
            'tipo' => 'postulante',
            'fondo_path' => 'images/fondocarnet_postulante.jpg',
            'ancho_mm' => 53.98,
            'alto_mm' => 85.6,
            'descripcion' => 'Plantilla por defecto para carnets de postulantes',
            'campos_config' => [
                'foto' => [
                    'left' => '50%',
                    'top' => '13.5mm',
                    'width' => '24mm',
                    'height' => '26mm',
                    'transform' => 'translateX(-70%)',
                    'visible' => true
                ],
                'qr_code' => [
                    'left' => '11mm',
                    'top' => '23mm',
                    'width' => '10mm',
                    'height' => '10mm',
                    'visible' => true
                ],
                'codigo_postulante' => [
                    'left' => '50%',
                    'top' => '39.5mm',
                    'fontSize' => '11pt',
                    'fontWeight' => 'bold',
                    'color' => 'white',
                    'letterSpacing' => '1mm',
                    'transform' => 'translateX(-70%)',
                    'textAlign' => 'center',
                    'visible' => true
                ],
                'nombre_completo' => [
                    'left' => '46%',
                    'top' => '44.9mm',
                    'fontSize' => '7pt',
                    'fontWeight' => '100',
                    'color' => 'white',
                    'letterSpacing' => '0.2mm',
                    'transform' => 'translateX(-55%)',
                    'textAlign' => 'center',
                    'visible' => true
                ],
                'dni' => [
                    'left' => '17mm',
                    'top' => '46mm',
                    'fontSize' => '8pt',
                    'color' => '#003d7a',
                    'visible' => true
                ],
                'grupo' => [
                    'left' => '22mm',
                    'top' => '51mm',
                    'fontSize' => '8pt',
                    'color' => '#003d7a',
                    'visible' => true
                ],
                'modalidad' => [
                    'left' => '30mm',
                    'top' => '55.5mm',
                    'fontSize' => '7pt',
                    'color' => '#003d7a',
                    'visible' => true
                ],
                'carrera' => [
                    'left' => '45%',
                    'top' => '64mm',
                    'fontSize' => '7pt',
                    'fontWeight' => 'bold',
                    'color' => '#003d7a',
                    'transform' => 'translateX(-60%)',
                    'textAlign' => 'center',
                    'visible' => true
                ]
            ],
            'activa' => true,
            'creado_por' => 1 // Usuario admin
        ]);

        $this->command->info('Plantilla de carnet por defecto creada exitosamente.');
    }
}
