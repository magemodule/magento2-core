<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 *  @author        MageModule admin@magemodule.com
 *  @copyright    2018 MageModule, LLC
 *  @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\ResourceModel;

use MageModule\Core\Api\Data\MediaGalleryValueInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;

class MediaGalleryValue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    private $mainTable;

    /**
     * MediaGallery constructor.
     *
     * @param Context     $context
     * @param string      $mainTable
     * @param null|string $connectionName
     */
    public function __construct(
        Context $context,
        $mainTable,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->mainTable  = $mainTable;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->mainTable, MediaGalleryValueInterface::VALUE_ID);
    }
}
