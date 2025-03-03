<?php

namespace Krga\Blog\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Krga\Blog\Model\ResourceModel\Post\CollectionFactory;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class PostsDataProviderEditForm extends AbstractDataProvider
{
    protected $loadedData;
    protected $request;
    protected $tagRelationCollectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        RequestInterface $request,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $data = [];
        $postId = $this->request->getParam('post_id');

        if ($postId) {
            $post = $this->collection->getItemById($postId);
            if ($post) {
                $data[$postId] = $post->getData();
            }
        }

        $data[$postId]['tag_ids'] = array();

        $relatedTags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('post_id', ['eq' => $postId])->getitems();
        if (is_array($relatedTags) && count($relatedTags) > 0) {
            foreach ($relatedTags as $relatedTag) {
                $data[$postId]['tag_ids'][] = $relatedTag->getTagId();
            }
        }
        
        return $data;
    }
}
