<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentAction lists payment actions.
 */
class PaymentAction implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Sale (authorization)')
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize (pre-authorization, requires completion)')
            ]
        ];
    }
}
