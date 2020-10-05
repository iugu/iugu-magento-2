<?php
namespace Iugu\Payment\Model;

use Exception;
use Iugu\Payment\Api\InvoiceManagementInterface;
use Iugu\Payment\Model\Api\IuguInvoice;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DB\TransactionFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\RefundInvoiceInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResource;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Service\InvoiceService;
use Zend_Http_Client_Exception;

class InvoiceManagement implements InvoiceManagementInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var IuguInvoice
     */
    private $iuguInvoice;

    /**
     * @var InvoiceFactory
     */
    private $invoiceFactory;

    /**
     * @var InvoiceResource
     */
    private $invoiceResource;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var RefundInvoiceInterface
     */
    private $invoiceRefunder;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * InvoiceManagement constructor.
     * @param Logger $logger
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceResource $invoiceResource
     * @param RefundInvoiceInterface $invoiceRefunder
     * @param TransactionFactory $transactionFactory
     * @param IuguInvoice $iuguInvoice
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Logger $logger,
        InvoiceFactory $invoiceFactory,
        InvoiceResource $invoiceResource,
        InvoiceService $invoiceService,
        PaymentFactory $paymentFactory,
        PaymentResource $paymentResource,
        RefundInvoiceInterface $invoiceRefunder,
        TransactionFactory $transactionFactory,
        IuguInvoice $iuguInvoice,
        DataObjectFactory $dataObjectFactory,
        OrderResource $orderResource
    ) {
        $this->logger = $logger;
        $this->invoiceFactory = $invoiceFactory;
        $this->invoiceResource = $invoiceResource;
        $this->invoiceRefunder = $invoiceRefunder;
        $this->invoiceService = $invoiceService;
        $this->paymentFactory = $paymentFactory;
        $this->paymentResource = $paymentResource;
        $this->transactionFactory = $transactionFactory;
        $this->iuguInvoice = $iuguInvoice;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->orderResource = $orderResource;
    }

    /**
     * {@inheritdoc}
     * @throws Zend_Http_Client_Exception
     */
    public function event($event, $data)
    {
        $this->logger->debug([$event, $data], null, true);

        if ($event == 'invoice.status_changed') {
            $invoiceId = $data['id'];
            $status = $data['status'];

            $invoices = $this->loadInvoice($invoiceId);

            switch ($status) {
                case 'paid':
                    return $this->pay($invoices);
                case 'refunded':
                    return $this->refund($invoices);
                case 'canceled':
                    return $this->cancel($invoices);
                default:
                    return json_encode(['error' => __('Status does not exists')]);
            }
        }
    }

    /**
     * @param $invoices
     * @return false|string
     */
    private function pay($invoices)
    {
        try {
            $magentoInvoice = $invoices->getMagentoInvoice();

            $magentoInvoice->setEmailSent(true);
            $magentoInvoice->pay();
            $order = $magentoInvoice->getOrder();
            $order->addCommentToStatusHistory(__('Captured amount of %1 online', $magentoInvoice->getGrandTotal()), Order::STATE_COMPLETE, true);
            $order->setState(Order::STATE_COMPLETE);

            $transaction = $this->transactionFactory->create();
            $transaction->addObject($magentoInvoice);
            $transaction->addObject($order);
            $transaction->save();

            return 'OK ' . __('paid');
        } catch (Exception $exception) {
            return json_encode(['error' => $exception->getMessage()]);
        }
    }

    /**
     * @param $invoices
     */
    private function refund($invoices)
    {
        $magentoInvoice = $invoices->getMagentoInvoice();

        $this->invoiceRefunder->execute($magentoInvoice->getId(), [], false);
    }

    private function cancel($invoices)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $magentoInvoice */
        $magentoInvoice = $invoices->getMagentoInvoice();
        $magentoInvoice->cancel();
        $order = $magentoInvoice->getOrder();
        $order->addCommentToStatusHistory(__('Canceled online'), Order::STATE_CANCELED, true);
        $transaction = $this->transactionFactory->create();
        $transaction->addObject($magentoInvoice);
        $transaction->addObject($order);
        $transaction->save();
    }

    /**
     * @param $invoiceId
     * @return DataObject
     * @throws Zend_Http_Client_Exception
     * @throws Exception
     */
    private function loadInvoice($invoiceId)
    {
        $result = $this->iuguInvoice->consult($invoiceId);

        if ($result->getStatus() !== 200) {
            throw new Exception($result->getMessage());
        }

        $iuguInvoice = $this->dataObjectFactory->create();
        $iuguInvoice->setData(json_decode($result->getBody(), true));

        $magentoInvoice = $this->invoiceFactory->create();
        $this->invoiceResource->load($magentoInvoice, $invoiceId, 'transaction_id');

        $invoices = $this->dataObjectFactory->create();
        $invoices->setData(['iugu_invoice' => $iuguInvoice, 'magento_invoice' => $magentoInvoice]);

        return $invoices;
    }
}
