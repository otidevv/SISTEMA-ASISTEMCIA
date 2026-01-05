<?php

namespace App\Exports;

use App\Models\Carnet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CarnetsEntregaExport implements WithMultipleSheets
{
    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    /**
     * Crear múltiples hojas, una por aula
     */
    public function sheets(): array
    {
        $sheets = [];

        // Obtener carnets filtrados
        $query = Carnet::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula', 'entregador']);

        // Aplicar filtros
        if (!empty($this->filtros['ciclo_id'])) {
            $query->where('ciclo_id', $this->filtros['ciclo_id']);
        }
        if (!empty($this->filtros['carrera_id'])) {
            $query->where('carrera_id', $this->filtros['carrera_id']);
        }
        if (!empty($this->filtros['turno_id'])) {
            $query->where('turno_id', $this->filtros['turno_id']);
        }
        if (!empty($this->filtros['aula_id'])) {
            $query->where('aula_id', $this->filtros['aula_id']);
        }
        if (isset($this->filtros['entregado']) && $this->filtros['entregado'] !== '') {
            $query->where('entregado', $this->filtros['entregado'] == '1');
        }
        if (isset($this->filtros['impreso']) && $this->filtros['impreso'] !== '') {
            $query->where('impreso', $this->filtros['impreso'] == '1');
        }

        $carnets = $query->orderBy('carrera_id')
            ->orderBy('turno_id')
            ->orderBy('aula_id')
            ->orderBy('estudiante_id')
            ->get();

        // Agrupar carnets por aula
        $carnetsPorAula = $carnets->groupBy(function($carnet) {
            return $carnet->aula_id ?? 'sin_aula';
        });

        // Crear una hoja por cada aula
        foreach ($carnetsPorAula as $aulaId => $carnetsAula) {
            $nombreAula = $aulaId === 'sin_aula' 
                ? 'Sin Aula Asignada' 
                : $carnetsAula->first()->aula->nombre;
            
            $sheets[] = new CarnetsEntregaSheet($carnetsAula, $nombreAula);
        }

        // Si no hay carnets, crear una hoja vacía
        if (empty($sheets)) {
            $sheets[] = new CarnetsEntregaSheet(collect([]), 'Sin Datos');
        }

        return $sheets;
    }
}
