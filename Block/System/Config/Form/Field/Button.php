<?php

namespace Openpay\Payment\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Button
 *
 * custom button class for implementing run min/max job on payment form in admin
 */
class Button extends Field
{
    /** @var string */
    protected $_template = 'Openpay_Payment::system/config/button.phtml';
    
    /**
     * button constructor
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * {@inheritDoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * custom url for executing controller
     *
     * @return string
     */
    public function getCustomUrl()
    {
        return $this->getUrl('openpay_admin/system_config/getMinMaxValue');
    }
    
    /**
     * {@inheritDoc}
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
        ->setData(
            [
                'id' => 'btn_id',
                'label' => __('Run Min/Max!'),
                'class' => "save primary"
            ]
        );
        return $button->toHtml();
    }
}
