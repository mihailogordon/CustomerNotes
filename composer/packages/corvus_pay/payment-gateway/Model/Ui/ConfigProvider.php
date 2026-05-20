<?php

namespace CorvusPay\PaymentGateway\Model\Ui;

use CorvusPay\Service\CheckoutService;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;

/**
 * Class ConfigProvider provides config.
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'corvuspay';

    const VAULT_CODE = 'corvuspay_vault';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    protected $request;

	private Repository $assetRepo;

    /**
     * ConfigProvider constructor.
     *
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param ConfigInterface $config
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo,
        RequestInterface $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo   = $assetRepo;
        $this->request     = $request;
    }

    /**
     * Gets configuration.
     *
     * @return array Configuration.
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'vaultCode'   => self::VAULT_CODE,
                    'description' => $this->getDescription( 'payment/corvuspay/description' )
                ]
            ]
        ];
    }

    /**
     * @param $path
     * @param $storeId
     *
     * @return mixed
     */
    public function getDescription( $path, $storeId = null ) {
        $search_array  = [];
        $replace_array = [];
        $subject       = $this->scopeConfig->getValue( $path, ScopeInterface::SCOPE_STORE, $storeId );

        $params = array( '_secure' => $this->request->isSecure() );

        foreach ( CheckoutService::CARD_BRANDS as $key => $value ) {
            $search_array[]  = ":" . $key . ":";
            $replace_array[] = '<img style="width:6%;margin:5px;" src=' . $this->assetRepo->getUrlWithParams( "CorvusPay_PaymentGateway::images/cards/{$key}.svg", $params ) . ' alt="' . $key . '">';
        }

        $search_array[]  = ":iban:";
        $replace_array[] = '<img style="width:6%;margin:5px;" src=' . $this->assetRepo->getUrlWithParams( 'CorvusPay_PaymentGateway::images/outline/iban.svg', $params ) . ' alt="iban">';

        $search_array[]  = ":paysafecard:";
        $replace_array[] = '<img style="width:6%;margin:5px;" src=' . $this->assetRepo->getUrlWithParams( 'CorvusPay_PaymentGateway::images/outline/paysafecard.svg', $params ) . ' alt="paysafecard">';

        $search_array[]  = ":card:";
        $replace_array[] = '<img style="width:6%;margin:5px;" src=' . $this->assetRepo->getUrlWithParams( 'CorvusPay_PaymentGateway::images/outline/card.svg', $params ) . ' alt="card">';

        $search_array[]  = ":wallet:";
        $replace_array[] = '<img style="width:6%;margin:5px;" src=' . $this->assetRepo->getUrlWithParams( 'CorvusPay_PaymentGateway::images/outline/wallet.svg', $params ) . ' alt="wallet">';

	    $search_array[]  = ":applepay:";
	    $replace_array[] = '<img style="width:6%;margin:5px;" src=' . $this->assetRepo->getUrlWithParams( 'CorvusPay_PaymentGateway::images/outline/applepay.svg', $params ) . ' alt="applepay">';

	    $search_array[]  = ":googlepay:";
	    $replace_array[] = '<img style="width:6%;margin:5px;" src=' . $this->assetRepo->getUrlWithParams( 'CorvusPay_PaymentGateway::images/outline/googlepay.svg', $params ) . ' alt="googlepay">';

	    $new_string = str_replace( $search_array, $replace_array, $subject );

        return $new_string;
    }
}
