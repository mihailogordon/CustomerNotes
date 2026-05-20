<?php

namespace CorvusPay\PaymentGateway\Gateway;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class Options handles CorvusPay options.
 */
class Options
{
    public $environment;
    public $store_id;
    public $encrypted_secret_key;
    public $secret_key;
    public $certificate;
    public $language;
    public $cardholder_fields;
    public $time_limit;
    public $auto_redirect;
    public $installments;
    public $installments_map;
    public $order_confirmation_email;
    public $creditor_reference;
    public $hide_tabs;

    /**
     * Options constructor.
     *
     * @param ConfigInterface $config
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ConfigInterface $config,
        EncryptorInterface $encryptor
    ) {
        $this->environment          = $config->getValue('environment');
        $this->store_id             = [
            'test' => $config->getValue('test_store_id'),
            'prod' => $config->getValue('prod_store_id'),
        ];
        $this->encrypted_secret_key = [
            'test' => $config->getValue('test_secret_key'),
            'prod' => $config->getValue('prod_secret_key'),
        ];
        $this->secret_key           = array_map([$encryptor, 'decrypt'], $this->encrypted_secret_key);
        $this->language             = $config->getValue('payment_form_language');
        $this->cardholder_fields    = $config->getValue('cardholder_fields');
        $this->time_limit           = $config->getValue('form_time_limit_enabled')
            ? $config->getValue('form_time_limit_seconds') : null;
        $this->auto_redirect        = (bool)$config->getValue('payment_form_auto_redirect');
        $this->certificate          = [
            'test' => [
                'certificate' => $config->getValue('test_certificate'),
                'password'    => $encryptor->decrypt($config->getValue('test_certificate_password')),
            ],
            'prod' => [
                'certificate' => $config->getValue('prod_certificate'),
                'password'    => $encryptor->decrypt($config->getValue('prod_certificate_password')),
            ]
        ];
        $this->installments         = $config->getValue('form_installments');

        if (null === $config->getValue('form_installments_map')) {
            $this->installments_map = [];
        } else {
            $serializer             = ObjectManager::getInstance()->get(Json::class);
            $this->installments_map = $serializer->unserialize($config->getValue('form_installments_map'));
        }

        $this->creditor_reference = $config->getValue('pis_enabled')
            ? $config->getValue('pis_creditor_reference') : null;

        $this->order_confirmation_email = (bool) $config->getValue('suppress_order_confirmation_email');

        $this->hide_tabs = $config->getValue('hide_tabs');
    }
}
