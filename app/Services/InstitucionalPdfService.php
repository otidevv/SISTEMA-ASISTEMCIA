<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class InstitucionalPdfService
{
    /**
     * Generar el Pack de Inscripción Institucional (CEPRE o Reforzamiento).
     *
     * @param array $data Datos necesarios para el PDF
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateRegistrationPack(array $data)
    {
        $data['mes_actual'] = $this->getCurrentMonthName();
        
        // Determinar configuración basada en el programa_id (1: CEPRE, 2: Reforzamiento)
        $programa_id = $data['programa_id'] ?? 1;
        
        if ($programa_id == 2) {
            $data['programa_nombre'] = 'REFORZAMIENTO';
            $data['programa_titulo'] = 'Programa de Reforzamiento Escolar';
            $data['programa_descripcion'] = 'PROGRAMA DE REFORZAMIENTO PARA NIVEL SECUNDARIA';
        } else {
            $data['programa_nombre'] = 'CEPRE UNAMAD';
            $data['programa_titulo'] = 'Ciclo Académico CEPRE';
            $data['programa_descripcion'] = 'CENTRO PREUNIVERSITARIO - INGRESO DIRECTO';
        }
        
        // Cargar la vista general
        $pdf = Pdf::loadView('pdf.pack_inscripcion_institucional', $data);

        return $pdf;
    }

    private function getCurrentMonthName()
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[(int) date('m')];
    }
}
