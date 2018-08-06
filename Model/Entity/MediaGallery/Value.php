<?php

namespace MageModule\Core\Model\Entity\MediaGallery;

class Value extends \Magento\Framework\Model\AbstractModel implements
    \MageModule\Core\Api\Data\MediaGalleryValueInterface
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
    public function setStoreId($id)
    {
        $this->setData(self::STORE_ID, $id);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
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
     * @param string|null|bool $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->setData(self::LABEL, $label);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->setData(self::POSITION, $position);

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @param int|bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->setData(self::DISABLED, $disabled);

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisabled()
    {
        return $this->getData(self::DISABLED);
    }
}
