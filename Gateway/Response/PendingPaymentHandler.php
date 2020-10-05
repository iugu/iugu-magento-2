<?php
namespace Iugu\Payment\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class PendingPaymentHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var PaymentDataObjectInterface **/
        $payment = SubjectReader::readPayment($handlingSubject);

        $order = $payment->getPayment()->getOrder();
        $order->addCommentToStatusHistory(__('Waiting for payment'), Order::STATE_PROCESSING, false);

    }
}
