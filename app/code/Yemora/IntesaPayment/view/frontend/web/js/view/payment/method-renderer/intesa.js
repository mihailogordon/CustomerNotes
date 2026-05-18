define([
    'Magento_Checkout/js/view/payment/default'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Yemora_IntesaPayment/payment/intesa'
        },

        redirectAfterPlaceOrder: false,

        getDescription: function () {
            return window.checkoutConfig.payment.intesa.description;
        },

        afterPlaceOrder: function () {
            window.location.replace(
                window.checkoutConfig.payment.intesa.redirectUrl +
                (window.checkoutConfig.payment.intesa.redirectUrl.indexOf('?') === -1 ? '?' : '&') +
                '_=' + Date.now()
            );
        }
    });
});
