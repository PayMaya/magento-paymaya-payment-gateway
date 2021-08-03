<?php

namespace PayMaya\Payment\Model;

use Magento\Store\Model\ScopeInterface;

class Config
{
    protected $scopeConfig;
    protected $resourceConfig;
    protected $logger;

    public static $moduleVersion = "1.0.0";

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->configWriter = $configWriter;
        $this->logger = $logger;
    }

    public function isEnabled()
    {
        $enabled = ((bool)$this->getConfigData('active')) && $this->initPayMaya();
        return $enabled;
    }

    public function getConfigData($field, $sectionKey = null, $storeId = null)
    {
        $section = "";

        if ($sectionKey)
            $section = "_$sectionKey";

        $data = $this->scopeConfig->getValue("payment/paymaya_payment$section/$field", ScopeInterface::SCOPE_STORE, $storeId);

        return $data;
    }

    public function setConfigData($field, $value, $sectionKey = null)
    {

        $section = "";

        if ($sectionKey)
            $section = "_$sectionKey";

        $this->logger->info("Field {$field}");
        $this->logger->info("Value {$value}");

        $data = $this->configWriter->save("payment/paymaya_payment$section/$field", $value);

        return $data;
    }
}
