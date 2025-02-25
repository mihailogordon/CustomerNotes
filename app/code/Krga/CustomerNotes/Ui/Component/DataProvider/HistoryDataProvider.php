<?php

namespace Krga\CustomerNotes\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Krga\CustomerNotes\Model\ResourceModel\History\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

class HistoryDataProvider extends DataProvider {
    protected $collectionFactory;
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
        CustomerRepositoryInterface $customerRepository,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
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
        $noteId = $this->request->getParam('note_id');
        $collection = $this->collectionFactory->create();

        if (!empty($noteId)) {
            $collection->addFieldToFilter('note_id', ['eq' => $noteId]);
        }

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
            $collection->setOrder('history_id', 'ASC');
        }

        $collection->setCurPage($this->getSearchCriteria()->getCurrentPage());
        $collection->setPageSize($this->getSearchCriteria()->getPageSize());

        $items = [];
        foreach ($collection as $note) {
        
            $items[] = [
                'history_id' => $note->getHistoryId(),
                'note_id' => $note->getNoteId(),
                'customer_id' => $note->getCustomerId(),
                'customer' => $this->getCustomerNicename($note->getCustomerId()),
                'previous_note' => $note->getPreviousNote(),
                'modified_at' => date('F j, Y', strtotime($note->getModifiedAt())),
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
}