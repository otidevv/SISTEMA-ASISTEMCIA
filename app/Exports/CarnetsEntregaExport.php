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

        // Obtener carnets filtrados y eager load inscripciones para evitar N+1
        $query = Carnet::with([
            'estudiante.inscripciones.aula',
            'estudiante.inscripcionesReforzamiento.aula',
            'ciclo', 'carrera', 'turno', 'aula', 'entregador'
        ]);

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
            $aula_id = $this->filtros['aula_id'];
            $query->where(function($q) use ($aula_id) {
                $q->where('aula_id', $aula_id)
                  ->orWhere(function($sq) use ($aula_id) {
                      $sq->whereNull('aula_id')
                         ->where(function($subSq) use ($aula_id) {
                             $subSq->whereHas('estudiante.inscripciones', function($iq) use ($aula_id) {
                                 $iq->where('aula_id', $aula_id)
                                    ->where('estado_inscripcion', 'activo')
                                    ->whereColumn('ciclo_id', 'carnets.ciclo_id');
                             })
                             ->orWhereHas('estudiante.inscripcionesReforzamiento', function($rq) use ($aula_id) {
                                 $rq->where('aula_id', $aula_id)
                                    ->whereColumn('ciclo_id', 'carnets.ciclo_id');
                             });
                         });
                  });
            });
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
            if ($carnet->aula_id) {
                return 'aula_' . $carnet->aula_id;
            }
            
            // Lógica similar a formatCarnetData para determinar aula real
            if ($carnet->modalidad === 'reforzamiento_colegio' || $carnet->modalidad === 'reforzamiento') {
                $inscripcion = $carnet->estudiante->inscripcionesReforzamiento
                    ->where('ciclo_id', $carnet->ciclo_id)
                    ->where('aula_id', '!=', null)
                    ->first();
            } else {
                $inscripcion = $carnet->estudiante->inscripciones
                    ->where('ciclo_id', $carnet->ciclo_id)
                    ->where('estado_inscripcion', 'activo')
                    ->where('aula_id', '!=', null)
                    ->first();
            }
            
            if ($inscripcion && $inscripcion->aula_id) {
                return 'aula_' . $inscripcion->aula_id;
            }
            
            if ($carnet->grupo) {
                return 'grupo_' . $carnet->grupo;
            }

            return 'sin_aula';
        });

        // Crear una hoja por cada aula
        foreach ($carnetsPorAula as $groupId => $carnetsAula) {
            $nombreAula = 'Sin Aula Asignada';
            
            if (str_starts_with($groupId, 'aula_')) {
                $aulaId = substr($groupId, 5);
                $first = $carnetsAula->first();
                
                if ($first->aula_id == $aulaId && $first->aula) {
                    $nombreAula = $first->aula->nombre;
                } else {
                    if ($first->modalidad === 'reforzamiento_colegio' || $first->modalidad === 'reforzamiento') {
                        $insc = $first->estudiante->inscripcionesReforzamiento->where('ciclo_id', $first->ciclo_id)->first();
                        if ($insc && $insc->aula) {
                            $nombreAula = $insc->aula->nombre;
                        }
                    } else {
                        $insc = $first->estudiante->inscripciones->where('ciclo_id', $first->ciclo_id)->where('estado_inscripcion', 'activo')->first();
                        if ($insc && $insc->aula) {
                            $nombreAula = $insc->aula->nombre;
                        }
                    }
                }
                
                // Fallback por BD si las relaciones fallaron de alguna manera
                if ($nombreAula === 'Sin Aula Asignada') {
                    $aulaBD = \App\Models\Aula::find($aulaId);
                    if ($aulaBD) {
                        $nombreAula = $aulaBD->nombre;
                    }
                }
            } else if (str_starts_with($groupId, 'grupo_')) {
                $nombreAula = substr($groupId, 6);
            }
            
            $sheets[] = new CarnetsEntregaSheet($carnetsAula, $nombreAula);
        }

        // Si no hay carnets, crear una hoja vacía
        if (empty($sheets)) {
            $sheets[] = new CarnetsEntregaSheet(collect([]), 'Sin Datos');
        }

        return $sheets;
    }
}
