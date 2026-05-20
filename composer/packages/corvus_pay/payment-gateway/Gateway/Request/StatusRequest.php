<?php

namespace CorvusPay\PaymentGateway\Gateway\Request;

use InvalidArgumentException;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StatusRequest builds a status request.
 */
class StatusRequest extends CorvusPayRequest implements BuilderInterface
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var string API endpoint */
    private $apiEndpoint;

    /** @var string general status API endpoint */
    const STATUS_GENERAL = 'status';

    /** @var string PIS status API endpoint */
    const STATUS_PIS = 'check_pis_status';

    /**
     * StatusRequest constructor.
     *
     * @param ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param Resolver $localeResolver
     * @param CountryFactory $countryFactory
     * @param EncryptorInterface $encryptor
     * @param OrderRepositoryInterface $orderRepository
     * @param string $apiEndpoint API endpoint
     */
    public function __construct(
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        Resolver $localeResolver,
        CountryFactory $countryFactory,
        EncryptorInterface $encryptor,
        OrderRepositoryInterface $orderRepository,
        $apiEndpoint
    ) {
        parent::__construct($config, $storeManager, $localeResolver, $countryFactory, $encryptor);
        $this->orderRepository = $orderRepository;
        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * Builds a status request.
     *
     * @param array $buildSubject
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['transactionId'])) {
            throw new InvalidArgumentException('Transaction ID should be provided');
        }

        $this->setBuildSubjectPayment($buildSubject);

        switch ($this->apiEndpoint) {
            case self::STATUS_GENERAL:
                $this->buildGeneralStatusRequest();
                break;
            case self::STATUS_PIS:
                $this->buildPisStatusRequest();
                break;
            default:
                throw new InvalidArgumentException('Unexpected API endpoint');
                break;
        }

        return [
            'parameters'   => $this->getParameters(),
            'api_endpoint' => $this->getApiEndpoint() . $this->apiEndpoint,
        ];
    }
}
