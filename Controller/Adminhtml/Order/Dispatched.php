<?php

namespace Openpay\Payment\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Openpay\Payment\Logger\Logger as OpenpayLogger;
use BusinessLayer\Openpay\PaymentManager as PaymentManager;
use Magento\Sales\Api\OrderRepositoryInterface;

class Dispatched extends Action
{

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var OpenpayLogger */
    protected $openpayLogger;

    /**
     * dispatched controller
     * 
     * @param Context                  $context
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfigHelper             $configHelper
     * @param OpenpayLogger            $openpayLogger
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        ConfigHelper $configHelper,
        OpenpayLogger $openpayLogger
    ) {
        $this->orderRepository = $orderRepository;
        $this->configHelper = $configHelper;
        $this->openpayLogger = $openpayLogger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $order = $this->orderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        
        $storeId = $this->configHelper->getStoreId();
        $backofficeParams = $this->configHelper->getBackendParams($storeId);
        try {
            $sdk = new PaymentManager($backofficeParams);
            $sdk->setUrlAttributes([$order->getToken()]);
            $response = $sdk->dispatch();
            $this->messageManager->addSuccessMessage(__('Goods dispatched successfully from Openpay API.'));
        } catch (\Exception $e) {
            $this->openpayLogger->debug($e->getMessage());
            $this->messageManager->addErrorMessage(__('Dispatch failed from Openpay API.'));
        }
        return $this->resultRedirectFactory->create()->setPath(
            'sales/order/view',
            [
                'order_id' => $order->getEntityId()
            ]
        );
    }
}