<?php

namespace PayMayaNexGen\Payment\Observer;

class WebhooksObserver implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        \PayMayaNexGen\Payment\Gateway\Order $orderHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->orderHelper = $orderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $checkout = $observer->getData('data');

        $this->logger->debug('Data ' . json_encode($checkout));

        $checkoutStatus = $checkout['status'];
        $paymentStatus = $checkout['paymentStatus'];
        $orderId = $checkout['requestReferenceNumber'];

        if ($checkoutStatus !== 'COMPLETED') return;

        $refNumber = $checkout['id'];

        $order = $this->orderHelper->loadOrderByIncrementId($orderId);

        $this->orderHelper->createTransaction($order, $refNumber);

        if ($paymentStatus === 'PAYMENT_SUCCESS') {
            $this->orderHelper->setAsPaid($order);
        }
    }
}
