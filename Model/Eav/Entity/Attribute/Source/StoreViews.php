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

namespace MageModule\Core\Model\Eav\Entity\Attribute\Source;

class StoreViews extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Store\Ui\Component\Listing\Column\Store\Options
     */
    private $storeOptions;

    /**
     * StoreViews constructor.
     *
     * @param \Magento\Store\Ui\Component\Listing\Column\Store\Options $storeOptions
     */
    public function __construct(
        \Magento\Store\Ui\Component\Listing\Column\Store\Options $storeOptions
    ) {
        $this->storeOptions = $storeOptions;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options['All Store Views']['label'] = __('All Store Views');
            $this->_options['All Store Views']['value'] = 0;
            $this->_options = array_merge(
                $this->_options,
                $this->storeOptions->toOptionArray()
            );

            $this->_options = array_values($this->_options);
        }

        return $this->_options;
    }
}
