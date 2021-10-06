<?php

namespace Openpay\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use BusinessLayer\Openpay\PaymentManager as PaymentManager;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Openpay\Payment\Helper\Data as DataHelper;
use Openpay\Payment\Logger\Logger;

/**
 * Class Reside
 *
 * It executes when payment will be complete from openpay
 * It will redirect to the page on the basis of result
 * If there will be successful payment, it will capture the payment as well as invoice will be generate
 * and then redirect to the success page of magento
 * If not, it will redirect to the cart page with some error message
 */
class Reside extends Action
{

    /** @var CartRepositoryInterface */
    protected $quoteRepository;

    /** @var CartManagementInterface */
    protected $quoteManagement;

    /** @var OrderRepository */
    protected $orderRepository;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var CheckoutSession */
    protected $checkoutSession;

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var DataHelper */
    protected $dataHelper;

    /** @var Logger */
    protected $logger;

    /**
     * reside constructor
     *
     * @param Context                 $context
     * @param CartRepositoryInterface $quoteRepository
     * @param CartManagementInterface $quoteManagement
     * @param OrderRepository         $orderRepository
     * @param ManagerInterface        $messageManager
     * @param CheckoutSession         $checkoutSession
     * @param ConfigHelper            $configHelper
     * @param DataHelper              $dataHelper
     * @param Logger                  $logger
     */
    public function __construct(
        Context $context,
        CartRepositoryInterface $quoteRepository,
        CartManagementInterface $quoteManagement,
        OrderRepository $orderRepository,
        ManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        ConfigHelper $configHelper,
        DataHelper $dataHelper,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $status = $this->getRequest()->getParam('status');
        $planid = $this->getRequest()->getParam('planid');
        $cartId = $this->getRequest()->getParam('orderid');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($status == "LODGED") {
            if ($cartId) {
                $quote = $this->quoteRepository->getActive($cartId);
                if ($quote->getCustomerIsGuest()) {
                    $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
                    $quote->getBillingAddress()->setEmail($quote->getCustomerEmail());
                }
                
                $quote->getPayment()->importData(['method' => 'openpay']);
                $quote->save();
                $quote->collectTotals();
        
                //call get order api
                $purchasePrice = 0;
                try {
                    $storeId = $this->configHelper->getStoreId();
                    $backofficeParams = $this->configHelper->getBackendParams($storeId);
                    $sdk = new PaymentManager($backofficeParams);
                    $sdk->setUrlAttributes([$planid]);
                    $response = $sdk->getOrder();
                    $purchasePrice = $response->purchasePrice;
                } catch (Exception $e) {
                    $this->logger->debug($e->getMessage());
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('SORRY! There is a problem. Please contact us.')
                    );
                }

                $totalFromCart = round((float)$quote->getGrandTotal(), 2);
                if (($totalFromCart * 100) == $purchasePrice) {
                    $orderId = $this->quoteManagement->placeOrder($quote->getId());
                    $order = $this->orderRepository->get($orderId);
                    $order->setToken($planid);
                    $order->save();
                } else {
                    $this->messageManager->addError(
                        __('Cart price is different to Openpay plan amount.')
                    );
                    return $resultRedirect->setPath(
                        'checkout/cart',
                        ['_current' => true]
                    );
                }

                // capture payment and generate invoice of order
                $this->dataHelper->generateInvoice($order);
                
                return $resultRedirect->setPath(
                    'checkout/onepage/success',
                    ['_current' => true]
                );
            }
        } else {
            $this->messageManager->addError(
                __('Openpay transaction was cancelled.')
            );
            return $resultRedirect->setPath(
                'checkout/cart/',
                ['_current' => true]
            );
        }
    }
}
