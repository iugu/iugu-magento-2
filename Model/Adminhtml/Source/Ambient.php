<?php

namespace Iugu\Payment\Model\Adminhtml\Source;

/**
 * Class PaymentAction
 */
class Ambient implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'test',
                'label' => __('Test')
            ],
            [
                'value' => 'production',
                'label' => __('Production')
            ]
        ];
    }
}
