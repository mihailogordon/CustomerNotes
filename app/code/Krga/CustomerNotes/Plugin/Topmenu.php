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
        if ($this->configHelper->isMenuItemEnabled()) {
            $menuItemLabel = $this->configHelper->getMenuItemLabel();

            $mainMenuNode = $this->nodeFactory->create(
                [
                    'data' => $this->getNodeAsArray($menuItemLabel, "customerNotes", false),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree(),
                ]
            );

            $customerNotesNode = $this->nodeFactory->create(
                [
                    'data' => $this->getNodeAsArray("Customer Notes", "customer_notes", "notes"),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree(),
                ]
            );

            $noteTagsNode = $this->nodeFactory->create(
                [
                    'data' => $this->getNodeAsArray("Note Tags", "note_tags", "notes/tags/all"),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree(),
                ]
            );

            $mainMenuNode->addChild($customerNotesNode);
            $mainMenuNode->addChild($noteTagsNode);

            $subject->getMenu()->addChild($mainMenuNode);
        }
    }

    protected function getNodeAsArray($name, $id, $urlPath = false)
    {
        $url = $urlPath ? $this->urlBuilder->getUrl($urlPath) : false;

        return [
            'name' => __($name),
            'id' => $id,
            'url' => $url,
            'has_active' => false,
            'is_active' => false,
        ];
    }
}
