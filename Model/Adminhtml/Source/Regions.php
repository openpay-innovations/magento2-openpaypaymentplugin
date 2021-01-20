<?php

namespace Openpay\Payment\Model\Adminhtml\Source;

/**
 * Class Regions
 */
class Regions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return
        [
            ['value' => 'Au', 'label' => __('Australia')],
            ['value' => 'En', 'label' => __('United Kingdom')]
        ];
    }
}