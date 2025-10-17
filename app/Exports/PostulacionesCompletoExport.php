<?php

namespace App\Exports;

use App\Models\Postulacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PostulacionesCompletoExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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

    public function collection()
    {
        $query = Postulacion::with(['estudiante.parentescos.padre', 'ciclo', 'carrera', 'turno', 'centroEducativo', 'inscripcion.aula', 'inscripcion.registradoPor']);

        if ($this->ciclo_id) {
            $query->where('ciclo_id', $this->ciclo_id);
        }

        if ($this->carrera_id) {
            $query->where('carrera_id', $this->carrera_id);
        }

        if ($this->turno_id) {
            $query->where('turno_id', $this->turno_id);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Codigo Postulante',
            'Nombres',
            'Apellido Paterno',
            'Apellido Materno',
            'DNI',
            'Email',
            'Telefono',
            'Género',
            'Dirección',
            'Ciclo',
            'Carrera',
            'Turno',
            'Aula',
            'Tipo Inscripcion',
            'Fecha Postulacion',
            'Estado',
            'Documentos Verificados',
            'Pago Verificado',
            'Numero Recibo',
            'Monto Total',
            'Colegio',
            'Apoderado',
            'Teléfono Apoderado',
            'Registrado Por',
        ];
    }

    public function map($postulacion): array
    {
        $estudiante = $postulacion->estudiante;
        $apoderado = $estudiante ? $estudiante->parentescos->first() : null;
        $padre = $apoderado ? $apoderado->padre : null;
        $inscripcion = $postulacion->inscripcion;
        $aula = $inscripcion ? $inscripcion->aula : null;
        $registradoPor = $inscripcion ? $inscripcion->registradoPor : null;

        return [
            $postulacion->id,
            $postulacion->codigo_postulante,
            $estudiante ? $estudiante->nombre : 'N/A',
            $estudiante ? $estudiante->apellido_paterno : 'N/A',
            $estudiante ? $estudiante->apellido_materno : 'N/A',
            $estudiante ? $estudiante->numero_documento : 'N/A',
            $estudiante ? $estudiante->email : 'N/A',
            $estudiante ? $estudiante->telefono : 'N/A',
            $estudiante ? $estudiante->genero : 'N/A',
            $estudiante ? $estudiante->direccion : 'N/A',
            $postulacion->ciclo ? $postulacion->ciclo->nombre : 'N/A',
            $postulacion->carrera ? $postulacion->carrera->nombre : 'N/A',
            $postulacion->turno ? $postulacion->turno->nombre : 'N/A',
            $aula ? $aula->nombre : 'N/A',
            $postulacion->tipo_inscripcion,
            $postulacion->fecha_postulacion ? $postulacion->fecha_postulacion->format('d/m/Y H:i') : 'N/A',
            $postulacion->estado,
            $postulacion->documentos_verificados ? 'Si' : 'No',
            $postulacion->pago_verificado ? 'Si' : 'No',
            $postulacion->numero_recibo,
            $postulacion->monto_total_pagado,
            $postulacion->centroEducativo ? $postulacion->centroEducativo->cen_edu : 'N/A',
            $padre ? ($padre->nombre . ' ' . $padre->apellido_paterno) : 'N/A',
            $padre ? $padre->telefono : 'N/A',
            $registradoPor ? $registradoPor->nombre_completo : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para la fila de encabezados
        $sheet->getStyle('1:1')->getFont()->setBold(true);
        $sheet->getStyle('1:1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('DDDDDD');

        // Auto-ajustar el tamaño de las columnas
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Añadir bordes a todas las celdas
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestRow())->applyFromArray($styleArray);

        return [];
    }
}
