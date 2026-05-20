<?php

namespace CorvusPay\PaymentGateway\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index handles payment form rendering.
 */
class Index extends Action
{
    protected $pageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;

        return parent::__construct($context);
    }

    /**
     * Builds redirect to payment gateway.
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
