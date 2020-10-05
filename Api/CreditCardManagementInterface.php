<?php
namespace Iugu\Payment\Api;

interface CreditCardManagementInterface
{
    /**
     * Set a new installment
     * @param int $installment
     * @return string
     */
    public function setInstallment($installment);
}
