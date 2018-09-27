<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 *  @author        MageModule admin@magemodule.com
 *  @copyright    2018 MageModule, LLC
 *  @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Model;

/**
 * Class AbstractExtensibleModel
 *
 * @package MageModule\Core\Model
 */
abstract class AbstractExtensibleModel extends \Magento\Framework\Model\AbstractExtensibleModel
{
    const ATTRIBUTE_SET_ID = 'attribute_set_id';
    const STORE_ID         = 'store_id';

    /**
     * @return \Magento\Framework\Model\AbstractExtensibleModel
     */
    public function _afterLoad()
    {
        if ($this->getOrigData() === null) {
            $this->setOrigData();
        }

        return parent::_afterLoad();
    }

    /**
     * @return null|\Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreManager()
    {
        return $this->_getData('storeManager');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setAttributeSetId($id)
    {
        $this->setData(self::ATTRIBUTE_SET_ID, $id);

        return $this;
    }

    /**
     * @return int
     */
    public function getAttributeSetId()
    {
        if ($this->isObjectNew() &&
            !$this->getData(self::ATTRIBUTE_SET_ID) &&
            $this->_resource instanceof \MageModule\Core\Model\ResourceModel\AbstractEntity
        ) {
            $this->setAttributeSetId(
                $this->_resource->getDefaultAttributeSetId()
            );
        }

        return $this->getData(self::ATTRIBUTE_SET_ID);
    }

    /**
     * @param string $attributeCode
     *
     * @return string|array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeText($attributeCode)
    {
        $result = $this->getData($attributeCode);

        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        $attribute = $this->_resource->getAttribute($attributeCode);

        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute) {
            /** @var \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface $source */
            $source = $attribute->getSource();

            if ($source instanceof \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface) {
                $data = $this->getData($attributeCode);
                try {
                    $data = json_decode($data);
                } catch (\Exception $e) {}
                $result = $source->getOptionText($data);
            }
        }

        return $result;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setStoreId($id)
    {
        $this->setData(self::STORE_ID, $id);

        return $this;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->hasData(self::STORE_ID)) {
            return $this->getData(self::STORE_ID);
        }

        if ($this->getStoreManager()) {
            return $this->getStoreManager()->getStore()->getId();
        }

        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
