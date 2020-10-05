<?php
namespace Iugu\Payment\Block\Sales\Totals;

use Iugu\Payment\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class InstallmentFee extends Template
{
    protected $_order;
    protected $_source;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent :: __construct($context, $data);
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function getStore()
    {
        return $this->_order->getStore();
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $payment = $this->_order->getPayment();

        if ($payment->getMethod() !== ConfigProvider::CC_CODE) {
            return $this;
        }

        $additionalInformation = $payment->getAdditionalInformation();

        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'installment_fee',
                'strong' => false,
                'value' => $additionalInformation['iugu_installment_calculated']['installmentAmount'],
                'label' => __('Installment fee'),
            ]
        );

        $parent->addTotal($total, 'installment_fee');

        return $this;
    }
}
