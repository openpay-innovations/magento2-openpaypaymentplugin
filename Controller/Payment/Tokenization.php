<?php

namespace Openpay\Payment\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\DataObject as DataObject;
use BusinessLayer\Openpay\PaymentManager as PaymentManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\GuestCart\GuestCartRepository;
use Openpay\Payment\Logger\Logger;

/**
 * Class Tokenization
 *
 * This controller class creates token after clicking place order button
 */
class Tokenization extends Action
{
    /** @var DataObject */
    protected $others = '';

    /** @var Session */
    protected $session;

    /** @var LayoutInterface */
    protected $layoutInterface;

    /** @var ResultFactory */
    protected $resultFactory;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var CartRepositoryInterface */
    protected $quoteRepository;

    /** @var GuestCartRepository */
    protected $guestCartRepository;

    /** @var Logger */
    protected $logger;

    /**
     * tokenization constructor
     *
     * @param Context                 $context
     * @param Session                 $session
     * @param LayoutInterface         $layoutInterface
     * @param ResultFactory           $resultFactory
     * @param StoreManagerInterface   $storeManager
     * @param ManagerInterface        $messageManager
     * @param ConfigHelper            $configHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param GuestCartRepository     $guestCartRepository
     * @param Logger                  $logger
     */
    public function __construct(
        Context $context,
        Session $session,
        LayoutInterface $layoutInterface,
        ResultFactory $resultFactory,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        ConfigHelper $configHelper,
        CartRepositoryInterface $quoteRepository,
        GuestCartRepository $guestCartRepository,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->layoutInterface = $layoutInterface;
        $this->resultFactory = $resultFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->configHelper = $configHelper;
        $this->quoteRepository = $quoteRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $result = [];
        $resultRedirect = $this->resultRedirectFactory->create();
        $cartId = $this->getRequest()->getParam('cartId');
        if ($cartId) {
            $quote = $this->quoteRepository->getActive($cartId);
        }

        if (!$quote->getCustomerId()) {
            $email = $this->getRequest()->getParam('email');
            $quote->setCustomerEmail($email);
            $quote->setCustomerIsGuest(true);
            $quote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
            $quote->save();
        }
        
        $layout = $this->layoutInterface;
        $others = new DataObject();
        $others->origin = "Online";
        $others->planCreationType = "pending";
        $others->merchantRedirectUrl = $this->storeManager->getStore()->getUrl('openpay/payment/reside');
        $others->cancleUrl = $this->storeManager->getStore()->getUrl('openpay/payment/reside');
        $others->merchantFailUrl = $this->storeManager->getStore()->getUrl('openpay/payment/reside');
        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        
        //getting values from back office configuration on the basis of store id
        $storeId = $this->configHelper->getStoreId();
        $backofficeParams = $this->configHelper->getBackendParams($storeId);

        try {
            /** @var PaymentManager */
            $sdk = new PaymentManager($backofficeParams);
            $sdk->setShopdata($quote, $others, null, null, null);
            $token = $sdk->getToken();
            $sdk->setShopdata(null, null, $token, null, $backofficeParams);
            $paymentPage = $sdk->getPaymentPage('redirect', false, 'GET');
        } catch (\Exception $ex) {
            $this->logger->debug($ex->getMessage());
            $this->messageManager->addError(
                __('There was a problem. Please try again.')
            );
            return $resultRedirect->setPath(
                'checkout/cart',
                ['_current' => true]
            );
            return $response;
        }
        
        $block = $layout->createBlock('Magento\Framework\View\Element\Template')
                ->setData([
                    'paymentPage' => $paymentPage,
                    'dir' => BP
                ])
                ->setTemplate('Openpay_Payment::submitOpenpay.phtml')
                ->toHtml();
        $this->getResponse()->setBody($block);
    }
}
