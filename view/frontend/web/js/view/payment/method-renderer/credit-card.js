/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Iugu_Payment/js/view/payment/method-renderer/base',
        'Iugu_Payment/js/view/payment/method-renderer/credit-card-validator',
        'Iugu_Payment/js/view/checkout/summary/installment-fee',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_SalesRule/js/model/coupon',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/cart/cache',
        'Magento_Checkout/js/model/totals',
        'mage/storage',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/action/redirect-on-success',
        'Iugu_Payment/js/view/formatter/formatter',
        'mage/translate'
    ],
    function (
        $,
        ko,
        Base,
        validator,
        installmentFee,
        fullScreenLoader,
        quote,
        coupon,
        defaultTotal,
        cartCache,
        totalsService,
        storage,
        urlBuilder,
        redirectOnSuccessAction,
        Formatter,
        $t
    ) {
        'use strict';

        return Base.extend({
            defaults: {
                template: 'Iugu_Payment/payment/credit-card',
                iuguCardNumber: '',
                iuguCardHolderName: '',
                iuguCardExpirationDate: '',
                iuguCardCvv: '',
                ccTypeAllowed: window.checkoutConfig.payment.iugu_cc.ccTypeAllowed,
                selectedInstallment: window.checkoutConfig.payment.iugu_cc.selectedInstallment
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            cardBrand: ko.observable(false),

            iuguCardInstallments: ko.observableArray(),

            totals: quote.getTotals(),

            isInitialTotalsUpdate: true,
            ignoreNextTotalsUpdate: false,

            initObservable: function () {
                this._super()
                    .observe([
                        'selectedInstallment',
                        'iuguCardInstallments',
                        'iuguCardNumber',
                        'iuguCardHolderName',
                        'iuguCardExpirationDate',
                        'iuguCardCvv',
                        'iuguCardToken',
                        'cardBrand'
                    ]);
                this.selectedInstallment.subscribe(function (selectedKey) {
                    if (selectedKey === undefined) {
                        return;
                    }
                    /*if (this.isInitialTotalsUpdate) {
                        this.isInitialTotalsUpdate = false;
                        return;
                    }*/
                    this.ignoreNextTotalsUpdate = true;
                    this.isPlaceOrderActionAllowed(false);
                    let that = this;
                    this.updateInstallments(selectedKey, function (installments) {
                        cartCache.set('totals', null);
                        defaultTotal.estimateTotals();
                        let funcPopulate = that.populateCardInstallmentsOptions.bind(that);
                        funcPopulate(installments);
                        that.isPlaceOrderActionAllowed(true);
                    })
                }, this);

                quote.paymentMethod.subscribe(this.reupdateInstallments, this);

                coupon.isApplied.subscribe(this.reupdateInstallments, this);

                return this;
            },

            initialize: function () {
                this._super();

                this.reupdateInstallments();

                return this;
            },

            getCode: function () {
                return 'iugu_cc';
            },

            iuguRendered: function () {
                this._super();


                let that = this;

                //Timeout to fix magento delay to render elements
                var timeIntervalCardNumber = null;
                timeIntervalCardNumber = setInterval(function () {
                    let CardNumberElement = document.getElementById(that.getCode()+'CardNumber');
                    if (CardNumberElement) {
                        let formattedCardNumber = new Formatter(CardNumberElement, {
                            'pattern': '{{9999}} {{9999}} {{9999}} {{9999}}',
                            'persistent': false
                        });
                        clearInterval(timeIntervalCardNumber);
                    }
                }, 500)

                //Timeout to fix magento delay to render elements
                var timeIntervalExpirationDate = null;
                timeIntervalExpirationDate = setInterval(function () {
                    let ExpirationElement = document.getElementById(that.getCode()+'CardExpirationDate');
                    if (ExpirationElement) {
                        let formattedExpiration = new Formatter(ExpirationElement, {
                            'pattern': '{{99}}/{{9999}}',
                            'persistent': false
                        });
                        clearInterval(timeIntervalExpirationDate);
                    }
                }, 500)

                return this;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'iugu_card_token': this.iuguCardToken,
                        'iugu_installment': this.selectedInstallment(),
                        'taxvat': this.taxvat()
                    }
                };
            },

            populateCardInstallmentsOptions: function (installments) {
                const Installment = function (installment, text) {
                    this.installment = installment;
                    this.text = text;
                };
                let installmentArray = [];
                Object.keys(installments).map(key => {
                    installmentArray.push(
                        new Installment(installments[key].installment, installments[key].text)
                    );
                })
                this.iuguCardInstallments(installmentArray);
            },

            updateInstallments: function (installmentKey, callback) {
                installmentKey = installmentKey ? installmentKey : 1;
                let serviceUrl = urlBuilder.createUrl('/iugu-payment/credit-card/installment', {});

                storage.post(
                    serviceUrl,
                    JSON.stringify({
                        'installment': installmentKey
                    })
                ).done(function (response) {
                    callback(response);
                }).fail(function (response) {
                    console.error(response);
                });
                return this;
            },

            reupdateInstallments: function (data) {
                if (this.getCode() != this.isChecked()) {
                    return;
                }
                let that = this;
                fullScreenLoader.startLoader();
                this.updateInstallments(this.selectedInstallment(), function (installments) {
                    let funcPopulate = that.populateCardInstallmentsOptions.bind(that);
                    funcPopulate(installments);
                    fullScreenLoader.stopLoader();
                });
            },

            /**
             * Hook the validate function.
             * Original source: validate(); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            validate: function () {
                var superValidate = this._super();
                var
                    prefix = '#' + this.getCode(),
                    fields = [
                        'CardNumber',
                        'CardHolderName',
                        'CardExpirationDate',
                        'CardSecurityCode'
                    ]
                ;

                $(prefix + 'Form').validation();
                return superValidate && fields.map(f=>$(prefix+f).valid()).every(valid=>valid);
            },

            getCardBrand: function (value) {
                if (!value) {
                    if (typeof this.iuguCardNumber === "function") {
                        value = this.iuguCardNumber();
                    } else {
                        value = this.iuguCardNumber;
                    }
                }
                if (!value) {
                    return this.cardBrand;
                }
                this.cardBrand = Iugu.utils.getBrandByCreditCardNumber(value);
                return this.cardBrand;
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

            /**
             * Generate Iugu token before proceed the placeOrder process.
             *
             * @return {void}
             */
            generateTokenAndPerformPlaceOrderAction: function () {

                if (!this.validate()) {
                    return false;
                }

                this.startPerformingPlaceOrderAction();

                let iuguCardExpirationDate = this.iuguCardExpirationDate().split('/');
                let iuguCardHolderName = this.iuguCardHolderName();
                let splittedName = iuguCardHolderName.trim().split(" ");

                this.creditCard = Iugu.CreditCard(
                    this.iuguCardNumber(),
                    iuguCardExpirationDate[0],
                    iuguCardExpirationDate[1],
                    splittedName[0],
                    iuguCardHolderName.replace(splittedName[0], ''),
                    this.iuguCardCvv()
                );

                let self = this;
                let failHandler = this.buildFailHandler(self);



                Iugu.createPaymentToken(this.creditCard, function (response) {
                    if (response.errors) {
                        self.stopPerformingPlaceOrderAction();
                        response.responseText = JSON.stringify(response.errors);
                        Object.keys(response.errors).forEach(errorKey => {
                            let keys = {
                                'number': $t('The card number'),
                                'expiration': $t('The expiration date'),
                                'first_name': $t('The first name'),
                                'last_name': $t('The last name'),
                                'verification_value': $t('The CVV'),
                                'is_invalid': $t('is invalid'),
                                'record_invalid': '',
                                'Validation failed: Number is invalid': $t('Validation failed: Number is invalid')
                            }
                            failHandler({responseText: JSON.stringify({ message: keys[errorKey] + ' ' + keys[response.errors[errorKey]]})});
                        })
                    } else {
                        self.iuguCardToken = response.id;
                        self.isPlaceOrderActionAllowed(true);
                        self.getPlaceOrderDeferredObject()
                        .fail(failHandler)
                        .done(function (order_id) {
                            redirectOnSuccessAction.execute();
                        });
                    }
                });
            }
        });
    }
);
