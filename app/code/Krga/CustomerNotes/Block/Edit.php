<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Krga\CustomerNotes\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class Edit extends Template
{
    protected $request;
    protected $noteFactory;
    protected $noteResourceModel;
    protected $customerRepository;
    protected $note = null;
    protected $customer = null;
    protected $tagCollectionFactory;
    protected $tagRelationCollectionFactory;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        NoteFactory $noteFactory,
        NoteResourceModel $noteResourceModel,
        CustomerRepositoryInterface $customerRepository,
        TagCollectionFactory $tagCollectionFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->noteFactory = $noteFactory;
        $this->noteResourceModel = $noteResourceModel;
        $this->customerRepository = $customerRepository;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get note ID from the request
     */
    public function getNoteId()
    {
        return (int) $this->request->getParam('note_id');
    }

    /**
     * Load Note from Database (Singleton Pattern)
     */
    private function getNote()
    {
        if ($this->note === null) {
            $noteId = $this->getNoteId();

            if ($noteId) {
                $this->note = $this->noteFactory->create();
                $this->noteResourceModel->load($this->note, $noteId);
            }
        }

        return $this->note;
    }

    /**
     * Get Note Content
     */
    public function getNoteContent()
    {
        $note = $this->getNote();
        return ($note && $note->getId()) ? $note->getNote() : '';
    }

    /**
     * Get Associated Customer ID
     */
    public function getNoteCustomerId()
    {
        $note = $this->getNote();
        return ($note && $note->getId()) ? $note->getCustomerId() : null;
    }

    /**
     * Get Customer Information (First & Last Name)
     */
    public function getNoteCustomer()
    {
        $customerId = $this->getNoteCustomerId();
        if (!$customerId) {
            return null;
        }

        if ($this->customer === null) {
            try {
                $this->customer = $this->customerRepository->getById($customerId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return $this->customer->getFirstname() . ' ' . $this->customer->getLastname();
    }

    public function getAllTags() {
        return $this->tagCollectionFactory->create()->getItems();
    }
    
    public function getTagsIds() {
        $relatedTags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('note_id', ['eq' => $this->getNoteId()])->getitems();
        $relatedTagsIds = array();
        
        if (is_array($relatedTags) && count($relatedTags) > 0) {
            foreach ($relatedTags as $relatedTag) {
                $relatedTagsIds[] = $relatedTag->getTagId();
            }
        }

        return $relatedTagsIds;
    }
}
