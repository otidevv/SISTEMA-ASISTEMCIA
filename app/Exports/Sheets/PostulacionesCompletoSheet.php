<?php

namespace App\Exports\Sheets;

use App\Models\Postulacion;
use App\Models\Ciclo;
use App\Models\InscripcionReforzamiento;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PostulacionesCompletoSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings, WithCustomStartCell, WithEvents, WithTitle
{
    protected $ciclo_id;
    protected $carrera_id;
    protected $turno_id;
    protected $tipo;
    protected $rowNumber = 0;

    private const COLORS = [
        'PRIMARY_BLUE' => 'FF1B365D',
        'ACCENT_GOLD'  => 'FFD4AF37',
        'LIGHT_BLUE'   => 'FFE8F0F8',
        'WHITE'        => 'FFFFFFFF',
        'TEXT_DARK'    => 'FF2C3E50',
    ];

    public function __construct($ciclo_id, $carrera_id, $turno_id, $tipo)
    {
        ini_set('max_execution_time', 600); // 10 minutos
        ini_set('memory_limit', '1G');
        
        $this->ciclo_id = $ciclo_id;
        $this->carrera_id = $carrera_id;
        $this->turno_id = $turno_id;
        $this->tipo = $tipo;
    }

    public function title(): string
    {
        return $this->tipo === 'aprobados' ? 'Aprobados' : 'Retirados';
    }

    public function collection()
    {
        $ciclo = Ciclo::find($this->ciclo_id);
        $isReforzamiento = $ciclo && $ciclo->programa_id == 2;

        if ($isReforzamiento) {
            $query = InscripcionReforzamiento::with(['estudiante', 'apoderados', 'pagos', 'aula'])
                ->where('ciclo_id', $this->ciclo_id);

            if ($this->tipo === 'aprobados') {
                $query->where('estado_inscripcion', 'validado');
            } elseif ($this->tipo === 'retirados') {
                $query->where('estado_inscripcion', 'retirado');
            }

            $results = $query->get();
        } else {
            $relaciones = [
                'estudiante.parentescos.padre', 
                'ciclo', 
                'carrera', 
                'turno', 
                'centroEducativo',
                'inscripcion.aula',
                'inscripcion.registradoPor'
            ];

            $query = Postulacion::with($relaciones);

            if ($this->ciclo_id) {
                $query->where('ciclo_id', $this->ciclo_id);
            }

            if ($this->carrera_id) {
                $query->where('carrera_id', $this->carrera_id);
            }

            if ($this->turno_id) {
                $query->where('turno_id', $this->turno_id);
            }

            if ($this->tipo === 'aprobados') {
                $query->where('estado', 'aprobado')
                      ->where(function ($q) {
                          $q->whereDoesntHave('inscripcion')
                            ->orWhereHas('inscripcion', function ($sub) {
                                $sub->where('estado_inscripcion', '!=', 'retirado');
                            });
                      });
            } elseif ($this->tipo === 'retirados') {
                $query->whereHas('inscripcion', function ($q) {
                    $q->where('estado_inscripcion', 'retirado');
                });
            }

            $results = $query->get();
        }

        // Ordenar alfabéticamente por Apellido Paterno, Apellido Materno y Nombres
        $results = $results->sortBy(function ($item) {
            $estudiante = $item->estudiante;
            if (!$estudiante) {
                return '';
            }
            return trim(mb_strtolower($estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno . ' ' . $estudiante->nombre, 'UTF-8'));
        })->values();

        // 🚀 PRE-FETCH API DATA: Cargar en caché todos los DNIs antes de mapear
        $this->prefetchReniecData($results);

        return $results;
    }

    private function prefetchReniecData($postulaciones)
    {
        $dnis = $postulaciones->map(fn($p) => $p->estudiante->numero_documento ?? null)
                             ->filter()
                             ->unique()
                             ->values();

        // Solo procesar DNIs que NO están en caché
        $missingDnis = $dnis->filter(fn($dni) => !Cache::has("reniec_data_{$dni}"));

        if ($missingDnis->isEmpty()) return;

        // Procesar en lotes pequeños para no saturar la red ni la API
        $chunks = $missingDnis->chunk(10); 
        
        foreach ($chunks as $chunk) {
            foreach ($chunk as $dni) {
                Cache::remember("reniec_data_{$dni}", 86400, function () use ($dni) {
                    try {
                        $response = Http::timeout(3)->get("https://apidatos.unamad.edu.pe/api/consulta/{$dni}");
                        if ($response->successful()) {
                            $data = $response->json();
                            return [
                                'UBIGEO_DIR' => $data['UBIGEO_DIR'] ?? 'N/A',
                                'UBIGEO_NAC' => $data['UBIGEO_NAC'] ?? 'N/A'
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::warning("Error pre-fetching API Reniec para DNI {$dni}: " . $e->getMessage());
                    }
                    return ['UBIGEO_DIR' => 'N/A', 'UBIGEO_NAC' => 'N/A'];
                });
            }
        }
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function drawings()
    {
        $drawings = [];
        
        $logoUnamad = public_path('assets/images/logo unamad constancia.png');
        if (file_exists($logoUnamad)) {
            $drawing = new Drawing();
            $drawing->setName('UNAMAD');
            $drawing->setPath($logoUnamad);
            $drawing->setHeight(80);
            $drawing->setCoordinates('A1');
            $drawings[] = $drawing;
        }

        $logoCepre = public_path('assets/images/logo cepre costancia.png');
        if (file_exists($logoCepre)) {
            $ciclo = Ciclo::find($this->ciclo_id);
            $isReforzamiento = $ciclo && $ciclo->programa_id == 2;
            $colLogo = $isReforzamiento ? 'R1' : 'AH1';

            $drawing2 = new Drawing();
            $drawing2->setName('CEPRE');
            $drawing2->setPath($logoCepre);
            $drawing2->setHeight(80);
            $drawing2->setCoordinates($colLogo);
            $drawings[] = $drawing2;
        }

        return $drawings;
    }

    public function headings(): array
    {
        $ciclo = Ciclo::find($this->ciclo_id);
        if ($ciclo && $ciclo->programa_id == 2) {
            return [
                'N°', 'DNI Estudiante', 'Apellido Paterno', 'Apellido Materno', 'Nombres', 'Fecha Nacimiento',
                'Telefono', 'Email', 'Grado', 'Turno/Sección', 'Colegio Procedencia', 
                'Estado', 'Nro Constancia', 'Fecha Registro', 'Apoderado', 'DNI Apoderado', 'Celular Apoderado', 
                'Monto Pagado', 'Mes Pagado', 'Nro Operación'
            ];
        }

        return [
            'N°', 'Codigo Postulante', 'Nombres', 'Apellido Paterno', 'Apellido Materno', 'DNI', 'Fecha Nacimiento', 'Email', 'Telefono', 'Género', 'Dirección',
            'Ciclo', 'Carrera', 'Turno', 'Aula', 'Tipo Inscripcion', 
            'Fecha Postulacion', 'Estado', 'Documentos Verificados', 'Pago Verificado', 'Numero Recibo', 'Monto Total',
            'Colegio', 'Año de Egreso', 'Cod. Modular', 'Ubigeo Col.', 'Dpto Col.', 'Prov Col.', 'Dist Col.', 'Dirección Col.', 'Nivel Col.', 'Gestión Col.',
            'Lugar Residencia (RENIEC)', 'Ubigeo Nacimiento (RENIEC)',
            'Apoderado', 'Teléfono Apoderado', 'Registrado Por',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;

        if ($item instanceof InscripcionReforzamiento) {
            $estudiante = $item->estudiante;
            $apoderado = $item->apoderados->first();
            $pagos = $item->pagos;
            
            // Lógica de Pagos Múltiples
            $montoTotal = $pagos->where('estado_pago', 'aprobado')->sum('monto');
            if ($montoTotal == 0) $montoTotal = $pagos->sum('monto');
            
            $operaciones = $pagos->pluck('numero_operacion')->filter()->implode(', ');
            
            // Atribución de Meses
            $meses = [];
            $ciclo = $item->ciclo;
            
            foreach ($pagos as $index => $pago) {
                if ($pago->mes_pagado && !str_contains(strtolower($pago->mes_pagado), 'inscrip')) {
                    $meses[] = $pago->mes_pagado;
                } else if ($ciclo && $ciclo->fecha_inicio) {
                    $mesAtribuido = \Carbon\Carbon::parse($ciclo->fecha_inicio)->addMonths($index)->translatedFormat('F Y');
                    $meses[] = ucwords($mesAtribuido);
                } else {
                    $meses[] = $pago->mes_pagado ?: 'N/A';
                }
            }
            $mesesList = !empty($meses) ? implode(', ', array_unique($meses)) : 'N/A';

            $fechaNacimiento = 'N/A';
            if ($estudiante && $estudiante->fecha_nacimiento) {
                try {
                    $fechaNacimiento = \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->format('d/m/Y');
                } catch (\Exception $e) {
                    $fechaNacimiento = 'N/A';
                }
            }

            return [
                $this->rowNumber,
                $estudiante ? $estudiante->numero_documento : 'N/A',
                $estudiante ? $estudiante->apellido_paterno : 'N/A',
                $estudiante ? $estudiante->apellido_materno : 'N/A',
                $estudiante ? $item->estudiante->nombre : 'N/A',
                $fechaNacimiento,
                $estudiante ? $estudiante->telefono : 'N/A',
                $estudiante ? $estudiante->email : 'N/A',
                $item->grado,
                $item->turno,
                $item->colegio_procedencia,
                $item->estado_inscripcion,
                $item->nro_constancia ?: 'Pte',
                $item->created_at->format('d/m/Y H:i'),
                $apoderado ? $apoderado->nombres : 'N/A',
                $apoderado ? $apoderado->numero_documento : 'N/A',
                $apoderado ? $apoderado->celular : 'N/A',
                $montoTotal,
                $mesesList,
                $operaciones ?: 'N/A'
            ];
        }

        $postulacion = $item;
        $estudiante = $postulacion->estudiante;
        $apoderado = $estudiante ? $estudiante->parentescos->first() : null;
        $padre = $apoderado ? $apoderado->padre : null;
        
        $inscripcion = $postulacion->inscripcion;
        
        $aula = $inscripcion ? $inscripcion->aula : null;
        $registradoPor = $inscripcion ? $inscripcion->registradoPor : null;
        $colegio = $postulacion->centroEducativo;

        // Datos de RENIEC vía API con Cache
        $reniecData = ['UBIGEO_DEP' => 'N/A', 'UBIGEO_PRO' => 'N/A', 'UBIGEO_DIS' => 'N/A', 'UBIGEO_NAC' => 'N/A'];
        if ($estudiante && $estudiante->numero_documento) {
            $dni = $estudiante->numero_documento;
            $reniecData = Cache::remember("reniec_data_{$dni}", 86400, function () use ($dni) {
                try {
                    $response = Http::timeout(5)->get("https://apidatos.unamad.edu.pe/api/consulta/{$dni}");
                    if ($response->successful()) {
                        $data = $response->json();
                        return [
                            'UBIGEO_DIR' => $data['UBIGEO_DIR'] ?? 'N/A',
                            'UBIGEO_NAC' => $data['UBIGEO_NAC'] ?? 'N/A'
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Error consultando API Reniec para DNI {$dni}: " . $e->getMessage());
                }
                return ['UBIGEO_DIR' => 'N/A', 'UBIGEO_NAC' => 'N/A'];
            });
        }

        $fechaNacimiento = 'N/A';
        if ($estudiante && $estudiante->fecha_nacimiento) {
            try {
                $fechaNacimiento = \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->format('d/m/Y');
            } catch (\Exception $e) {
                $fechaNacimiento = 'N/A';
            }
        }

        return [
            $this->rowNumber,
            $postulacion->codigo_postulante,
            $estudiante ? $estudiante->nombre : 'N/A',
            $estudiante ? $estudiante->apellido_paterno : 'N/A',
            $estudiante ? $estudiante->apellido_materno : 'N/A',
            $estudiante ? $estudiante->numero_documento : 'N/A',
            $fechaNacimiento,
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
            ($inscripcion && $inscripcion->estado_inscripcion === 'retirado') ? 'Retirado' : ucfirst($postulacion->estado),
            $postulacion->documentos_verificados ? 'Si' : 'No',
            $postulacion->pago_verificado ? 'Si' : 'No',
            $postulacion->numero_recibo,
            $postulacion->monto_total_pagado,
            $colegio ? $colegio->cen_edu : 'N/A',
            $postulacion->anio_egreso ?: 'N/A',
            $colegio ? $colegio->cod_mod : 'N/A',
            $colegio ? $colegio->codgeo : 'N/A',
            $colegio ? $colegio->d_dpto : 'N/A',
            $colegio ? $colegio->d_prov : 'N/A',
            $colegio ? $colegio->d_dist : 'N/A',
            $colegio ? $colegio->dir_cen : 'N/A',
            $colegio ? $colegio->d_niv_mod : 'N/A',
            $colegio ? $colegio->d_gestion : 'N/A',
            $reniecData['UBIGEO_DIR'],
            $reniecData['UBIGEO_NAC'],
            $padre ? ($padre->nombre . ' ' . $padre->apellido_paterno) : 'N/A',
            $padre ? $padre->telefono : 'N/A',
            $registradoPor ? ($registradoPor->nombre_completo ?? $registradoPor->nombre) : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $ciclo = Ciclo::find($this->ciclo_id);
        $isReforzamiento = $ciclo && $ciclo->programa_id == 2;

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // 1. Table Header Styling (Row 7)
        $sheet->getStyle('A7:' . $highestColumn . '7')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => self::COLORS['WHITE']],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => self::COLORS['PRIMARY_BLUE']],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension('7')->setRowHeight(35);

        // 2. Body Styling (Zebra Stripes & Alignment)
        for ($i = 8; $i <= $highestRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':' . $highestColumn . $i)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB(self::COLORS['LIGHT_BLUE']);
            }
            $sheet->getRowDimension($i)->setRowHeight(20);
        }

        // 3. Global Borders
        $sheet->getStyle('A7:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFBDC3C7'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'indent' => 1,
            ],
        ]);

        // 4. Formatear DNI y Teléfonos como Texto
        if ($highestRow >= 8) {
            if ($isReforzamiento) {
                $sheet->getStyle('B8:B' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                $sheet->getStyle('P8:P' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            } else {
                $sheet->getStyle('F8:F' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                $sheet->getStyle('I8:I' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                $sheet->getStyle('AJ8:AJ' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            }
        }

        // 5. Ajuste de columnas inteligente + Manual para las más largas
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $manualWidths = [
            'C' => 25, // Nombres
            'D' => 20, // Apellido P.
            'E' => 20, // Apellido M.
            'G' => 15, // Fecha Nacimiento
            'K' => 35, // Dirección
            'M' => 25, // Carrera
            'W' => 45, // Colegio
            'X' => 15, // Año de Egreso
            'AD' => 45, // Dirección Col.
            'AG' => 30, // Lugar Residencia
            'AI' => 30, // Apoderado
        ];

        foreach ($manualWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setAutoSize(false);
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ciclo = Ciclo::find($this->ciclo_id);
                $isReforzamiento = $ciclo && $ciclo->programa_id == 2;
                $highestColumn = $isReforzamiento ? 'T' : 'AK';

                // 1. Títulos Institucionales (A1:A4)
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->setCellValue('A1', 'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1B365D']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->mergeCells("A2:{$highestColumn}2");
                $sheet->setCellValue('A2', 'CENTRO PRE UNIVERSITARIO - CEPRE-UNAMAD');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1B365D']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->mergeCells("A3:{$highestColumn}3");
                $tituloReporte = $this->tipo === 'aprobados' ? 'REPORTE COMPLETO DE POSTULACIONES (APROBADOS)' : 'REPORTE COMPLETO DE POSTULACIONES (RETIRADOS)';
                $sheet->setCellValue('A3', $tituloReporte);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF2C3E50']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->mergeCells("A4:{$highestColumn}4");
                $sheet->setCellValue('A4', 'Fecha de generación: ' . date('d/m/Y H:i'));
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                if ($isReforzamiento) {
                    // --- DISEÑO PARA REFORZAMIENTO ---
                    $sheet->mergeCells('A6:H6');
                    $sheet->setCellValue('A6', 'DATOS DEL ESTUDIANTE');
                    $this->applyHeaderGroupStyle($sheet, 'A6:H6', 'FF3498DB');

                    $sheet->mergeCells('I6:K6');
                    $sheet->setCellValue('I6', 'DATOS ACADÉMICOS');
                    $this->applyHeaderGroupStyle($sheet, 'I6:K6', 'FF2ECC71');

                    $sheet->mergeCells('L6:N6');
                    $sheet->setCellValue('L6', 'PROCESO Y ESTADO');
                    $this->applyHeaderGroupStyle($sheet, 'L6:N6', 'FFE67E22');

                    $sheet->mergeCells('O6:Q6');
                    $sheet->setCellValue('O6', 'DATOS DEL APODERADO');
                    $this->applyHeaderGroupStyle($sheet, 'O6:Q6', 'FF9B59B6');

                    $sheet->mergeCells('R6:T6');
                    $sheet->setCellValue('R6', 'PAGOS');
                    $this->applyHeaderGroupStyle($sheet, 'R6:T6', 'FF1ABC9C');

                    $this->applySubHeaderStyle($sheet, 'A7:H7', 'FF2980B9');
                    $this->applySubHeaderStyle($sheet, 'I7:K7', 'FF27AE60');
                    $this->applySubHeaderStyle($sheet, 'L7:N7', 'FFD35400');
                    $this->applySubHeaderStyle($sheet, 'O7:Q7', 'FF8E44AD');
                    $this->applySubHeaderStyle($sheet, 'R7:T7', 'FF16A085');

                } else {
                    // --- DISEÑO PARA CEPRE REGULAR ---
                    $sheet->mergeCells('A6:K6');
                    $sheet->setCellValue('A6', 'DATOS DEL POSTULANTE');
                    $this->applyHeaderGroupStyle($sheet, 'A6:K6', 'FF3498DB');

                    $sheet->mergeCells('L6:P6');
                    $sheet->setCellValue('L6', 'DATOS ACADÉMICOS');
                    $this->applyHeaderGroupStyle($sheet, 'L6:P6', 'FF2ECC71');

                    $sheet->mergeCells('Q6:V6');
                    $sheet->setCellValue('Q6', 'PROCESO Y ESTADO');
                    $this->applyHeaderGroupStyle($sheet, 'Q6:V6', 'FFE67E22');

                    $sheet->mergeCells('W6:AF6');
                    $sheet->setCellValue('W6', 'DATOS DEL COLEGIO');
                    $this->applyHeaderGroupStyle($sheet, 'W6:AF6', 'FF9B59B6');

                    $sheet->mergeCells('AG6:AH6');
                    $sheet->setCellValue('AG6', 'VALIDACIÓN RENIEC');
                    $this->applyHeaderGroupStyle($sheet, 'AG6:AH6', 'FF1ABC9C');

                    $sheet->mergeCells('AI6:AK6');
                    $sheet->setCellValue('AI6', 'REFERENCIAS Y REGISTRO');
                    $this->applyHeaderGroupStyle($sheet, 'AI6:AK6', 'FF95A5A6');

                    $this->applySubHeaderStyle($sheet, 'A7:K7', 'FF2980B9');
                    $this->applySubHeaderStyle($sheet, 'L7:P7', 'FF27AE60');
                    $this->applySubHeaderStyle($sheet, 'Q7:V7', 'FFD35400');
                    $this->applySubHeaderStyle($sheet, 'W7:AF7', 'FF8E44AD');
                    $this->applySubHeaderStyle($sheet, 'AG7:AH7', 'FF16A085');
                    $this->applySubHeaderStyle($sheet, 'AI7:AK7', 'FF7F8C8D');
                }
                
                $sheet->getRowDimension('1')->setRowHeight(30);
                $sheet->getRowDimension('2')->setRowHeight(25);
                $sheet->getRowDimension('3')->setRowHeight(35);
                $sheet->getRowDimension('4')->setRowHeight(20);
                $sheet->getRowDimension('6')->setRowHeight(25);
                $sheet->getRowDimension('7')->setRowHeight(35);

                foreach (range('A', $highestColumn) as $col) {
                    $columnDimension = $sheet->getColumnDimension($col);
                    if (!$columnDimension->getAutoSize()) {
                        continue;
                    }
                    $columnDimension->setAutoSize(true);
                }
            },
        ];
    }

    private function applyHeaderGroupStyle($sheet, $range, $color)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFFFFFFF']]]
        ]);
    }

    private function applySubHeaderStyle($sheet, $range, $color)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]]
        ]);
    }
}
