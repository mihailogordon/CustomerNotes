<?php

namespace Krga\Blog\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Krga\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Krga\Blog\Model\ResourceModel\TagRelation\CollectionFactory as TagRelationCollectionFactory;

class PostTagsDataProvider extends DataProvider {
    protected $collectionFactory;
    protected $tagRelationCollectionFactory;
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
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->tagRelationCollectionFactory = $tagRelationCollectionFactory;
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
            $collection->setOrder('tag_id', 'ASC');
        }

        $collection->setCurPage($this->getSearchCriteria()->getCurrentPage());
        $collection->setPageSize($this->getSearchCriteria()->getPageSize());

        $items = [];
        foreach ($collection as $tag) {
        
            $items[] = [
                'tag_id' => $tag->getTagId(),
                'tag_name' => $tag->getTagName(),
                'count' => $this->getNumberOfPosts($tag),
                'created_at' => date('F j, Y', strtotime($tag->getCreatedAt())),
            ];
        }

        return [
            'items' => $items,
            'totalRecords' => $collection->getSize(),
        ];
    }

    public function getNumberOfPosts($tag) {
        $numberOfPosts = $this->tagRelationCollectionFactory->create()->addFieldToFilter('tag_id', ['eq' => $tag->getTagId()])->getItems();

        return count($numberOfPosts);
    }
}