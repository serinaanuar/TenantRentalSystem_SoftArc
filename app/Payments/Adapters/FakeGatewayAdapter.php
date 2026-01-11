<?php

namespace App\Payments\Adapters;

use App\Payments\PaymentGatewayInterface;
use App\Payments\Gateways\FakeGatewayApi;

class FakeGatewayAdapter implements PaymentGatewayInterface
{
    private FakeGatewayApi $gateway;

    public function __construct(FakeGatewayApi $gateway)
    {
        $this->gateway = $gateway;
    }

    public function charge(array $payload): array
    {
        // Translate our system request → gateway request
        $amount = (float) ($payload['amount'] ?? 0);
        $method = (string) ($payload['method'] ?? 'card');
        $userId = (int) ($payload['user_id'] ?? 0);

        $result = $this->gateway->makePayment($amount, $method, $userId);

        // Translate gateway response → our system response
        return [
            'success' => (bool) ($result['ok'] ?? false),
            'transaction_id' => (string) ($result['trx'] ?? ''),
            'message' => (string) ($result['note'] ?? 'Unknown result'),
        ];
    }
}
