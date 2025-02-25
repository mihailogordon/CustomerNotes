<?php

namespace Krga\CustomerNotes\Ui\Component\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Krga\CustomerNotes\Model\ResourceModel\Settings\CollectionFactory;

class SettingsDataProvider extends AbstractDataProvider
{
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        // We pass empty strings so that the provider doesn't wrap data by record ID.
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function addFilter(Filter $filter)
    {
        // Skip filters with an empty value.
        if (!$filter->getValue()) {
            return $this;
        }
        return parent::addFilter($filter);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $settingsData = [];
        foreach ($this->collection->getItems() as $item) {
            // Your model should have getOptionName() and getOptionValue() methods.
            $settingsData[$item->getOptionName()] = $item->getOptionValue();
        }

        // Return data keyed by "notes_settings_form" to match the UI component's dataScope.
        $this->loadedData = ['notes_settings_form' => $settingsData];
        return $this->loadedData;
    }
}
