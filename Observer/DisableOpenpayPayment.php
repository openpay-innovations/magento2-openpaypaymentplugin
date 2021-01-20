<?php

namespace Openpay\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Openpay\Payment\Helper\Config as ConfigHelper;

/**
 * Class DisableOpenpayPayment
 *
 * This class will disable Openpay if grantotal price is not between limit values
 */
class DisableOpenpayPayment implements ObserverInterface
{
    /** @var ConfigHelper */
    protected $configHelper;
    
    /**
     * disableopenpaypayment constructor
     *
     * @param ConfigHelper  $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;    
    }
    
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $hidePaymentMetod = false;
        if ($quote) {
            $total = $quote->getGrandTotal();
            $hidePaymentMetod = false;
            $storeId = $this->configHelper->getStoreId();
            $min = $this->configHelper->getMinimum($storeId);
            $max = $this->configHelper->getMaximum($storeId);
            
            if ($total < $min || $total > $max) {
                $hidePaymentMetod = true;
            }
            
            if($observer->getEvent()->getMethodInstance()->getCode() == "openpay" && $hidePaymentMetod == true){
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        } 
    }
}