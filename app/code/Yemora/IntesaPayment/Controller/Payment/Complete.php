<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Controller\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Yemora\IntesaPayment\Model\ReturnUrlToken;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Complete implements HttpGetActionInterface
{
    private const RESULT_SUCCESS = 'success';
    private const RESULT_FAIL = 'fail';

    public function __construct(
        private readonly RedirectFactory $redirectFactory,
        private readonly RequestInterface $request,
        private readonly OrderFactory $orderFactory,
        private readonly CheckoutSession $checkoutSession,
        private readonly ManagerInterface $messageManager,
        private readonly ReturnUrlToken $returnUrlToken
    ) {
    }

    public function execute(): Redirect
    {
        $result = (string) $this->request->getParam('result', '');
        $message = (string) $this->request->getParam('message', '');
        $order = $this->loadOrder();

        if (!$order->getId() || !$this->canHandleOrder($order)) {
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->returnUrlToken->validate($order, $result, $message, (string) $this->request->getParam('token', ''))) {
            $this->messageManager->addErrorMessage(__('Unable to verify the Intesa payment return.'));

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if ($result === self::RESULT_SUCCESS) {
            $this->prepareCheckoutSuccessSession($order);

            return $this->redirectFactory->create()->setPath('checkout/onepage/success');
        }

        if ($result === self::RESULT_FAIL) {
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addErrorMessage(
                $message !== ''
                    ? $message
                    : __('The card payment was not completed. Please try again or choose another payment method.')
            );
        }

        return $this->redirectFactory->create()->setPath('checkout/cart');
    }

    private function loadOrder(): Order
    {
        return $this->orderFactory->create()->loadByIncrementId((string) $this->request->getParam('oid', ''));
    }

    private function canHandleOrder(Order $order): bool
    {
        return $order->getPayment()->getMethod() === ConfigProvider::CODE;
    }

    private function prepareCheckoutSuccessSession(Order $order): void
    {
        $quoteId = (int) $order->getQuoteId();

        if ($quoteId > 0) {
            $this->checkoutSession->setLastQuoteId($quoteId);
            $this->checkoutSession->setLastSuccessQuoteId($quoteId);
        }

        $this->checkoutSession->setLastOrderId((int) $order->getId());
        $this->checkoutSession->setLastRealOrderId((string) $order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus((string) $order->getStatus());
    }
}
