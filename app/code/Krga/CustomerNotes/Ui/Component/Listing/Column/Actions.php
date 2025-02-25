<?php

namespace Krga\CustomerNotes\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Krga\CustomerNotes\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;

class Actions extends Column
{
    const EDIT_PATH = 'notes/item/edit';
    const TRASH_PATH = 'notes/item/trash';
    const RESTORE_PATH = 'notes/item/restore';
    const DELETE_PATH = 'notes/item/delete';
    const SHOW_HISTORY = 'notes/history/index';

    protected $urlBuilder;
    protected $collectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        HistoryCollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');

                if (isset($item['note_id'])) {
                    $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('note_id', ['eq' => $item['note_id']]);

                    $relatedHistory = $collection->getItems();

                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::EDIT_PATH, ['note_id' => $item['note_id']]),
                        'label' => __('Edit')
                    ];

                    if (!$item['is_deleted']) {
                        $item[$name]['trash'] = [
                            'href' => $this->urlBuilder->getUrl(self::TRASH_PATH, ['note_id' => $item['note_id']]),
                            'label' => __('Move to Trash'),
                            'confirm' => [
                                'title' => __('Move to Trash'),
                                'message' => __('Are you sure you want to move this note to trash?')
                            ]
                        ];
                    } else {
                        $item[$name]['restore'] = [
                            'href' => $this->urlBuilder->getUrl(self::RESTORE_PATH, ['note_id' => $item['note_id']]),
                            'label' => __('Restore'),
                            'confirm' => [
                                'title' => __('Restore Note'),
                                'message' => __('Are you sure you want to restore this note?')
                            ]
                        ];
                    }

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::DELETE_PATH, ['note_id' => $item['note_id']]),
                        'label' => __('Delete Permanently'),
                        'confirm' => [
                            'title' => __('Delete Permanently'),
                            'message' => __('Are you sure you want to delete this note permanently? This action cannot be undone.')
                        ]
                    ];

                    if (is_array($relatedHistory) && count($relatedHistory) > 0) {
                        $item[$name]['history'] = [
                            'href' => $this->urlBuilder->getUrl(self::SHOW_HISTORY, ['note_id' => $item['note_id']]),
                            'label' => __('Show History'),
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }

}