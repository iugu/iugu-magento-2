<?php
namespace Iugu\Payment\Gateway;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class SubjectReader
 */
class SubjectReader
{
    /**
     * Reads response object from subject
     *
     * @param array $subject
     * @return object
     */
    public function readResponseObject(array $subject)
    {
        if (!isset($subject['response']) || !is_array($subject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        return $subject['response'];
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        if (!isset($subject['payment'])
            || !$subject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        return $subject['payment'];
    }
}
