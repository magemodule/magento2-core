define([
    'ko',
    'Magento_Ui/js/grid/columns/multiselect'
], function (ko, Component) {
    'use strict';

    /**
     * this component exists for the purpose of keeping track of ALL selections so
     * that the grid can be a part of the actual form
     */
    return Component.extend({
        defaults: {
            allIds: []
        },
        selectAll: function() {
            var $self = this;
            $self._super();
            $self.allIds.forEach(function(value) {
                $self.selected.push(value);
            });

            return this;
        },
        deselectAll: function() {
            var $self = this;
            $self._super();
            $self.allIds.forEach(function(value) {
                $self.excluded.push(value);
            });

            return this;
        }
    });
});
