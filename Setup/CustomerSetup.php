<?php

namespace Iugu\Payment\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CustomerSetup extends EavSetup
{
    protected $eavConfig;

    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        parent :: __construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    public function installAttributes($customerSetup)
    {
        $this->installCustomerAttributes($customerSetup);
        $this->installCustomerAddressAttributes($customerSetup);
    }

    public function installCustomerAttributes($customerSetup)
    {
        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'iugu_id',
            [
                'label' => 'Iugu Customer Id',
                'system' => 0,
                'position' => 100,
                'sort_order' =>100,
                'visible' =>  false,
                'note' => '',
                'type' => 'varchar',
                'input' => 'text',
            ]
        );

        $customerSetup->getEavConfig()->getAttribute('customer', 'iugu_id')->save();
    }

    public function installCustomerAddressAttributes($customerSetup)
    {
    }

    public function getEavConfig()
    {
        return $this->eavConfig;
    }
}
