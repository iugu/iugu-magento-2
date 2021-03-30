<?php
namespace Iugu\Payment\Helper;

use Iugu\Payment\Model\Api\IuguCustomer as IuguCustomerApi;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\ResourceModel\CustomerFactory as CustomerResourceFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Payment\Gateway\ConfigInterface;

class IuguHelper extends AbstractHelper
{
    /**
     * @var ConfigInterface
     */
    private $configCC;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerResourceFactory
     */
    private $customerResourceFactory;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var IuguCustomerApi
     */
    private $iuguCustomerApi;

    /**
     * IuguHelper constructor.
     * @param Context $context
     * @param ConfigInterface $configCC
     * @param CheckoutSession $checkoutSession
     * @param PricingHelper $pricingHelper
     * @param CustomerFactory $customerFactory
     * @param CustomerResource $customerResource
     * @param IuguCustomerApi $iuguCustomerApi
     * @param CustomerResourceFactory $customerResourceFactory
     */
    public function __construct(
        Context $context,
        ConfigInterface $configCC,
        CheckoutSession $checkoutSession,
        PricingHelper $pricingHelper,
        CustomerFactory $customerFactory,
        CustomerResource $customerResource,
        CustomerResourceFactory $customerResourceFactory,
        IuguCustomerApi $iuguCustomerApi
    ) {
        $this->configCC = $configCC;
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
        $this->customerResourceFactory = $customerResourceFactory;
        $this->iuguCustomerApi = $iuguCustomerApi;
        parent::__construct($context);
    }

    public function formatPriceCents($price)
    {
        return number_format($price, 2, '', '');
    }

    public function calculateInstallments()
    {
        $this->checkoutSession->unsIuguInstallment();
        $this->checkoutSession->getQuote()->collectTotals();
        $latestInterest = 0;
        $grandTotal = $this->checkoutSession->getQuote()->getGrandTotal();

        $maxInstallmentQty = (int) $this->configCC->getValue('max_installment_qty');
        $maxInstallmentQty = $this->verifyMaxInstallmentQty($maxInstallmentQty, $grandTotal);

        $installmentsResult = [];

        for ($x = 1; $x <= $maxInstallmentQty; $x++) {
            $interest = (float) str_replace(',', '.', $this->configCC->getValue('installment_' . $x));

            if ($interest > $latestInterest) {
                $latestInterest = $interest;
            } else {
                $interest = $latestInterest;
            }

            $installmentAmount = ($grandTotal * ($interest / 100));

            $installmentTotal = $grandTotal + $installmentAmount;

            $installmentValue = $installmentTotal / $x;

            $installmentValue = $this->pricingHelper->currency(number_format($installmentValue, 2), true, false);

            $installment = [
                'installment' => $x,
                'interest' => $interest,
                'text' => sprintf(__('%dx of %s %s'), $x, $installmentValue, $interest > 0 ? __('with interest') : __('without interest')),
                'grandTotal' => $installmentTotal,
                'installmentValue' => $installmentValue,
                'installmentAmount' => $installmentAmount
            ];

            $installmentsResult[$x] = $installment;
        }

        return $installmentsResult;
    }

    /**
     * @param int $maxInstallmentQty
     * @param float $grandTotal
     * @return int
     */
    private function verifyMaxInstallmentQty($maxInstallmentQty, $grandTotal)
    {
        $minInstallmentValue = (float) str_replace(',', '.', $this->configCC->getValue('min_installment_value'));

        $installmentValue = 0;
        $returnInstallmentQty = $maxInstallmentQty + 1;

        while ($installmentValue < $minInstallmentValue && $returnInstallmentQty > 1) {
            $returnInstallmentQty--;
            $installmentValue = $grandTotal / $returnInstallmentQty;
        }

        return $returnInstallmentQty;
    }

    /**
     * @return integer
     */
    public function getIuguCustomerId($customer)
    {
        if (!method_exists($customer, 'getDataModel')) {
            $customerId = $customer->getId();
            $customer = $this->customerFactory->create();
            $this->customerResource->load($customer, $customerId);
        }

        $iuguCustomerId = $customer->getIuguId() ?? $customer->getCustomAttribute('iugu_id');
        if (is_object($iuguCustomerId)) {
            $iuguCustomerId = $iuguCustomerId->getValue();
        }

        if (!empty($iuguCustomerId)) {
            return $iuguCustomerId;
        }

        $createData = [
            'email' => $customer->getEmail(),
            'name' => $customer->getName(),
            'cpf_cnpj' => $customer->getTaxvat()
        ];

        $iuguCustomer = $this->iuguCustomerApi->create($createData);
        $iuguCustomer = json_decode($iuguCustomer->getBody(), true);
        $iuguCustomerId = $iuguCustomer['id'];

        $customerData = $customer->getDataModel();
        $customerData->setCustomAttribute('iugu_id', $iuguCustomerId);
        $customer->updateData($customerData);
        $customerResource = $this->customerResourceFactory->create();
        $customerResource->saveAttribute($customer, 'iugu_id');

        return $iuguCustomerId;
    }
}
