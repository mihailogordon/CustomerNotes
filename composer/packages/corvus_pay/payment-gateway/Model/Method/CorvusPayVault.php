<?php

namespace CorvusPay\PaymentGateway\Model\Method;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Vault\Model\Method\Vault;

/**
 * Class CorvusPayVault overrides vault options for CorvusPay.
 */
class CorvusPayVault extends Vault
{
    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        return AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
    }
}
