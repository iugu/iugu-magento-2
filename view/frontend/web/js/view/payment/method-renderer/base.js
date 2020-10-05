define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Iugu_Payment/js/view/formatter/formatter',
        'Iugu_Payment/js/view/payment/method-renderer/taxvat-validator',
        'Magento_Checkout/js/action/redirect-on-success',
    ],
    function (
        $,
        ko,
        Component,
        errorProcessor,
        fullScreenLoader,
        Formatter,
        TaxvatValidator,
        redirectOnSuccessAction,
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                'ask_taxvat': window.checkoutConfig.payment.iugu_payment.askTaxvat
            },

            taxvat: ko.observable(),
            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function () {
                return 'iugu_payment';
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'taxvat': this.taxvat()
                    }
                };
            },

            iuguRendered: function () {
                Iugu.setAccountID(this.getAccountKey());
                Iugu.setTestMode(this.isTest());
                setTimeout(function () {
                    Iugu.setup(); }, 3000);

                if (!this.ask_taxvat) {
                    return this;
                }

                let that = this;

                //Timeout to fix magento delay to render elements
                var timeIntervalTaxvat = null;
                timeIntervalTaxvat = setInterval(function () {
                    let element = document.getElementById(that.getCode()+'Taxvat');
                    if (element) {
                        let formatted = new Formatter(element, {
                            'patterns': [
                                { '^.{0,11}$': '{{999}}.{{999}}.{{999}}-{{99}}' },
                                { '^.{12,}$' : '{{99}}.{{999}}.{{999}}.{{9999}}/{{99}}' },
                            ],
                            'persistent': false
                        });
                        clearInterval(timeIntervalTaxvat);
                    }
                }, 500)

                return this;
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function () {
                return true;
            },

            getAccountKey: function () {
                return window.checkoutConfig.payment.iugu_payment.accountKey;
            },

            /**
             * Checks if test is turned on
             *
             * @return {boolean}
             */
            isTest: function () {
                return window.checkoutConfig.payment.iugu_payment.isTest;
            },

            /**
             * Creates a fail handler for given context
             *
             * @return {boolean}
             */
            buildFailHandler(context) {
                return function (response) {
                    errorProcessor.process(response, context.messageContainer);
                    fullScreenLoader.stopLoader();
                    context.isPlaceOrderActionAllowed(true);
                }
            },

            /**
             * Hook the validate function.
             * Original source: validate(); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            validate: function () {
                if (!this.ask_taxvat) {
                    return true;
                }
                var
                    prefix = '#' + this.getCode(),
                    fields = [
                        'Taxvat'
                    ]
                ;

                $(prefix + 'Form').validation();
                return fields.map(f=>$(prefix+f).valid()).every(valid=>valid);
            },

            /**
             * Start performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            startPerformingPlaceOrderAction: function () {
                fullScreenLoader.startLoader();
            },

            /**
             * Stop performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            stopPerformingPlaceOrderAction: function () {
                fullScreenLoader.stopLoader();
            },

            placeOrder() {
                if (!this.validate()) {
                    return false;
                }

                this.startPerformingPlaceOrderAction();

                var failHandler = this.buildFailHandler(this);

                this.getPlaceOrderDeferredObject()
                .fail(failHandler)
                .done(function (order_id) {
                    redirectOnSuccessAction.execute();
                });

            }

        });
    }
);
