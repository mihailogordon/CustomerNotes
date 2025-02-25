<?php

namespace Krga\CustomerNotes\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;
use Krga\CustomerNotes\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;
use Krga\CustomerNotes\Model\TagFactory;
use Krga\CustomerNotes\Model\ResourceModel\Tag as TagResource;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerNotesDataProvider extends DataProvider {
    protected $collectionFactory;
    protected $tagRelationCollectionFactory;
    protected $tagFactory;
    protected $tagResource;
    protected $customerRepository;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $reporting;
    protected $meta = [];
    protected $data = [];

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        TagRelationCollectionFactory $tagRelationCollectionFactory,
        TagFactory $tagFactory,
        TagResource $tagResource,
        CustomerRepositoryInterface $customerRepository,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    public function getData()
    {
        $collection = $this->collectionFactory->create();

        foreach ($this->getSearchCriteria()->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $field = $filter->getField();
                if ($field) {
                    $condition = $filter->getConditionType() ?: 'eq';
                    $collection->addFieldToFilter($field, [$condition => $filter->getValue()]);
                }
            }
        }

        $sortApplied = false;
        foreach ($this->getSearchCriteria()->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            if ($field) {
                $collection->setOrder($field, $sortOrder->getDirection());
                $sortApplied = true;
            }
        }

        if (!$sortApplied) {
            $collection->setOrder('note_id', 'ASC');
        }

        $collection->setCurPage($this->getSearchCriteria()->getCurrentPage());
        $collection->setPageSize($this->getSearchCriteria()->getPageSize());

        $items = [];
        foreach ($collection as $note) {
            $items[] = [
                'note_id' => $note->getId(),
                'note' => $note->getNote(),
                'customer_id' => $note->getCustomerId(),
                'customer' => $this->getCustomerNicename($note->getCustomerId()),
                'created_at' => date('F j, Y', strtotime($note->getCreatedAt())),
                'is_deleted' => $note->getIsDeleted(),
                'tags' => $this->getNoteTags($note)
            ];
        }

        return [
            'items' => $items,
            'totalRecords' => $collection->getSize(),
        ];
    }


    private function getCustomerNicename($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            return trim($customer->getFirstname() . ' ' . $customer->getLastname());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return __('Unknown Customer')->render();
        }
    }

    private function getNoteTags($note) {
        $noteTags = array();

        $currentTags = $this->tagRelationCollectionFactory->create()->addFieldToFilter('note_id', ['eq' => $note->getId()])->getItems();
        if (is_array($currentTags) && count($currentTags) > 0) {
            foreach ($currentTags as $currentTag) {
                $currentTagId = $currentTag->getTagId();
                $tagItem = $this->tagFactory->create();
                $this->tagResource->load($tagItem, $currentTagId);
                $noteTags[] = strtolower($tagItem->getName());
            }
        }

        return implode(', ', $noteTags);
    }
}