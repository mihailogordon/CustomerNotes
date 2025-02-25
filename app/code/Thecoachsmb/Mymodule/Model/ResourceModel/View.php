<?php

declare(strict_types=1);

namespace Thecoachsmb\Mymodule\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class View extends AbstractDb {
    public function _construct() {
        $this->_init('thecoachsmb_article', 'article_id');
    }
}