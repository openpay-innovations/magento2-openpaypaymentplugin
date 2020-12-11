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
                type: 'openpay',
                component: 'Openpay_Payment/js/view/payment/method-renderer/openpay'
            }
        );
        return Component.extend({});
    }
);