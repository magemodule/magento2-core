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
 * @author       MageModule admin@magemodule.com 
 * @copyright   2018 MageModule
 * @license       http://www.magemodule.com/magento2-ext-license.html
 *
 */

namespace MageModule\Core\Framework\File;

use Magento\Framework\Exception\LocalizedException;

class Csv extends \Magento\Framework\File\Csv
{
    /**
     * @param string|null $delimiter
     *
     * @return bool
     */
    public function validateDelimiter($delimiter)
    {
        if ($delimiter == '\t') {
            $delimiter = "\t";
        }

        return $delimiter === null ||
               $delimiter === '' ||
               strlen($delimiter) === 1 ||
               $delimiter === "\t";
    }

    /**
     * @param string $enclosure
     *
     * @return bool
     */
    public function validateEnclosure($enclosure)
    {
        return $enclosure === null || $enclosure === '' || strlen($enclosure) === 1;
    }

    /**
     * @param string $delimiter
     *
     * @return \Magento\Framework\File\Csv
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setDelimiter($delimiter)
    {
        if (!$this->validateDelimiter($delimiter)) {
            throw new LocalizedException(
                __('CSV delimiters can only be one character in length unless using \t for "tab".')
            );
        }

        return parent::setDelimiter($delimiter);
    }

    /**
     * @param string $enclosure
     *
     * @return \Magento\Framework\File\Csv
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setEnclosure($enclosure)
    {
        if (!$this->validateEnclosure($enclosure)) {
            throw new LocalizedException(
                __('CSV field enclosures can only be one character in length.')
            );
        }

        return parent::setEnclosure($enclosure);
    }
}
