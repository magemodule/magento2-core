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

namespace MageModule\Core\Model\Entity;

class MediaGallery extends \Magento\Framework\Model\AbstractModel implements
    \MageModule\Core\Api\Data\MediaGalleryInterface
{
    /**
     * @param int $id
     *
     * @return $this
     */
    public function setValueId($id)
    {
        $this->setData(self::VALUE_ID, $id);

        return $this;
    }

    /**
     * @return int
     */
    public function getValueId()
    {
        return $this->getData(self::VALUE_ID);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setAttributeId($id)
    {
        $this->setData(self::ATTRIBUTE_ID, $id);

        return $this;
    }

    /**
     * @return int
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setEntityId($id)
    {
        $this->setData(self::ENTITY_ID, $id);

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param string|null|bool $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->setData(self::VALUE, $value);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }
}
