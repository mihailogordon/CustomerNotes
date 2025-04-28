<?php

namespace Krga\Blog\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class CommentsGridActions extends Column
{
    const APPROVE_PATH = 'posts/comments/approve';
    const UNAPPROVE_PATH = 'posts/comments/unapprove';
    const DELETE_PATH = 'posts/comments/delete';

    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');

                if (isset($item['comment_id'])) {
                    if ($item['is_approved'] == 1) {
                        $item[$name]['unapprove'] = [
                            'href' => $this->urlBuilder->getUrl(self::UNAPPROVE_PATH, ['comment_id' => $item['comment_id']]),
                            'label' => __('Unapprove'),
                            'confirm' => [
                                'title' => __('Unapprove this comment?'),
                                'message' => __('Are you sure you want to unapprove this comment?')
                            ]
                        ];
                    } else {
                        $item[$name]['approve'] = [
                            'href' => $this->urlBuilder->getUrl(self::APPROVE_PATH, ['comment_id' => $item['comment_id']]),
                            'label' => __('Approve'),
                            'confirm' => [
                                'title' => __('Approve this comment?'),
                                'message' => __('Are you sure you want to approve this comment?')
                            ]
                        ];
                    }

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::DELETE_PATH, ['comment_id' => $item['comment_id']]),
                        'label' => __('Delete Permanently'),
                        'confirm' => [
                            'title' => __('Delete Permanently'),
                            'message' => __('Are you sure you want to delete this comment permanently? This action cannot be undone.')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

}