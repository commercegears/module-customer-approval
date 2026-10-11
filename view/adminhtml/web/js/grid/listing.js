/**
 * Softaware Customer Approval: approval queue grid with its own "nothing found" text instead of Magento's
 * generic "We couldn't find any records." (filters or search applied, or the last account was just handled).
 */
define([
    'underscore',
    'Magento_Ui/js/grid/listing'
], function (_, Listing) {
    'use strict';

    return Listing.extend({
        defaults: {
            template: 'Softaware_CustomerApproval/grid/listing',
            appliedFilters: {},
            searchQuery: '',
            imports: {
                appliedFilters: '${ $.provider }:params.filters',
                searchQuery: '${ $.provider }:params.search'
            },
            tracks: {
                appliedFilters: true,
                searchQuery: true
            }
        },

        /**
         * @returns {Boolean}
         */
        isFiltered: function () {
            var filters = _.omit(this.appliedFilters || {}, 'placeholder');

            return !!this.searchQuery || _.some(filters, function (value) {
                return _.isObject(value) ? _.some(value, Boolean) : !!value;
            });
        }
    });
});
