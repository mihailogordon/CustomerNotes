<?php

namespace Krga\Blog\Plugin;

use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Topmenu as MagentoGlobalTopMenu;
use Krga\Blog\Helper\Config;

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
                    'data' => $this->getNodeAsArray($menuItemLabel, "blog", false),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree(),
                ]
            );

            $blogNode = $this->nodeFactory->create(
                [
                    'data' => $this->getNodeAsArray("Posts", "blog_posts", "posts"),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree(),
                ]
            );

            $blogTagsNode = $this->nodeFactory->create(
                [
                    'data' => $this->getNodeAsArray("Tags", "blog_tags", "posts/tags/all"),
                    'idField' => 'id',
                    'tree' => $subject->getMenu()->getTree(),
                ]
            );

            $mainMenuNode->addChild($blogNode);
            $mainMenuNode->addChild($blogTagsNode);

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
