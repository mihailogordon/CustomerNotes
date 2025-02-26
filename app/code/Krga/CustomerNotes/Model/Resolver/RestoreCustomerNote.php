<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;

class RestoreCustomerNote implements ResolverInterface
{
    protected $noteFactory;
    protected $noteResourceModel;

    public function __construct(
        NoteFactory $noteFactory,
        NoteResourceModel $noteResourceModel,
    ) {
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
        $noteId = $args['noteId'] ?? null;
        $result = false;

        if ($noteId) {
            $note = $this->noteFactory->create();
            $this->noteResourceModel->load($note, $noteId);
    
            if($note->getIsDeleted() == 1){
                $note->setIsDeleted(0);
                $this->noteResourceModel->save($note);
                $result = true;
            }
        }

        return $result;
    }
}
