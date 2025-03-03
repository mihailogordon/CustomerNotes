<?php

namespace Krga\Blog\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class PostsGridActions extends Column
{
    const EDIT_PATH = 'posts/item/edit';
    const TRASH_PATH = 'posts/item/trash';
    const RESTORE_PATH = 'posts/item/restore';
    const DELETE_PATH = 'posts/item/delete';

    protected $urlBuilder;
    protected $collectionFactory;

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

                if (isset($item['post_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::EDIT_PATH, ['post_id' => $item['post_id']]),
                        'label' => __('Edit')
                    ];

                    if (!$item['is_deleted']) {
                        $item[$name]['trash'] = [
                            'href' => $this->urlBuilder->getUrl(self::TRASH_PATH, ['post_id' => $item['post_id']]),
                            'label' => __('Move to Trash'),
                            'confirm' => [
                                'title' => __('Move to Trash'),
                                'message' => __('Are you sure you want to move this post to trash?')
                            ]
                        ];
                    } else {
                        $item[$name]['restore'] = [
                            'href' => $this->urlBuilder->getUrl(self::RESTORE_PATH, ['post_id' => $item['post_id']]),
                            'label' => __('Restore'),
                            'confirm' => [
                                'title' => __('Restore Post'),
                                'message' => __('Are you sure you want to restore this post?')
                            ]
                        ];
                    }

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::DELETE_PATH, ['post_id' => $item['post_id']]),
                        'label' => __('Delete Permanently'),
                        'confirm' => [
                            'title' => __('Delete Permanently'),
                            'message' => __('Are you sure you want to delete this post permanently? This action cannot be undone.')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

}