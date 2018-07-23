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

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

use MageModule\Core\Model\AbstractExtensibleModel;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractBackend extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($object)
    {
        parent::validate($object);

        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getAttributeCode();
        $value     = $object->getData($attrCode);

        if (!$object->getData(AbstractExtensibleModel::STORE_ID) &&
            $attribute->getIsVisible() &&
            $attribute->getIsRequired() &&
            $attribute->isValueEmpty($value)
        ) {
            $label = $attribute->getFrontend()->getLabel();
            throw new LocalizedException(__('The value of attribute "%1" must be set', $label));
        }

        return true;
    }
}
