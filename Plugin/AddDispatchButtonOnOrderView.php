<?php

namespace Openpay\Payment\Plugin;

use Magento\Framework\UrlInterface;

/**
 * class AddDispatchButtonOnOrderView
 * 
 * Custom button on admin sales order view
 */
class AddDispatchButtonOnOrderView
{
    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * adddispatchbuttononorderview constructor
     * 
     * @param UrlInterface $url
     */
    public function __construct(
        UrlInterface $url
    ) {
        $this->urlBuilder = $url;
    }

    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $subject){
        $order = $subject->getOrder();
        $method = $order->getPayment()->getMethod();
        if ($method == 'openpay') {
            $url = $this->urlBuilder->getUrl('openpay_admin/*/dispatched', ['id' => $subject->getOrderId()]);
            $message = __('Are you sure you want to dispatch goods manually?');
            $subject->addButton(
                'goods_dispatched_btn',
                [
                    'label' => __('Goods Dispatched'),
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')",
                    'class' => 'primary'
                ],
                -1
            );
        }
        return null;
    }
}