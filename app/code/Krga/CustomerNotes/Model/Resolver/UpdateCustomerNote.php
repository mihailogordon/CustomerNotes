<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;

class UpdateCustomerNote implements ResolverInterface
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
        $newNote = $args['note'] ?? '';

        if ($noteId) {
            $note = $this->noteFactory->create();
            $this->noteResourceModel->load($note, $noteId);
            $note->setNote($newNote);
            $this->noteResourceModel->save($note);
            
            return $note;
        }

        throw new \Exception(__('Failed to delete customer note.'));
    }
}
