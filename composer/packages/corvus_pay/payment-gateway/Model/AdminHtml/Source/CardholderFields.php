<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class CardholderFields list cardholder fields options.
 */
class CardholderFields implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'none',
                'label' => __('None')
            ],
            [
                'value' => 'mandatory',
                'label' => __('Mandatory')
            ],
            [
                'value' => 'all',
                'label' => __('Both mandatory and optional')
            ]
        ];
    }
}
