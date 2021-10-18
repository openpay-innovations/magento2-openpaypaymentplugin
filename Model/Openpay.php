<?php

namespace Openpay\Payment\Model;

use Magento\Framework\DataObject as DataObject;
use BusinessLayer\Openpay\PaymentManager as PaymentManager;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Magento\Sales\Model\Order;
use Openpay\Payment\Logger\Logger as OpenpayLogger;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class Openpay
 *
 * Payment method class which will extend all feature of magento basic methods
 */
class Openpay extends \Magento\Payment\Model\Method\AbstractMethod
{

    /** @var string */
    protected $_code = 'openpay';

    /** @var bool */
    protected $_canCapture = true;

    /** @var bool */
    protected $_canRefund = true;

    /** @var bool */
    protected $_canRefundInvoicePartial = true;

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var OpenpayLogger */
    protected $openpayLogger;

    /**
     * openpay constructor
     *
     * @param Context                    $context
     * @param Registry                   $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory      $customAttributeFactory
     * @param Data                       $paymentData
     * @param ScopeConfigInterface       $scopeConfig
     * @param Logger                     $logger
     * @param ConfigHelper               $configHelper
     * @param OpenpayLogger              $openpayLogger
     * @param array                      $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ConfigHelper $configHelper,
        OpenpayLogger $openpayLogger,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->configHelper = $configHelper;
        $this->openpayLogger = $openpayLogger;
    }
    
    /**
     * Capture payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
        }
        $storeId = $this->configHelper->getStoreId();
        $backofficeParams = $this->configHelper->getBackendParams($storeId);
        $order = $payment->getOrder();
        $ids = [$order->getToken()];
        $others = new DataObject();
        $others->orderid = $order->getIncrementId();
        try {
            $sdk = new PaymentManager($backofficeParams);
            $sdk->setUrlAttributes($ids);
            $sdk->setShopdata(null, $others);
            $response = $sdk->getCapture();
            if ($response) {
                $payment->setTransactionId($response->orderId);
                $payment->save();
            }
        } catch (\Exception $e) {
            $this->openpayLogger->debug($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('SORRY! There is a problem. Please contact us.')
            );
        }
        return $this;
    }
    
    /**
     * Refund specified amount for payment
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }

        $order = $payment->getOrder();
        $isFullRefund = false;

        $totalPaid = $order->getTotalPaid();
        $totalOnlineRefund = $order->getTotalOnlineRefunded();

        $remainingAmount = $totalPaid - $totalOnlineRefund;
        if (round($remainingAmount, 6) == 0) {
            $isFullRefund = true;
        }
        $reduce = round((float)$amount, 2);
        $prices = [
            'newPrice' => 0,
            'reducePriceBy'=> ($reduce * 100),
            'isFullRefund' => $isFullRefund
        ];

        $storeId = $this->configHelper->getStoreId();
        $backofficeParams = $this->configHelper->getBackendParams($storeId);
        try {
            $sdk = new PaymentManager($backofficeParams);
            
            if($order->getToken()){
                $token = $order->getToken();            
            } else {
                $token = $payment->getParentTransactionId();                
            }
            
            $sdk->setUrlAttributes([$token]);
            $sdk->setShopdata(null, $prices);
            $response = $sdk->refund();
        } catch (\Exception $e) {
            $this->openpayLogger->debug($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('SORRY! There is a problem. Please contact us.')
            );
        }
        return $this;
    }
}
