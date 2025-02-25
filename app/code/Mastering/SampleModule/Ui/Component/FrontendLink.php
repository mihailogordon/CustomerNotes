<?php

namespace Mastering\SampleModule\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\AbstractComponent;

class FrontendLink extends AbstractComponent
{
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function getComponentName()
    {
        return 'frontendLink';
    }

    public function prepare()
    {
        $config = $this->getData('config');
        $config['url'] = $this->urlBuilder->getUrl($config['url']);
        $this->setData('config', $config);
        parent::prepare();
    }
}
