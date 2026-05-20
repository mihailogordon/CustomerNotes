<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Source;

use CorvusPay\Service\CheckoutService;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Tab lists tabs showed during checkout.
 */
class Tab implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
	    $hide_tabs = [];

	    foreach ( CheckoutService::TABS as $code => $name ) {
		    $hide_tabs[] = [
			    'value' => $code,
			    'label' => __( $name )
		    ];
	    }

	    return $hide_tabs;
    }
}
