define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-payment-information',
        'mage/url',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (Component, quote, urlBuilder, storage, $, ko, additionalValidators, setPaymentInformationAction, url, customer, placeOrderAction, fullScreenLoader, messageList, totals, getPaymentInformationAction, errorProcessor) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Openpay_Payment/payment/openpay'
            },
            isCustomerLoggedIn: customer.isLoggedIn,
            totals: quote.getTotals(),
            openpaySrc: window.checkoutConfig.payment.openpay.openpaySrc,
            initObservable: function() {
                var self = this._super();
                return self;
            },
            isEnable : function() {
                var isModuleEnable = window.checkoutConfig.payment.openpay.is_enable;
                if (isModuleEnable == 1) {
                    return true;
                } 
                return false;
            },
            getTitle : function() {
                return window.checkoutConfig.payment.openpay.title;
            },
            getDescription : function() {
                return window.checkoutConfig.payment.openpay.description;
            },

            /**
             * @override
             */
             /** Process Payment */
            prepareForTokenization: function (context, event) { 
                if (!additionalValidators.validate()) {
                    return;
                }          
                var billingAddress = quote.billingAddress();
                var shippingAddress = quote.shippingAddress();
                var paymentMethodId = $(".payment-methods input[type='radio']:checked").attr('id');
                var isBillingAddressSame = $("#billing-address-same-as-shipping-"+paymentMethodId).prop('checked');
                var quoteId = window.checkoutConfig.payment.openpay.quote_id;
                var emailId = '';
                
                if (quote.guestEmail) {
                    emailId = quote.guestEmail;
                } else {
                    emailId = window.checkoutConfig.customerData.email
                }

                fullScreenLoader.startLoader();
                if (!isBillingAddressSame) {
                    /**
                     * Checkout for guest and registered customer.
                     */
                    var serviceUrl,
                    payload,
                    messageContainer;

                    if (!customer.isLoggedIn()) {
                        serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/billing-address', {
                            cartId: quote.getQuoteId()
                        });
                        payload = {
                            cartId: quote.getQuoteId(),
                            address: quote.billingAddress()
                        };
                    } else {
                        serviceUrl = urlBuilder.createUrl('/carts/mine/billing-address', {});
                        payload = {
                            cartId: quote.getQuoteId(),
                            address: quote.billingAddress()
                        };
                    }
                    storage.post(
                        serviceUrl, JSON.stringify(payload)
                    ).done(
                        function () {
                            var tokenizationUrl = urlBuilder.createUrl('/payment/tokenization', {});
                            var customPayload = {
                                cartId: quoteId,
                                email: emailId
                            };
                            storage.post(
                                tokenizationUrl, 
                                JSON.stringify(customPayload),
                                true
                            ).done(
                                function (response) {
                                    fullScreenLoader.stopLoader();
                                    window.location.href = response;
                                }
                            ).fail(
                                function (response) {
                                    fullScreenLoader.stopLoader();
                                    errorProcessor.process(response, messageContainer);
                                }
                            );
                        }
                    ).fail(
                        function (response) {
                            fullScreenLoader.stopLoader();
                            errorProcessor.process(response, messageContainer);
                        }
                    );
                } else {
                    var tokenizationUrl = urlBuilder.createUrl('/payment/tokenization', {});
                    var customPayload = {
                        cartId: quoteId,
                        email: emailId
                    };
                    storage.post(
                        tokenizationUrl, 
                        JSON.stringify(customPayload),
                        true
                    ).done(
                        function (response) {
                            fullScreenLoader.stopLoader();
                            window.location.href = response;
                        }
                    ).fail(
                        function (response) {
                            fullScreenLoader.stopLoader();
                            errorProcessor.process(response, messageContainer);
                        }
                    );
                }
            },
            getWidgetEnabled: function () {
                var widgetEnabled = window.checkoutConfig.widgetEnabled;
                if (widgetEnabled !== 1) {
                    return 0;
                } else {
                    return 1;
                }
            },

            getInstalmentText: function () {
                var widgetEnabled = window.checkoutConfig.widgetEnabled;
                if (widgetEnabled !== 1) {
                    return '';
                }
                var widgetSettingConfig = window.checkoutConfig.widgetSetting;
                return widgetSettingConfig.instalment_text;
            },

            getRedirectText: function () {
                var widgetEnabled = window.checkoutConfig.widgetEnabled;
                if (widgetEnabled !== 1) {
                    return '';
                }
                var widgetSettingConfig = window.checkoutConfig.widgetSetting;
                return widgetSettingConfig.redirect_text;
            },
            
            getMonthText: function () {
                var widgetEnabled = window.checkoutConfig.widgetEnabled;
                if (widgetEnabled !== 1) {
                    return '';
                }
                var widgetSettingConfig = window.checkoutConfig.widgetSetting;
                return widgetSettingConfig.month_text;
            },

            placeOrder: function () {
                this.prepareForTokenization();
            }
        });
    }
)