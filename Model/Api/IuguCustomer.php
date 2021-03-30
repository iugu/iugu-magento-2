<?php
namespace Iugu\Payment\Model\Api;

use Zend_Http_Client_Exception;
use Zend_Http_Response;

class IuguCustomer extends IuguBase
{
    const POSTFIX = 'api_postfix_customer_create';

    /**
     * @param $body
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    public function create($body)
    {
        $this->setBody($body);
        return parent::execute(self::POSTFIX);
    }
}
