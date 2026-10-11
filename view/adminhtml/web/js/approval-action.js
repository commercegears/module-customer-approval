/**
 * CommerceGears Customer Approval: approve / reject buttons on the customer edit page.
 * Confirms (with an optional reason for rejections) and posts with the admin form key.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/prompt',
    'mage/dataPost'
], function ($, _, confirm, prompt, dataPost) {
    'use strict';

    return function (config, element) {
        var post = function (data) {
                dataPost().postData({action: config.url, data: data || {}});
            },
            buttons = function (accept, cancel) {
                return [{
                    text: config.cancelLabel || $.mage.__('Cancel'),
                    class: 'action-secondary action-dismiss',
                    click: cancel
                }, {
                    text: config.acceptLabel || $.mage.__('OK'),
                    class: 'action-primary action-accept',
                    click: accept
                }];
            };

        $(element).on('click', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            if (config.mode === 'reject') {
                prompt({
                    title: config.title,
                    content: config.message,
                    label: config.fieldLabel ? _.escape(config.fieldLabel) : '',
                    value: '',
                    // prompt: closeModal(true) confirms, closeModal() cancels
                    buttons: buttons(function () {
                        this.closeModal(true);
                    }, function () {
                        this.closeModal();
                    }),
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
                // confirm: closeModal(event, true) confirms, closeModal(event) cancels
                buttons: buttons(function (event) {
                    this.closeModal(event, true);
                }, function (event) {
                    this.closeModal(event);
                }),
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
