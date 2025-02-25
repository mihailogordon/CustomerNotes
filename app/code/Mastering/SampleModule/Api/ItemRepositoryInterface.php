<?php

namespace Mastering\SampleModule\Api;

interface ItemRepositoryInterface
{
    /**
     * @return \Mastering\SampleModule\Api\Data\ItemInterface[]
     */
    public function getList();
}
