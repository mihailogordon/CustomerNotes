/*browser:true*/
/*global define*/
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
                type: 'corvuspay',
                component: 'CorvusPay_PaymentGateway/js/view/payment/method-renderer/corvuspay'
            }
        );

        return Component.extend({});
    }
);
