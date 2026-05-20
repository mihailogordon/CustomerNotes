<?php

namespace CorvusPay\PaymentGateway\Controller\Success;

use CorvusPay\PaymentGateway\Gateway\Request\CorvusPayRequest;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\OrderFactory;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Index handles customers returning to success URL.
 */
class Index extends Action
{
    /** @var OrderFactory */
    private $orderFactory;

    /** @var OrderSender */
    protected $orderSender;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var Json */
    private $serializer;

    /** @var LoggerInterface */
    private $logger;

    /** @var PaymentTokenFactory */
    private $paymentTokenFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param OrderSender $orderSender
     * @param EncryptorInterface $encryptor
     * @param Json $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        OrderSender $orderSender,
        EncryptorInterface $encryptor,
        Json $serializer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->orderSender  = $orderSender;
        $this->encryptor    = $encryptor;
        $this->serializer   = $serializer;
        $this->logger       = $logger;

        $tokenTypes = [
            'card' => PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD,
        ];
        $this->paymentTokenFactory = new PaymentTokenFactory(ObjectManager::getInstance(), $tokenTypes);

        if (interface_exists(CsrfAwareActionInterface::class)) {
            $request = $this->getRequest();
            if ($request instanceof Http && $request->isPost() && empty($request->getParam('form_key'))) {
                $formKey = $this->_objectManager->get(FormKey::class);
                $request->setParam('form_key', $formKey->getFormKey());
            }
        }
    }

    /**
     * Handles success and redirects to Magento success page.
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException|Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        if (!($request instanceof Http && $request->isPost())) {
            $this->logger->error('Redirect to CorvusPay Success URL without POST parameters');
            throw new LocalizedException(__('Redirect to CorvusPay Success URL without POST parameters.'));
        }
        $this->logger->debug('Redirect to CorvusPay Success URL', ['parameters' => $request->getPost()]);

        $exploded = explode(
            CorvusPayRequest::ORDER_NUMBER_DELIMITER,
            $request->getPostValue('order_number')
        );
        $orderIncrementId = end($exploded);

        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);

        $payments = $order->getPaymentsCollection()->getItems();
        $paymentData = $this->serializer->unserialize(end($payments)->getAdditionalData());
        $secret_key = $this->encryptor->decrypt($paymentData['encrypted_secret_key']);
        $signed_parameters = [
            'order_number',
            'language',
            'approval_code',
            'account_id',
            'subscription_exp_date',
            'discount_used',
        ];
        $parameters = array_intersect_key((array)$request->getPost(), array_flip($signed_parameters));
        ksort($parameters);
        $data = '';
        foreach ($parameters as $key => $value) {
            $data .= $key . $value;
        }
        if (hash_hmac('sha256', $data, $secret_key) !== $request->getPost()['signature']) {
            $this->messageManager->addErrorMessage(__('Payment failed! ERROR: Invalid signature.'));
            $this->logger->warning(
                'Invalid signature for order {$orderIncrementId}',
                ['parameters' => $request->getPost()]
            );
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
        }

        $status = $paymentData['require_complete'] ? 'pre_authorized' : 'authorized';

        /** @var $payment Payment */
        $payment = $order->getPayment();
        $payment->setTransactionId("{$orderIncrementId} [{$status}]");
        $payment->setParentTransactionId($payment->getLastTransId());
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(true);
        $details = array_intersect_key((array)$request->getPost(), array_flip(['order_number', 'approval_code']));
        $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $details);

        if ($paymentData['require_complete']) {
            $payment->registerAuthorizationNotification($payment->getAmountOrdered());
        } else {
            $order->setState(Order::STATE_PROCESSING);
            $payment->registerCaptureNotification($payment->getAmountOrdered(), true);
        }

        /* Vault Token */
        $token = $request->getPostValue('account_id');
        if (null !== $token) {
            $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
            $paymentToken->setGatewayToken($token);

            $transactionInfo = $payment->getMethodInstance()
                                       ->fetchTransactionInfo($payment, $payment->getLastTransId());

            $paymentToken->setExpiresAt(strtotime($transactionInfo['subscription-exp-date']));

            $paymentToken->setTokenDetails(
                $this->serializer->serialize(
                    [
                        'cardType'       => $transactionInfo['cc-type'],
                        'maskedPan'      => $transactionInfo['card-details'],
                        'expirationDate' => substr($transactionInfo['subscription-exp-date'], 0, 7)
                    ]
                )
            );
            $payment->getExtensionAttributes()->setVaultPaymentToken($paymentToken);
        }

        $payment->save();
        $order->save();

        if ($paymentData['order_confirmation_email']) {
            $this->orderSender->send($order);
        }

        $this->logger->notice("CorvusPay order {$orderIncrementId} succeeded", ['parameters' => $request->getPost()]);
        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }
}
