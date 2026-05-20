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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Yemora\IntesaPayment\Model\Config;
use Yemora\IntesaPayment\Model\Response\HashValidator;
use Yemora\IntesaPayment\Model\Response\OrderResponseValidator;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Success implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private readonly RedirectFactory $redirectFactory,
        private readonly RequestInterface $request,
        private readonly OrderFactory $orderFactory,
        private readonly Config $config,
        private readonly CheckoutSession $checkoutSession,
        private readonly ManagerInterface $messageManager,
        private readonly LoggerInterface $logger,
        private readonly OrderSender $orderSender,
        private readonly HashValidator $hashValidator,
        private readonly OrderResponseValidator $orderResponseValidator
    ) {
    }

    public function execute(): Redirect
    {
        $params = $this->request->getParams();
        $order = $this->loadOrder($params);

        if (!$order->getId()) {
            $this->messageManager->addErrorMessage(__('Unable to find the order returned by Intesa.'));

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->canHandleOrder($order)) {
            $this->logger->warning(
                'Intesa success callback rejected: order payment method does not match.',
                ['order_id' => $order->getIncrementId()]
            );
            $this->messageManager->addErrorMessage(__('Unable to process the Intesa payment response.'));

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->validateResponseHash($params, $order)) {
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->validateResponseMatchesOrder($params, $order)) {
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->isApprovedResponse($params)) {
            $this->logger->warning(
                'Intesa success callback rejected: response is not approved.',
                ['order_id' => $order->getIncrementId(), 'params' => $this->sanitizeParams($params)]
            );
            $this->messageManager->addErrorMessage(__('The card payment was not approved by Intesa.'));
            $this->checkoutSession->restoreQuote();

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        $transactionId = $this->extractTransactionId($params) ?: 'intesa-' . $order->getIncrementId();

        if (!$this->canProcessApprovedOrder($order)) {
            if ($this->isDuplicateApprovedCallback($order, $transactionId)) {
                $this->prepareCheckoutSuccessSession($order);

                return $this->redirectFactory->create()->setPath('checkout/onepage/success');
            }

            $this->logger->warning(
                'Intesa success callback rejected: order is not pending payment.',
                [
                    'order_id' => $order->getIncrementId(),
                    'state' => $order->getState(),
                    'status' => $order->getStatus(),
                    'params' => $this->sanitizeParams($params),
                ]
            );
            $this->messageManager->addErrorMessage(__('Unable to process the Intesa payment response.'));

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->registerPaymentNotification($order, $params, $transactionId)) {
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

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

    private function canHandleOrder(Order $order): bool
    {
        return $order->getPayment()->getMethod() === ConfigProvider::CODE;
    }

    private function canProcessApprovedOrder(Order $order): bool
    {
        return $order->getState() === Order::STATE_PENDING_PAYMENT;
    }

    private function isDuplicateApprovedCallback(Order $order, string $transactionId): bool
    {
        if ($transactionId === '') {
            return false;
        }

        return $order->getPayment()->getLastTransId() === $transactionId
            && in_array($order->getState(), [Order::STATE_PROCESSING, Order::STATE_COMPLETE], true);
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
    private function extractTransactionId(array $params): string
    {
        return trim((string) (
            $params['TransId']
            ?? $params['transid']
            ?? $params['transaction_id']
            ?? $params['HostRefNum']
            ?? $params['AuthCode']
            ?? ''
        ));
    }

    /**
     * @param array<string, mixed> $params
     */
    private function registerPaymentNotification(Order $order, array $params, string $transactionId): bool
    {
        $payment = $order->getPayment();
        $paymentAction = $this->config->getPaymentAction((int) $order->getStoreId());

        $payment->setTransactionId($transactionId);

        $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $this->getTransactionDetails($params));
        $payment->setIsTransactionClosed(false);

        try {
            if ($paymentAction === MethodInterface::ACTION_AUTHORIZE_CAPTURE) {
                $payment->registerCaptureNotification($order->getBaseGrandTotal(), true);
            } else {
                $payment->registerAuthorizationNotification($order->getBaseGrandTotal());
            }
        } catch (\Throwable $exception) {
            $this->logger->critical(
                'Unable to register Intesa payment notification.',
                ['order_id' => $order->getIncrementId(), 'message' => $exception->getMessage()]
            );
            $this->messageManager->addErrorMessage(__('Unable to process the Intesa payment response.'));

            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, string>
     */
    private function getTransactionDetails(array $params): array
    {
        $details = [];

        foreach (['oid', 'TransId', 'HostRefNum', 'AuthCode', 'Response', 'ProcReturnCode', 'amount', 'currency'] as $key) {
            if (isset($params[$key]) && !is_array($params[$key])) {
                $details[$key] = (string) $params[$key];
            }
        }

        return $details;
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

    /**
     * @param array<string, mixed> $params
     */
    private function isApprovedResponse(array $params): bool
    {
        $response = strtolower(trim((string) ($params['Response'] ?? '')));
        $code = trim((string) ($params['ProcReturnCode'] ?? ''));

        if (!in_array($response, ['approved', 'success'], true)) {
            return false;
        }

        return $code === '' || $code === '00';
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateResponseHash(array $params, Order $order): bool
    {
        try {
            $this->hashValidator->validate($params, (int) $order->getStoreId());
        } catch (LocalizedException $exception) {
            $this->logger->warning(
                'Intesa success callback rejected: invalid response hash.',
                ['message' => $exception->getMessage(), 'params' => $this->sanitizeParams($params)]
            );
            $this->messageManager->addErrorMessage(__('Unable to verify the Intesa payment response.'));

            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateResponseMatchesOrder(array $params, Order $order): bool
    {
        try {
            $this->orderResponseValidator->validate($params, $order, true);
        } catch (LocalizedException $exception) {
            $this->logger->warning(
                'Intesa success callback rejected: response does not match order.',
                ['message' => $exception->getMessage(), 'params' => $this->sanitizeParams($params)]
            );
            $this->messageManager->addErrorMessage(__('Unable to verify the Intesa payment response.'));

            return false;
        }

        return true;
    }
}
