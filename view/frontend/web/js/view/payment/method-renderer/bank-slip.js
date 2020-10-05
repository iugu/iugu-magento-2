/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Iugu_Payment/js/view/payment/method-renderer/base'
    ],
    function (
        $,
        ko,
        Base,
    ) {
        'use strict';

        return Base.extend({
            defaults: {
                template: 'Iugu_Payment/payment/bank-slip',
            },

            getCode: function () {
                return 'iugu_bs';
            },
        });
    }
)
