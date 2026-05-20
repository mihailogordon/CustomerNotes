<?php

namespace CorvusPay\PaymentGateway\Model;

use CorvusPay\PaymentGateway\Model\Ui\ConfigProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class CorvusPayOrderSender overrides OrderSender to suppress early order confirmation emails.
 */
class CorvusPayOrderSender
{
    /** @var Json */
    private $serializer;

    /**
     * CorvusPayOrderSender constructor.
     *
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Overrides default send behavior to suppress early order confirmation emails.
     *
     * Sends an order confirmation email after successful payment.
     *
     * @param OrderSender $subject
     * @param callable $proceed
     * @param Order $order
     * @param bool $forceSyncMode
     * @return mixed
     */
    public function aroundSend(OrderSender $subject, callable $proceed, Order $order, $forceSyncMode = false)
    {
        if (ConfigProvider::CODE === $order->getPayment()->getMethod()) {
            $payments = $order->getPaymentsCollection()->getItems();
            $paymentData = $this->serializer->unserialize(end($payments)->getAdditionalData());
            if ($paymentData['order_confirmation_email']) { // Override default behavior
                if ($order->getPayment()->getBaseAmountPaid() > 0 ||
                    $order->getPayment()->getBaseAmountAuthorized() > 0) {
                    return $proceed($order, $forceSyncMode);
                } else {
                    return false;
                }
            }
        }
        return $proceed($order, $forceSyncMode);
    }
}
