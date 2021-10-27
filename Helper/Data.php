<?php

namespace Openpay\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use BusinessLayer\Openpay\PaymentManager as PaymentManager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\DB\TransactionFactory;
use Openpay\Payment\Logger\Logger;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Cache\Manager as CacheManager;

/**
 * Class Data
 */
class Data extends AbstractHelper
{

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var WriterInterface */
    protected $configWriter;

    /** @var CheckoutSession */
    protected $checkoutSession;

    protected $orderCollection;

    /** @var InvoiceService */
    protected $invoiceService;

    /** @var TransactionFactory */
    protected $transactionFactory;

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var Logger */
    protected $logger;

    /** @var DateTime */
    protected $date;

    /** @var CacheManager */
    protected $cacheManager;

    /**
     * data constructor
     *
     * @param Context            $context
     * @param WriterInterface    $configWriter
     * @param CheckoutSession    $checkoutSession
     * @param OrderCollection    $checkoutSession
     * @param InvoiceService     $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param ConfigHelper       $configHelper
     * @param Logger             $logger
     * @param DateTime           $date
     * @param CacheManager       $cacheManager
     */
    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        CheckoutSession $checkoutSession,
        OrderCollection $orderCollection,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        ConfigHelper $configHelper,
        Logger $logger,
        DateTime $date,
        CacheManager $cacheManager
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->configWriter = $configWriter;
        $this->checkoutSession = $checkoutSession;
        $this->orderCollection = $orderCollection;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->date = $date;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Get min/max value from Api call
     *
     * @return json
     */
    public function getLimits($backofficeparams)
    {    
        $response = [];
        try {
            /** @var PaymentManager $sdk */
            $sdk = new PaymentManager($backofficeparams);

            $config = $sdk->getConfiguration();
            
            // get values from openpay pay api
            $minValue = ((int)$config->minPrice)/100;
            $maxValue = ((int)$config->maxPrice)/100;

            $this->cacheManager->clean($this->cacheManager->getAvailableTypes());
            $response = ['min' => $minValue, 'max' => $maxValue];
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $this->logger->debug($e->getMessage());
        }
        return $response;
    }

    /**
     * @return CheckoutSession
     */
    public function getCurrentQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Get all product skus from current quote
     *
     * @return []
     */
    public function getProductSkus()
    {
        $skus = [];
        $quote = $this->getCurrentQuote();
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $skus[] = $item->getSku();
        }
        return $skus;
    }

    /**
     * Get categories of all products which are on current quote
     *
     * @return []
     */
    public function getCategoriesOfProducts()
    {
        $categories = [];
        $quote = $this->getCurrentQuote();
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            $type = $item->getProduct()->getTypeId();
            if ($type == 'simple') {
                $categoryCollection = $item->getProduct()->getCategoryCollection();
                foreach ($categoryCollection as $category) {
                    $path = explode('/', $category->getPath());
                    $categories[] = $path;
                }
            }
        }

        $allCatIds = [];
        if (count($categories) > 0) {
            foreach ($categories as $ids) {
                foreach ($ids as $id) {
                    $allCatIds[] = $id;
                }
            }
            $allCatIds = array_unique($allCatIds);
        }

        return $allCatIds;
    }

    /**
     * Check if categories are excluded
     *
     * @return bool
     */
    public function checkForExcludeCategories($categories)
    {
        $catIds = [];
        if ($categories) {
            $catIds = explode(',', $categories);
            $categoryInQuote = $this->getCategoriesOfProducts();
            return empty(array_intersect($categoryInQuote, $catIds));
        }
        return true;
    }

    /**
     * Check if products are excluded
     *
     * @return bool
     */
    public function checkForExcludeProducts($products)
    {
        $skus = [];
        if ($products) {
            //check for space after comma and remove it
            if (strpos($products, ', ') !== false) {
                $products = str_replace(', ', ',', $products);
            }

            //explode array with comma seperator
            $skus = explode(',', $products);
            $productInQuote = $this->getProductSkus();
            return empty(array_intersect($productInQuote, $skus));
        }
        return true;
    }

    /**
     * function will get orders with pending status and has openpay payment method
     * and also applied filters to created_at
     * convert orders into cancelled order
     */
    public function checkOrder($backofficeParams)
    {
        $minutes = $backofficeParams['job_frequency'] ? $backofficeParams['job_frequency'] : 0;
        $collection = $this->orderCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('status', 'pending')
                ->addFieldToFilter(
                    'created_at',
                    [
                        'lt' => new \Zend_Db_Expr(
                            "DATE_SUB('" .
                            $this->date->gmtDate() . "', INTERVAL " . $minutes . " MINUTE
                            )"
                        )
                        ]
                );

        $collection->getSelect()
            ->join(
                ["sop" => "sales_order_payment"],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where('sop.method = ?', 'openpay');
    
        if ($collection->getSize() > 0) {
            $storeId = $this->configHelper->getStoreId();
            $backofficeParams = $this->configHelper->getBackendParams($storeId);

            /** @var PaymentManager $sdk */
            $sdk = new PaymentManager($backofficeParams);
            foreach ($collection as $order) {
                if ($order->getToken() != null) {
                    try {
                        $sdk->setUrlAttributes([$order->getToken()]);
                        $response = $sdk->getOrder();
                    } catch (\Exception $e) {
                        $message = $e->getMessage();
                        //if (strpos($message, 'Error 12704') !== false) {
                        //    $order->cancel();
                        //    $order->save();
                        //} else {
                            $this->logger->debug($e->getMessage());
                        //}
                    }
                    if ($response->orderStatus == 'Approved' && $response->planStatus == 'Active') {
                        //capture payment and generate invoice on magento
                        $this->generateInvoice($order);
                    } else {
                        $order->cancel();
                        $order->save();
                    }
                }
            }
        }
    }

    /**
     * @param $order
     *
     * Captured order and generate invoice
     */
    public function generateInvoice($order)
    {
        //capture the payment
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
        $invoice->register();

        $transaction = $this->transactionFactory->create()
        ->addObject($invoice)
        ->addObject($invoice->getOrder());

        $transaction->save();
    }
}
