<?php

namespace Krga\CustomerNotes\Model\Config\Source;

use Krga\CustomerNotes\Model\ResourceModel\Tag\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Tags implements OptionSourceInterface
{
    protected $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $tags = $this->collectionFactory->create()->getItems();

        $options[] = array();

        foreach ($tags as $tag) {
            $options[] = [
                'value' => $tag->getId(),
                'label' => $tag->getName()
            ];
        }

        return $options;
    }
}
