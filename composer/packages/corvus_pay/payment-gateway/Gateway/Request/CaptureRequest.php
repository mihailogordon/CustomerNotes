<?php

namespace CorvusPay\PaymentGateway\Gateway\Request;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CaptureRequest builds a capture request.
 */
class CaptureRequest extends CorvusPayRequest implements BuilderInterface
{
    /**
     * Builds a capture request.
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

        if ($this->order->getGrandTotalAmount() == $buildSubject['amount']) {
            $this->buildRequest();

            return [
                'parameters'   => $this->getParameters(),
                'api_endpoint' => $this->getApiEndpoint() . 'complete',
            ];
        } else {
            $this->buildPartialRequest($buildSubject['amount']);

            return [
                'parameters'   => $this->getParameters(),
                'api_endpoint' => $this->getApiEndpoint() . 'partial_complete',
            ];
        }
    }
}
