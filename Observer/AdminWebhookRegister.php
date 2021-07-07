<?php

namespace PayMayaNexGen\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use PayMayaNexGen\Payment\Api\PayMayaApi;

class AdminWebhookRegister implements ObserverInterface {
    protected $logger;
    protected $config;
    protected $client;
    protected $storeManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \PayMayaNexGen\Payment\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;

        $mode = $config->getConfigData('paymaya_mode', 'basic');
        $encryptedSecretKey = $config->getConfigData("paymaya_{$mode}_sk", 'basic');
        $secretKey = $encryptor->decrypt($encryptedSecretKey);

        $this->logger->info("Secret key {$secretKey}");

        $payMayaApi = new PayMayaApi($secretKey);
        $payMayaApi->createApiClient();

        $this->client = $payMayaApi;

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
