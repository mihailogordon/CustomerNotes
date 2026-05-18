<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;
use Magento\Framework\UrlInterface;
use Yemora\IntesaPayment\Model\Ui\ConfigProvider;

class Intesa extends AbstractMethod
{
    protected $_code = ConfigProvider::CODE;

    protected $_isGateway = true;

    protected $_isOffline = false;

    protected $_isInitializeNeeded = true;

    protected $_canUseCheckout = true;

    protected $_canUseInternal = false;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        private readonly UrlInterface $urlBuilder,
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
