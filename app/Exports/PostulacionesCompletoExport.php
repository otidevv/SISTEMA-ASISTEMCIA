<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\PostulacionesCompletoSheet;

class PostulacionesCompletoExport implements WithMultipleSheets
{
    protected $ciclo_id;
    protected $carrera_id;
    protected $turno_id;

    public function __construct($ciclo_id, $carrera_id, $turno_id)
    {
        $this->ciclo_id = $ciclo_id;
        $this->carrera_id = $carrera_id;
        $this->turno_id = $turno_id;
    }

    public function sheets(): array
    {
        return [
            new PostulacionesCompletoSheet($this->ciclo_id, $this->carrera_id, $this->turno_id, 'aprobados'),
            new PostulacionesCompletoSheet($this->ciclo_id, $this->carrera_id, $this->turno_id, 'retirados'),
        ];
    }
}
