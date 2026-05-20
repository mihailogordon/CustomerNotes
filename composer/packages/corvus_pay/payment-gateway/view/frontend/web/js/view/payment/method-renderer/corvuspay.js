/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
    ],
    function (Component, VaultEnabler, fullScreenLoader, urlBuilder) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'CorvusPay_PaymentGateway/payment/form',
                redirectAfterPlaceOrder: false
            },

            initialize: function () {
                this._super();
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                return this;
            },

            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            getData: function () {
                var data = {
                    'method': this.getCode()
                };

                this.vaultEnabler.visitAdditionalData(data);

                return data;
            },

            getCode: function () {
                return 'corvuspay';
            },

            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].vaultCode;
            },

            afterPlaceOrder: function () {
                fullScreenLoader.startLoader();
                window.location.replace(urlBuilder.build('corvuspay/payment'));
            },

            getDescription: function () {
                return window.checkoutConfig.payment[this.getCode()].description;
            },
        });
    }
);
