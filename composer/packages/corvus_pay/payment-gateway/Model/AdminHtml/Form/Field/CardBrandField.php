<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Form\Field;

use CorvusPay\PaymentGateway\Model\AdminHtml\Source\CardBrand;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class CardBrandField renders card brand field in installments map option.
 */
class CardBrandField extends Select
{
    protected $cardBrand;

    /**
     * CardBrand constructor.
     *
     * @param Context $context
     * @param CardBrand $cardBrand
     * @param array $data
     */
    public function __construct(
        Context $context,
        CardBrand $cardBrand,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->cardBrand = $cardBrand;
    }

    /**
     * Sets input name.
     *
     * @param string $value
     *
     * @return CardBrandField
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render as HTML.
     *
     * @return string
     */
    public function _toHtml() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if (!$this->getOptions()) {
            $attributes = $this->cardBrand->toOptionArray();

            foreach ($attributes as $attribute) {
                $this->addOption($attribute['value'], $attribute['label']);
            }
        }

        return parent::_toHtml();
    }
}
