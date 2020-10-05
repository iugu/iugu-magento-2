<?php
namespace Iugu\Payment\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Payment\Gateway\ConfigInterface;

class IuguHelper extends AbstractHelper
{
    /**
     * @var ConfigInterface
     */
    private $configCC;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * IuguHelper constructor.
     * @param Context $context
     * @param ConfigInterface $configCC
     * @param CheckoutSession $checkoutSession
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        Context $context,
        ConfigInterface $configCC,
        CheckoutSession $checkoutSession,
        PricingHelper $pricingHelper
    ) {
        $this->configCC = $configCC;
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context);
    }

    public function formatPriceCents($price)
    {
        return number_format($price, 2, '', '');
    }

    public function calculateInstallments()
    {
        $this->checkoutSession->unsIuguInstallment();
        $this->checkoutSession->getQuote()->collectTotals();
        $latestInterest = 0;
        $grandTotal = $this->checkoutSession->getQuote()->getGrandTotal();

        $maxInstallmentQty = (int) $this->configCC->getValue('max_installment_qty');
        $maxInstallmentQty = $this->verifyMaxInstallmentQty($maxInstallmentQty, $grandTotal);

        $installmentsResult = [];

        for ($x = 1; $x <= $maxInstallmentQty; $x++) {
            $interest = (float) str_replace(',', '.', $this->configCC->getValue('installment_' . $x));

            if ($interest > $latestInterest) {
                $latestInterest = $interest;
            } else {
                $interest = $latestInterest;
            }

            $installmentAmount = ($grandTotal * ($interest / 100));

            $installmentTotal = $grandTotal + $installmentAmount;

            $installmentValue = $installmentTotal / $x;

            $installmentValue = $this->pricingHelper->currency(number_format($installmentValue, 2), true, false);

            $installment = [
                'installment' => $x,
                'interest' => $interest,
                'text' => sprintf(__('%dx of %s %s'), $x, $installmentValue, $interest > 0 ? __('with interest') : __('without interest')),
                'grandTotal' => $installmentTotal,
                'installmentValue' => $installmentValue,
                'installmentAmount' => $installmentAmount
            ];

            $installmentsResult[$x] = $installment;
        }

        return $installmentsResult;
    }

    /**
     * @param int $maxInstallmentQty
     * @param float $grandTotal
     * @return int
     */
    private function verifyMaxInstallmentQty($maxInstallmentQty, $grandTotal)
    {
        $minInstallmentValue = (float) str_replace(',', '.', $this->configCC->getValue('min_installment_value'));

        $installmentValue = 0;
        $returnInstallmentQty = $maxInstallmentQty + 1;

        while ($installmentValue < $minInstallmentValue && $returnInstallmentQty > 1) {
            $returnInstallmentQty--;
            $installmentValue = $grandTotal / $returnInstallmentQty;
        }

        return $returnInstallmentQty;
    }
}
