<?php

namespace Openpay\Payment\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class EnvRadioBtn
 */
class EnvRadioBtn implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return
        [
            ['value' => 'test', 'label' => __('Sandbox')],
            ['value' => 'live', 'label' => __('Production')]
        ];
    }
}
