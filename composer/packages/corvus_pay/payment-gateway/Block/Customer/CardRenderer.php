<?php

namespace CorvusPay\PaymentGateway\Block\Customer;

use CorvusPay\PaymentGateway\Model\Ui\ConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

/**
 * Class CardRenderer renders stored cards (tokens).
 */
class CardRenderer extends AbstractCardRenderer
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CODE;
    }

    /**
     * Get last four digits of masked PAN.
     *
     * @return string
     */
    public function getNumberLast4Digits()
    {
        return substr($this->getTokenDetails()['maskedPan'], -4);
    }

    /**
     * Get expiration date.
     *
     * @return string
     */
    public function getExpDate()
    {
        return $this->getTokenDetails()['expirationDate'];
    }

    /**
     * Get icon URL.
     *
     * @return string
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->getTokenDetails()['cardType'])['url'];
    }

    /**
     * Get icon height.
     *
     * @return int
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->getTokenDetails()['cardType'])['height'];
    }

    /**
     * Get icon width.
     *
     * @return int
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->getTokenDetails()['cardType'])['width'];
    }
}
