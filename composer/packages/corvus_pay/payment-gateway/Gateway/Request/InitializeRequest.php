<?php

namespace CorvusPay\PaymentGateway\Gateway\Request;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class InitializeRequest builds a payment request.
 */
class InitializeRequest extends CorvusPayRequest implements BuilderInterface
{
    /**
     * Builds a payment request.
     *
     * @param array $buildSubject
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['paymentAction'])) {
            throw new InvalidArgumentException('Payment action should be provided');
        }

        $this->setBuildSubjectPaymentAction($buildSubject);

        $this->buildPaymentRequest();

        return [
            'parameters'               => $this->getParameters(),
            'checkout_url'             => $this->getCheckoutUrl(),
            'auto_redirect'            => $this->getAutoRedirect(),
            'encrypted_secret_key'     => $this->getEncryptedSecretKey(),
            'order_confirmation_email' => $this->getOrderConfirmationEmail(),
            'require_complete'         =>
                AbstractMethod::ACTION_AUTHORIZE === $buildSubject['paymentAction'],
            'environment'              => $this->getEnvironment()
        ];
    }
}
