<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;

class GetNotesByTag implements ResolverInterface
{
    protected $tagRelationCollectionFactory;
    protected $noteFactory;
    protected $noteResourceModel;

    public function __construct(
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        NoteFactory $noteFactory,
        NoteResourceModel $noteResourceModel
    ) {
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->noteFactory = $noteFactory;
        $this->noteResourceModel = $noteResourceModel;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null ) 
    {
        $tagId = $args['tagId'] ?? null;
        $result = array();

        if ($tagId) {
            $tagRelations = $this->tagRelationCollectionFactory->create()->addFieldToFilter('tag_id', ['eq' => $tagId])->getItems();

            if (is_array($tagRelations) && count($tagRelations) > 0) {
                foreach ($tagRelations as $tagRelation) {
                    $note = $this->noteFactory->create();
                    $this->noteResourceModel->load($note, $tagRelation->getNoteId());
                    $result[] = $note;
                }
            }
        }

        return $result;
    }
}
