<?php

namespace PayMayaNexGen\Payment\Model\Adminhtml\Source;

class ModuleVersion extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return \PayMayaNexGen\Payment\Model\Config::$moduleVersion;
    }
}
