<?php

namespace PayMaya\Payment\Observer;
use Magento\Sales\Model\Order as MagentoOrder;

class PaymentWebhookObserver implements \Magento\Framework\Event\ObserverInterface
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
        $payment = $observer->getData('data');

        $this->logger->info('[Handle Webhook][Data] ' . json_encode($payment));

        $paymentStatus = $payment['status'];
        $orderId = $payment['requestReferenceNumber'];

        $refNumber = $payment['id'];

        $order = $this->orderHelper->loadOrderByIncrementId($orderId);

        $this->orderHelper->createTransaction($order, $refNumber);

        if ($order->getStatus() === MagentoOrder::STATE_PROCESSING) {
            $this->logger->debug('[Handle Webhook] Order ' . $orderId . ' has already been paid.');
            return;
        }

        if ($paymentStatus === 'PAYMENT_SUCCESS') {
            $this->orderHelper->setAsPaid($order);
        } else if ($paymentStatus === 'PAYMENT_FAILED' || $paymentStatus === 'PAYMENT_EXPIRED') {
            $this->orderHelper->setAsFailed($order, $refNumber);
        }
    }
}
