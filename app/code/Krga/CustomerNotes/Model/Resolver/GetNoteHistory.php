<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\CustomerNotes\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;

class GetNoteHistory implements ResolverInterface
{
    protected $historyCollectionFactory;

    public function __construct(
        HistoryCollectionFactory $historyCollectionFactory,
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null ) 
    {
        $noteId = $args['noteId'] ?? null;
        $result = array();

        if ($noteId) {
            $result = $this->historyCollectionFactory->create()
            ->addFieldToFilter('note_id', ['eq' => $noteId])
            ->getItems();
        }

        return $result;
    }
}
