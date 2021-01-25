<?php

namespace Openpay\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 *
 * Get admin configuration values as well as has some common functions
 * which we use on all other files
 */
class Config extends AbstractHelper
{

    /** @var string */
    const XML_PATH_IS_ENABLE = 'payment/openpay/active';

    /** @var string */
    const XML_PATH_PAYMENT_MODE = 'payment/openpay/payment_mode';

    /** @var string */
    const XML_PATH_TITLE = 'payment/openpay/title';

    /** @var string */
    const XML_PATH_DESCRIPTION = 'payment/openpay/description';

    /** @var string */
    const XML_PATH_USERNAME = 'payment/openpay/auth_user';

    /** @var string */
    const XML_PATH_PASSWORD = 'payment/openpay/auth_token';

    /** @var string */
    const XML_PATH_API_URL = 'payment/openpay/service_url';

    /** @var string */
    const XML_PATH_REDIRECT_URL = 'payment/openpay/handover_url';

    /** @var string */
    const XML_PATH_MINIMUM = 'payment/openpay/minimum';

    /** @var string */
    const XML_PATH_MAXIMUM = 'payment/openpay/maximum';

    /** @var string */
    const XML_PATH_DISABLE_CATEGORIES = 'payment/openpay/disable_categories';

    /** @var string */
    const XML_PATH_DISABLE_PRODUCTS = 'payment/openpay/disable_products';

    /** @var string */
    const XML_PATH_JOB_FREQUENCY = 'payment/openpay/job_frequency';
        
    /** @var string */
    const XML_PATH_REGION = 'payment/openpay/region';

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * config constructor
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * Get module is enable or disable
     *
     * @return bool
     */
    public function isModuleEnabled($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_IS_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get payment mode whether it is test or live
     *
     * @return string
     */
    public function getPaymentMode($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_PAYMENT_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * get title of the payment method
     *
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get description of the payment method
     *
     * @return string
     */
    public function getDescription($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Auth user
     *
     * @return string
     */
    public function getUsername($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_USERNAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Auth Token
     *
     * @return string
     */
    public function getPassword($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_PASSWORD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Api Url
     *
     * @return string
     */
    public function getApiUrl($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_API_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Redirect Url
     *
     * @return string
     */
    public function getRedirectUrl($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_REDIRECT_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Job frequency
     * It will use for getting orders which will be older than this frequency
     *
     * @return int
     */
    public function getJobFrequency($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_JOB_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get minimum value
     *
     * @return int
     */
    public function getMinimum($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_MINIMUM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get maximum value
     *
     * @return int
     */
    public function getMaximum($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_MAXIMUM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getRegion($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_REGION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * This function will extract all the values of the backend form
     * $backendParams will create new array from all the values
     *
     * @return []
     */
    public function getBackendParams($storeId = null)
    {
        $storeId = $this->getStoreId();
        $backendParams = [
            'payment_mode' => $this->getPaymentMode($storeId),
            'auth_user' => $this->getUsername($storeId),
            'auth_token' => $this->getPassword($storeId),
            'minimum' => $this->getMinimum($storeId),
            'maximum' => $this->getMaximum($storeId),
            'job_frequency' => $this->getJobFrequency($storeId),
            'region' => $this->getRegion($storeId)
        ];
        return $backendParams;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * list of all categories
     *
     * @return string
     */
    public function getExcludedCategories($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_DISABLE_CATEGORIES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * list of all products
     *
     * @return string
     */
    public function getExcludedProducts($storeId = null)
    {
        return $this->getScopeConfig()->getValue(
            self::XML_PATH_DISABLE_PRODUCTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
