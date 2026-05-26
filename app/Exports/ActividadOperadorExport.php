<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\ActividadOperadorPostulacionesSheet;
use App\Exports\Sheets\ActividadOperadorReforzamientoSheet;

class ActividadOperadorExport implements WithMultipleSheets
{
    protected $postulaciones;
    protected $reforzamientos;
    protected $operadorNombre;
    protected $rangoFechas;

    public function __construct($postulaciones, $reforzamientos, $operadorNombre, $rangoFechas)
    {
        $this->postulaciones = $postulaciones;
        $this->reforzamientos = $reforzamientos;
        $this->operadorNombre = $operadorNombre;
        $this->rangoFechas = $rangoFechas;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new ActividadOperadorPostulacionesSheet($this->postulaciones, $this->operadorNombre, $this->rangoFechas),
            new ActividadOperadorReforzamientoSheet($this->reforzamientos, $this->operadorNombre, $this->rangoFechas)
        ];
    }
}
