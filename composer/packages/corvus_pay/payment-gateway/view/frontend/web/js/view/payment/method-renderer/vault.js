/*browser:true*/
/*global define*/
define([
    'Magento_Vault/js/view/payment/method-renderer/vault',
], function (VaultComponent) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Magento_Vault/payment/form'
        },

        getToken: function () {
            return this.publicHash;
        },

        getMaskedCard: function () {
            return this.details['maskedPan'].substring(this.details['maskedPan'].length - 4);
        },

        getExpirationDate: function () {
            return this.details['expirationDate'];
        },

        getCardType: function () {
            return this.details['cardType'];
        }
    });
});
