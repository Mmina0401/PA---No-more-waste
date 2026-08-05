<?php

class API
{
    private static $baseURL = "http://localhost:8080";

    // Construit les en-têtes envoyés à chaque appel : le type de contenu,
    // et en plus le jeton de connexion si la personne est connectée
    // (stocké en session lors de la connexion, voir login.php).
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
        $url = self::$baseURL . $endpoint;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());

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
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::enTetes());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response, true);
    }
}