<?php

namespace PayMayaNexGen\Payment\Model\Ui;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CODE = 'paymayanexgen_payment';

    public function getConfig()
    {
        return [
            // 'key' => 'value' pairs of configuration
        ];
    }
}
