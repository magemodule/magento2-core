<?php

namespace MageModule\Core\Model\Eav\Entity\Attribute\Backend;

/**
 * This class only formats a string like a url key. It does not
 * generate unique keys or create url rewrites
 *
 * Class UrlKeyFormat
 *
 * @package MageModule\Core\Model\Eav\Entity\Attribute\Backend
 */
class UrlKeyFormat extends \MageModule\Core\Model\Eav\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * UrlKeyFormat constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Filter\FilterManager   $filterManager
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($resource);
        $this->filterManager = $filterManager;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return \MageModule\Core\Model\Eav\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getName();
        if ($object->getData($attrCode)) {
            $object->setData(
                $attrCode,
                $this->filterManager->translitUrl(
                    $object->getData($attrCode)
                )
            );
        }

        $this->validate($object);

        return parent::beforeSave($object);
    }
}
