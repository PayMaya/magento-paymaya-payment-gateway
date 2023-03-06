<?php

namespace PayMaya\Payment\Controller\Checkout;

use GuzzleHttp\Exception\ClientException;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $client;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PayMaya\Payment\Api\PayMayaClient $client,
        \Magento\Checkout\Model\Session $checkoutSession,
        \PayMaya\Payment\Logger\Logger $logger
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

        try {
            $response = $this->client->createCheckout($order);
            $checkout = json_decode($response, true);

            $this->logger->debug('[Create Checkout][Response]' . $response);

            $this->_redirect($checkout["redirectUrl"]);
        } catch (ClientException $e) {
            $this->logger->error('[Create Checkout]' . $e->getResponse()->getBody()->__toString());

            $this->checkoutSession->restoreQuote();
            $this->messageManager->addErrorMessage('Something went wrong with the payment');
            $this->_redirect('checkout/cart');
        }
    }
}
