<?php

namespace CorvusPay\PaymentGateway\Gateway\Response;

use InvalidArgumentException;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Class InitializeHandler handles gateway responses.
 */
class InitializeHandler implements HandlerInterface
{
    /** @var Json */
    private $serializer;

    /**
     * InitializeHandler constructor.
     *
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Handles payment request data
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @return void
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
        $payment->setAdditionalData($this->serializer->serialize(($response)));
        $payment->prependMessage(__('Redirected customer to CorvusPay payment gateway.')); // FIXME

        /** @var Order $order */
        $order = $payment->getOrder();
        $order->setCustomerNoteNotify(false);

        /** @var DataObject $stateObject */
        $stateObject = $handlingSubject['stateObject'];
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);
    }
}
