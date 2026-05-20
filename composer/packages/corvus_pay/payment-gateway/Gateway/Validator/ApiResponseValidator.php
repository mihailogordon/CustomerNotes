<?php

namespace CorvusPay\PaymentGateway\Gateway\Validator;

use InvalidArgumentException;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class ApiResponseValidator validates responses.
 */
class ApiResponseValidator extends AbstractValidator
{
    /**
     * Validates response
     *
     * @param array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        $xml = simplexml_load_string($response['response']);

        /** @var ResultInterface $result */
        if (!$xml || 'errors' === $xml->getName()) {
            $result = $this->createResult(false, [__('Gateway rejected the transaction.')]);

            return $result;
        }

        $responseCode = (string)$xml->{'response-code'};

        if ('0' === $responseCode || '300' === $responseCode) {
            $result = $this->createResult(true);
        } else {
            $result = $this->createResult(false, [__('Gateway rejected the transaction.')], [$responseCode]);
        }

        return $result;
    }
}
