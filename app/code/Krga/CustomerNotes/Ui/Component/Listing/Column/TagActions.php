<?php

namespace Krga\CustomerNotes\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class TagActions extends Column
{
    const EDIT_PATH = 'notes/tags/edit';
    const DELETE_PATH = 'notes/tags/delete';

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

                if (isset($item['tag_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::EDIT_PATH, ['tag_id' => $item['tag_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::DELETE_PATH, ['tag_id' => $item['tag_id']]),
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