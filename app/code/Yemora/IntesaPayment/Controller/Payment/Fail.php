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
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Yemora\IntesaPayment\Model\Response\HashValidator;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Fail implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private readonly RedirectFactory $redirectFactory,
        private readonly RequestInterface $request,
        private readonly OrderFactory $orderFactory,
        private readonly CheckoutSession $checkoutSession,
        private readonly ManagerInterface $messageManager,
        private readonly HashValidator $hashValidator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): Redirect
    {
        $params = $this->request->getParams();
        $order = $this->loadOrder($params);

        if ($order->getId()) {
            if (!$this->canHandleOrder($order)) {
                $this->logger->warning(
                    'Intesa fail callback rejected: order payment method does not match.',
                    ['order_id' => $order->getIncrementId()]
                );
                $this->messageManager->addErrorMessage(__('Unable to process the Intesa payment response.'));

                return $this->redirectFactory->create()->setPath('checkout/cart');
            }

            if (!$this->validateResponseHash($params, $order)) {
                return $this->redirectFactory->create()->setPath('checkout/cart');
            }

            $order->addCommentToStatusHistory(
                $this->getOrderFailComment($params)
            );

            if ($order->canCancel()) {
                $order->cancel();
            }

            $order->save();
            $this->checkoutSession->restoreQuote();
        }

        $this->messageManager->addErrorMessage($this->getCustomerErrorMessage($params));

        return $this->redirectFactory->create()->setPath('checkout/cart');
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
    private function getCustomerErrorMessage(array $params): string
    {
        $error = trim((string) ($params['ErrMsg'] ?? ''));
        $response = trim((string) ($params['Response'] ?? ''));
        $code = trim((string) ($params['ProcReturnCode'] ?? ''));

        if ($error !== '') {
            return (string) __('The card payment was declined by Intesa: %1', $error);
        }

        if ($response !== '' || $code !== '') {
            return (string) __('The card payment was declined by Intesa. Response: %1, code: %2', $response, $code);
        }

        return (string) __('The card payment was not completed. Please try again or choose another payment method.');
    }

    /**
     * @param array<string, mixed> $params
     */
    private function getOrderFailComment(array $params): string
    {
        $response = trim((string) ($params['Response'] ?? 'Declined'));
        $code = trim((string) ($params['ProcReturnCode'] ?? ''));
        $error = trim((string) ($params['ErrMsg'] ?? ''));
        $transactionId = trim((string) (
            $params['TransId']
            ?? $params['transid']
            ?? $params['transaction_id']
            ?? $params['HostRefNum']
            ?? $params['AuthCode']
            ?? ''
        ));

        return (string) __(
            'Registered Intesa notification about declined payment. Transaction ID: "%1". Response: %2%3%4',
            $transactionId !== '' ? $transactionId : __('N/A'),
            $response,
            $code !== '' ? ' (' . $code . ')' : '',
            $error !== '' ? '. Error: ' . $error : ''
        );
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
                'Intesa fail callback rejected: invalid response hash.',
                ['message' => $exception->getMessage(), 'params' => $this->sanitizeParams($params)]
            );
            $this->messageManager->addErrorMessage(__('Unable to verify the Intesa payment response.'));

            return false;
        }

        return true;
    }
}
