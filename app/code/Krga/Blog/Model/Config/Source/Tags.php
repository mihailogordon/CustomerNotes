<?php

namespace Krga\Blog\Model\Config\Source;

use Krga\Blog\Model\ResourceModel\Tag\CollectionFactory;
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
                'value' => $tag->getTagId(),
                'label' => $tag->getTagName()
            ];
        }

        return $options;
    }
}
