<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class CardholderFields
 */
class Installments implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'none',
                'label' => __('Disabled')
            ],
            [
                'value' => 'all',
                'label' => __('Simple')
            ],
            [
                'value' => 'map',
                'label' => __('Advanced')
            ]
        ];
    }
}
