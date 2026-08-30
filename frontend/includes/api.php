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
        $ch = curl_init(self::baseURL() . $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());

        $response = curl_exec($ch);

        if ($response === false) {
            return [];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        return json_decode($response, true);
    }

    public static function post($endpoint, $data)
    {
        $ch = curl_init(self::baseURL() . $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            return [];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        return json_decode($response, true);
    }

    public static function put($endpoint, $data)
    {
        $ch = curl_init(self::baseURL() . $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            return [];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        return json_decode($response, true);
    }

    public static function delete($endpoint, $data)
    {
        $ch = curl_init(self::baseURL() . $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            return [];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode < 200 || $httpCode >= 300) {
            return [];
        }

        return json_decode($response, true);
    }

    public static function postAvecStatut($endpoint, $data)
    {
        $ch = curl_init(self::baseURL() . $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            return [
                "status" => 0,
                "data" => ["error" => "API_INDISPONIBLE"]
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            $decoded = ["error" => trim((string) $response)];
        }

        return [
            "status" => $httpCode,
            "data" => $decoded
        ];
    }

}