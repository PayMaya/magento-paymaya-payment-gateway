<?php

namespace PayMayaNexGen\Payment\Controller\Webhooks;

class Index extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PayMayaNexGen\Payment\Gateway\Webhooks $webhooks
    )
    {
        parent::__construct($context);

        $this->webhooks = $webhooks;
    }

    public function execute()
    {
        $this->webhooks->lock();
        $this->webhooks->dispatchEvent();
        $this->webhooks->unlock();
    }
}
