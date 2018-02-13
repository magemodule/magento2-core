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

namespace MageModule\Core\Helper\Db;

class Collection extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Helper\Context       $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->dateTime   = $dateTime;
        $this->localeDate = $localeDate;
    }

    /**
     * Takes today's date, in the current timezone, and converts it for filtering timestamp column
     *
     * @param string $date
     *
     * @return string
     */
    public function getFromDateFilter($date)
    {
        $timestamp = strtotime($date . ' 00:00:00') - $this->dateTime->calculateOffset($this->localeDate->getConfigTimezone());

        return $this->dateTime->gmtDate(null, $timestamp);
    }

    /**
     * Takes today's date, in the current timezone, and converts it for filtering timestamp column
     *
     * @param string $date
     *
     * @return string
     */
    public function getToDateFilter($date)
    {
        $timestamp = strtotime($date . ' 23:59:59') - $this->dateTime->calculateOffset($this->localeDate->getConfigTimezone());

        return $this->dateTime->gmtDate(null, $timestamp);
    }
}
