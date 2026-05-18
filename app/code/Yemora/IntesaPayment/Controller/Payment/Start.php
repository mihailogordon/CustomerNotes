<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Controller\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;
use Yemora\IntesaPayment\Model\Request\PaymentRequestBuilder;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Start implements HttpGetActionInterface
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly ForwardFactory $forwardFactory,
        private readonly ManagerInterface $messageManager,
        private readonly PageFactory $pageFactory,
        private readonly PaymentRequestBuilder $paymentRequestBuilder
    ) {
    }

    public function execute(): ResultInterface
    {
        $order = $this->checkoutSession->getLastRealOrder();

        if (!$order || !$order->getEntityId()) {
            return $this->forwardNoRoute();
        }

        if (!$this->canStartPayment($order)) {
            return $this->forwardNoRoute();
        }

        try {
            $request = $this->paymentRequestBuilder->build($order);
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->forwardNoRoute();
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $resultPage->setHeader('Pragma', 'no-cache', true);
        $resultPage->setHeader('Expires', '0', true);
        $block = $resultPage->getLayout()->getBlock('intesa.payment.redirect');

        if ($block) {
            $block->setData('gateway_url', $request['gateway_url']);
            $block->setData('payment_fields', $request['fields']);
        }

        return $resultPage;
    }

    private function forwardNoRoute(): Forward
    {
        $resultForward = $this->forwardFactory->create();

        return $resultForward->forward('noroute');
    }

    private function canStartPayment(Order $order): bool
    {
        return $order->getPayment()->getMethod() === ConfigProvider::CODE
            && $order->getState() === Order::STATE_PENDING_PAYMENT;
    }
}
