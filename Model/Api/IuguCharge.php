<?php
namespace Iugu\Payment\Model\Api;

use Zend_Http_Client_Exception;
use Zend_Http_Response;

class IuguCharge extends IuguBase
{
    const POSTFIX = 'api_postfix_charge';

    /**
     * @param $body
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    public function charge($body)
    {
        $this->setData(self::BODY, $body);
        return parent::execute(self::POSTFIX);
    }
}
