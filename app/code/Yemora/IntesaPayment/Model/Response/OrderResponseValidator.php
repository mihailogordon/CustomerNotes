<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Yemora\IntesaPayment\Model\Config;

class OrderResponseValidator
{
    private const CURRENCY_CODES = [
        'RSD' => '941',
        'EUR' => '978',
        'USD' => '840',
    ];

    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function validate(array $params, Order $order, bool $requireFinancialFields): void
    {
        $this->validateOrderId($params, $order);
        $this->validateMerchantId($params, $order);
        $this->validateAmount($params, $order, $requireFinancialFields);
        $this->validateCurrency($params, $order, $requireFinancialFields);
        $this->validateTransactionType($params, $order);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateOrderId(array $params, Order $order): void
    {
        $responseOrderId = $this->getFirstParamValue($params, ['oid', 'OrderId', 'order_id']);

        if ($responseOrderId === '' || $responseOrderId !== (string) $order->getIncrementId()) {
            throw new LocalizedException(__('Intesa response order ID does not match the order.'));
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateMerchantId(array $params, Order $order): void
    {
        $merchantId = $this->getFirstParamValue($params, ['clientid', 'clientId', 'ClientId', 'merchantId']);

        if ($merchantId === '') {
            return;
        }

        if ($merchantId !== $this->config->getMerchantId((int) $order->getStoreId())) {
            throw new LocalizedException(__('Intesa response merchant ID does not match the configured merchant.'));
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateAmount(array $params, Order $order, bool $required): void
    {
        $amount = $this->getFirstParamValue($params, ['amount', 'Amount']);

        if ($amount === '') {
            if ($required) {
                throw new LocalizedException(__('Intesa response amount is missing.'));
            }

            return;
        }

        if ($this->formatAmount((float) $amount) !== $this->formatAmount((float) $order->getGrandTotal())) {
            throw new LocalizedException(__('Intesa response amount does not match the order.'));
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateCurrency(array $params, Order $order, bool $required): void
    {
        $currency = strtoupper($this->getFirstParamValue($params, ['currency', 'Currency', 'currencyAlphaCode']));

        if ($currency === '') {
            if ($required) {
                throw new LocalizedException(__('Intesa response currency is missing.'));
            }

            return;
        }

        $orderCurrency = strtoupper((string) $order->getOrderCurrencyCode());
        $expectedNumericCurrency = self::CURRENCY_CODES[$orderCurrency] ?? '';

        if ($currency !== $orderCurrency && $currency !== $expectedNumericCurrency) {
            throw new LocalizedException(__('Intesa response currency does not match the order.'));
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateTransactionType(array $params, Order $order): void
    {
        $transactionType = $this->getFirstParamValue($params, ['trantype', 'TranType', 'transactionType']);

        if ($transactionType === '') {
            return;
        }

        $expectedTransactionType = $this->config->getPaymentAction((int) $order->getStoreId())
            === MethodInterface::ACTION_AUTHORIZE_CAPTURE ? 'Auth' : 'PreAuth';

        if (strcasecmp($transactionType, $expectedTransactionType) !== 0) {
            throw new LocalizedException(__('Intesa response transaction type does not match the payment action.'));
        }
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * @param array<string, mixed> $params
     * @param string[] $keys
     */
    private function getFirstParamValue(array $params, array $keys): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $params)) {
                return trim((string) $params[$key]);
            }
        }

        return '';
    }
}
