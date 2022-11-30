<?php

namespace PayMaya\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminWebhookRegister implements ObserverInterface {
    protected $logger;
    protected $config;
    protected $client;
    protected $storeManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \PayMaya\Payment\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PayMaya\Payment\Api\PayMayaClient $client
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

        $webhookBaseUrl = $this->config->getConfigData('webhook_base_url', 'webhooks');

        $this->client->createWebhook('CHECKOUT_SUCCESS', "{$webhookBaseUrl}/paymaya/webhooks");
        $this->client->createWebhook('CHECKOUT_FAILURE', "{$webhookBaseUrl}/paymaya/webhooks");
        $this->client->createWebhook('PAYMENT_SUCCESS', "{$webhookBaseUrl}/paymaya/webhooks/payment");
        $this->client->createWebhook('PAYMENT_FAILED', "{$webhookBaseUrl}/paymaya/webhooks/payment");
        $this->client->createWebhook('PAYMENT_EXPIRED', "{$webhookBaseUrl}/paymaya/webhooks/payment");
    }
}
