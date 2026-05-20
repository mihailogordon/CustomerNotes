<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Currency;

use Magento\Framework\Exception\LocalizedException;

class NestPayCurrencyCodeResolver
{
    private const CURRENCY_CODES = [
        'RSD' => '941',
        'EUR' => '978',
        'USD' => '840',
    ];

    public function resolveNumericCode(string $currencyCode): string
    {
        $currencyCode = strtoupper($currencyCode);

        if (!isset(self::CURRENCY_CODES[$currencyCode])) {
            throw new LocalizedException(
                __('Intesa payment does not support order currency "%1".', $currencyCode)
            );
        }

        return self::CURRENCY_CODES[$currencyCode];
    }

    public function matches(string $responseCurrency, string $orderCurrency): bool
    {
        $responseCurrency = strtoupper($responseCurrency);
        $orderCurrency = strtoupper($orderCurrency);

        return $responseCurrency === $orderCurrency
            || $responseCurrency === (self::CURRENCY_CODES[$orderCurrency] ?? '');
    }
}
