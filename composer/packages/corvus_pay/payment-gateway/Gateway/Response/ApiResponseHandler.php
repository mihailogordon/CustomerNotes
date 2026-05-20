<?php

namespace CorvusPay\PaymentGateway\Gateway\Response;

use CorvusPay\PaymentGateway\Gateway\Request\CorvusPayRequest;
use Exception;
use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class ApiResponseHandler handles CorvusPay API responses.
 */
class ApiResponseHandler implements HandlerInterface
{
    /**
     * Handles capture
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @throws Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $handlingSubject['payment']->getPayment();
        if (!$payment instanceof Payment) {
            throw new InvalidArgumentException('Payment data object should contain a payment');
        }
        /** @var $payment Payment */

        $xml = simplexml_load_string($response['response']);

        $orderNumber  = (string)$xml->{'order-number'};
        $approvalCode = (string)$xml->{'approval-code'};
        $status       = (string)$xml->{'status'};

        $exploded = explode(
            CorvusPayRequest::ORDER_NUMBER_DELIMITER,
            $orderNumber
        );

        $orderIncrementId = end($exploded);

        $payment->setTransactionId("{$orderIncrementId} [{$status}]");
        $lastTransId = $payment->getLastTransId();
        if (null !== $lastTransId) {
            $payment->setParentTransactionId($lastTransId);
            $payment->setShouldCloseParentTransaction(true);
        }
        $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, [
            'order_number'  => $orderNumber,
            'approval_code' => $approvalCode,
        ]);
        $payment->setIsTransactionClosed(false);
        if (null === $lastTransId) {
            $payment->getOrder()->save();
            $payment->setParentId($payment->getOrder()->getId());
            $payment->addTransaction(Transaction::TYPE_AUTH);
        }
        $payment->save();
    }
}
