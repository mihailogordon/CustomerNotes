<?php

namespace Krga\Blog\Model\Config\Source;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class Customers implements OptionSourceInterface
{
    protected $customerRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function toOptionArray()
    {
        $customers = $this->getCustomers();

        $options[] = [
            'value' => '',
            'label' => 'Select Customer'
        ];

        foreach ($customers as $customer) {
            $options[] = [
                'value' => $customer->getId(),
                'label' => $customer->getFirstname() . ' ' . $customer->getLastname()
            ];
        }

        return $options;
    }

    private function getCustomers()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $customerSearchResults = $this->customerRepository->getList($searchCriteria);
        return $customerSearchResults->getItems();
    }
}
