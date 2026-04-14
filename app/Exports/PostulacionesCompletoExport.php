<?php

namespace App\Exports;

use App\Models\Postulacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PostulacionesCompletoExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings, WithCustomStartCell, WithEvents
{
    protected $ciclo_id;
    protected $carrera_id;
    protected $turno_id;

    private const COLORS = [
        'PRIMARY_BLUE' => 'FF1B365D',
        'ACCENT_GOLD'  => 'FFD4AF37',
        'LIGHT_BLUE'   => 'FFE8F0F8',
        'WHITE'        => 'FFFFFFFF',
        'TEXT_DARK'    => 'FF2C3E50',
    ];

    public function __construct($ciclo_id, $carrera_id, $turno_id)
    {
        // ⚡ OPTIMIZACIÓN: Aumentar límites para evitar timeouts
        ini_set('max_execution_time', 600); // 10 minutos
        ini_set('memory_limit', '1G');
        
        $this->ciclo_id = $ciclo_id;
        $this->carrera_id = $carrera_id;
        $this->turno_id = $turno_id;
    }

    public function collection()
    {
        $ciclo = \App\Models\Ciclo::find($this->ciclo_id);
        $isReforzamiento = $ciclo && $ciclo->programa_id == 2;

        if ($isReforzamiento) {
            return \App\Models\InscripcionReforzamiento::with(['estudiante', 'apoderados', 'pagos', 'aula'])
                ->where('ciclo_id', $this->ciclo_id)
                ->get();
        }
        
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

        $results = $query->get();

        // 🚀 PRE-FETCH API DATA: Cargar en caché todos los DNIs antes de mapear
        $this->prefetchReniecData($results);

        return $results;
    }

    /**
     * Pre-carga los datos de la API para todos los estudiantes en el reporte
     * Esto evita el problema N+1 de HTTP y aprovecha el Cache.
     */
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
                // Como la API es individual, hacemos las llamadas aquí.
                // El Cache se encarga de guardarlo para el map() posterior.
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
            $ciclo = \App\Models\Ciclo::find($this->ciclo_id);
            $isReforzamiento = $ciclo && $ciclo->programa_id == 2;
            $colLogo = $isReforzamiento ? 'Q1' : 'AE1';

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
        $ciclo = \App\Models\Ciclo::find($this->ciclo_id);
        if ($ciclo && $ciclo->programa_id == 2) {
            return [
                'ID', 'DNI Estudiante', 'Apellido Paterno', 'Apellido Materno', 'Nombres', 
                'Telefono', 'Email', 'Grado', 'Turno/Sección', 'Colegio Procedencia', 
                'Estado', 'Nro Constancia', 'Fecha Registro', 'Apoderado', 'DNI Apoderado', 'Celular Apoderado', 
                'Monto Pagado', 'Mes Pagado', 'Nro Operación'
            ];
        }

        return [
            'ID', 'Codigo Postulante', 'Nombres', 'Apellido Paterno', 'Apellido Materno', 'DNI', 'Email', 'Telefono', 'Género', 'Dirección',
            'Ciclo', 'Carrera', 'Turno', 'Aula', 'Tipo Inscripcion', 
            'Fecha Postulacion', 'Estado', 'Documentos Verificados', 'Pago Verificado', 'Numero Recibo', 'Monto Total',
            'Colegio', 'Cod. Modular', 'Ubigeo Col.', 'Dpto Col.', 'Prov Col.', 'Dist Col.', 'Dirección Col.', 'Nivel Col.', 'Gestión Col.',
            'Lugar Residencia (RENIEC)', 'Ubigeo Nacimiento (RENIEC)',
            'Apoderado', 'Teléfono Apoderado', 'Registrado Por',
        ];
    }

    public function map($item): array
    {
        if ($item instanceof \App\Models\InscripcionReforzamiento) {
            $estudiante = $item->estudiante;
            $apoderado = $item->apoderados->first();
            $pago = $item->pagos->first();
            
            return [
                $item->id,
                $estudiante ? $estudiante->numero_documento : 'N/A',
                $estudiante ? $estudiante->apellido_paterno : 'N/A',
                $estudiante ? $estudiante->apellido_materno : 'N/A',
                $estudiante ? $estudiante->nombre : 'N/A',
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
                $pago ? $pago->monto : 0,
                $pago ? $pago->mes_pagado : 'N/A',
                $pago ? $pago->numero_operacion : 'N/A'
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
            $colegio ? $colegio->cen_edu : 'N/A',
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
        if ($isReforzamiento) {
            $sheet->getStyle('B8:B' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('O8:O' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        } else {
            $sheet->getStyle('F8:F' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('H8:H' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('AH8:AH' . $highestRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        }

        // 5. Ajuste de columnas inteligente + Manual para las más largas
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Si el auto-size falla o es insuficiente, forzamos anchos mínimos para columnas críticas
        $manualWidths = [
            'C' => 25, // Nombres
            'D' => 20, // Apellido P.
            'E' => 20, // Apellido M.
            'J' => 35, // Dirección
            'L' => 25, // Carrera
            'V' => 45, // Colegio
            'AB' => 45, // Dirección Col.
            'AE' => 30, // Lugar Residencia
            'AG' => 30, // Apoderado
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
                $ciclo = \App\Models\Ciclo::find($this->ciclo_id);
                $isReforzamiento = $ciclo && $ciclo->programa_id == 2;
                $highestColumn = $isReforzamiento ? 'S' : 'AI';

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
                $sheet->setCellValue('A3', 'REPORTE COMPLETO DE POSTULACIONES');
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

                $ciclo = \App\Models\Ciclo::find($this->ciclo_id);
                $isReforzamiento = $ciclo && $ciclo->programa_id == 2;

                if ($isReforzamiento) {
                    // --- DISEÑO PARA REFORZAMIENTO ---
                    // Categoría: DATOS DEL ESTUDIANTE (A6-G6) - Azul
                    $sheet->mergeCells('A6:G6');
                    $sheet->setCellValue('A6', 'DATOS DEL ESTUDIANTE');
                    $this->applyHeaderGroupStyle($sheet, 'A6:G6', 'FF3498DB');

                    // Categoría: DATOS ACADÉMICOS (H6-J6) - Verde
                    $sheet->mergeCells('H6:J6');
                    $sheet->setCellValue('H6', 'DATOS ACADÉMICOS');
                    $this->applyHeaderGroupStyle($sheet, 'H6:J6', 'FF2ECC71');

                    // Categoría: PROCESO Y ESTADO (K6-M6) - Naranja
                    $sheet->mergeCells('K6:M6');
                    $sheet->setCellValue('K6', 'PROCESO Y ESTADO');
                    $this->applyHeaderGroupStyle($sheet, 'K6:M6', 'FFE67E22');

                    // Categoría: DATOS DEL APODERADO (N6-P6) - Púrpura
                    $sheet->mergeCells('N6:P6');
                    $sheet->setCellValue('N6', 'DATOS DEL APODERADO');
                    $this->applyHeaderGroupStyle($sheet, 'N6:P6', 'FF9B59B6');

                    // Categoría: PAGOS (Q6-S6) - Turquesa
                    $sheet->mergeCells('Q6:S6');
                    $sheet->setCellValue('Q6', 'PAGOS');
                    $this->applyHeaderGroupStyle($sheet, 'Q6:S6', 'FF1ABC9C');

                    // Estilo sub-header (Fila 7)
                    $this->applySubHeaderStyle($sheet, 'A7:G7', 'FF2980B9');
                    $this->applySubHeaderStyle($sheet, 'H7:J7', 'FF27AE60');
                    $this->applySubHeaderStyle($sheet, 'K7:M7', 'FFD35400');
                    $this->applySubHeaderStyle($sheet, 'N7:P7', 'FF8E44AD');
                    $this->applySubHeaderStyle($sheet, 'Q7:S7', 'FF16A085');

                } else {
                    // --- DISEÑO PARA CEPRE REGULAR ---
                    // Categoría: DATOS DEL POSTULANTE (A6-J6) - Azul
                    $sheet->mergeCells('A6:J6');
                    $sheet->setCellValue('A6', 'DATOS DEL POSTULANTE');
                    $this->applyHeaderGroupStyle($sheet, 'A6:J6', 'FF3498DB');

                    // Categoría: DATOS ACADÉMICOS (K6-O6) - Verde
                    $sheet->mergeCells('K6:O6');
                    $sheet->setCellValue('K6', 'DATOS ACADÉMICOS');
                    $this->applyHeaderGroupStyle($sheet, 'K6:O6', 'FF2ECC71');

                    // Categoría: PROCESO Y ESTADO (P6-U6) - Naranja
                    $sheet->mergeCells('P6:U6');
                    $sheet->setCellValue('P6', 'PROCESO Y ESTADO');
                    $this->applyHeaderGroupStyle($sheet, 'P6:U6', 'FFE67E22');

                    // Categoría: DATOS DEL COLEGIO (V6-AD6) - Púrpura
                    $sheet->mergeCells('V6:AD6');
                    $sheet->setCellValue('V6', 'DATOS DEL COLEGIO');
                    $this->applyHeaderGroupStyle($sheet, 'V6:AD6', 'FF9B59B6');

                    // Categoría: VALIDACIÓN RENIEC (AE6-AF6) - Turquesa
                    $sheet->mergeCells('AE6:AF6');
                    $sheet->setCellValue('AE6', 'VALIDACIÓN RENIEC');
                    $this->applyHeaderGroupStyle($sheet, 'AE6:AF6', 'FF1ABC9C');

                    // Categoría: REFERENCIAS (AG6-AI6) - Gris
                    $sheet->mergeCells('AG6:AI6');
                    $sheet->setCellValue('AG6', 'REFERENCIAS Y REGISTRO');
                    $this->applyHeaderGroupStyle($sheet, 'AG6:AI6', 'FF95A5A6');

                    // Estilo de Cabecera Detallada (Fila 7)
                    $this->applySubHeaderStyle($sheet, 'A7:J7', 'FF2980B9');
                    $this->applySubHeaderStyle($sheet, 'K7:O7', 'FF27AE60');
                    $this->applySubHeaderStyle($sheet, 'P7:U7', 'FFD35400');
                    $this->applySubHeaderStyle($sheet, 'V7:AD7', 'FF8E44AD');
                    $this->applySubHeaderStyle($sheet, 'AE7:AF7', 'FF16A085');
                    $this->applySubHeaderStyle($sheet, 'AG7:AI7', 'FF7F8C8D');
                }
                
                // Set row heights
                $sheet->getRowDimension('1')->setRowHeight(30);
                $sheet->getRowDimension('2')->setRowHeight(25);
                $sheet->getRowDimension('3')->setRowHeight(35);
                $sheet->getRowDimension('4')->setRowHeight(20);
                $sheet->getRowDimension('6')->setRowHeight(25); // Fila de categorías
                $sheet->getRowDimension('7')->setRowHeight(35); // Fila de cabeceras

                // Forzar auto-size en todas las columnas para evitar recortes
                foreach (range('A', $highestColumn) as $col) {
                    $columnDimension = $sheet->getColumnDimension($col);
                    if (!$columnDimension->getAutoSize()) {
                        // Si ya tiene ancho manual, lo dejamos
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
