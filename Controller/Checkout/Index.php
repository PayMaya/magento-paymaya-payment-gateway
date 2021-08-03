<?php

namespace PayMaya\Payment\Controller\Checkout;

class Index  extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PayMaya\Payment\Api\PayMayaClient $client,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function execute()
    {
        $orderSession = $this->checkoutSession->getLastRealOrder();
        $incrementId = $orderSession->getIncrementId();
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId($incrementId);

        $response = $this->client->createCheckout($order);
        $checkout = json_decode($response, true);

        $this->logger->debug('Checkout response ' . $response);

        $this->_redirect($checkout["redirectUrl"]);
    }
}
