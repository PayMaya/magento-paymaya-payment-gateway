<?php

namespace PayMaya\Payment\Gateway;

use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order as MagentoOrder;

class Order
{
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Api\Data\OrderInterface $order
    )
    {
        $this->orderSender = $orderSender;
        $this->order = $order;
    }

    /**
     * Set order as paid
     */
    public function setAsPaid($order) {
        /** Set order state and status to processing */
        $order->setState(MagentoOrder::STATE_PROCESSING, true)->save();
        $order->setStatus(MagentoOrder::STATE_PROCESSING)->save();

        /** Send order confirmation e-mail */
        $this->orderSender->send($order);
    }

    public function setAsFailed($order, $paymentId) {
        $order->setState(MagentoOrder::STATE_CANCELED, true)->save();
        $order->setStatus(MagentoOrder::STATE_CANCELED)->save();
        $order->addCommentToStatusHistory("Failed payment {$paymentId}", MagentoOrder::STATE_HOLDED, true)->save();
    }

    /**
     * Create transaction records for the order with a PayMongo payment ID
     */
    public function createTransaction($order, $paymentId) {
        /** Get associated payment model */
        $payment = $order->getPayment();

        /** Set the transaction ID using PayMongo Payment ID */
        $payment->setTransactionId($paymentId);

        /**
         * Since there is no manual captures, set the last transaction ID to the
         * Paymongo Payment ID
         */
        $payment->setLastTransId($paymentId);

        /**
         * Don't settle transactions in case of manual refunds since refunds are not
         * yet available through the PayMongo API
         */
        $payment->setIsTransactionClosed(0);

        /** Save the payment changes above */
        $payment->save();

        /** Add a transaction record */
        $transaction = $payment->addTransaction(Transaction::TYPE_ORDER, null, false);

        /** Save the transaction record */
        $transaction->save();
    }

    public function loadOrderByIncrementId($orderId, $count = 7)
    {
        $order = $this->order->loadByIncrementId($orderId);

        if (empty($order) || empty($order->getId()) && $count >= 0)
        {
            // Webhooks Race Condition: Sometimes we may receive the webhook before Magento commits the order to the database,
            // so we give it a few seconds and try again. Can happen when multiple subscriptions are purchased together.
            sleep(4);
            return $this->loadOrderByIncrementId($orderId, $count - 1);
        }

        if (empty($order) || empty($order->getId()))
            throw new \Exception("Received webhook with Order #$orderId but could not find the order in Magento; ignoring", 400);

        return $order;
    }
}
