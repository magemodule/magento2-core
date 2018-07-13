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

namespace MageModule\Core\Model\Eav\Entity;

class Attribute extends \Magento\Eav\Model\Entity\Attribute implements
    \MageModule\Core\Api\Data\AttributeInterface
{
    /**
     * @param int|bool $isWysiwygEnabled
     *
     * @return $this
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled)
    {
        $this->setData(self::IS_WYSIWYG_ENABLED, (int)$isWysiwygEnabled);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsWysiwygEnabled()
    {
        return (bool)$this->getData(self::IS_WYSIWYG_ENABLED);
    }
}
