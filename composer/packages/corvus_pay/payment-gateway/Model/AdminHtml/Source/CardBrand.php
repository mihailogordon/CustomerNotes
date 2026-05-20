<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class CardBrand lists card brands.
 */
class CardBrand implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'amex',
                'label' => __('American Express')
            ],
            [
                'value' => 'dina',
                'label' => __('DinaCard')
            ],
            [
                'value' => 'diners',
                'label' => __('Diners')
            ],
            [
                'value' => 'discover',
                'label' => __('Discover Card')
            ],
            [
                'value' => 'iban',
                'label' => __('IBAN')
            ],
            [
                'value' => 'maestro',
                'label' => __('Maestro')
            ],
            [
                'value' => 'master',
                'label' => __('Mastercard')
            ],
            [
                'value' => 'visa',
                'label' => __('Visa')
            ],
        ];
    }
}
