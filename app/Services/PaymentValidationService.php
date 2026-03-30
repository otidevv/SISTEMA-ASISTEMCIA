<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentValidationService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = 'https://daa-documentos.unamad.edu.pe:8081/api/data/payments';
        // Token provided by user. In a real app, this should be in .env
        $this->token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIwOTU0YWRlOC1iZjZkLTRlMDUtYTczMy1mMWVlZmVlMDRkZDgiLCJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoidm1lZ28iLCJuYW1lIjoidm1lZ28iLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOlsiT2ZpY2luYSIsIkFwaUNvbnN1bWVyIl0sImV4cCI6MTc0OTIyOTc5NiwiaXNzIjoiYzk4NGRmYjFhMDE3YTNlZjhiOTdlMjUzOWY3ZWNhYWEifQ.mOtiqZrBN8j6QRn4gWDnyYIUOnLQivnlM6j_kFW8wSw';
    }

    /**
     * Validate a payment voucher. 
     */
    public function validateVoucher($dni, $voucherCode, $onlyPostulation = false)
    {
        try {
            // Query API by DNI to get all payments with increased timeout
            $response = Http::withToken($this->token)
                ->withoutVerifying()
                ->timeout(30) // Increased timeout to 30 seconds
                ->get("{$this->baseUrl}/{$dni}");

            if ($response->successful()) {
                $payments = $response->json();
                
                if (!is_array($payments)) {
                    return null;
                }

                $foundVouchers = [];

                foreach ($payments as $voucher) {
                    // Intento de captura de serial con múltiples fallbacks (la API usa camelCase mayormente)
                    $serial = $voucher['serialVoucher'] ?? 
                              $voucher['serial_voucher'] ?? 
                              $voucher['numero_operacion'] ?? 
                              $voucher['cod_operacion'] ?? 
                              $voucher['recibo'] ?? 
                              '---';
                    
                    if (!isset($foundVouchers[$serial])) {
                        $foundVouchers[$serial] = [
                            'serial' => $serial,
                            'monto_total' => 0,
                            'monto_matricula' => 0,
                            'monto_ensenanza' => 0,
                            'fecha' => null,
                            'lugar' => $voucher['payments'][0]['name'] ?? 'CEPRE',
                            'items' => []
                        ];
                    }

                    if (isset($voucher['payments']) && is_array($voucher['payments'])) {
                        foreach ($voucher['payments'] as $detail) {
                            $monto = (float)($detail['total'] ?? 0);
                            $foundVouchers[$serial]['monto_total'] += $monto;
                            $foundVouchers[$serial]['fecha'] = $detail['paymentDate'] ?? $foundVouchers[$serial]['fecha'];
                            
                            $desc = $detail['description'] ?? '';
                            if (str_contains($desc, '582')) {
                                $foundVouchers[$serial]['monto_matricula'] += $monto;
                            } else if (str_contains($desc, '583') || str_contains($desc, '598')) {
                                // 583 es enseñanza, 598 es reforzamiento (similar a enseñanza)
                                $foundVouchers[$serial]['monto_ensenanza'] += $monto;
                            } else {
                                $foundVouchers[$serial]['monto_ensenanza'] += $monto;
                            }

                            $foundVouchers[$serial]['items'][] = [
                                'descripcion' => $desc,
                                'monto' => number_format($monto, 2, '.', ''),
                            ];
                        }
                    }
                }

                // Convertir a lista
                $result = array_values($foundVouchers);
                
                // Filtrar para postulación solo si se solicita
                if ($onlyPostulation) {
                    $result = array_filter($result, function($v) {
                        return (float)$v['monto_matricula'] > 0 || (float)$v['monto_ensenanza'] > 0;
                    });
                }

                // Ordenar por fecha descendente (lo más reciente primero) siempre
                usort($result, function($a, $b) {
                    return strtotime($b['fecha'] ?? 0) - strtotime($a['fecha'] ?? 0);
                });

                foreach ($result as &$v) {
                    $v['monto_total'] = number_format($v['monto_total'], 2, '.', '');
                    $v['monto_matricula'] = number_format($v['monto_matricula'], 2, '.', '');
                    $v['monto_ensenanza'] = number_format($v['monto_ensenanza'], 2, '.', '');
                    
                    // ALINEACIÓN CON FRONTEND: Añadir alias para campos comunes
                    $v['monto'] = $v['monto_total'];
                    $v['total'] = $v['monto_total'];
                    $v['serial_voucher'] = $v['serial'];
                    
                    $v['concepto'] = !empty($v['items']) ? $v['items'][0]['descripcion'] : 'Pago CEPRE';
                    if (count($v['items']) > 1) {
                        $v['concepto'] .= ' (+ ' . (count($v['items']) - 1) . ' items)';
                    }
                }

                return !empty($result) ? array_values($result) : null;
            }
 
            Log::warning("No payments found for DNI: {$dni}");
            return null;

        } catch (\Exception $e) {
            Log::error("Error connecting to payment API: " . $e->getMessage());
            return null;
        }
    }
}
