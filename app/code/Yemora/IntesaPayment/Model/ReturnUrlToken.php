<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model;

use Magento\Sales\Model\Order;

class ReturnUrlToken
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    public function generate(Order $order, string $result, string $message = ''): string
    {
        return hash_hmac('sha256', $this->getPayload($order, $result, $message), $this->getStoreKey($order));
    }

    public function validate(Order $order, string $result, string $message, string $token): bool
    {
        if ($token === '') {
            return false;
        }

        return hash_equals($this->generate($order, $result, $message), $token);
    }

    private function getPayload(Order $order, string $result, string $message): string
    {
        return implode('|', [
            (string) $order->getIncrementId(),
            (string) $order->getQuoteId(),
            $result,
            $message,
        ]);
    }

    private function getStoreKey(Order $order): string
    {
        return $this->config->getStoreKey((int) $order->getStoreId());
    }
}
