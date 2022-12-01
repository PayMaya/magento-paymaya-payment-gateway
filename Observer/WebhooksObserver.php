<?php

namespace PayMaya\Payment\Observer;

class WebhooksObserver implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        \PayMaya\Payment\Gateway\Order $orderHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->orderHelper = $orderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->logger->info("Passthrough");
        return;
    }
}
