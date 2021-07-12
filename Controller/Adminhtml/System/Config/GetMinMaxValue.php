<?php

namespace Openpay\Payment\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Openpay\Payment\Helper\Config as ConfigHelper;
use Openpay\Payment\Helper\Data as DataHelper;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\Result\Json;

/**
 * Class GetMinMaxValue
 *
 * This is ajax controller
 * It executes and get min/max value from api call
 */
class GetMinMaxValue extends Action
{

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var ConfigHelper */
    protected $configHelper;

    /** @var DataHelper */
    protected $dataHelper;

    /** @var ConfigHelper */
    protected $configWriter;

    /**
     * getminmaxvalue constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigHelper $configHelper
     * @param DataHelper $dataHelper
     * @param WriterInterface $configWriter
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ConfigHelper $configHelper,
        DataHelper $dataHelper,
        WriterInterface $configWriter
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
        $this->configWriter = $configWriter;
        parent::__construct($context);
    }

    /**
     * Get min and max value from api call
     *
     * @return Json
     */
    public function execute()
    {
        $authUser = $this->getRequest()->getParam('auth_user');
        $authToken = $this->getRequest()->getParam('auth_token');
        $paymentMode  = $this->getRequest()->getParam('payment_mode');
        $region = $this->getRequest()->getParam('region');

        /** @var Json $result */
        $result = $this->resultJsonFactory->create();

        if ($authUser && $authToken) {
            $storeId = $this->configHelper->getStoreId();
            $backofficeParams = $this->configHelper->getBackendParams($storeId);
            $backofficeParams['auth_user'] = $authUser;
            $backofficeParams['auth_token'] = $authToken;
            $backofficeParams['payment_mode'] = $paymentMode;
            $backofficeParams['region'] = $region;
            $response = $this->dataHelper->getLimits($backofficeParams);
            if (array_key_exists('message', $response)) {
                return $result->setData(
                    [
                        'success' => false,
                        'message' => 'Retailer identity key supplied not valid!'
                    ]
                );
            }

            return $result->setData(
                [
                    'success' => true,
                    'min' => $response['min'],
                    'max' => $response['max']
                ]
            );
        }
    }
}
