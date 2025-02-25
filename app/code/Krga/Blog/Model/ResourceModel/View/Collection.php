<?php

namespace Krga\Blog\Model\ResourceModel\View;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {
    public function _construct() {
        $this->_init(\Krga\Blog\Model\View::class, \Krga\Blog\Model\ResourceModel\View::class);
    }
}