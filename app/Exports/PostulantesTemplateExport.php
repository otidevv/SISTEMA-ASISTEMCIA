<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PostulantesTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return collect([
            [
                '78945612', // dni
                'JUAN CARLOS', // nombres
                'PEREZ', // apellido_paterno
                'GOMEZ', // apellido_materno
                'juan.perez@email.com', // email
                '987654321', // telefono
                'AV. LOS INCAS 123', // direccion
                '2005-05-15', // fecha_nacimiento
                'M', // genero
                'ENFERMERIA', // carrera
                'MAÑANA', // turno
                'postulante', // modalidad
                'COLEGIO NACIONAL', // colegio
                '2022', // anio_egreso
                '12345678', // dni_padre
                'CARLOS PEREZ', // nombre_padre_completo
                '999888777', // celular_padre
                '87654321', // dni_madre
                'MARIA GOMEZ', // nombre_madre_completo
                '999111222' // celular_madre
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'dni',
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'email',
            'telefono',
            'direccion',
            'genero', // M o F
            'fecha_nacimiento', // YYYY-MM-DD
            'carrera', // Obligatorio
            'turno', // MAÑANA / TARDE / NOCHE
            'modalidad', // POSTULANTE
            'codigo_postulante', // Opcional (Manual)
            'colegio', // Opcional (Nombre Colegio)
            'dni_padre',
            'nombre_padre_completo',
            'celular_padre',
            'dni_madre',
            'nombre_madre_completo',
            'celular_madre'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
