<?php

namespace Krga\CustomerNotes\Plugin;

use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Topmenu as MagentoGlobalTopMenu;
use Krga\CustomerNotes\Helper\Config;

class Topmenu
{
    protected $nodeFactory;
    protected $urlBuilder;
    protected $configHelper;

    public function __construct(
        NodeFactory $nodeFactory, 
        UrlInterface $urlBuilder,
        Config $configHelper
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->urlBuilder = $urlBuilder;
        $this->configHelper = $configHelper;
    }

    public function beforeGetHtml(MagentoGlobalTopMenu $subject, $outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        if ( $this->configHelper->isMenuItemEnabled() ) {
            $menuItemLabel = $this->configHelper->getMenuItemLabel();

            $menuNode = $this->nodeFactory->create(
                [
                    'data' => $this->getNodeAsArray($menuItemLabel, "customerNotes"),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree(),
                ]
            );
    
            $subject->getMenu()->addChild($menuNode);
        }
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