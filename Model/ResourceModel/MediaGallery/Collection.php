<?php

namespace MageModule\Core\Model\ResourceModel\MediaGallery;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \MageModule\Core\Model\MediaGallery::class,
            \MageModule\Core\Model\ResourceModel\MediaGallery::class
        );
    }
}
