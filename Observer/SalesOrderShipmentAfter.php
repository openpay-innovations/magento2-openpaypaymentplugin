<?php

namespace Openpay\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Openpay\Payment\Logger\Logger as OpenpayLogger;
use BusinessLayer\Openpay\PaymentManager as PaymentManager;

/**
 * Class SalesOrderShipmentAfter
 *
 * This class will execute dispatch api when shipment trigger
 */
class SalesOrderShipmentAfter implements ObserverInterface
{
    /** @var ConfigHelper */
    protected $configHelper;

    /** @var OpenpayLogger */
    protected $openpayLogger;

    /**
     * salesordershipmentafter constructor
     *
     * @param ConfigHelper  $configHelper
     * @param OpenpayLogger $openpayLogger
     */
    public function __construct(
        ConfigHelper $configHelper,
        OpenpayLogger $openpayLogger
    ) {
        $this->configHelper = $configHelper;
        $this->openpayLogger = $openpayLogger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $method = $order->getPayment()->getMethod();
        if ($method == 'openpay') {
            $storeId = $this->configHelper->getStoreId();
            $backofficeParams = $this->configHelper->getBackendParams($storeId);
            try {
                $sdk = new PaymentManager($backofficeParams);
                $sdk->setUrlAttributes([$order->getToken()]);
                $response = $sdk->dispatch();
            } catch (\Exception $e) {
                $this->openpayLogger->debug($e->getMessage());
            }
        }
    }
}
