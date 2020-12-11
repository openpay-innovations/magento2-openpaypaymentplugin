<?php

namespace Openpay\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Openpay\Payment\Helper\Data as DataHelper;

/**
 * Class customconfigprovider
 *
 * This class retreive all the admin configuration settings
 * We will use these values on frontend checkout page
 */
class CustomConfigProvider implements ConfigProviderInterface
{

    /** @var string */
    const METHOD_CODE = 'openpay';

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var DataHelper */
    protected $dataHelper;

    /**
     * customconfigprovider constructor
     *
     * @param ConfigHelper $configHelper
     * @param DataHelper   $dataHelper
     */
    public function __construct(
        ConfigHelper $configHelper,
        DataHelper $dataHelper
    ) {
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
    }

    public function getConfig()
    {
        $storeId = $this->configHelper->getStoreId();
        $categories = $this->configHelper->getExcludedCategories($storeId);
        $productSkus = $this->configHelper->getExcludedProducts($storeId);
        $isEnable = $this->configHelper->isModuleEnabled($storeId);
        if ($isEnable) {
            $isExcludeCategory = $this->dataHelper->checkForExcludeCategories($categories);
           
            if ($isExcludeCategory) {
                $isExcludeProduct = $this->dataHelper->checkForExcludeProducts($productSkus);
                if (!$isExcludeProduct) {
                    $isEnable = $isExcludeProduct;
                }
            } else {
                $isEnable = $isExcludeCategory;
            }
        }
        $config = [
            'payment' =>  [
                self::METHOD_CODE =>  [
                    'is_enable' => $isEnable,
                    'title' => $this->configHelper->getTitle($storeId),
                    'description' => $this->configHelper->getDescription($storeId),
                    'username' => $this->configHelper->getUsername($storeId),
                    'password' => $this->configHelper->getPassword($storeId),
                    'api_url' => $this->configHelper->getApiUrl($storeId),
                    'redirect_url' => $this->configHelper->getRedirectUrl($storeId),
                    'min' => $this->configHelper->getMinimum(),
                    'max' => $this->configHelper->getMaximum(),
                    'quote_id' => $this->dataHelper->getCurrentQuote()->getId(),
                    'openpaySrc' => 'https://static.openpay.com.au/brand/logo/amber-lozenge-logo.svg'
                ]
            ]
        ];
        return $config;
    }
}
