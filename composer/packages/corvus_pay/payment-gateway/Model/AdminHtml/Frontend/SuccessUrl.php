<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Frontend;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Url;

/**
 * Class SuccessUrl renders success URL option.
 */
class SuccessUrl extends Field
{
    /**
     * @var Url
     */
    protected $frontendUrlBuilder;

    /**
     * SuccessUrl constructor.
     *
     * @param Context $context
     * @param Url $frontendUrlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Url $frontendUrlBuilder,
        array $data = []
    ) {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        parent::__construct($context, $data);
    }

    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * _getElementHtml
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    { // phpcs:enable PSR2.Methods.MethodDeclaration.Underscore
        return $this->escapeHtml($this->frontendUrlBuilder->getUrl(
            'corvuspay/success',
            ['_secure' => true, '_nosid' => true, '_use_rewrite' => true]
        ));
    }
}
