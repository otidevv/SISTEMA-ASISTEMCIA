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
        
        // Limpiar emojis de nombres para evitar errores de encoding (??) en el PDF
        if (isset($data['carrera_nombre'])) {
            $data['carrera_nombre'] = $this->stripEmojis($data['carrera_nombre']);
        }
        if (isset($data['turno_nombre'])) {
            $data['turno_nombre'] = $this->stripEmojis($data['turno_nombre']);
        }
        if (isset($data['estudiante_nombre'])) {
            $data['estudiante_nombre'] = $this->stripEmojis($data['estudiante_nombre']);
        }
        
        // Cargar la vista general
        $pdf = Pdf::loadView('pdf.pack_inscripcion_institucional', $data);

        return $pdf;
    }

    /**
     * Elimina emojis y caracteres especiales de 4 bytes que DomPDF no maneja bien.
     * También limpia signos de interrogación basura que a veces quedan por errores de importación.
     */
    private function stripEmojis($text)
    {
        if (empty($text)) return $text;
        
        // 1. Limpiar caracteres de control y el "replacement character" () que a veces se ve como ?
        $text = str_replace(["\xEF\xBF\xBD", "\r", "\n", "\t"], '', $text);
        
        // 2. Mantener SOLO letras, números, espacios y puntuación básica
        // Esto elimina emojis, símbolos extraños y cualquier caracter de 4 bytes.
        $text = preg_replace('/[^\p{L}\p{N}\s\.,\(\)\[\]\-\/]/u', '', $text);
        
        // 3. Limpiar cualquier basura no alfanumérica al inicio (como el ? persistente)
        $text = preg_replace('/^[^\p{L}\p{N}]+/u', '', $text);
        
        return trim($text);
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
