<?php
namespace Iugu\Payment\Gateway\Response;

use Iugu\Payment\Model\Ui\ConfigProvider;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        $payment->setAdditionalInformation(ConfigProvider::CODE, $response['body']);
        $payment->setLastTransId($response['body']['invoice_id']);
    }
}
