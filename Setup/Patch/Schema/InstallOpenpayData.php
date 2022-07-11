<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Openpay\Payment\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InstallOpenpayData implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */

    /** @var SalesSetupFactory */
    protected $salesSetupFactory;

    /**
     *
     * InstallData constructor
     *
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup, SalesSetupFactory $salesSetupFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        
        $orderTable = 'sales_order';

        //Order table
        $this->moduleDataSetup->getConnection()
            ->addColumn(
                $this->moduleDataSetup->getTable($orderTable),
                'token',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' =>'Openpay Plan'
                ]
            );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
