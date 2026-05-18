define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push({
        type: 'intesa',
        component: 'Yemora_IntesaPayment/js/view/payment/method-renderer/intesa'
    });

    return Component.extend({});
});
