<?php

namespace Krga\Blog\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Krga\Blog\Model\ResourceModel\Tag\CollectionFactory;

class PostTagsDataProviderEditForm extends AbstractDataProvider
{
    protected $loadedData;
    protected $request;

    public function __construct(
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $data = [];
        $tagId = $this->request->getParam('tag_id');

        if ($tagId) {
            $tag = $this->collection->getItemById($tagId);
            if ($tag) {
                $data[$tagId] = $tag->getData();
            }
        }
        
        return $data;
    }
}
