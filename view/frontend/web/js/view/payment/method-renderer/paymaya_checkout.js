define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url'
    ],
    function (Component, redirectOnSuccessAction, urlBuilder) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'PayMaya_Payment/payment/paymaya_checkout'
            },
            afterPlaceOrder: function () {
                this.redirectAfterPlaceOrder = true;
                redirectOnSuccessAction.redirectUrl = urlBuilder.build('paymaya/checkout/index');
            }
            // add required logic here
        });
    }
);
