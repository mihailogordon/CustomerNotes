<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;
use Yemora\IntesaPayment\Model\Config;
use Yemora\IntesaPayment\Model\Currency\NestPayCurrencyCodeResolver;

class NestPayClient
{
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly NestPayCurrencyCodeResolver $currencyCodeResolver
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function capture(Order $order, float $amount): array
    {
        return $this->sendTransaction($order, 'PostAuth', $amount);
    }

    /**
     * @return array<string, string>
     */
    public function void(Order $order, ?string $transactionId = null): array
    {
        return $this->sendTransaction($order, 'Void', null, $transactionId);
    }

    /**
     * @return array<string, string>
     */
    public function refund(Order $order, float $amount, ?string $transactionId = null): array
    {
        return $this->sendTransaction($order, 'Credit', $amount, $transactionId);
    }

    /**
     * @return array<string, string>
     */
    private function sendTransaction(
        Order $order,
        string $type,
        ?float $amount = null,
        ?string $transactionId = null
    ): array {
        $storeId = (int) $order->getStoreId();
        $this->validateConfiguration($storeId);

        $fields = [
            'Name' => $this->config->getApiUsername($storeId),
            'Password' => $this->config->getApiPassword($storeId),
            'ClientId' => $this->config->getMerchantId($storeId),
            'Type' => $type,
        ];

        if ($transactionId !== null && $transactionId !== '') {
            $fields['TransId'] = $transactionId;
        } else {
            $fields['OrderId'] = (string) $order->getIncrementId();
        }

        if ($amount !== null) {
            $fields['Currency'] = $this->resolveCurrencyCode($order);
            $fields['Total'] = $this->formatAmount($amount);
        }

        $response = $this->postXml($this->config->getApiUrl($storeId), $this->buildXml($fields));

        if (!$this->isApproved($response)) {
            throw new LocalizedException(
                __('Intesa API rejected %1 request: %2', $type, $this->getErrorMessage($response))
            );
        }

        return $response;
    }

    private function validateConfiguration(int $storeId): void
    {
        if (
            $this->config->getApiUrl($storeId) === ''
            || $this->config->getApiUsername($storeId) === ''
            || $this->config->getApiPassword($storeId) === ''
            || $this->config->getMerchantId($storeId) === ''
        ) {
            throw new LocalizedException(
                __('Intesa API is missing URL, username, password or merchant ID configuration.')
            );
        }
    }

    /**
     * @param array<string, string> $fields
     */
    private function buildXml(array $fields): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><CC5Request/>');

        foreach ($fields as $name => $value) {
            $xml->addChild($name, htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        }

        return (string) $xml->asXML();
    }

    /**
     * @return array<string, string>
     */
    private function postXml(string $url, string $xml): array
    {
        try {
            $this->curl->setTimeout(30);
            $this->curl->post($url, ['DATA' => $xml]);
        } catch (\Throwable $exception) {
            throw new LocalizedException(
                __('Unable to connect to Intesa API: %1', $exception->getMessage()),
                $exception
            );
        }

        $status = $this->curl->getStatus();
        $body = $this->curl->getBody();

        if ($status < 200 || $status >= 300) {
            throw new LocalizedException(__('Intesa API returned HTTP status %1.', $status));
        }

        $parsedXml = simplexml_load_string($body);

        if ($parsedXml === false) {
            throw new LocalizedException(__('Intesa API returned an invalid XML response.'));
        }

        return $this->flattenXmlResponse($parsedXml);
    }

    /**
     * @return array<string, string>
     */
    private function flattenXmlResponse(\SimpleXMLElement $xml): array
    {
        $response = [];

        foreach ($xml as $key => $value) {
            if ($key === 'Extra') {
                foreach ($value as $extraKey => $extraValue) {
                    if ($extraValue->count() > 0) {
                        continue;
                    }

                    $response[strtolower((string) $extraKey)] = trim((string) $extraValue);
                }

                continue;
            }

            $response[strtolower((string) $key)] = trim((string) $value);
        }

        return $response;
    }

    /**
     * @param array<string, string> $response
     */
    private function isApproved(array $response): bool
    {
        $gatewayResponse = strtolower($response['response'] ?? '');
        $procReturnCode = $response['procreturncode'] ?? $response['proc_ret_cd'] ?? '';

        return $gatewayResponse === 'approved'
            && ($procReturnCode === '' || $procReturnCode === '00');
    }

    /**
     * @param array<string, string> $response
     */
    private function getErrorMessage(array $response): string
    {
        return $response['errmsg']
            ?? $response['err_msg']
            ?? $response['error']
            ?? (string) __('Unknown gateway error.');
    }

    private function resolveCurrencyCode(Order $order): string
    {
        return $this->currencyCodeResolver->resolveNumericCode((string) $order->getOrderCurrencyCode());
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
