<?php

namespace Openpay\Payment\Cron;

use Openpay\Payment\Helper\Data as DataHelper;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Magento\Framework\App\ResourceConnection as ResourceConnection;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class class LimitConfiguration
 *
 * It execute using cron
 * It will update the min and max value from api
 */
class LimitConfiguration
{

    /** @var DataHelper */
    protected $dataHelper;

    /** @var ConfigHelper */
    protected $configHelper;
    
    /** @var ResourceConnection */
    protected $resource;
    
    /** @var WriterInterface */
    protected $configWriter;

    /**
     * configuration constructor
     *
     * @param DataHelper   $dataHelper
     * @param ConfigHelper $configHelper
     * @param ResourceConnection $resource
     * @param WriterInterface    $configWriter
     */
    public function __construct(
        DataHelper $dataHelper,
        ConfigHelper $configHelper,
        ResourceConnection $resource,
        WriterInterface $configWriter
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->resource = $resource;
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $scopeConfig = $this->configHelper->getScopeConfig();
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            'core_config_data'
        )->where(
            'path = ?',
            'payment/openpay/minimum'
        );
        $rows = $connection->fetchAll($select);
        $backofficeParams = [];
        foreach ($rows as $row) {
            $enabled = $scopeConfig->getValue(
                ConfigHelper::XML_PATH_IS_ENABLE,
                $row['scope'],
                $row['scope_id']
            );
            if ($enabled !== '1') {
                continue;
            }
            $backofficeParams['payment_mode'] = $scopeConfig->getValue(
                ConfigHelper::XML_PATH_PAYMENT_MODE,
                $row['scope'],
                $row['scope_id']
            );
            $backofficeParams['region'] = $scopeConfig->getValue(
                ConfigHelper::XML_PATH_REGION,
                $row['scope'],
                $row['scope_id']
            );
            $backofficeParams['auth_user'] = $scopeConfig->getValue(
                ConfigHelper::XML_PATH_USERNAME,
                $row['scope'],
                $row['scope_id']
            );
            $backofficeParams['auth_token'] = $scopeConfig->getValue(
                ConfigHelper::XML_PATH_PASSWORD,
                $row['scope'],
                $row['scope_id']
            );
            $values = $this->dataHelper->getLimits($backofficeParams);

            $this->configWriter->save(ConfigHelper::XML_PATH_MINIMUM, $values['min'], $row['scope'], $row['scope_id']);
            $this->configWriter->save(ConfigHelper::XML_PATH_MAXIMUM, $values['max'], $row['scope'], $row['scope_id']); 
        }

        return $this;
    }
}
