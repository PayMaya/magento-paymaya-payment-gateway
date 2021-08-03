<?php

namespace PayMaya\Payment\Model\Ui;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CODE = 'paymaya_payment';

    public function getConfig()
    {
        return [
            // 'key' => 'value' pairs of configuration
        ];
    }
}
