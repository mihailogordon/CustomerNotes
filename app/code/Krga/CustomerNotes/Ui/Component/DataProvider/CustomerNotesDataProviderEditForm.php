<?php

namespace Krga\CustomerNotes\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class CustomerNotesDataProviderEditForm extends AbstractDataProvider
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
        $noteId = $this->request->getParam('note_id');

        if ($noteId) {
            $note = $this->collection->getItemById($noteId);
            if ($note) {
                $data[$noteId] = $note->getData();
            }
        }

        $data[$noteId]['tag_ids'] = array();

        $relatedTags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('note_id', ['eq' => $noteId])->getitems();
        if (is_array($relatedTags) && count($relatedTags) > 0) {
            foreach ($relatedTags as $relatedTag) {
                $data[$noteId]['tag_ids'][] = $relatedTag->getTagId();
            }
        }
        
        return $data;
    }
}
