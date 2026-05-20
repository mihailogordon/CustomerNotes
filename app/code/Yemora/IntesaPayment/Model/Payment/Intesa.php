<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\UrlInterface;
use Yemora\IntesaPayment\Model\Api\NestPayClient;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Intesa extends AbstractMethod
{
    protected $_code = ConfigProvider::CODE;

    protected $_isGateway = true;

    protected $_isOffline = false;

    protected $_isInitializeNeeded = true;

    protected $_canUseCheckout = true;

    protected $_canUseInternal = false;

    protected $_canCapture = true;

    protected $_canCapturePartial = false;

    protected $_canVoid = true;

    protected $_canRefund = true;

    protected $_canRefundInvoicePartial = true;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        private readonly UrlInterface $urlBuilder,
        private readonly NestPayClient $nestPayClient,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function getOrderPlaceRedirectUrl(): string
    {
        return $this->urlBuilder->getUrl('intesa/payment/start', ['_secure' => true]);
    }

    /**
     * @return $this
     */
    public function capture(InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);

        $orderPayment = $this->getOrderPayment($payment);
        $order = $orderPayment->getOrder();
        $response = $this->nestPayClient->capture($order, $this->convertBaseAmountToOrderAmount($order, (float) $amount));
        $this->applyApiResponseToPayment($orderPayment, $response);

        return $this;
    }

    /**
     * @return $this
     */
    public function void(InfoInterface $payment)
    {
        parent::void($payment);

        $orderPayment = $this->getOrderPayment($payment);
        $response = $this->nestPayClient->void(
            $orderPayment->getOrder(),
            $this->getGatewayTransactionId((string) $orderPayment->getParentTransactionId())
        );
        $this->applyApiResponseToPayment($orderPayment, $response, false);

        return $this;
    }

    /**
     * @return $this
     */
    public function refund(InfoInterface $payment, $amount)
    {
        parent::refund($payment, $amount);

        $orderPayment = $this->getOrderPayment($payment);
        $order = $orderPayment->getOrder();
        $response = $this->nestPayClient->refund(
            $order,
            $this->convertBaseAmountToOrderAmount($order, (float) $amount),
            $this->getGatewayTransactionId((string) $orderPayment->getRefundTransactionId())
        );
        $this->applyApiResponseToPayment($orderPayment, $response);

        return $this;
    }

    private function getOrderPayment(InfoInterface $payment): OrderPayment
    {
        if (!$payment instanceof OrderPayment) {
            throw new LocalizedException(__('Intesa payment action requires a sales order payment.'));
        }

        return $payment;
    }

    private function convertBaseAmountToOrderAmount(Order $order, float $baseAmount): float
    {
        $baseGrandTotal = (float) $order->getBaseGrandTotal();

        if ($baseGrandTotal <= 0.0) {
            return $baseAmount;
        }

        return $baseAmount * ((float) $order->getGrandTotal() / $baseGrandTotal);
    }

    private function getGatewayTransactionId(string $transactionId): ?string
    {
        $transactionId = trim($transactionId);

        if ($transactionId === '' || str_starts_with($transactionId, 'intesa-')) {
            return null;
        }

        return $transactionId;
    }

    /**
     * @param array<string, string> $response
     */
    private function applyApiResponseToPayment(
        OrderPayment $payment,
        array $response,
        bool $updateTransactionId = true
    ): void {
        $transactionId = $response['transid']
            ?? $response['trans_id']
            ?? $response['hostrefnum']
            ?? $payment->getTransactionId();

        if ($updateTransactionId && $transactionId) {
            $payment->setTransactionId((string) $transactionId);
        }

        $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $response);
        $payment->setIsTransactionClosed(false);
    }

    /**
     * Keep the order waiting for the external Intesa confirmation.
     *
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setData('state', Order::STATE_PENDING_PAYMENT);
        $stateObject->setData('status', Order::STATE_PENDING_PAYMENT);
        $stateObject->setData('is_notified', false);

        return $this;
    }
}
