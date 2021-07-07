<?php

namespace PayMayaNexGen\Payment\Api;

use GuzzleHttp\Client as GC;

class PayMayaApi {
    const BASE_URL = 'https://pg-sandbox.paymaya.com';

    protected $apiKey;
    protected $client;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function createApiClient($options = array()) {
        $defaultOptions = $options;

        $defaultOptions['base_uri'] = self::BASE_URL;
        $defaultOptions['headers']['authorization'] = $this->getAuthHeader();

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

    private function getAuthHeader() {
        return "Basic " . base64_encode($this->apiKey . ':');
    }
}
