<?php

namespace PayMaya\Payment\Observer;

class WebhooksObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected $logger;
    protected $orderHelper;

    public function __construct(
        \PayMaya\Payment\Gateway\Order $orderHelper,
        \PayMaya\Payment\Logger\Logger $logger
    ) {
        $this->logger = $logger;
        $this->orderHelper = $orderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->logger->info("Passthrough");
        return;
    }
}
