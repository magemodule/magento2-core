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

namespace MageModule\Core\Model\Entity\Attribute\Backend;

use MageModule\Core\Api\Data\MediaGalleryInterface;
use MageModule\Core\Model\MediaGalleryFactory;
use MageModule\Core\Model\MediaGalleryRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class MediaGallery extends \MageModule\Core\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var MediaGalleryFactory
     */
    private $objectFactory;

    /**
     * @var MediaGalleryRepository
     */
    private $repository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * MediaGallery constructor.
     *
     * @param MediaGalleryFactory    $objectFactory
     * @param MediaGalleryRepository $repository
     * @param SearchCriteriaBuilder  $searchCriteriaBuilder
     * @param ResourceConnection     $resource
     */
    public function __construct(
        MediaGalleryFactory $objectFactory,
        MediaGalleryRepository $repository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resource
    ) {
        parent::__construct($resource);

        $this->objectFactory         = $objectFactory;
        $this->repository            = $repository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return AbstractBackend
     */
    public function afterLoad($object)
    {
        $attribute = $this->getAttribute();

        $this->searchCriteriaBuilder->addFilter(
            MediaGalleryInterface::ATTRIBUTE_ID,
            $attribute->getAttributeId()
        );

        $this->searchCriteriaBuilder->addFilter(
            MediaGalleryInterface::ENTITY_ID,
            $object->getId()
        );

        $list = $this->repository->getList(
            $this->searchCriteriaBuilder->create()
        );

        $object->setData(
            $attribute->getAttributeCode(),
            $list->getItems()
        );

        return parent::afterLoad($object);
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return AbstractBackend
     * @throws CouldNotSaveException
     * @throws CouldNotDeleteException
     */
    public function afterSave($object)
    {
        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getAttributeCode();
        $value     = $object->getData($attrCode);

        if (is_array($value) && isset($value['images'])) {
            foreach ($value['images'] as $image) {
                /** @var MediaGalleryInterface|DataObject $media */
                $media = $this->objectFactory->create();
                $media->addData($image);
                $media->setValue($image['file']);
                $media->setEntityId($object->getId());
                $media->setAttributeId($attribute->getAttributeId());

                if ($media->getData('removed')) {
                    if ($media->getValueId()) {
                        $this->repository->delete($media);
                    }

                    continue;
                }

                if ($media instanceof DataObject && !$media->getValueId()) {
                    $media->unsetData(MediaGalleryInterface::VALUE_ID);
                }

                $this->repository->save($media);
            }
        }

        return parent::afterSave($object);
    }
}
