<?php

namespace Openpay\Payment\Model;

use Magento\Framework\DataObject as DataObject;
use BusinessLayer\Openpay\PaymentManager as PaymentManager;

class TokenizationApiManagement implements \Openpay\Payment\Api\TokenizationApiManagementInterface
{
    public function getTokenizationData($cartId, $email)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($cartId) {
            $quote = $objectManager->create('Magento\Quote\Api\CartRepositoryInterface')->getActive($cartId);
        }

        if (!$quote->getCustomerId()) {
            $quote->setCustomerEmail($email);
            $quote->setCustomerIsGuest(true);
            $quote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
            $quote->save();
        }
        
        $totals = [];
        $totals['retailerOrderNo']= $quote->getId();
        $totals['subtotal']= $quote->getSubtotal();
        $totals['shipping']= $quote->getShippingAddress()->getShippingAmount();
        $totals['tax'] = $quote->getShippingAddress()->getShippingTaxAmount();
        $totals['grandTotal'] = $quote->getGrandTotal();
        $totalsAmountInfo = json_encode($totals);
        
        $layout = $objectManager->create('Magento\Framework\View\LayoutInterface');
        $others = new DataObject();
        $others->origin = "Online";
        $others->planCreationType = "pending";
        $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $others->merchantRedirectUrl = $storeManager->getStore()->getUrl('openpay/payment/reside');
        $others->cancleUrl = $storeManager->getStore()->getUrl('openpay/payment/reside');
        $others->merchantFailUrl = $storeManager->getStore()->getUrl('openpay/payment/reside');
        $logger = $objectManager->create('Openpay\Payment\Logger\Logger');
        $logger->info($totalsAmountInfo);
        
        // //getting values from back office configuration on the basis of store id
        $configHelper = $objectManager->create('Openpay\Payment\Helper\Config');
        $storeId = $configHelper->getStoreId();
        $backofficeParams = $configHelper->getBackendParams($storeId);

        try {
            /** @var PaymentManager */
            $sdk = new PaymentManager($backofficeParams);
            $sdk->setShopdata($quote, $others, null, null, null);
            $token = $sdk->getToken();
            $sdk->setShopdata(null, null, $token, null, $backofficeParams);
            $paymentPage = $sdk->getPaymentPage('redirect', false, 'GET');
            return $paymentPage->endpointUrl;
        } catch (\Exception $ex) {
            $logger->debug($ex->getMessage());
            return false;
        }
    }
}
