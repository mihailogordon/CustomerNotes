<?php

namespace Krga\CustomerNotes\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Krga\CustomerNotes\Model\ResourceModel\Note\CollectionFactory;

class CustomerNote extends Column
{
    protected $customerNoteCollectionFactory;
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $criteria,
        CollectionFactory $customerNoteCollectionFactory, // Inject the collection factory
        array $components = [], 
        array $data = []
    ) {
        $this->_searchCriteria = $criteria;
        $this->customerNoteCollectionFactory = $customerNoteCollectionFactory; // Initialize the factory
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $customerId = $item['entity_id'];
                $customerNotes = $this->getCustomerNotes($customerId);
                $item[$this->getData('name')] = $customerNotes;
            }
        }

        return $dataSource;
    }

    /**
     * Fetch customer notes based on customer_id
     *
     * @param int $customerId
     * @return string
     */
    private function getCustomerNotes($customerId) {
        $collection = $this->customerNoteCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('created_at', 'ASC');
        $notes = [];

        foreach ($collection as $note) {
            $notes[] = '"' . $note->getNote() . '"';
        }

        return implode(', ', $notes);
    }
}
