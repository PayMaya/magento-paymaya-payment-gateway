<?php


namespace PayMayaNexGen\Payment\Block;


class Form extends \Magento\Payment\Block\Form\Cc
{
//    protected $_template = 'form/paymaya_payments.phtml';

    public $config;
    public $setupIntent;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,

        array $data = []
    )
    {
        parent::__construct($context, $paymentConfig, $data);
    }

}
