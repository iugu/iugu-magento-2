<?php
namespace Iugu\Payment\Observer;

use Iugu\Payment\Helper\IuguHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
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
     * DataAssignObserver constructor.
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

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData('additional_data');

        $paymentInfo = $method->getInfoInstance();

        if (!empty($additionalData['iugu_card_token'])) {
            $paymentInfo->setAdditionalInformation(
                'iugu_card_token',
                $additionalData['iugu_card_token']
            );
        }

        if (!empty($additionalData['iugu_installment'])) {
            $installment = $this->checkoutSession->getIuguInstallment();
            $paymentInfo->setAdditionalInformation(
                'iugu_installment',
                $additionalData['iugu_installment']
            );
            $paymentInfo->setAdditionalInformation(
                'iugu_installment_calculated',
                $installment
            );
        }

        if (!empty($additionalData['taxvat'])) {
            $paymentInfo->setAdditionalInformation(
                'taxvat',
                $additionalData['taxvat']
            );
        }
    }
}
