<?php

namespace Openpay\Payment\Model\Config\Backend;

class AuthUser extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     */
    public function afterSave()
    { 
        if ($this->isValueChanged()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $configHelper = $objectManager->get('\Openpay\Payment\Helper\Config');
            $dataHelper = $objectManager->get('\Openpay\Payment\Helper\Data');
            $storeId = $configHelper->getStoreId();
            $backofficeParams = $configHelper->getBackendParams($storeId);
            $backofficeParams['auth_user'] = $this->getValue(); 
            $dataHelper->getLimits($backofficeParams);
        }
        return parent::afterSave();
    }
}