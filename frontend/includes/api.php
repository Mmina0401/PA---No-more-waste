<?php

class API
{
    private static $baseURLCalculee = "";

    private static function baseURL()
    {
        if (self::$baseURLCalculee !== "") {
            return self::$baseURLCalculee;
        }

        $adresseAPI = getenv("ADRESSE_API");

        if ($adresseAPI) {
            self::$baseURLCalculee = "http://" . $adresseAPI;
        } else {
            self::$baseURLCalculee = "http://localhost:8080";
        }

        return self::$baseURLCalculee;
    }

    private static function enTetes()
    {
        $enTetes = ["Content-Type: application/json"];

        if (isset($_SESSION["jeton"])) {
            $enTetes[] = "Authorization: Bearer " . $_SESSION["jeton"];
        }

        return $enTetes;
    }

    public static function get($endpoint)
    {
        $url = self::baseURL() . $endpoint;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());

        $response = curl_exec($ch);

        if ($response === false) {
            return [];
        }

        return json_decode($response, true);
    }

    public static function post($endpoint, $data)
    {
        $url = self::baseURL() . $endpoint;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            return [];
        }

        return json_decode($response, true);
    }
}
