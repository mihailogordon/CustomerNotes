<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Environment lists environment options.
 */
class Environment implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'test',
                'label' => __('Test')
            ],
            [
                'value' => 'prod',
                'label' => __('Production')
            ]
        ];
    }
}
