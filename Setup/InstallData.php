<?php

namespace Openpay\Payment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Class InstallData
 *
 * This class install orderId attribute to the sales order table
 */
class InstallData implements InstallDataInterface
{
    /** @var SalesSetupFactory */
    protected $salesSetupFactory;

    /**
     *
     * InstallData constructor
     *
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $orderTable = 'sales_order';

        //Order table
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'token',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Token'
                ]
            );

        $setup->endSetup();
    }
}
