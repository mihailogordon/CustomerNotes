<?php

namespace Krga\CustomerNotes\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Krga\CustomerNotes\Model\ResourceModel\Settings as SettingsResource;
use Krga\CustomerNotes\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;

class Settings extends AbstractModel
{
    private $settingsCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        SettingsCollectionFactory $settingsCollectionFactory
    ) {
        $this->settingsCollectionFactory = $settingsCollectionFactory;
        parent::__construct($context, $registry);
    }

    protected function _construct()
    {
        $this->_init(SettingsResource::class);
    }

    public function getOption($key, $default = null)
    {
        $option = $this->settingsCollectionFactory->create()
            ->addFieldToFilter('option_name', ['eq' => $key])
            ->getFirstItem();

        return $option->getId() ? $option->getOptionValue() : $default;
    }
}
