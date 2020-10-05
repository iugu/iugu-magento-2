<?php

namespace Iugu\Payment\Model\Adminhtml\Source;

/**
 * Class PaymentAction
 */
class Installments implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $installments = [];

        for ($x = 1; $x <= 12; $x++) {
            $installments[] = [
                'value' => $x,
                'label' => $x
            ];
        }

        return $installments;
    }
}
