/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'iugu_cc',
                component: 'Iugu_Payment/js/view/payment/method-renderer/credit-card'
            },
            {
                type: 'iugu_bs',
                component: 'Iugu_Payment/js/view/payment/method-renderer/bank-slip'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
