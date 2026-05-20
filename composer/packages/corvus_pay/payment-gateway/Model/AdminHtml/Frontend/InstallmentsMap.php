<?php

namespace CorvusPay\PaymentGateway\Model\AdminHtml\Frontend;

use CorvusPay\PaymentGateway\Model\AdminHtml\Form\Field\CardBrandField;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class InstallmentsMap renders installments map option.
 */
class InstallmentsMap extends AbstractFieldArray
{
    protected $cardBrand;

    /**
     * Get card brand options.
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function getCardBrandRenderer()
    {
        if (!$this->cardBrand) {
            $this->cardBrand = $this->getLayout()->createBlock(
                CardBrandField::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->cardBrand;
    }

    /**
     * Prepares installments map table for render.
     *
     * @throws LocalizedException
     */
    protected function _prepareToRender() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $this->addColumn(
            'card_brand',
            [
                'label' => __('Card brand'),
                'renderer' => $this->getCardBrandRenderer()
            ]
        );
        $this->addColumn('min_installments', ['label' => __('Minimum installments')]);
        $this->addColumn('max_installments', ['label' => __('Maximum installments')]);
        $this->addColumn('general_percentage', ['label' => __('General discount')]);
        $this->addColumn('specific_percentage', ['label' => __('Specific discount')]);

        $this->_addAfter = false;
    }

    /**
     * Prepare existing row data object.
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $options = [];
        $customAttribute = $row->getData('card_brand');

        $key = 'option_' . $this->getCardBrandRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);
    }
}
