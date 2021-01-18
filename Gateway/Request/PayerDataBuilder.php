<?php

namespace Iugu\Payment\Gateway\Request;

use Exception;
use Iugu\Payment\Gateway\SubjectReader;
use Iugu\Payment\Helper\IuguHelper;
use Magento\Checkout\Model\Cart;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteRosource;
use Zend_Filter;

class PayerDataBuilder implements BuilderInterface
{
    const PAYER = 'payer';

    const EMAIL = 'email';

    const IUGU_CUSTOMER_ID = 'customer_id';

    const TAX_VAT = 'cpf_cnpj';

    const NAME = 'name';

    const TELEPHONE_PREFIX = 'phone_prefix';

    const TELEPHONE = 'phone';

    const ADDRESS = 'address';

    const POSTCODE = 'zip_code';

    const STREET = 'street';

    const STREET_NUMBER = 'number';

    const STREET_DISTRICT = 'district';

    const STREET_COMPLEMENT = 'complement';

    const CITY = 'city';

    const REGION = 'state';

    const COUNTRY = 'country';

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteRosource
     */
    private $quoteResource;

    /**
     * @var IuguHelper
     */
    private $iuguHelper;

    /**
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param QuoteFactory $quoteFactory
     * @param QuoteRosource $quoteResource
     * @param IuguHelper $iuguHelper
     * @param Cart $cart
     */
    public function __construct(
        ConfigInterface $config,
        SubjectReader $subjectReader,
        QuoteFactory $quoteFactory,
        QuoteRosource $quoteResource,
        IuguHelper $iuguHelper,
        Cart $cart
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->iuguHelper = $iuguHelper;
        $this->cart = $cart;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws Exception
     */
    public function build(array $buildSubject)
    {
        /** @var PaymentDataObjectInterface $payment */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();

        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $payment->getOrder()->getQuoteId());

        $address = $this->cart->getQuote()->getBillingAddress();

        $taxvat = null;

        $customer = $quote->getCustomer();

        if ($this->config->getValue('use_taxvat')) {
            $taxvat = Zend_Filter::filterStatic($customer->getTaxvat(), 'Digits');
        } else {
            $taxvat = Zend_Filter::filterStatic($data['taxvat'], 'Digits');
        }

        return [
            self::EMAIL => $address->getEmail(),
            self::IUGU_CUSTOMER_ID => $this->iuguHelper->getIuguCustomerId($customer),
            self::PAYER => [
                self::TAX_VAT => $taxvat,
                self::NAME => $address->getFirstname() . ' ' . $address->getLastname(),
                self::TELEPHONE_PREFIX => $this->getPhonePrefix($address->getTelephone()),
                self::TELEPHONE => $this->getPhone($address->getTelephone()),
                self::ADDRESS => [
                    self::POSTCODE => Zend_Filter::filterStatic($address->getPostcode(), 'Digits'),
                    self::STREET => $address->getStreetLine((int) $this->config->getValue('street_line')),
                    self::STREET_NUMBER => $address->getStreetLine((int) $this->config->getValue('street_number_line')),
                    self::STREET_DISTRICT => $address->getStreetLine((int) $this->config->getValue('street_district_line')),
                    self::STREET_COMPLEMENT => $address->getStreetLine((int) $this->config->getValue('street_complement_line')),
                    self::CITY => $address->getCity(),
                    self::REGION => $address->getRegionCode(),
                    self::COUNTRY => $address->getCountryId()
                ]
            ]
        ];
    }

    private function getPhonePrefix($telephone)
    {
        $telephone = Zend_Filter::filterStatic($telephone, 'Digits');
        return substr($telephone, 0, 2);
    }

    private function getPhone($telephone)
    {
        $telephone = Zend_Filter::filterStatic($telephone, 'Digits');
        return substr($telephone, 2);
    }
}
