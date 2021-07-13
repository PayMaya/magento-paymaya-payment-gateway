define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paymayanexgen_checkout',
                component: 'PayMayaNexGen_Payment/view/frontend/web/js/view/payment/method-renderer/paymayanexgen_checkout'
            },
            // other payment method renderers if required
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
