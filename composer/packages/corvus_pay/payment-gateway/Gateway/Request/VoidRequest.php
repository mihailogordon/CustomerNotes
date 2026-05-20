<?php

namespace CorvusPay\PaymentGateway\Gateway\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class VoidRequest builds a void/cancel request.
 */
class VoidRequest extends CorvusPayRequest implements BuilderInterface
{
    /**
     * Builds a void request.
     *
     * @param array $buildSubject
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        $this->setBuildSubjectPayment($buildSubject);

        $this->buildRequest();

        return [
            'parameters'   => $this->getParameters(),
            'api_endpoint' => $this->getApiEndpoint() . 'cancel',
        ];
    }
}
