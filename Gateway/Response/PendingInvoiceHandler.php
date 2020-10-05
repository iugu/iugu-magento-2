<?php
namespace Iugu\Payment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class PendingInvoiceHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface **/
        $payment = SubjectReader::readPayment($handlingSubject);
        /** @var Order $order */
        $order = $payment->getPayment()->getOrder();

        $invoice = $order->prepareInvoice();
        //$invoice->setRequestedCaptureCase(false);
        $invoice->setTransactionId($response['body']['invoice_id']);
        $invoice->register();

        $order->addRelatedObject($invoice);
    }
}
