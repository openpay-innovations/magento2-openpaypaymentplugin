<?php

namespace Openpay\Payment\Cron;

use Openpay\Payment\Helper\Data as DataHelper;
use Openpay\Payment\Helper\Config as ConfigHelper;

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

    /**
     * configuration constructor
     *
     * @param DataHelper   $dataHelper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        DataHelper $dataHelper,
        ConfigHelper $configHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $storeId = $this->configHelper->getStoreId();
        $backofficeParams = $this->configHelper->getBackendParams($storeId);
        $this->dataHelper->getLimits($backofficeParams);
        return $this;
    }
}
