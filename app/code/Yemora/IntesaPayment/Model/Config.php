<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Config
{
    private const XML_PATH_PREFIX = 'payment/' . ConfigProvider::CODE . '/';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function getValue(string $field, ?int $storeId = null): ?string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PREFIX . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value !== null ? (string) $value : null;
    }

    public function getEnvironment(?int $storeId = null): string
    {
        return $this->getValue('environment', $storeId) ?: 'test';
    }

    public function getGatewayUrl(?int $storeId = null): string
    {
        return (string) $this->getValue($this->getEnvironment($storeId) . '_gateway_url', $storeId);
    }

    public function getMerchantId(?int $storeId = null): string
    {
        return (string) $this->getValue($this->getEnvironment($storeId) . '_merchant_id', $storeId);
    }

    public function getStoreKey(?int $storeId = null): string
    {
        $value = $this->getValue($this->getEnvironment($storeId) . '_store_key', $storeId);

        return $value ? $this->encryptor->decrypt($value) : '';
    }

    public function getApiUrl(?int $storeId = null): string
    {
        return (string) $this->getValue($this->getEnvironment($storeId) . '_api_url', $storeId);
    }

    public function getApiUsername(?int $storeId = null): string
    {
        return (string) $this->getValue($this->getEnvironment($storeId) . '_api_username', $storeId);
    }

    public function getApiPassword(?int $storeId = null): string
    {
        $value = $this->getValue($this->getEnvironment($storeId) . '_api_password', $storeId);

        return $value ? $this->encryptor->decrypt($value) : '';
    }

    public function getLanguage(?int $storeId = null): string
    {
        return $this->getValue('lang', $storeId) ?: 'auto';
    }

    public function getPaymentAction(?int $storeId = null): string
    {
        $paymentAction = $this->getValue('payment_action', $storeId);

        return $paymentAction ?: MethodInterface::ACTION_AUTHORIZE_CAPTURE;
    }
}
