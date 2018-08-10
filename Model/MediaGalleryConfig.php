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

namespace MageModule\Core\Model;

use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class MediaGalleryConfig implements MediaGalleryConfigInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $baseMediaPath;

    /**
     * MediaGalleryConfig constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param string                $baseMediaPath
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        $baseMediaPath
    ) {
        $this->storeManager  = $storeManager;
        $this->baseMediaPath = $baseMediaPath;
    }

    /**
     * @return string
     */
    public function getBaseMediaPath()
    {
        return trim($this->baseMediaPath, DIRECTORY_SEPARATOR);
    }

    /**
     * @return string
     */
    public function getBaseTmpMediaPath()
    {
        return 'tmp/' . $this->getBaseMediaPath();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        $baseUrl = rtrim(
            $this->storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            DIRECTORY_SEPARATOR
        );

        return $baseUrl . DIRECTORY_SEPARATOR . $this->getBaseMediaPath();
    }

    /**
     * @param string $file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . DIRECTORY_SEPARATOR . $this->prepareFilename($file);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'tmp/' . $this->getBaseMediaPath();
    }

    /**
     * @param string $file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->prepareFilename($file);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . DIRECTORY_SEPARATOR . $this->prepareFilename($file);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function prepareFilename($file)
    {
        return ltrim(str_replace('\\', DIRECTORY_SEPARATOR, $file), DIRECTORY_SEPARATOR);
    }
}
