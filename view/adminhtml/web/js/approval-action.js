/**
 * Softaware Customer Approval: approve / reject buttons on the customer edit page.
 * Confirms (with an optional reason for rejections) and posts with the admin form key.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/prompt',
    'mage/dataPost'
], function ($, confirm, prompt, dataPost) {
    'use strict';

    return function (config, element) {
        var post = function (data) {
            dataPost().postData({action: config.url, data: data || {}});
        };

        $(element).on('click', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            if (config.mode === 'reject') {
                prompt({
                    title: config.title,
                    content: config.message,
                    value: '',
                    actions: {
                        confirm: function (reason) {
                            post({reason: reason || ''});
                        }
                    }
                });
                return false;
            }
            confirm({
                title: config.title,
                content: config.message,
                actions: {
                    confirm: function () {
                        post();
                    }
                }
            });
            return false;
        });
    };
});
