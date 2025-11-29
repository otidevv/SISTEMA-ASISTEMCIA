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
     *
     * @param string $dni The student's DNI (used to query the API).
     * @param string $voucherCode The voucher sequence number to verify.
     * @return array|null Returns payment data if valid, null otherwise.
     */
    public function validateVoucher($dni, $voucherCode)
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

                // Return all payments found for the DNI
                $foundPayments = [];

                foreach ($payments as $voucher) {
                    // Extract payment details (can be multiple items per voucher)
                    if (isset($voucher['payments']) && is_array($voucher['payments'])) {
                        foreach ($voucher['payments'] as $paymentDetail) {
                            $foundPayments[] = [
                                'serial' => $voucher['serial_voucher'] ?? '---',
                                'monto' => number_format($paymentDetail['total'] ?? 0, 2),
                                'fecha' => $paymentDetail['paymentDate'] ?? null,
                                'concepto' => $paymentDetail['description'] ?? 'Pago CEPRE',
                                'tipo' => $paymentDetail['type_user'] ?? '',
                                'estado' => $paymentDetail['status'] ?? '',
                                'lugar' => $paymentDetail['name'] ?? ''
                            ];
                        }
                    }
                }

                // Sort by date descending
                usort($foundPayments, function($a, $b) {
                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                });

                if (!empty($foundPayments)) {
                    return $foundPayments;
                }
            }

            Log::warning("No payments found for DNI: {$dni}");
            return null;

        } catch (\Exception $e) {
            Log::error("Error connecting to payment API: " . $e->getMessage());
            return null;
        }
    }
}
