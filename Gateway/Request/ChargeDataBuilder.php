<?php

namespace Iugu\Payment\Gateway\Request;

use Exception;
use Iugu\Payment\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Item;

class ChargeDataBuilder implements BuilderInterface
{
    const ORDER_ID = 'order_id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws Exception
     */
    public function build(array $buildSubject)
    {
        /** @var PaymentDataObjectInterface $payment */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        return [
            self::ORDER_ID => $order->getOrderIncrementId()
        ];
    }
}
