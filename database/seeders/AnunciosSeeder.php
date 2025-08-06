<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Anuncio;
use App\Models\User;
use Carbon\Carbon;

class AnunciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first(); // Tomar el primer usuario como creador

        $anuncios = [
            [
                'titulo' => 'Bienvenidos al Sistema de Asistencia',
                'contenido' => 'Este es el nuevo sistema de control de asistencia. Aquí podrás registrar tu entrada y salida, consultar tus registros y mucho más.',
                'descripcion' => 'Bienvenida al nuevo sistema de asistencia',
                'es_activo' => true,
                'fecha_publicacion' => Carbon::now(),
                'fecha_expiracion' => Carbon::now()->addMonth(),
                'prioridad' => 2,
                'tipo' => 'informativo',
                'dirigido_a' => 'todos',
                'creado_por' => $admin?->id,
            ],
            [
                'titulo' => 'Mantenimiento Programado',
                'contenido' => 'El sistema estará en mantenimiento el próximo domingo de 2:00 AM a 4:00 AM. Durante este tiempo no se podrán registrar asistencias.',
                'descripcion' => 'Mantenimiento programado para el domingo',
                'es_activo' => true,
                'fecha_publicacion' => Carbon::now(),
                'fecha_expiracion' => Carbon::now()->addWeek(),
                'prioridad' => 3,
                'tipo' => 'mantenimiento',
                'dirigido_a' => 'todos',
                'creado_por' => $admin?->id,
            ],
            [
                'titulo' => 'Recordatorio para Docentes',
                'contenido' => 'Recuerden registrar su asistencia al inicio y final de cada clase. Esto nos ayuda a mantener un mejor control académico.',
                'descripcion' => 'Recordatorio sobre registro de asistencia',
                'es_activo' => true,
                'fecha_publicacion' => Carbon::now()->subDay(),
                'fecha_expiracion' => Carbon::now()->addWeeks(2),
                'prioridad' => 2,
                'tipo' => 'importante',
                'dirigido_a' => 'docentes',
                'creado_por' => $admin?->id,
            ],
        ];

        foreach ($anuncios as $anuncio) {
            Anuncio::create($anuncio);
        }

        $this->command->info('Anuncios de prueba creados exitosamente.');
    }
}