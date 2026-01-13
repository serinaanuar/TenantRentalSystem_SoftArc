<?php

namespace App\Payments;

interface PaymentUI
{
    /**
     * Standard method the system expects.
     * Returns normalized result for the controller.
     */
    public function handlePayment(float $amount, string $method): array;
}
