<?php

namespace PayMaya\Payment\Api;

use GuzzleHttp\Client as GC;
use GuzzleHttp\Exception\ClientException;

class PayMayaClient
{
    const SANDBOX_BASE_URL = 'https://pg-sandbox.paymaya.com';
    const PRODUCTION_BASE_URL = 'https://pg.paymaya.com';

    protected $client;
    protected $config;
    protected $logger;
    protected $storeManager;

    public function __construct(
        \PayMaya\Payment\Model\Config $config,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \PayMaya\Payment\Logger\Logger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->storeManager = $storeManager;

        $mode = $config->getConfigData('paymaya_mode', 'basic');
        $encryptedSecretKey = $config->getConfigData("paymaya_{$mode}_sk", 'basic');
        $secretKey = $encryptor->decrypt($encryptedSecretKey);
        $moduleVersion = $config::$moduleVersion;

        $defaultOptions['base_uri'] = $mode === 'test' ? self::SANDBOX_BASE_URL : self::PRODUCTION_BASE_URL;
        $defaultOptions['headers']['authorization'] = $this->getAuthHeader($secretKey);
        $defaultOptions['headers']['x-paymaya-sdk'] = 'magento-v' . $moduleVersion;

        $client = new GC($defaultOptions);

        $this->client = $client;
    }

    public function retrieveWebhooks() {
        try {
            $response = $this->client->get('/checkout/v1/webhooks');
            return $response->getBody();
        } catch (ClientException $err) {
            $response = $err->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 404) {
                return "[]";
            }

            throw $err;
        }
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

        $this->logger->debug('[Create Checkout][Payload]' . json_encode($payload));

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

    private function formatBirthdate($rawBirthDate) {
        if (!isset($rawBirthDate)) return '';

        $time = strtotime($rawBirthDate);
        return date('Y-m-d', $time);
    }

    private function formatGender($rawGender) {
        switch ($rawGender) {
            // Mapping out Unspecified option in Magento to Male in Maya by default
            case 0:
            case 1: {
                return 'M';
            }
            case 2: {
                return 'F';
            }
        }
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

        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();

        $addressGetter = isset($shippingAddress) ? $shippingAddress : $billingAddress;
        $rawBirthDate = $order->getCustomerDob();
        $rawGender = $order->getCustomerGender();

        $buyerData = [
            "firstName" => $order->getCustomerFirstname(),
            "middleName" => $order->getCustomerMiddlename(),
            "lastName" => $order->getCustomerLastname(),
            "birthday"=> $this->formatBirthdate($rawBirthDate),
            "sex" => $this->formatGender($rawGender),
            "contact" => [
                "phone" => $addressGetter->getTelephone(),
                "email" => $order->getCustomerEmail()
            ],
            "shippingAddress" => [
                "firstName" => $order->getCustomerFirstname(),
                "middleName" => $order->getCustomerMiddlename(),
                "lastName" => $order->getCustomerLastname(),
                "phone" => $addressGetter->getTelephone(),
                "email" => $order->getCustomerEmail(),
                "line1" => $addressGetter->getStreet(1)[0],
                "line2" => $addressGetter->getStreet(2)[0],
                "city" => $addressGetter->getCity(),
                "state" => $addressGetter->getRegionCode(),
                "zipCode" => $addressGetter->getPostCode(),
                "countryCode" => $addressGetter->getCountryId(),
                "shippingType" => "ST" // ST - for standard, SD - for same day
            ],
            "billingAddress" => [
                "line1" => $addressGetter->getStreet(1)[0],
                "line2" => $addressGetter->getStreet(2)[0],
                "city" => $addressGetter->getCity(),
                "state" => $addressGetter->getRegionCode(),
                "zipCode" => $addressGetter->getPostCode(),
                "countryCode" => $addressGetter->getCountryId(),
            ]
        ];

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
            "buyer" => $buyerData,
            "items"=> $orderItems,
            "redirectUrl" => [
                "success" => "{$baseUrl}paymaya/checkout/catcher?type=success",
                "failure" => "{$baseUrl}paymaya/checkout/catcher?type=fail",
                "cancel" => "{$baseUrl}paymaya/checkout/catcher?type=cancel"
            ],
            "requestReferenceNumber" => $order->getIncrementId(),
        ];

        return $payMayaArray;
    }
}
