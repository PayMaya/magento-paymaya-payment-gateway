<?php

namespace PayMayaNexGen\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminWebhookRegister implements ObserverInterface {
    protected $logger;
    protected $config;
    protected $client;
    protected $storeManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \PayMayaNexGen\Payment\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PayMayaNexGen\Payment\Api\PayMayaClient $client
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->client = $client;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $body = $this->client->retrieveWebhooks();
        $webhooks = json_decode($body, true);

        foreach ($webhooks as $webhook) {
            $this->client->deleteWebhook($webhook['id']);
        }

        $checkoutSuccessUrl = $this->config->getConfigData('checkout_success_url', 'webhooks');
        $checkoutFailureUrl = $this->config->getConfigData('checkout_failure_url', 'webhooks');

        $this->client->createWebhook('CHECKOUT_SUCCESS', $checkoutSuccessUrl);
        $this->client->createWebhook('CHECKOUT_FAILURE', $checkoutFailureUrl);
    }
}
