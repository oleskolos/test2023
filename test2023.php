<?php

interface SaleRequestInterface {
    public function getOrderData();
    public function getHash();
}

interface SaleResponseInterface {
    public function isSuccess();
    public function getResponseData();
}

class APIIntegration {
    private $apiUrl;
    private $clientKey;
    private $clientPass;

    public function __construct($apiUrl, $clientKey, $clientPass) {
        $this->apiUrl = $apiUrl;
        $this->clientKey = $clientKey;
        $this->clientPass = $clientPass;
    }

    public function sendSaleRequest(SaleRequestInterface $request) {
        $requestData = [
            'action' => 'SALE',
            'client_key' => $this->clientKey,
            'order_id' => $request->getOrderData()['order_id'],
            'order_amount' => $request->getOrderData()['order_amount'],
            'card_number' => $request->getOrderData()['card_number'],
            'card_exp_month' => $request->getOrderData()['card_exp_month'],
            'card_exp_year' => $request->getOrderData()['card_exp_year'],
            'card_cvv2' => $request->getOrderData()['card_cvv2'],
            'payer_first_name' => $request->getOrderData()['payer_first_name'],
            'payer_last_name' => $request->getOrderData()['payer_last_name'],
            'payer_address' => $request->getOrderData()['payer_address'],
            'payer_country' => $request->getOrderData()['payer_country'],
            'payer_state' => $request->getOrderData()['payer_state'],
            'payer_city' => $request->getOrderData()['payer_city'],
            'payer_zip' => $request->getOrderData()['payer_zip'],
            'payer_email' => $request->getOrderData()['payer_email'],
            'payer_phone' => $request->getOrderData()['payer_phone'],
            'payer_ip' => $request->getOrderData()['payer_ip'],
            'term_url_3ds' => $request->getOrderData()['term_url_3ds'],
            'hash' => $request->getHash(),
        ];

        $response = $this->sendRequest($requestData);

        return $response;
    }

    private function sendRequest($requestData) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

class SaleResponse implements SaleResponseInterface {
    private $responseData;

    public function __construct($responseData) {
        $this->responseData = $responseData;
    }

    public function isSuccess() {
        return isset($this->responseData['success']) && $this->responseData['success'] === true;
    }

    public function getResponseData() {
        return $this->responseData;
    }
}

class SaleRequest implements SaleRequestInterface {
    private $orderData;
    private $clientPass;

    public function __construct($orderData, $clientPass) {
        $this->orderData = $orderData;
        $this->clientPass = $clientPass;
    }

    public function getOrderData() {
        return $this->orderData;
    }

    public function getHash() {
        $email = $this->orderData['payer_email'];
        $cardData = strrev(substr($this->orderData['card_number'], 0, 6) . substr($this->orderData['card_number'], -4));
        $hash = md5(strtoupper(sprintf('%s%s%s', strrev($email), $this->clientPass, $cardData)));

        return $hash;
    }
}

// initialization parameters for API
$apiUrl = 'https://dev-api.rafinita.com/post';
$clientKey = 'c2b8fb04-110f-11ea-bcd3-0242c0a85004';
$clientPass = '13a4822c5907ed235f3a068c76184fc3';

// initializating of APIIntegration object
$apiIntegration = new APIIntegration($apiUrl, $clientKey, $clientPass);

// data for SALE request
$orderData = [
    'order_id' => 'ORDER-12345',
    'order_amount' => 1.99,
    'card_number' => '4111111111111111',
    'card_exp_month' => '01',
    'card_exp_year' => '2025',
    'card_cvv2' => '000',
    'payer_first_name' => 'John',
    'payer_last_name' => 'Doe',
    'payer_address' => 'Big street',
    'payer_country' => 'US',
    'payer_state' => 'CA',
    'payer_city' => 'City',
    'payer_zip' => '123456',
    'payer_email' => 'doe@example.com',
    'payer_phone' => '199999999',
    'payer_ip' => '123.123.123.123',
    'term_url_3ds' => 'http://client.site.com/return.php',
];

$saleRequest = new SaleRequest($orderData, $clientPass);

$response = $apiIntegration->sendSaleRequest($saleRequest);

$responseData = json_decode($response, true);

$saleResponse = new SaleResponse($responseData);

print_r($saleResponse->getResponseData());

?>