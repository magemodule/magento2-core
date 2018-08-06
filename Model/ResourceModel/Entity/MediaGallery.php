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
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\ResourceModel\Entity;

use Magento\Framework\Model\ResourceModel\Db\Context;

class MediaGallery extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    private $entityTable;

    /**
     * @var string
     */
    private $tableSuffix = '_media_gallery';

    /**
     * @var string
     */
    private $valueTableSuffix = '_value';

    /**
     * MediaGallery constructor.
     *
     * @param Context     $context
     * @param string $entityTable
     * @param string|null $connectionName
     * @param string|null $tableSuffix
     * @param string|null $valueTableSuffix
     */
    public function __construct(
        Context $context,
        $entityTable,
        $connectionName = null,
        $tableSuffix = null,
        $valueTableSuffix = null
    ) {
        parent::__construct($context, $connectionName);

        $this->entityTable = $entityTable;

        if ($tableSuffix) {
            $this->tableSuffix = $tableSuffix;
        }

        if ($valueTableSuffix) {
            $this->valueTableSuffix = $valueTableSuffix;
        }
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        return $this->entityTable . $this->tableSuffix;
    }

    /**
     * @return null|string
     */
    public function getValueTable()
    {
        if ($this->valueTableSuffix) {
            return $this->getMainTable() . $this->valueTableSuffix;
        }

        return null;
    }

    protected function _construct()
    {
        $this->_init(
            $this->getMainTable(), 'value_id'
        );
    }
}
