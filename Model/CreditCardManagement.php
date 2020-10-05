<?php
namespace Iugu\Payment\Model;

use Iugu\Payment\Api\CreditCardManagementInterface;
use Iugu\Payment\Helper\IuguHelper;
use Magento\Checkout\Model\Session;

class CreditCardManagement implements CreditCardManagementInterface
{
    /**
     * @var IuguHelper
     */
    private $iuguHelper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * InstallmentFee constructor.
     * @param IuguHelper $iuguHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        IuguHelper $iuguHelper,
        Session $checkoutSession
    ) {
        $this->iuguHelper = $iuguHelper;
        $this->checkoutSession = $checkoutSession;
    }

    public function setInstallment($installment)
    {
        $installments = $this->iuguHelper->calculateInstallments();

        if (!isset($installments[$installment])) {
            $installment = 1;
        }

        $this->checkoutSession->setIuguInstallment($installments[$installment]);

        return $installments;
    }
}
