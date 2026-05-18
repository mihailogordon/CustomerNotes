<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Yemora\IntesaPayment\Model\Config;

class PaymentRequestBuilder
{
    private const STORE_TYPE = '3d_pay_hosting';
    private const HASH_ALGORITHM = 'ver2';
    private const ENCODING = 'utf-8';
    private const TRANSACTION_TYPE_AUTHORIZE = 'PreAuth';
    private const TRANSACTION_TYPE_CAPTURE = 'Auth';
    private const CURRENCY_CODES = [
        'RSD' => '941',
        'EUR' => '978',
        'USD' => '840',
    ];

    public function __construct(
        private readonly Config $config,
        private readonly ResolverInterface $localeResolver,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * @return array{gateway_url: string, fields: array<string, string>}
     */
    public function build(OrderInterface $order): array
    {
        $storeId = (int) $order->getStoreId();
        $gatewayUrl = $this->config->getGatewayUrl($storeId);
        $merchantId = $this->config->getMerchantId($storeId);
        $storeKey = $this->config->getStoreKey($storeId);

        if ($gatewayUrl === '' || $merchantId === '' || $storeKey === '') {
            throw new LocalizedException(
                __('Intesa payment is missing Gateway URL, Merchant ID or StoreKey configuration.')
            );
        }

        $fields = [
            'currency' => $this->resolveCurrencyCode($order),
            'trantype' => $this->resolveTransactionType($storeId),
            'okUrl' => $this->urlBuilder->getUrl('intesa/payment/success', ['_secure' => true]),
            'failUrl' => $this->urlBuilder->getUrl('intesa/payment/fail', ['_secure' => true]),
            'amount' => $this->formatAmount((float) $order->getGrandTotal()),
            'oid' => (string) $order->getIncrementId(),
            'clientid' => $merchantId,
            'storetype' => self::STORE_TYPE,
            'lang' => $this->resolveLanguage($storeId),
            'hashAlgorithm' => self::HASH_ALGORITHM,
            'rnd' => bin2hex(random_bytes(16)),
            'encoding' => self::ENCODING,
        ];

        $fields['hash'] = $this->generateHash($fields, $storeKey);

        return [
            'gateway_url' => $gatewayUrl,
            'fields' => $fields,
        ];
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    private function resolveCurrencyCode(OrderInterface $order): string
    {
        $currencyCode = strtoupper((string) $order->getOrderCurrencyCode());

        if (!isset(self::CURRENCY_CODES[$currencyCode])) {
            throw new LocalizedException(
                __('Intesa payment does not support order currency "%1".', $currencyCode)
            );
        }

        return self::CURRENCY_CODES[$currencyCode];
    }

    private function resolveLanguage(int $storeId): string
    {
        $language = $this->config->getLanguage($storeId);

        if ($language !== 'auto') {
            return $language;
        }

        $locale = strtolower($this->localeResolver->getLocale());

        return str_starts_with($locale, 'en') ? 'en' : 'sr';
    }

    private function resolveTransactionType(int $storeId): string
    {
        if ($this->config->getPaymentAction($storeId) === MethodInterface::ACTION_AUTHORIZE_CAPTURE) {
            return self::TRANSACTION_TYPE_CAPTURE;
        }

        return self::TRANSACTION_TYPE_AUTHORIZE;
    }

    /**
     * Hash ver2 sequence from Banca Intesa/NestPay guidance:
     * clientid|oid|amount|okurl|failurl|trantype||rnd||||currency|StoreKey
     */
    private function generateHash(array $fields, string $storeKey): string
    {
        $hashFields = [
            $fields['clientid'],
            $fields['oid'],
            $fields['amount'],
            $fields['okUrl'],
            $fields['failUrl'],
            $fields['trantype'],
            '',
            $fields['rnd'],
            '',
            '',
            '',
            $fields['currency'],
            $storeKey,
        ];

        $plainText = implode('|', array_map([$this, 'escapeHashValue'], $hashFields));

        return base64_encode(hash('sha512', $plainText, true));
    }

    private function escapeHashValue(string $value): string
    {
        return str_replace(['\\', '|'], ['\\\\', '\\|'], $value);
    }
}
