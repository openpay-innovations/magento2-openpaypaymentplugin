<?php

namespace Openpay\Payment\Cron;

use Openpay\Payment\Helper\Data as DataHelper;
use Openpay\Payment\Helper\Config as ConfigHelper;

/**
 * Class class CheckPendingOrder
 *
 * It execute using cron
 * It will check openpay order with pending state and changed their status to cancelled
 */
class CheckPendingOrder
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
        $this->dataHelper->checkOrder($backofficeParams);
        return $this;
    }
}
