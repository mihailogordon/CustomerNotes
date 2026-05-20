<?php

namespace CorvusPay\PaymentGateway\Gateway\Request;

use CorvusPay\PaymentGateway\Model\Ui\ConfigProvider;
use InvalidArgumentException;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RefundRequest builds a refund request.
 */
class RefundRequest extends CorvusPayRequest implements BuilderInterface
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * RefundRequest constructor.
     *
     * @param ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param Resolver $localeResolver
     * @param CountryFactory $countryFactory
     * @param EncryptorInterface $encryptor
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        Resolver $localeResolver,
        CountryFactory $countryFactory,
        EncryptorInterface $encryptor,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($config, $storeManager, $localeResolver, $countryFactory, $encryptor);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Builds a refund request.
     *
     * @param array $buildSubject
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['amount'])) {
            throw new InvalidArgumentException('Amount should be provided');
        }

        $this->setBuildSubjectPayment($buildSubject);

        $order = $this->orderRepository->get($this->order->getId());
        if (!$order instanceof Order) {
            throw new InvalidArgumentException('Order expected');
        }
        /** @var $order Order */

        $captured = 0;
        $toRefund = $buildSubject['amount'];

        /** @var OrderPaymentInterface $payment */
        foreach ($order->getPaymentsCollection()->getItems() as $payment) {
            if (ConfigProvider::CODE === $payment->getMethod()) {
                $captured += $payment->getBaseAmountPaidOnline();
            }
        }

	    $total = $captured - $order->getTotalRefunded();

        if ($total == $toRefund) {
            $this->buildRequest();

            return [
                'parameters'   => $this->getParameters(),
                'api_endpoint' => $this->getApiEndpoint() . 'refund',
            ];
        } else {
            $this->buildPartialRequest($total - $toRefund);

            return [
                'parameters'   => $this->getParameters(),
                'api_endpoint' => $this->getApiEndpoint() . 'partial_refund',
            ];
        }
    }
}
