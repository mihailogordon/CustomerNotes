<?php

namespace CorvusPay\PaymentGateway\Block;

use CorvusPay\CorvusPayClient;
use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class PaymentFormRedirect renders payment form redirect page.
 */
class PaymentFormRedirect extends Template
{
    /**
     * @var array Disables form_key hidden field.
     */
    protected $_publicActions = ['index']; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

    /** @var Session */
    private $session;

    /** @var Json */
    private $serializer;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var LoggerInterface */
    private $logger;

    /** @var CorvusPayClient CorvusPay Client */
    private $client;

    /**
     * PaymentFormRedirect constructor.
     *
     * @param Context $context
     * @param Session $session
     * @param Json $serializer
     * @param LoggerInterface $logger
     * @param array $data
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        Session $session,
        Json $serializer,
        LoggerInterface $logger,
        array $data,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context, $data);
        $this->session    = $session;
        $this->serializer = $serializer;
        $this->logger     = $logger;
        $this->encryptor  = $encryptor;
    }

    /**
     * Prepare layout.
     *
     * @return Template|void
     */
    protected function _prepareLayout() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $payments = $this->session->getLastRealOrder()->getPaymentsCollection()->getItems();
        $request  = $this->serializer->unserialize(end($payments)->getAdditionalData());

        $type = 'auto';
        if(!$request['auto_redirect'])
            $type = __('Continue to payment');

        $secret_key = $this->encryptor->decrypt($request['encrypted_secret_key']);
        $params = ['store_id' => $request['parameters']['store_id'], 'secret_key' => $secret_key, 'environment' => $request['environment']];
        $this->client = new CorvusPayClient($params);

        $form = $this->client->checkout->create($request['parameters'], $type, false);

        $this->setForm($form);

        $this->logger->debug('Rendering a CorvusPay payment from', ['parameters' => $request['parameters']]);
    }
}
