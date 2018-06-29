<?php

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

class UrlKey extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * UrlKey constructor.
     *
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->filterManager = $filterManager;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($object->getData($attributeCode)) {
            $object->setData(
                $attributeCode,
                $this->filterManager->translitUrl(
                    $object->getData($attributeCode)
                )
            );
        }

        return $this;
    }
}
