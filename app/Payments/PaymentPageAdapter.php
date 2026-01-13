<?php

namespace App\Payments;

class PaymentPageAdapter implements PaymentUI
{
    private PaymentPage $paymentPage; // Adaptee

    public function __construct(PaymentPage $paymentPage)
    {
        $this->paymentPage = $paymentPage;
    }

    public function handlePayment(float $amount, string $method): array
    {
        // Call adaptee (legacy method)
        $result = $this->paymentPage->processPayment($amount, $method);

        // Translate to a standard structure expected by controller
        return [
            'success' => $result['ok'] ?? false,
            'transaction_id' => $result['ref'] ?? null,
            'message' => $result['msg'] ?? 'Unknown result',
        ];
    }
}
