<?php

namespace CorvusPay\PaymentGateway\Gateway\Request;

use CorvusPay\ApiRequestor;
use CorvusPay\BaseCorvusPayClient;
use CorvusPay\PaymentGateway\Gateway\Options;
use CorvusPay\Service\CheckoutService;
use Exception;
use InvalidArgumentException;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order\Item\Interceptor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CorvusPayRequest builds a CorvusPay request.
 */
class CorvusPayRequest
{
    /** @var string CorvusPay API version. */
    const API_VERSION = BaseCorvusPayClient::API_VERSION;

    const PRODUCTION = BaseCorvusPayClient::PRODUCTION;

    const SANDBOX = BaseCorvusPayClient::SANDBOX;

    /**
     * Delimiter for CorvusPay order_number. CorvusPay requires all test orders to have a unique order_number. Test
     * orders have a prefix to make them unique. Delimiter is used to join and split prefix and Order ID.
     */
    const ORDER_NUMBER_DELIMITER = ' - ';

    /**
     * Maximum length for cart description. CorvusPay limits cart description length to 255 characters.
     */
    const ORDER_NUMBER_MAX_LENGTH = 36;

    /**
     * Maximum length for cart description. CorvusPay limits cart description length to 255 characters.
     */
    const CART_MAX_LENGTH = 250;

    /**
     * List of languages supported by CorvusPay. ISO 639-1 codes (almost).
     */
    const SUPPORTED_LANGUAGES = CheckoutService::SUPPORTED_LANGUAGES;

    /**
     * Currency codes conversion. ISO 4217 codes.
     */
    const CURRENCY_CODES = CheckoutService::CURRENCY_CODES;

    /**
     * CorvusPay Checkout URLs for test and production.
     */
    const CHECKOUT_URL = CheckoutService::CHECKOUT_URL;

    /**
     * CorvusPay API endpoints for test and production.
     */
    const API_ENDPOINTS = ApiRequestor::API_ENDPOINTS;

    /** @var InfoInterface */
    protected $payment;
    /** @var OrderAdapterInterface */
    protected $order;
    /** @var string */
    protected $orderId;
    /** @var AddressAdapterInterface|OrderAddressInterface */
    protected $address;
    /** @var Options */
    private $options;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var Resolver */
    private $localeResolver;
    /** @var CountryFactory */
    private $countryFactory;
    /** @var array */
    private $parameters;
    /** @var bool Type of transaction. Is preauth. */
    private $preauth;

    /**
     * CorvusPayRequest constructor.
     *
     * @param ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param Resolver $localeResolver
     * @param CountryFactory $countryFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        Resolver $localeResolver,
        CountryFactory $countryFactory,
        EncryptorInterface $encryptor
    ) {
        $this->options        = new Options($config, $encryptor);
        $this->storeManager   = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Builds a payment request.
     *
     * @throws Exception|NoSuchEntityException
     */
    public function buildPaymentRequest()
    {
        /* Mandatory fields */
        $this->setParameterVersion();
        $this->setParameterStoreId();
        $this->setParameterOrderNumber();
        $this->setParameterLanguage();
        $this->setParameterCurrency();
        $this->setParameterAmount();
        $this->setParameterCart();
        $this->setParameterRequireComplete();

        /* Optional fields */
        if ($this->options->cardholder_fields === 'mandatory' ||
            $this->options->cardholder_fields === 'all') {
            $this->setParameterCardholderName();
            $this->setParameterCardholderSurname();
            $this->setParameterCardholderEmail();
        }
        if ($this->options->cardholder_fields === 'all') {
            $this->setParameterCardholderAddress();
            $this->setParameterCardholderCity();
            $this->setParameterCardholderZipCode();
            $this->setParameterCardholderCountry();
            $this->setParameterCardholderPhone();
        }

        if (null != $this->options->time_limit) {
            $this->setParameterBestBefore();
        }

        if ( ! is_null( $this->options->hide_tabs ) ) {
            $this->set_parameter_hide_tabs();
        }

        /* Subscriptions */
        if ($this->payment->getAdditionalInformation('is_active_payment_token_enabler')) {
            $this->setParameterSubscription();
        }

        if ('all' === $this->options->installments) {
            $this->setParameterPaymentAll();
        } elseif ('map' === $this->options->installments) {
            $this->setParameterInstallmentsMap();
        }

        if (null != $this->options->creditor_reference) {
            $this->setParameterCreditorReference();
        }
    }

    /**
     * Sets 'version' parameter. Fixed value.
     *
     * @param string $version
     */
    private function setParameterVersion($version = self::API_VERSION)
    {
        $this->parameters['version'] = $version;
    }

    /**
     * Sets 'store_id' parameter. Depends on environment.
     */
    private function setParameterStoreId()
    {
        $this->parameters['store_id'] = $this->options->store_id[$this->options->environment];
    }

    /**
     * Sets 'order_number' parameter. Adds a prefix for test environment.
     *
     * @throws NoSuchEntityException
     */
    private function setParameterOrderNumber()
    {
        $this->parameters['order_number'] = self::PRODUCTION === $this->options->environment
            ? $this->orderId
            : mb_strimwidth( // prefix
                $this->storeManager->getStore($this->order->getStoreId())->getName(),
                0,
                self::ORDER_NUMBER_MAX_LENGTH - mb_strlen(self::ORDER_NUMBER_DELIMITER . $this->orderId),
                '...'
            ) . self::ORDER_NUMBER_DELIMITER . $this->orderId;
    }

    /**
     * Sets 'language' parameter. Tries to guess language if set to 'Auto'. Does a sanity check. Falls back to 'en'.
     */
    private function setParameterLanguage()
    {
        $language = $this->options->language;
        if ('auto' === $language) {
            $language = explode('_', $this->localeResolver->getLocale(), 2)[0];

            // Convert ISO 639-1 language code to CorvusPay language code.
            switch ($language) {
                case 'cs': /* Czech */
                    $language = 'cz';
                    break;
                case 'sr': /* Serbian */
                    $language = 'se';
                    break;
            }
        }
        $this->parameters['language'] = array_key_exists($language, self::SUPPORTED_LANGUAGES)
            ? $language : 'en';
    }

    /**
     * Sets 'currency' parameter.
     *
     * @throws PaymentException Specified currency code not supported.
     */
    private function setParameterCurrency()
    {
        if (!array_key_exists($this->order->getCurrencyCode(), self::CURRENCY_CODES)) {
            throw new PaymentException(__('Specified currency code not supported.'));
        }
        $this->parameters['currency'] = $this->order->getCurrencyCode();
    }

    /**
     * Sets 'amount' parameter.
     *
     * @throws Exception
     */
    private function setParameterAmount()
    {
        $this->parameters['amount'] = self::prepareAmount($this->getTotalAmount());
    }

    /**
     * Formats amount for CorvusPay. Rounds to required number of decimal places (2) and adds a decimal point.
     *
     * @param double $amount Amount to format.
     *
     * @return string Formatted amount.
     * @throws PaymentException Specified amount is not supported.
     */
    private function prepareAmount($amount)
    {
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION;
        if ($amount <= 0 || round($amount, $precision) != $amount) {
            throw new PaymentException(__('Specified amount is not supported.'));
        }

        return number_format($amount, $precision, '.', '');
    }

    /**
     * Total order amount getter.
     *
     * @return double Total order amount.
     */
    private function getTotalAmount()
    {
        return (double)$this->order->getGrandTotalAmount();
    }

    /**
     * Sets 'cart' parameter. Doesn't do a sanity check.
     */
    private function setParameterCart()
    {
        $items = [];
        /** @var Interceptor $item */
        foreach ($this->order->getItems() as $item) {
            if (empty($item->getChildrenItems())) {
                $items[] = $item->getName() . ' • ' . (double)$item->getQtyOrdered();
            }
        }
        $cart                     = implode(', ', $items);
        $this->parameters['cart'] = mb_strlen($cart) > self::CART_MAX_LENGTH ?
            mb_strimwidth($cart, 0, self::CART_MAX_LENGTH, '...') : $cart;
    }

    /**
     * Sets 'require_complete' parameter.
     */
    private function setParameterRequireComplete()
    {
        $this->parameters['require_complete'] = $this->preauth ? 'true' : 'false';
    }

    /**
     * Sets 'cardholder_name' parameter.
     */
    private function setParameterCardholderName()
    {
        $this->parameters['cardholder_name'] = $this->address->getFirstname();
    }

    /**
     * Sets 'cardholder_surname' parameter.
     */
    private function setParameterCardholderSurname()
    {
        $this->parameters['cardholder_surname'] = $this->address->getLastname();
    }

    /**
     * Sets 'cardholder_email' parameter.
     */
    private function setParameterCardholderEmail()
    {
        $this->parameters['cardholder_email'] = $this->address->getEmail();
    }

    /**
     * Sets 'cardholder_address' parameter.
     */
    private function setParameterCardholderAddress()
    {
        if ($this->address->getStreetLine1())
            $this->parameters['cardholder_address'] = $this->address->getStreetLine1();
        else
            $this->parameters['cardholder_address'] = $this->address->getStreetLine(1);
    }

    /**
     * Sets 'cardholder_city' parameter.
     */
    private function setParameterCardholderCity()
    {
        $this->parameters['cardholder_city'] = $this->address->getCity();
    }

    /**
     * Sets 'cardholder_zip_code' parameter.
     */
    private function setParameterCardholderZipCode()
    {
        $this->parameters['cardholder_zip_code'] = $this->address->getPostcode();
    }

    /**
     * Sets 'cardholder_country' parameter.
     */
    private function setParameterCardholderCountry()
    {
        $this->parameters['cardholder_country'] = $this->countryFactory->create()->loadByCode(
            $this->address->getCountryId()
        )->getName();
    }

    /**
     * Sets 'cardholder_phone' parameter.
     */
    private function setParameterCardholderPhone()
    {
        $this->parameters['cardholder_phone'] = $this->address->getTelephone();
    }

    /**
     * Sets 'best_before' parameter.
     *
     * @throws PaymentException Unsupported time limit value.
     */
    private function setParameterBestBefore()
    {
        if ($this->options->time_limit <= 0 || $this->options->time_limit > 900) {
            throw new PaymentException(__('Unsupported time limit value.'));
        }
        $this->parameters['best_before'] = time() + $this->options->time_limit;
    }

    /**
     * Sets 'payment_all' parameter.
     */
    private function setParameterPaymentAll()
    {
        $this->parameters['payment_all'] = 'Y0299';
    }

    /**
     * Sets 'installments_map' parameter. Doesn't do a sanity check.
     *
     * @throws Exception
     */
    private function setParameterInstallmentsMap()
    {
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION;
        $amount           = $this->getTotalAmount();
        $installments_map = [];

        foreach ($this->options->installments_map as $installment) {
            $start = (int)$installment['min_installments'];
            $end = (int)$installment['max_installments'];
            for ($installments = $start; $installments <= $end; $installments++) {
                if ('' !== $installment['general_percentage']) {
                    $discounted_amount=round($amount * (100 - (double)$installment['general_percentage']) / 100, $precision);
                    $installments_map[$installment['card_brand']][$installments]['amount'] =
                        self::prepareAmount($discounted_amount);
                }

                if ('' !== $installment['specific_percentage']) {
                    $discounted_amount=round($amount * (100 - (double)$installment['specific_percentage']) / 100, $precision);
                    $installments_map[$installment['card_brand']][$installments]['discounted_amount'] =
                        self::prepareAmount($discounted_amount);
                }
            }
        }

        $this->parameters['installments_map'] = json_encode($installments_map);
    }

    /**
     * Builds a capture/refund request.
     *
     * @throws NoSuchEntityException
     */
    public function buildRequest()
    {
        $this->setParameterOrderNumber();
        $this->setParameterStoreId();
        $this->setParameterHash();
    }

    /**
     * Sets 'hash' parameter. Hashes using Secret Key. Depends on environment.
     */
    private function setParameterHash()
    {
        $this->parameters['hash'] = sha1(
            $this->options->secret_key[$this->options->environment] .
            implode($this->parameters)
        );
    }

    /**
     * Builds a partial capture/refund request.
     *
     * @param string $amount Amount to partially capture/refund.
     *
     * @throws Exception|NoSuchEntityException
     */
    public function buildPartialRequest($amount)
    {
        $this->setParameterOrderNumber();
        $this->setParameterStoreId();
        $this->setParameterHash();
        $this->setParameterNewAmount($amount);
		$this->setParameterCurrency();
    }

    /**
     * Sets 'new_amount' parameter.
     *
     * @param double $amount Amount.
     *
     * @throws Exception
     */
    public function setParameterNewAmount($amount)
    {
        $this->parameters['new_amount'] = self::prepareAmount($amount);
    }

    /**
     * Builds a general status request.
     *
     * @throws Exception|NoSuchEntityException
     */
    public function buildGeneralStatusRequest()
    {
        $this->setParameterOrderNumber();
        $this->setParameterStoreId();
        $this->setParameterCurrencyCode();
        $this->setParameterTimestamp();
        $this->setParameterVersion();
        $this->setParameterHash();
    }

    /**
     * Builds a PIS status request.
     *
     * @throws Exception|NoSuchEntityException
     */
    public function buildPisStatusRequest()
    {
        $this->setParameterOrderNumber();
        $this->setParameterStoreId();
        $this->setParameterTimestamp();
        $this->setParameterCurrencyCode();
        $this->setParameterVersion();
    }

    /**
     * Builds a next subscription payment request.
     *
     * @param double $amount Amount.
     *
     * @throws Exception|NoSuchEntityException
     */
    public function setBuildSubjectNextSubscriptionPaymentRequest($amount)
    {
        $accountId = $this->payment->getExtensionAttributes()->getVaultPaymentToken()->getGatewayToken();

        $this->setParameterOrderNumber();
        $this->setParameterStoreId();
        $this->setParameterHash();
        $this->setParameterVersion();
        $this->setParameterSubscription();
        $this->setParameterAccountId($accountId);
        $this->setParameterNewAmount($amount);
		$this->setParameterCurrency();
    }

    /**
     * Sets attributes from buildSubject. Used for API actions.
     *
     * @param array $buildSubject
     */
    public function setBuildSubjectPayment($buildSubject)
    {
        if (!isset($buildSubject['payment'])
             || !$buildSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        $this->setBuildSubject($buildSubject);
    }

    /**
     * Sets attributes from buildSubject.
     *
     * @param array $buildSubject
     */
    private function setBuildSubject($buildSubject)
    {
        /** @var $paymentDO PaymentDataObjectInterface */
        $paymentDO = $buildSubject['payment'];
        $this->payment = $paymentDO->getPayment();
        $this->order   = $paymentDO->getOrder();
        $this->orderId = $this->order->getOrderIncrementId();
        $this->address = $this->order->getBillingAddress();
    }

    /**
     * Sets attributes from buildSubject. Used for Payment actions.
     *
     * @param array $buildSubject
     */
    public function setBuildSubjectPaymentAction($buildSubject)
    {
        if (!isset($buildSubject['payment'])
             || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
             || !isset($buildSubject['paymentAction'])) {
            throw new InvalidArgumentException('Payment data object and payment action should be provided');
        }

        $this->setBuildSubject($buildSubject);
        $this->preauth = AbstractMethod::ACTION_AUTHORIZE === $buildSubject['paymentAction'];
    }

    /**
     * Checkout URL getter.
     *
     * @return string Checkout URL
     */
    public function getCheckoutUrl()
    {
        return self::CHECKOUT_URL[$this->options->environment];
    }

    /**
     * API endpoint getter.
     *
     * @return string API endpoint.
     */
    public function getApiEndpoint()
    {
        return self::API_ENDPOINTS[$this->options->environment];
    }

    /**
     * Encrypted secret key getter.
     *
     * @return string Encrypted secret key.
     */
    public function getEncryptedSecretKey()
    {
        return $this->options->encrypted_secret_key[$this->options->environment];
    }

    /**
     * Auto redirect getter.
     *
     * @return bool Should auto redirect?
     */
    public function getAutoRedirect()
    {
        return $this->options->auto_redirect;
    }

    /**
     * Environment getter.
     *
     * @return string environment.
     */
    public function getEnvironment()
    {
        return $this->options->environment;
    }

    /**
     * Order confirmation email getter.
     *
     * @return bool Should send order confirmation email?
     */
    public function getOrderConfirmationEmail()
    {
        return $this->options->order_confirmation_email;
    }

    /**
     * Request parameters getter.
     *
     * @return array Request parameters.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets 'currency_code' parameter.
     *
     * @throws PaymentException Specified currency code not supported.
     */
    private function setParameterCurrencyCode()
    {
        if (!array_key_exists($this->order->getCurrencyCode(), self::CURRENCY_CODES) ||
            null === self::CURRENCY_CODES[$this->order->getCurrencyCode()]
        ) {
            throw new PaymentException(__('Specified currency code not supported.'));
        }
        $this->parameters['currency_code'] = $this->order->getCurrencyCode();
    }

    /**
     * Sets 'timestamp' parameter.
     */
    private function setParameterTimestamp()
    {
        $this->parameters['timestamp'] = date('YmdHis');
    }

    /**
     * Sets 'subscription' parameter to 'true'.
     */
    private function setParameterSubscription()
    {
        $this->parameters['subscription'] = 'true';
    }

    /**
     * Sets 'account_id' parameter.
     *
     * @param int $accountId Account ID.
     */
    private function setParameterAccountId($accountId)
    {
        $this->parameters['account_id'] = $accountId;
    }

    /**
     * Sets the 'creditor_reference' parameter.
     */
    private function setParameterCreditorReference()
    {
        $this->parameters['creditor_reference'] = strtr(
            $this->options->creditor_reference,
            [
                '${orderId}' => $this->orderId
            ]
        );
    }

    /**
     * Sets 'hide_tabs' parameter.
     */
    private function set_parameter_hide_tabs() {
        $this->parameters['hide_tabs'] = $this->options->hide_tabs;
    }
}
