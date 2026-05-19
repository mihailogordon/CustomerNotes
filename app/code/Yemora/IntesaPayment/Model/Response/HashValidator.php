<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Response;

use Magento\Framework\Exception\LocalizedException;
use Yemora\IntesaPayment\Model\Config;

class HashValidator
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function validate(array $params, int $storeId): void
    {
        $receivedHash = (string) ($params['HASH'] ?? $params['hash'] ?? '');
        $hashParams = (string) ($params['HASHPARAMS'] ?? '');
        $hashParamsValue = (string) ($params['HASHPARAMSVAL'] ?? '');
        $storeKey = $this->config->getStoreKey($storeId);

        if ($receivedHash === '' || $hashParams === '' || $hashParamsValue === '' || $storeKey === '') {
            throw new LocalizedException(__('Intesa response hash data is missing.'));
        }

        $expectedHashParamsValue = $this->buildHashParamsValue($hashParams, $params);

        if (!hash_equals($expectedHashParamsValue, $hashParamsValue)) {
            throw new LocalizedException(__('Intesa response hash parameter values do not match.'));
        }

        if (!hash_equals($this->generateHash($hashParamsValue, $hashParams, $storeKey), $receivedHash)) {
            throw new LocalizedException(__('Intesa response hash is invalid.'));
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function buildHashParamsValue(string $hashParams, array $params): string
    {
        $delimiter = str_contains($hashParams, '|') ? '|' : ':';
        $fields = array_filter(explode($delimiter, $hashParams), static fn (string $field): bool => $field !== '');
        $values = [];

        foreach ($fields as $field) {
            $values[] = (string) ($params[$field] ?? '');
        }

        return implode($delimiter, $values);
    }

    private function generateHash(string $hashParamsValue, string $hashParams, string $storeKey): string
    {
        $delimiter = str_contains($hashParams, '|') ? '|' : ':';
        $plainText = $hashParamsValue . $delimiter . $this->escapeHashValue($storeKey);

        return base64_encode(hash('sha512', $plainText, true));
    }

    private function escapeHashValue(string $value): string
    {
        return str_replace(['\\', '|'], ['\\\\', '\\|'], $value);
    }
}
