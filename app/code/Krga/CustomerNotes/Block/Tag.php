<?php

namespace Krga\CustomerNotes\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\RequestInterface;
use Krga\CustomerNotes\Helper\Config;
use Krga\CustomerNotes\Model\TagFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag as TagResourceModel;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\CustomerNotes\Model\NoteFactory;
use Krga\CustomerNotes\Model\ResourceModel\Note as NoteResourceModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class Tag extends Template
{
    protected $request;
    protected $configHelper;
    protected $tagFactory;
    protected $tagResourceModel;
    protected $tagRelationCollectionFactory;
    protected $noteFactory;
    protected $noteResourceModel;
    protected $customerCollectionFactory;
    private $tag = null;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        Config $configHelper,
        TagFactory $tagFactory,
        TagResourceModel $tagResourceModel,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        NoteFactory $noteFactory,
        NoteResourceModel $noteResourceModel,
        CustomerCollectionFactory $customerCollectionFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->configHelper = $configHelper;
        $this->tagFactory = $tagFactory;
        $this->tagResourceModel = $tagResourceModel;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->noteFactory = $noteFactory;
        $this->noteResourceModel = $noteResourceModel;
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getTagSinglePageSize()
    {
        return $this->configHelper->getTagSinglePageSize();
    }
    
    public function isTagSinglePaginationEnabled()
    {
        return $this->configHelper->isTagSinglePaginationEnabled();
    }

    public function getTagId()
    {
        return (int) $this->request->getParam('tag_id');
    }

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

    public function getTagName()
    {
        $tag = $this->getTag();
        return ($tag && $tag->getId()) ? $tag->getName() : '';
    }

    public function getTagNotesCollection() {
        $page = (int)$this->getRequest()->getParam('p', 1);
        $tag = $this->getTag();
        $tagRelations = array();

        if ($tag) {
            $tagRelations = $this->tagRelationCollectionFactory->create()
            ->addFieldToFilter('tag_id', array('eq' => $tag->getTagId()))
            ->setPageSize($this->getTagSinglePageSize())
            ->setCurPage($page);
        }

        return $tagRelations;
    }

    public function getTagNotes() {
        $tagRelations = $this->getTagNotesCollection()->getItems();
        $tagNotes = array();

        if (is_array($tagRelations) && count($tagRelations) > 0) {
            foreach($tagRelations as $tagRelation) {
                $note = $this->noteFactory->create();
                $this->noteResourceModel->load($note, $tagRelation->getNoteId());
                $tagNotes[] = $note;
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
