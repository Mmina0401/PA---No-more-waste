<?php

class API
{
    private static $baseURL = "http://localhost:8080";

    public static function get($endpoint)
    {
        $url = self::$baseURL . $endpoint;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return [];
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    public static function post($endpoint, $data)
    {
        $url = self::$baseURL . $endpoint;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response, true);
    }
}