<?php

namespace App\Payments\Gateways;

class FakeGatewayApi
{
    // This simulates a 3rd party gateway with different method name + response format
    public function makePayment(float $amount, string $method, int $userId): array
    {
        return [
            'ok' => true,
            'trx' => 'TRX-' . time(),
            'note' => "Paid RM {$amount} using {$method} for user {$userId}"
        ];
    }
}
