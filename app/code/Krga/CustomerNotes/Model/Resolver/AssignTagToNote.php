<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\CustomerNotes\Model\TagRelationFactory;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class AssignTagToNote implements ResolverInterface
{
    protected $tagRelationFactory;
    protected $tagRelationCollectionFactory;

    public function __construct(
        TagRelationFactory $tagRelationFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory
    ) {
        $this->tagRelationFactory = $tagRelationFactory;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null ) 
    {
        $noteId = $args['noteId'] ?? null;
        $tagId = $args['tagId'] ?? null;
        $result = false;
        $newTagIds = array($tagId);

        if ($noteId && $tagId) {
            $currentTags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('note_id', ['eq' => $noteId])->getItems();
            
            if (is_array($currentTags) && count($currentTags)>0) {
                foreach ($currentTags as $currentTag) {
                    $currentTagId = $currentTag->getTagId();
                    if (!in_array($currentTagId, $newTagIds, true)) {
                        $newTagIds[] = $currentTagId;
                    }
                }
            }

            $this->tagRelationFactory->create()->assignTags($noteId, $newTagIds);
            $result = true;
        }

        return $result;
    }
}
