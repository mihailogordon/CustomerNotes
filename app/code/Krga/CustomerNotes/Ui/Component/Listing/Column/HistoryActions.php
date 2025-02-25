<?php

namespace Krga\CustomerNotes\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class HistoryActions extends Column
{
    const REVERT_PATH = 'notes/history/revert';
    const DELETE_PATH = 'notes/history/delete';

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

                if (isset($item['history_id'])) {
                    $item[$name]['revert'] = [
                        'href' => $this->urlBuilder->getUrl(self::REVERT_PATH, ['history_id' => $item['history_id']]),
                        'label' => __('Revert'),
                        'confirm' => [
                            'title' => __('Revert Note'),
                            'message' => __('Are you sure you want to revert this note?')
                        ]
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::DELETE_PATH, ['history_id' => $item['history_id']]),
                        'label' => __('Delete Permanently'),
                        'confirm' => [
                            'title' => __('Delete Permanently'),
                            'message' => __('Are you sure you want to delete this note permanently? This action cannot be undone.')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

}