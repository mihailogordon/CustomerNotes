<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Source;

use CorvusPay\Service\CheckoutService;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class PaymentFormLanguage lists payment form languages.
 */
class PaymentFormLanguage implements ArrayInterface
{
    /**
     * @inheritdoc
     */
	public function toOptionArray() {
		//Get supported languages.
		$supported_languages = [
			[
				'value' => 'auto',
				'label' => __( 'Autodetect' )
			]
		];
		foreach ( CheckoutService::SUPPORTED_LANGUAGES as $code => $name ) {
			$supported_languages[] = [
				'value' => $code,
				'label' => __( $name )
			];
		}

		return $supported_languages;
	}
}
