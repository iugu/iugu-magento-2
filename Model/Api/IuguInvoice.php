<?php
namespace Iugu\Payment\Model\Api;

use Zend_Http_Client_Exception;
use Zend_Http_Response;

class IuguInvoice extends IuguBase
{
    const POSTFIX_INVOICE_CONSULT = 'api_postfix_invoice_consult';

    /**
     * @param $invoiceId
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    public function consult($invoiceId)
    {
        return parent::execute(self::POSTFIX_INVOICE_CONSULT, ['%id' => $invoiceId], parent::METHOD_GET);
    }
}
