<?php

namespace CorvusPay\PaymentGateway\Controller\Cancel;

use CorvusPay\PaymentGateway\Gateway\Request\CorvusPayRequest;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Index handles customers returning to cancel URL.
 */
class Index extends Action
{
    private $orderFactory;
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderFactory    = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->logger          = $logger;

        if (interface_exists(CsrfAwareActionInterface::class)) {
            $request = $this->getRequest();
            if ($request instanceof Http && $request->isPost() && empty($request->getParam('form_key'))) {
                $formKey = $this->_objectManager->get(FormKey::class);
                $request->setParam('form_key', $formKey->getFormKey());
            }
        }
    }

    /**
     * Handles cancellation and redirects to checkout page.
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException|Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        if (!($request instanceof Http && $request->isPost())) {
            $this->logger->error('Redirect to CorvusPay Cancel URL without POST parameters');
            throw new LocalizedException(__('Redirect to CorvusPay Cancel URL without POST parameters.'));
        }
        $this->logger->debug('Redirect to CorvusPay Cancel URL', ['parameters' => $request->getPost()]);

        $exploded         = explode(
            CorvusPayRequest::ORDER_NUMBER_DELIMITER,
            $request->getPostValue('order_number')
        );
        $orderIncrementId = end($exploded);

        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);

        $this->checkoutSession->restoreQuote();

        /** @var $payment Payment */
        $payment = $order->getPayment();

        $status = 'canceled';
        $payment->setTransactionId("{$orderIncrementId} [{$status}]");
        $payment->setParentTransactionId($payment->getLastTransId());
        $payment->setIsTransactionClosed(true);
        $details = array_intersect_key((array)$request->getPost(), array_flip(['order_number']));
        $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $details);

        $payment->setMessage('Customer canceled the payment at the payment gateway.');
        $payment->registerVoidNotification($payment->getAmountOrdered());

        if ($order->canCancel()) {
            $order->cancel();
        }

        $payment->save();
        $order->save();

        $this->logger->notice("CorvusPay order {$orderIncrementId} canceled", ['parameters' => $request->getPost()]);
        return $this->resultRedirectFactory->create()->setPath('checkout');
    }
}
