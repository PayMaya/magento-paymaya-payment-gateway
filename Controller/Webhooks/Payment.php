<?php

namespace PayMaya\Payment\Controller\Webhooks;

class Payment extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PayMaya\Payment\Gateway\Webhooks $webhooks
    )
    {
        parent::__construct($context);

        $this->webhooks = $webhooks;
    }

    public function execute()
    {
        $this->webhooks->lock();
        $this->webhooks->dispatchEvent('paymaya_payment_webhook_event');
        $this->webhooks->unlock();
    }
}
