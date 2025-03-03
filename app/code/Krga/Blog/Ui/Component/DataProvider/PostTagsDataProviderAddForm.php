<?php 

namespace Krga\Blog\Ui\Component\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;

class PostTagsDataProviderAddForm extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData()
    {
        $result = [];

        foreach ($this->collection->getItems() as $item) {
            $result[$item->getId()] = $item->getData();
        }

        return $result;
    }
}
