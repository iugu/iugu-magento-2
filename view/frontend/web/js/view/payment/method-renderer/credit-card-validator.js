/* @api */
define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $('.payment-method-content input[type="number"]').on('keyup', function () {
        if ($(this).val() < 0) {
            $(this).val($(this).val().replace(/^-/, ''));
        }
    });

    $.each({
        'validate-cc-type': [
            function (number, element) {
                var brand = element.dataset.brand;
                var allowedTypes = element.dataset.allowedtypes.split(',');
                var i, l;
                for (i = 0, l = allowedTypes.length; i < l; i++) {
                    if (brand === allowedTypes[i]) { //eslint-disable-line eqeqeq
                        return true;
                    }
                }
                return false;
            },
            $.mage.__('This credit card is not allowed.')
        ],
        'validate-card-number': [

            /**
             * Validate credit card number based on mod 10
             *
             * @param {*} number - credit card number
             * @return {Boolean}
             */
            function (number) {
                return Iugu.utils.validateCreditCardNumber(number);
            },
            $.mage.__('Please enter a valid credit card number.')
        ],
        'validate-card-holder': [

            /**
             * Validate credit card number based on mod 10
             *
             * @param {*} number - credit card number
             * @return {Boolean}
             */
            function (name) {
                var iuguCardHolderName = name.trim().split(" ");
                var isValidFirstName = Iugu.utils.validateFirstName(iuguCardHolderName[0]);
                var isValidLastName = Iugu.utils.validateLastName(name.replace(iuguCardHolderName[0], ''));

                return isValidFirstName && isValidLastName;
            },
            $.mage.__('Please enter the firstname and surname.')
        ],
        'validate-card-date': [

            /**
             * Validate credit card expiration month
             *
             * @param {String} date - month
             * @return {Boolean}
             */
            function (date) {
                return Iugu.utils.validateExpirationString(date);
            },
            $.mage.__('Incorrect credit card expiration date.')
        ],
        'validate-card-cvv': [

            /**
             * Validate cvv
             *
             * @param {String} cvv - card verification value
             * @return {Boolean}
             */
            function (cvv, element) {
                return Iugu.utils.validateCVV(cvv, element.dataset.brand);
            },
            $.mage.__('Please enter a valid credit card verification number.')
        ]

    }, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
});
