<?php 

namespace Krga\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class View extends AbstractDb {
    public function _construct() {
        $this->_init('blog', 'post_id');
    }
}