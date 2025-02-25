<?php

namespace Krga\CustomerNotes\Plugin;

use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Topmenu as MagentoGlobalTopMenu;

class Topmenu
{
    protected $nodeFactory;
    protected $urlBuilder;

    public function __construct(NodeFactory $nodeFactory, UrlInterface $urlBuilder)
    {
        $this->nodeFactory = $nodeFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function beforeGetHtml(MagentoGlobalTopMenu $subject, $outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $menuNode = $this->nodeFactory->create(
            [
                'data' => $this->getNodeAsArray("Customer Notes", "customerNotes"),
                'idField' => 'id',
                'tree' => $subject->getMenu()->getTree(),
            ]
        );

        $subject->getMenu()->addChild($menuNode);

    }

    protected function getNodeAsArray($name, $id)
    {
        $url = $this->urlBuilder->getUrl("notes");

        return [
            'name' => __($name),
            'id' => $id,
            'url' => $url,
            'has_active' => false,
            'is_active' => false,
        ];
    }
}