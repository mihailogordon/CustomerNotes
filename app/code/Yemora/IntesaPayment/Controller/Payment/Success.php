<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Controller\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Yemora\IntesaPayment\Model\Config;

class Success implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private readonly RedirectFactory $redirectFactory,
        private readonly RequestInterface $request,
        private readonly OrderFactory $orderFactory,
        private readonly InvoiceService $invoiceService,
        private readonly Transaction $transaction,
        private readonly Config $config,
        private readonly CheckoutSession $checkoutSession,
        private readonly ManagerInterface $messageManager,
        private readonly LoggerInterface $logger,
        private readonly OrderSender $orderSender
    ) {
    }

    public function execute(): Redirect
    {
        $params = $this->request->getParams();
        $this->logger->info('Intesa success callback received.', ['params' => $this->sanitizeParams($params)]);
        $order = $this->loadOrder($params);

        if (!$order->getId()) {
            $this->messageManager->addErrorMessage(__('Unable to find the order returned by Intesa.'));

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if ($order->getState() === Order::STATE_PENDING_PAYMENT) {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus(Order::STATE_PROCESSING);
        }

        $transactionId = (string) ($params['TransId'] ?? $params['transid'] ?? $params['transaction_id'] ?? '');

        if ($transactionId !== '') {
            $order->getPayment()->setLastTransId($transactionId);
        }

        if ($this->config->getPaymentAction((int) $order->getStoreId()) === MethodInterface::ACTION_AUTHORIZE_CAPTURE) {
            $this->registerInvoice($order, $transactionId);
        } else {
            $order->getPayment()->setAmountAuthorized($order->getGrandTotal());
            $order->getPayment()->setBaseAmountAuthorized($order->getBaseGrandTotal());
        }

        $order->addCommentToStatusHistory(
            $this->getOrderSuccessComment($order, $params, $transactionId)
        );
        $order->save();
        $this->sendOrderEmail($order);
        $this->prepareCheckoutSuccessSession($order);

        return $this->redirectFactory->create()->setPath('checkout/onepage/success');
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function loadOrder(array $params): Order
    {
        $incrementId = (string) ($params['oid'] ?? $params['OrderId'] ?? $params['order_id'] ?? '');

        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function sanitizeParams(array $params): array
    {
        unset($params['HASH'], $params['hash'], $params['HASHPARAMSVAL']);

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function getOrderSuccessComment(Order $order, array $params, string $transactionId): string
    {
        $paymentAction = $this->config->getPaymentAction((int) $order->getStoreId());
        $actionLabel = $paymentAction === MethodInterface::ACTION_AUTHORIZE_CAPTURE ? __('captured') : __('authorized');
        $amount = (string) ($params['amount'] ?? $order->getGrandTotal());
        $currency = (string) ($params['currencyAlphaCode'] ?? $order->getOrderCurrencyCode());
        $response = trim((string) ($params['Response'] ?? 'Approved'));
        $code = trim((string) ($params['ProcReturnCode'] ?? ''));

        if ($transactionId === '') {
            $transactionId = trim((string) ($params['HostRefNum'] ?? $params['AuthCode'] ?? ''));
        }

        return (string) __(
            'Registered Intesa notification about %1 amount of %2 %3. Transaction ID: "%4". Response: %5%6',
            $actionLabel,
            $amount,
            $currency,
            $transactionId !== '' ? $transactionId : __('N/A'),
            $response,
            $code !== '' ? ' (' . $code . ')' : ''
        );
    }

    private function registerInvoice(Order $order, string $transactionId): void
    {
        if (!$order->canInvoice()) {
            return;
        }

        $invoice = $this->invoiceService->prepareInvoice($order);

        if (!$invoice->getTotalQty()) {
            return;
        }

        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->setTransactionId($transactionId);
        $invoice->register();
        $invoice->pay();

        $order->addRelatedObject($invoice);
        $this->transaction->addObject($invoice)->addObject($order)->save();
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

    private function sendOrderEmail(Order $order): void
    {
        if ($order->getEmailSent()) {
            return;
        }

        try {
            $this->orderSender->send($order);
        } catch (\Throwable $exception) {
            $this->logger->critical($exception);
        }
    }
}
