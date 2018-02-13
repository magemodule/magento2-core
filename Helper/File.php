<?php
/**
 * Copyright (c) 2018 MageModule: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: http://www.magemodule.com/magento2-ext-license.html.
 *
 * If you did not receive a copy of the EULA and are unable to obtain it through
 * the web, please send a note to admin@magemodule.com so that we can mail
 * you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright     2018 MageModule
 * @license       http://www.magemodule.com/magento2-ext-license.html
 *
 */

namespace MageModule\Core\Helper;

class File extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $fileIo;

    /**
     * File constructor.
     *
     * @param \Magento\Framework\App\Helper\Context           $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->fileIo        = $fileIo;
    }

    /**
     * If the filepath begins with "/" it is considered to already be the absolute path. If it does
     * not begin with a "/" then the Magento root path will be prepended to the filepath
     *
     * @param string $filepath
     *
     * @return string
     */
    public function getAbsolutePath($filepath)
    {
        if (strpos($filepath, DIRECTORY_SEPARATOR) !== 0) {
            $filepath = rtrim($this->directoryList->getRoot(), DIRECTORY_SEPARATOR) .
                        DIRECTORY_SEPARATOR .
                        trim($filepath);
        }

        return $filepath;
    }

    /**
     * Is file exists
     *
     * @param string $file
     * @param bool   $onlyFile
     *
     * @return bool
     */
    public function fileExists($file, $onlyFile = true)
    {
        return $this->fileIo->fileExists($file, $onlyFile);
    }
}
