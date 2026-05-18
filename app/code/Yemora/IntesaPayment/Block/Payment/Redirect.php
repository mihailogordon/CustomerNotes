<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Block\Payment;

use Magento\Framework\View\Element\Template;

class Redirect extends Template
{
    /**
     * @return array<string, string>
     */
    public function getPaymentFields(): array
    {
        return (array) $this->getData('payment_fields');
    }

    public function getGatewayUrl(): string
    {
        return (string) $this->getData('gateway_url');
    }
}
