<?php

namespace App\Payments;

use Illuminate\Support\Str;

class PaymentPage
{
    /**
     * Legacy / existing method (different name & output structure).
     * Adapter will translate to PaymentUI format.
     */
    public function processPayment(float $amount, string $method): array
    {
        // Simulated internal payment logic (NO external system)
        if ($amount <= 0) {
            return [
                'ok' => false,
                'msg' => 'Invalid amount.',
            ];
        }

        $allowed = ['CARD', 'FPX', 'CASH'];
        if (!in_array(strtoupper($method), $allowed)) {
            return [
                'ok' => false,
                'msg' => 'Unsupported payment method.',
            ];
        }

        return [
            'ok' => true,
            'ref' => 'PMT-' . strtoupper(Str::random(10)),
            'msg' => 'Payment processed internally.',
        ];
    }
}
