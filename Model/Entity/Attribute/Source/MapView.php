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

namespace MageModule\Core\Model\Entity\Attribute\Source;

/**
 * Class MapView
 *
 * @package MageModule\Core\Model\Entity\Attribute\Source
 */
class MapView extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const ROADMAP   = 'roadmap';
    const SATELLITE = 'satellite';
    const TERRAIN   = 'terrain';
    const HYBRID    = 'hybrid';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Roadmap'), 'value' => self::ROADMAP],
                ['label' => __('Satellite'), 'value' => self::SATELLITE],
                ['label' => __('Terrain'), 'value' => self::TERRAIN],
                ['label' => __('Hybrid'), 'value' => self::HYBRID],
            ];
        }

        return $this->_options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }
}
