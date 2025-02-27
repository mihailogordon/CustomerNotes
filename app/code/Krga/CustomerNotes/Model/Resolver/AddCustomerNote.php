<?php

namespace Krga\CustomerNotes\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AddCustomerNote implements ResolverInterface {
    
    protected $noteFactory;
    protected $noteResourceModel;
    protected $customerRepository;

    public function __construct(
        NoteFactory $noteFactory,
        NoteResourceModel $noteResourceModel,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->noteFactory = $noteFactory;
        $this->noteResourceModel = $noteResourceModel;
        $this->customerRepository = $customerRepository;
    }

    public function resolve(
        Field $field, 
        $context, 
        ResolveInfo $info, 
        ?array $value = null, 
        ?array $args = null
    ) {
        $customerId = $args['customerId'] ?? null;
        $note = $args['note'] ?? null;

        if (empty($customerId) || empty($note)) {
            throw new LocalizedException(__('Customer ID and note are required.'));
        }

        try {
            $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Customer with ID %1 does not exist.', $customerId));
        }

        try {
            $newNote = $this->noteFactory->create();
            $newNote->setCustomerId($customerId);
            $newNote->setNote($note);
            $this->noteResourceModel->save($newNote);

            return $newNote;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Failed to add customer note: %1', $e->getMessage()));
        }
    }
}