<?php

namespace App\Payments;

interface PaymentGatewayInterface
{
    /**
     * Processes a payment request and returns a result array.
     * Example return:
     * [
     *   'success' => true,
     *   'transaction_id' => 'TX123',
     *   'message' => 'Paid successfully'
     * ]
     */
    public function charge(array $payload): array;
}
