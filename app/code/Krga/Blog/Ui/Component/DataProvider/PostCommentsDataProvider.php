<?php

namespace Krga\Blog\Ui\Component\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Krga\Blog\Model\ResourceModel\Comment\CollectionFactory;

class PostCommentsDataProvider extends DataProvider {
    protected $collectionFactory;
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
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
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
            $collection->setOrder('comment_id', 'ASC');
        }

        $collection->setCurPage($this->getSearchCriteria()->getCurrentPage());
        $collection->setPageSize($this->getSearchCriteria()->getPageSize());

        $items = [];
        foreach ($collection as $comment) {
        
            $items[] = [
                'comment_id' => $comment->getCommentId(),
                'post_id' => $comment->getPostId(),
                'author_name' => $comment->getAuthorName(),
                'author_email' => $comment->getAuthorEmail(),
                'content' => $comment->getContent(),
                'created_at' => date('F j, Y', strtotime($comment->getCreatedAt())),
                'updated_at' => date('F j, Y', strtotime($comment->getUpdatedAt())),
                'is_approved' => $comment->getIsApproved(),
            ];
        }

        return [
            'items' => $items,
            'totalRecords' => $collection->getSize(),
        ];
    }
}