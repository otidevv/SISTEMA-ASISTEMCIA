<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ReforzamientoPdfService
{
    /**
     * Generar el Pack de Inscripción (Carta de Compromiso) en HTML/DOMPDF.
     *
     * @param array $data Datos del estudiante y apoderado
     * @return \Barryvdh\DomPDF\PDF
     */
    public function fillRegistrationPack(array $data)
    {
        $data['mes_actual'] = $this->getCurrentMonthName();
        
        // Cargar la vista Blade de recursos/views/pdf/carta_compromiso_reforzamiento.blade.php
        $pdf = Pdf::loadView('pdf.carta_compromiso_reforzamiento', $data);

        return $pdf;
    }

    private function getCurrentMonthName()
    {
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        return $months[(int) date('m')];
    }
}
