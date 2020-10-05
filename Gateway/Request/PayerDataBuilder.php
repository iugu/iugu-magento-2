<?php

namespace Iugu\Payment\Gateway\Request;

use Exception;
use Iugu\Payment\Gateway\SubjectReader;
use Magento\Checkout\Model\Cart;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Zend_Filter;

class PayerDataBuilder implements BuilderInterface
{
    const PAYER = 'payer';

    const EMAIL = 'email';

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
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ConfigInterface $config,
        SubjectReader $subjectReader,
        Cart $cart
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
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

        $address = $this->cart->getQuote()->getBillingAddress();

        $taxvat = null;

        if ($this->config->getValue('use_taxvat')) {
            $taxvat = Zend_Filter::filterStatic($this->cart->getQuote()->getCustomer()->getTaxvat(), 'Digits');
        } else {
            $taxvat = Zend_Filter::filterStatic($data['taxvat'], 'Digits');
        }

        return [
            self::EMAIL => $address->getEmail(),
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
