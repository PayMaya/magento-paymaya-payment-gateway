<?php

namespace PayMayaNexGen\Payment\Api;

use GuzzleHttp\Client as GC;

class PayMayaClient
{
    const BASE_URL = 'https://pg-sandbox.paymaya.com';

    protected $client;

    public function __construct(
        \PayMayaNexGen\Payment\Model\Config $config,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->storeManager = $storeManager;

        $mode = $config->getConfigData('paymaya_mode', 'basic');
        $encryptedSecretKey = $config->getConfigData("paymaya_{$mode}_sk", 'basic');
        $secretKey = $encryptor->decrypt($encryptedSecretKey);

        $defaultOptions['base_uri'] = self::BASE_URL;
        $defaultOptions['headers']['authorization'] = $this->getAuthHeader($secretKey);

        $client = new GC($defaultOptions);

        $this->client = $client;
    }

    public function retrieveWebhooks() {
        $response = $this->client->get('/checkout/v1/webhooks');
        return $response->getBody();
    }

    public function deleteWebhook($id) {
        $response = $this->client->delete("/checkout/v1/webhooks/{$id}");
        return $response->getBody();
    }

    public function createWebhook($type, $url) {
        $response = $this->client->post('/checkout/v1/webhooks', [
            'json' => array(
                'name' => $type,
                'callbackUrl' => $url
            ),
        ]);

        return $response->getBody();
    }

    public function createCheckout($order) {
        $mode = $this->config->getConfigData('paymaya_mode', 'basic');
        $publicKey = $this->config->getConfigData("paymaya_{$mode}_pk", 'basic');

        $payload = $this->formatOrderForPayment($order);

        $this->logger->debug('Payload ' . json_encode($payload));

        $response = $this->client->post('/checkout/v1/checkouts', [
            'json' => $payload,
            'headers' => [
                'authorization' => $this->getAuthHeader($publicKey)
            ]
        ]);

        return $response->getBody();
    }

    private function getAuthHeader($secretKey) {
        return "Basic " . base64_encode($secretKey . ':');
    }

    private function formatOrderForPayment(\Magento\Sales\Model\Order $order)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        $orderItems = [];

        foreach($order->getAllItems() as $item)
        {
            array_push($orderItems, [
                "name" => $item->getName(),
                "quantity" => $item->getQtyOrdered(),
                "description" => empty($item->getDescription()) ? $item->getName() : $item->getDescription(),
                "code" => $item->getSku(),
                "amount" => [
                    "value" => $item->getPrice()
                ],
                "totalAmount" => [
                    "value" => $item->getQtyOrdered() * $item->getPrice()
                ]
            ]);
        }

        $payMayaArray = [
            "totalAmount" => [
                "value" => $order->getTotalDue(),
                "currency" => $order->getOrderCurrencyCode(),
                "details" => [
                    "discount" => 0,
                    "serviceCharge" => 0,
                    "shippingFee" => $order->getShippingAmount(),
                    "tax" => $order->getTaxAmount(),
                    "subtotal" => $order->getBaseSubtotal()
                ]
            ],
            "buyer" => [
                "firstName" => $order->getCustomerFirstname(),
                "middleName" => $order->getCustomerMiddlename(),
                "lastName" => $order->getCustomerLastname(),
                "birthday"=> $order->getCustomerDob(),
                //"customerSince" => "1995-10-24",
                "sex" => $order->getCustomerGender(),
                "contact" => [
                    "phone" => $order->getShippingAddress()->getTelephone(),
                    "email" => $order->getCustomerEmail()
                ],
                "shippingAddress" => [
                    "firstName" => $order->getCustomerFirstname(),
                    "middleName" => $order->getCustomerMiddlename(),
                    "lastName" => $order->getCustomerLastname(),
                    "phone" => "+639202020202",
                    "email" => $order->getCustomerEmail(),
                    "line1" => $order->getShippingAddress()->getStreet(1)[0],
                    "line2" => $order->getShippingAddress()->getStreet(2)[0],
                    "city" => $order->getShippingAddress()->getCity(),
                    "state" => $order->getShippingAddress()->getRegionCode(),
                    "zipCode" => $order->getShippingAddress()->getPostCode(),
                    "countryCode" => $order->getShippingAddress()->getCountryId(),
                    "shippingType" => "ST" // ST - for standard, SD - for same day
                ],
                "billingAddress" => [
                    "line1" => $order->getShippingAddress()->getStreet(1)[0],
                    "line2" => $order->getShippingAddress()->getStreet(2)[0],
                    "city" => $order->getShippingAddress()->getCity(),
                    "state" => $order->getShippingAddress()->getRegionCode(),
                    "zipCode" => $order->getShippingAddress()->getPostCode(),
                    "countryCode" => $order->getShippingAddress()->getCountryId(),
                ]
            ],
            "items"=> $orderItems,
            "redirectUrl" => [
                "success" => "{$baseUrl}/checkout/onepage/success",
                "failure" => "{$baseUrl}/paymaya/fail",
                "cancel" => "{$baseUrl}/checkout/onepage/success"
            ],
            "requestReferenceNumber" => $order->getIncrementId(),
        ];

        //echo "<pre>order items: "; print_r($payMayaArray); die;
        return $payMayaArray;
    }
}
