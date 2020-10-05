<?php

namespace Iugu\Payment\Model\Ui;

use Iugu\Payment\Helper\IuguHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'iugu_payment';

    const CC_CODE = 'iugu_cc';

    const BS_CODE = 'iugu_bs';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ConfigInterface
     */
    private $configBS;

    /**
     * @var ConfigInterface
     */
    private $configCC;

    /**
     * @var IuguHelper
     */
    private $iuguHelper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * ConfigProvider constructor.
     * @param ConfigInterface $config
     * @param ConfigInterface $configBS
     * @param IuguHelper $iuguHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        ConfigInterface $config,
        ConfigInterface $configBS,
        ConfigInterface $configCC,
        IuguHelper $iuguHelper,
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->configBS = $configBS;
        $this->configCC = $configCC;
        $this->iuguHelper = $iuguHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $installment = $this->checkoutSession ? $this->checkoutSession->getIuguInstallment() : null;
        return [
            'payment' => [
                self::CODE => [
                    'isTest' => (bool) ($this->config->getValue('ambient') == 'test'),
                    'accountKey' => $this->config->getValue('account_key'),
                    'askTaxvat' => !$this->config->getValue('use_taxvat'),
                ],
                self::CC_CODE => [
                    'selectedInstallment' => $installment ? $installment['installment'] : 1,
                    'ccTypeAllowed' => explode(',', $this->configCC->getValue('cc_type_allowed'))
                ],
                self::BS_CODE => [
                    'extra_days' => $this->configBS->getValue('extra_days'),
                    'information'  => $this->configBS->getValue('information'),
                ]
            ]
        ];
    }
}
