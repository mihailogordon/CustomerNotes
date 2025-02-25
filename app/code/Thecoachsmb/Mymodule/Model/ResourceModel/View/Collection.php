<?php

declare(strict_types=1);

namespace Thecoachsmb\Mymodule\Model\ResourceModel\View;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {
    public function _construct() {
        $this->_init(\Thecoachsmb\Mymodule\Model\View::class, \Thecoachsmb\Mymodule\Model\ResourceModel\View::class);
    }
}