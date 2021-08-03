<?php

namespace PayMaya\Payment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{
    protected $config;
    protected $logger;
    protected $storeManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PayMaya\Payment\Model\Config $config
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->logger->debug('Checking webhook URL default values');

        $store = $this->storeManager->getStore();

        $checkoutSuccessUrl = $this->config->getConfigData('webhook_base_url', 'webhooks', $store->getStoreId());

        $this->logger->info("Checkout success URL is {$checkoutSuccessUrl}");
        $this->logger->info("Base URL is {$store->getBaseUrl()}");

        if (empty($checkoutSuccessUrl)) {
            $baseUrl = substr($store->getBaseUrl(), 0, -1);

            $this->config->setConfigData('webhook_base_url', "{$baseUrl}", 'webhooks');
        }
    }
}
