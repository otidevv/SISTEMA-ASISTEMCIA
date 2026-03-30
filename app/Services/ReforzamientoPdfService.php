<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class ReforzamientoPdfService
{
    /**
     * Generar el Pack de Inscripción (Carta de Compromiso) relleno automáticamente.
     *
     * @param array $data Datos del estudiante y apoderado
     * @return Fpdi
     */
    public function fillRegistrationPack(array $data)
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false); // Evitar saltos de página accidentales

        $templatePath = storage_path('app/templates/carta de compromiso.pdf');
        if (!file_exists($templatePath))
            throw new \Exception("Plantilla no encontrada");
        $pdf->setSourceFile($templatePath);

        // =====================================================================
        // PÁGINA 1: FICHA DEL APODERADO
        // = [ Edita los números de abajo para mover los campos de la Pág 1 ] ===
        $p1_nombre = [74, 56];
        $p1_dni = [98, 68];
        $p1_celular = [71, 76];
        $p1_direccion = [56, 89];
        $p1_fecha = [68, 162]; // Día (Día de Puerto Maldonado)
        // =====================================================================

        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(1), 0, 0, 210);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($p1_nombre[0], $p1_nombre[1]);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($data['apoderado_nombre'] ?? '---')));
        $pdf->SetXY($p1_dni[0], $p1_dni[1]);
        $pdf->Cell(0, 5, utf8_decode($data['apoderado_dni'] ?? '---'));
        $pdf->SetXY($p1_celular[0], $p1_celular[1]);
        $pdf->Cell(0, 5, utf8_decode($data['apoderado_celular'] ?? '---'));
        $pdf->SetXY($p1_direccion[0], $p1_direccion[1]);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($data['apoderado_direccion'] ?? '---')));
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($p1_fecha[0], $p1_fecha[1]);
        $pdf->Cell(10, 5, date('d'));
        $pdf->SetXY($p1_fecha[0] + 21, $p1_fecha[1]); // Mes (Puerto Maldonado) 
        $pdf->Cell(30, 5, $this->getCurrentMonthName());

        // =====================================================================
        // PÁGINA 2: CARTA COMPROMISO
        // =====================================================================
        $p2_estudiante = [31, 62];
        $p2_dni_firma = [92, 174];
        // =====================================================================

        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(2), 0, 0, 210);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY($p2_estudiante[0], $p2_estudiante[1]);
        $pdf->Cell(160, 5, utf8_decode(strtoupper($data['estudiante_nombre'] ?? '---')));
        $pdf->SetXY($p2_dni_firma[0], $p2_dni_firma[1]);
        $pdf->Cell(0, 5, utf8_decode($data['estudiante_dni'] ?? '---'));

        // =====================================================================
        // PÁGINA 3: DECLARACIÓN JURADA
        // =====================================================================
        $p3_apoderado = [30, 48];
        $p3_dni = [46, 55];
        $p3_hijo = [23, 81];
        // =====================================================================

        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(3), 0, 0, 210);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($p3_apoderado[0], $p3_apoderado[1]);
        $pdf->Cell(110, 5, utf8_decode(strtoupper($data['apoderado_nombre'] ?? '---')));
        $pdf->SetXY($p3_dni[0], $p3_dni[1]);
        $pdf->Cell(45, 5, utf8_decode($data['apoderado_dni'] ?? '---'));
        $pdf->SetXY($p3_hijo[0], $p3_hijo[1]);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($data['estudiante_nombre'] ?? '---')));

        // =====================================================================
        // PÁGINA 4: RETIRO ESTUDIANTE
        // =====================================================================
        $p4_apoderado = [30, 51];
        $p4_dni = [138, 51];
        $p4_check_x = [55.5, 92]; // Checkbox Reforzamiento
        // =====================================================================

        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(4), 0, 0, 210);
        $pdf->SetXY($p4_apoderado[0], $p4_apoderado[1]);
        $pdf->Cell(110, 5, utf8_decode(strtoupper($data['apoderado_nombre'] ?? '---')));
        $pdf->SetXY($p4_dni[0], $p4_dni[1]);
        $pdf->Cell(0, 5, utf8_decode($data['apoderado_dni'] ?? '---'));
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY($p4_check_x[0], $p4_check_x[1]);
        $pdf->Cell(5, 5, 'X', 0, 0, 'C');

        // =====================================================================
        // PÁGINA 5: TRATAMIENTO DE DATOS BIOMÉTRICOS
        // =====================================================================
        $p5_ap_nom = [70, 75];
        $p5_ap_dni = [50, 83];
        $p5_est_nom = [69, 114];
        $p5_est_dni = [44, 121];
        $p5_check_x = [41, 150];
        // =====================================================================

        $pdf->AddPage();
        $pdf->useTemplate($pdf->importPage(5), 0, 0, 210);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($p5_ap_nom[0], $p5_ap_nom[1]);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($data['apoderado_nombre'] ?? '---')));
        $pdf->SetXY($p5_ap_dni[0], $p5_ap_dni[1]);
        $pdf->Cell(0, 5, utf8_decode($data['apoderado_dni'] ?? '---'));
        $pdf->SetXY($p5_est_nom[0], $p5_est_nom[1]);
        $pdf->Cell(0, 5, utf8_decode(strtoupper($data['estudiante_nombre'] ?? '---')));
        $pdf->SetXY($p5_est_dni[0], $p5_est_dni[1]);
        $pdf->Cell(0, 5, utf8_decode($data['estudiante_dni'] ?? '---'));
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY($p5_check_x[0], $p5_check_x[1]);
        $pdf->Cell(5, 5, 'X', 0, 0, 'C');

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
