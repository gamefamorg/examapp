<?php
// apiCaller.php

class ApiCaller {
    private $baseUrl;
    private $bearerToken;

    public function __construct($baseUrl, $bearerToken = null) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->bearerToken = $bearerToken;
    }

    public function call($method, $endpoint, $data = null) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout sau 10 giây nếu không kết nối được
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout tổng cộng sau 30 giây

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($this->bearerToken) {
            $headers[] = "Authorization: Bearer {$this->bearerToken}";
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        switch (strtoupper($method)) {
            case 'GET':
                if ($data) {
                    $url .= '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            default:
                break; //throw new Exception("Unsupported HTTP method: $method");
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($error) {
            return ['status' => 500, 'body' => null];
			//throw new Exception("cURL Error: $error. Status code: $statusCode");
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['status' => 500, 'body' => null];//throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }

        return [
            'status' => $statusCode,
            'body' => $decodedResponse
        ];
    }
}