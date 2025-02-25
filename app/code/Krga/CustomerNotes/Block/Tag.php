<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use Krga\CustomerNotes\Model\TagFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag as TagResourceModel;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class Tag extends Template
{
    protected $request;
    protected $tagFactory;
    protected $tagResourceModel;
    protected $tagRelationCollectionFactory;
    protected $noteFactory;
    protected $noteResourceModel;
    protected $customerCollectionFactory;
    private $tag = null; // Cache loaded note

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        TagFactory $tagFactory,
        TagResourceModel $tagResourceModel,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        NoteFactory $noteFactory,
        NoteResourceModel $noteResourceModel,
        CustomerCollectionFactory $customerCollectionFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->tagFactory = $tagFactory;
        $this->tagResourceModel = $tagResourceModel;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->noteFactory = $noteFactory;
        $this->noteResourceModel = $noteResourceModel;
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get note ID from the request
     */
    public function getTagId()
    {
        return (int) $this->request->getParam('tag_id');
    }

    /**
     * Load Note from Database (Singleton Pattern)
     */
    private function getTag()
    {
        if ($this->tag === null) {
            $tagId = $this->getTagId();

            if ($tagId) {
                $this->tag = $this->tagFactory->create();
                $this->tagResourceModel->load($this->tag, $tagId);
            }
        }

        return $this->tag;
    }

    /**
     * Get Note Content
     */
    public function getTagName()
    {
        $tag = $this->getTag();
        return ($tag && $tag->getId()) ? $tag->getName() : '';
    }

    public function getTagNotes() {
        $tag = $this->getTag();
        $tagNotes = array();

        if ($tag) {
            $tagRelations = $this->tagRelationCollectionFactory->create()->addFieldToFilter('tag_id', array('eq' => $tag->getTagId()))->getItems();
            
            if (is_array($tagRelations) && count($tagRelations) > 0) {
                foreach($tagRelations as $tagRelation) {
                    $note = $this->noteFactory->create();
                    $this->noteResourceModel->load($note, $tagRelation->getNoteId());
                    $tagNotes[] = $note;
                }
            }
        }

        return $tagNotes;
    }

    public function getCustomers()
    {
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->addAttributeToSelect(['entity_id', 'firstname', 'lastname', 'email']);

        return $customerCollection->getItems();
    }
}
