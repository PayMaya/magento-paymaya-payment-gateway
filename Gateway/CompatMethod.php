<?php

namespace PayMayaNexGen\Payment\Gateway;

class CompatMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'paymayanexgen_payment';

    // protected $_infoBlockType = 'PayMaya\Payment\Block\Info';

    protected $_canAuthorize = true;
    protected $_canCapture = false;
    protected $_isGateway = true;
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = false;
    protected $_canFetchTransactionInfo = false;
    // protected $_canUseForMultishipping = true;

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }
}
