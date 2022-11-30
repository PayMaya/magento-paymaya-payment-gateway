<?php

namespace PayMaya\Payment\Observer;

class PaymentWebhookObserver implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        \PayMaya\Payment\Gateway\Order $orderHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->orderHelper = $orderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $payment = $observer->getData('data');

        $this->logger->debug('Data ' . json_encode($payment));

        $paymentStatus = $payment['status'];
        $orderId = $payment['requestReferenceNumber'];

        $refNumber = $payment['id'];

        $order = $this->orderHelper->loadOrderByIncrementId($orderId);

        $this->orderHelper->createTransaction($order, $refNumber);

        if ($paymentStatus === 'PAYMENT_SUCCESS') {
            $this->orderHelper->setAsPaid($order);
        } else if ($paymentStatus === 'PAYMENT_FAILED' || $paymentStatus === 'PAYMENT_EXPIRED') {
            $this->orderHelper->setAsFailed($order, $refNumber);
        }
    }
}
