/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (Abstract, quote, priceUtils) {
        "use strict";
        return Abstract.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Iugu_Payment/checkout/summary/installment-fee',
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function () {
                return this.isFullMode();
            },
            /**
             * Get pure value.
             */
            getPureValue: function () {
                var totals = quote.getTotals()();

                var installment = totals.total_segments.filter(segment => {
                    return segment.code === 'installment_fee';
                })

                return installment.length ? installment[0].value : 0;
            },

            /**
             * @return {*|String}
             */
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue());
            },

            canShow: function () {
                return this.getPureValue() > 0;
            }
        });
    }
);
