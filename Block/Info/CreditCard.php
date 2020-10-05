<?php
namespace Iugu\Payment\Block\Info;

use Magento\Framework\Phrase;
use Magento\Payment\Block\Info;

class CreditCard extends Info
{
    /**
     * @var string
     */
    protected $_template = 'Iugu_Payment::info/default.phtml';

    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Returns value view
     *
     * @param string $field
     * @param string $value
     * @return string | Phrase
     */
    protected function getValueView($field, $value)
    {
        return parent::getValueView($field, $value);
    }

    public function getSpecificInformation()
    {
        $additionalInformations = $this->getInfo()->getAdditionalInformation();
        $specificInformation = [
            'Status' => $additionalInformations['iugu_payment']['message'],
            'Installments' => $additionalInformations['iugu_installment_calculated']['text'],
            'Taxvat' => $additionalInformations['taxvat'],
            'Invoice' => sprintf('<a href="%s" target="_blank">%s</a>', $additionalInformations['iugu_payment']['url'], $additionalInformations['iugu_payment']['invoice_id'])
        ];
        return $this->_prepareSpecificInformation($specificInformation)->getData();
    }
}
