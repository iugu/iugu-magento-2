<?php

namespace Iugu\Payment\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Order invoice shipping total calculation model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class InstallmentFee extends AbstractTotal
{
    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $informations = $invoice->getOrder()->getPayment()->getAdditionalInformation();
        if (!empty($informations['iugu_installment_calculated'])) {
            //$invoice->setInstallmentFee(informations['iugu_installment_calculated']['installmentValue']);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $informations['iugu_installment_calculated']['installmentAmount']);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $informations['iugu_installment_calculated']['installmentAmount']);
        }
        return $this;
    }
}
