<?php

namespace Iugu\Payment\Model\Adminhtml\Source;

/**
 * Class PaymentAction
 */
class CCTypes implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'amex',
                'label' => __('Amex')
            ],
            [
                'value' => 'visa',
                'label' => __('Visa')
            ],
            [
                'value' => 'diners',
                'label' => __('Diners')
            ],
            [
                'value' => 'mastercard',
                'label' => __('Mastercard')
            ]
        ];
    }
}
