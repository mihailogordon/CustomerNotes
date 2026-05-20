<?php

namespace CorvusPay\PaymentGateway\Gateway\Request;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class NextSubscriptionPaymentRequest builds a token payment request.
 */
class NextSubscriptionPaymentRequest extends CorvusPayRequest implements BuilderInterface
{
    /**
     * Builds a next subscription payment request.
     *
     * @param array $buildSubject
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['amount'])) {
            throw new InvalidArgumentException('Amount should be provided');
        }

        $this->setBuildSubjectPayment($buildSubject);

        $this->setBuildSubjectNextSubscriptionPaymentRequest((double)$buildSubject['amount']);

        return [
            'parameters'   => $this->getParameters(),
            'api_endpoint' => $this->getApiEndpoint() . 'next_sub_payment',
        ];
    }
}
