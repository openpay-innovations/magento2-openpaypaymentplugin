define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-payment-information',
        'mage/url',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, $, ko, additionalValidators, setPaymentInformationAction, url, customer, placeOrderAction, fullScreenLoader, messageList, totals) {
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
                    var subtotal = quote.totals().subtotal;
                    var min = window.checkoutConfig.payment.openpay.min;
                    var max = window.checkoutConfig.payment.openpay.max;
                    if (subtotal < min || subtotal > max) {
                        return false;
                    }
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
                $('.openpay-error').html('');
                
                var shippingAddress = quote.shippingAddress();              
                var billingAddress = quote.billingAddress();
                if (!shippingAddress.region && !billingAddress.region) {
                    $('.openpay-error').html('Please enter the state on both shipping and billing address');
                    return;
                }
                if (!shippingAddress.region) {
                    $('.openpay-error').html('Please enter the state on shipping address');
                    return;
                }

                if (!billingAddress.region) {
                    $('.openpay-error').html('Please enter the state on billing address');
                    return;
                }

                var quoteId = window.checkoutConfig.payment.openpay.quote_id;
                $('<form action="'+url.build('openpay/payment/tokenization')+'" method="POST">' +
                '<input type="hidden" name="cartId" value="' + quoteId + '" />' +
                '<input type="hidden" name="email" value="' + quote.guestEmail + '" />' +
                '</form>').appendTo('body').submit();
            }
        });
    }
);