<?php

namespace Iugu\Payment\Gateway\Request;

use Exception;
use Iugu\Payment\Gateway\SubjectReader;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class BankSlipDataBuilder implements BuilderInterface
{
    const METHOD = 'method';
    const RESTRICT_PAYMENT_METHOD = 'restrict_payment_method';
    const EXTRA_DAYS = 'bank_slip_extra_days';

    /**
     * @var ConfigInterface
     */
    private $configBS;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * BankSlipDataBuilder constructor.
     * @param ConfigInterface $configBS
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ConfigInterface $configBS,
        SubjectReader $subjectReader
    ) {
        $this->configBS = $configBS;
        $this->subjectReader = $subjectReader;
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
        return [
            self::METHOD => 'bank_slip',
            self::RESTRICT_PAYMENT_METHOD => true,
            self::EXTRA_DAYS => (int) $this->configBS->getValue('extra_days')
        ];
    }
}
