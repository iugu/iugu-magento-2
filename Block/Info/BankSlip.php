<?php
namespace Iugu\Payment\Block\Info;

use Magento\Framework\Phrase;
use Magento\Payment\Block\Info;

class BankSlip extends Info
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
        $specificInformation = [];
        $additionalInformations = $this->getInfo()->getAdditionalInformation();
        if (!empty($additionalInformations['iugu_payment']['message'])) {
            $specificInformation['Status'] = $additionalInformations['iugu_payment']['message'];
        }
        if (!empty($additionalInformations['iugu_installment_calculated'])) {
            $specificInformation['Installments'] = $additionalInformations['iugu_installment_calculated']['text'];
        }
        $specificInformation['Taxvat'] = $additionalInformations['taxvat'];
        $specificInformation['Invoice'] = sprintf('<a href="%s" target="_blank">%s</a>', $additionalInformations['iugu_payment']['url'], $additionalInformations['iugu_payment']['invoice_id']);

        return $this->_prepareSpecificInformation($specificInformation)->getData();
    }
}
