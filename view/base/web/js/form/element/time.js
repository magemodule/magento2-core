/*
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
 *  @author        MageModule admin@magemodule.com
 *  @copyright    2018 MageModule, LLC
 *  @license        https://www.magemodule.com/magento2-ext-license.html
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/date',
    'moment',
    'mage/translate'
], function(Component, moment, $t) {
    'use strict';

    /**
     * this component allows us to skip time validation and accepts our time as entered.
     * we use 'storeTimeZone' => 'Etc/GMT' because we want the users value to be accepted without tz conversion
     */
    return Component.extend({
        initialize: function() {
            this._super();
            this.options.showsDate = false;
            this.options.showsTime = true;
            this.options.timeOnly = true;
            this.options.currentText = $t('Now');
            this.outputDateTimeToISO = false;

            return this;
        },
        onValueChange: function(value) {
            this.shiftedValue(value);
        },
        onShiftedValueChange: function(shiftedValue) {
            this.value(shiftedValue);
        },
        formatValue(value) {
            var parts = value().split(" ");
            if (parts.length === 3) {
                parts.shift();

                return parts.join(" ");
            }

            return value;
        }
    });
});
