<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Plugin;

use Magento\Sales\Model\Order;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class OrderCanInvoiceAfterVoid
{
    public function afterCanInvoice(Order $order, bool $result): bool
    {
        if (!$result) {
            return false;
        }

        $payment = $order->getPayment();

        if (!$payment || $payment->getMethod() !== ConfigProvider::CODE) {
            return true;
        }

        $authorizationTransaction = $payment->getAuthorizationTransaction();

        if (!$authorizationTransaction || !(int) $authorizationTransaction->getIsClosed()) {
            return true;
        }

        return (float) $payment->getBaseAmountPaidOnline() > 0.0
            || (float) $payment->getBaseAmountPaid() > 0.0;
    }
}
