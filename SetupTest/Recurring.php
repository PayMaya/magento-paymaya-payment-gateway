<?php

namespace PayMayaNexGen\Payment\Setup;

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
        \PayMayaNexGen\Payment\Model\Config $config
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->logger->debug('Checking webhook URL default values');

        $store = $this->storeManager->getStore();

        $checkoutSuccessUrl = $this->config->getConfigData('checkout_success_url', 'webhooks', $store->getStoreId());

        $this->logger->info("Checkout success URL is {$checkoutSuccessUrl}");

        if (empty($checkoutSuccessUrl)) {
            $baseUrl = $store->getBaseUrl();

            $this->config->setConfigData('checkout_success_url', "{$baseUrl}paymayanexgen/webhooks", 'webhooks', null, $store->getStoreId());
        }
    }
}
