<?php
namespace Iugu\Payment\Api;

interface InvoiceManagementInterface
{
    /**
     * On changed Invoice
     * @param string $event
     * @param mixed $data
     * @return string
     */
    public function event($event, $data);
}
