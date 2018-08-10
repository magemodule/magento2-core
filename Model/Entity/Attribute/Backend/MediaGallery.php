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
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;

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
     * MediaGallery constructor.
     *
     * @param MediaGalleryFactory    $objectFactory
     * @param MediaGalleryRepository $repository
     * @param ResourceConnection     $resource
     */
    public function __construct(
        MediaGalleryFactory $objectFactory,
        MediaGalleryRepository $repository,
        ResourceConnection $resource
    ) {
        parent::__construct($resource);

        $this->objectFactory = $objectFactory;
        $this->repository    = $repository;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return AbstractBackend
     * @throws CouldNotSaveException
     */
    public function afterSave($object)
    {
        //TODO still need to move image to specific directory
        $attribute = $this->getAttribute();
        $attrCode = $attribute->getAttributeCode();
        $value    = $object->getData($attrCode);

        if (is_array($value) && isset($value['images'])) {
            foreach ($value['images'] as $image) {
                /** @var MediaGalleryInterface $media */
                $media = $this->objectFactory->create(['data' => $image]);
                $media->setEntityId($object->getId());
                $media->setAttributeId($attribute->getAttributeId());
                $media->setValue($image['file']);

                if ($media instanceof DataObject && !$media->getValueId()) {
                    $media->unsetData(MediaGalleryInterface::VALUE_ID);
                }

                $this->repository->save($media);
            }
        }

        return parent::afterSave($object);
    }
}
