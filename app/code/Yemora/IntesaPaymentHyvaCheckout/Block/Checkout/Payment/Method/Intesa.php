<?php

declare(strict_types=1);

namespace Yemora\IntesaPaymentHyvaCheckout\Block\Checkout\Payment\Method;

use Magento\Framework\View\Element\Template;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Intesa extends Template
{
    public function __construct(
        Template\Context $context,
        private readonly ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getDescription(): string
    {
        $config = $this->configProvider->getConfig();

        return (string) ($config['payment'][ConfigProvider::CODE]['description'] ?? '');
    }
}
