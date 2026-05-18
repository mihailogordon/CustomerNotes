<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'intesa';

    private const XML_PATH_DESCRIPTION = 'payment/intesa/description';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'description' => (string) $this->scopeConfig->getValue(
                        self::XML_PATH_DESCRIPTION,
                        ScopeInterface::SCOPE_STORE
                    ),
                    'redirectUrl' => $this->urlBuilder->getUrl('intesa/payment/start', ['_secure' => true]),
                ],
            ],
        ];
    }
}
