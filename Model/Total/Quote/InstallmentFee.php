<?php
namespace Iugu\Payment\Model\Total\Quote;

use Iugu\Payment\Model\Ui\ConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class InstallmentFee extends AbstractTotal
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * InstallmentFee constructor.
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if ($total->getSubtotal() && $quote->getPayment()->getMethod() === ConfigProvider::CC_CODE) {
            $installment = $this->checkoutSession->getIuguInstallment() ?? ['installmentAmount' => 0];

            $total->addTotalAmount($this->getCode(), $installment['installmentAmount']);
            $total->addBaseTotalAmount($this->getCode(), $installment['installmentAmount']);
        }

        return $this;
    }

    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if ($quote->getPayment()->getMethod() !== ConfigProvider::CC_CODE) {
            return [];
        }

        $installment = $this->checkoutSession->getIuguInstallment() ?? ['installmentAmount' => 0];

        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => $installment['installmentAmount']
        ];
    }
}
