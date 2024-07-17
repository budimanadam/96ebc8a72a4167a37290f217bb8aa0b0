<?php
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "85ldofi";
$issuedAt = time();
$expirationTime = $issuedAt + 3600;  // jwt valid for 1 hour from the issued time
$issuer = "http://example.org";

function authenticate() {
    global $key, $issuedAt, $expirationTime, $issuer;
    $token = array(
        "iss" => $issuer,
        "aud" => $issuer,
        "iat" => $issuedAt,
        "exp" => $expirationTime
    );

    $jwt = JWT::encode($token, $key, 'HS256');
    return $jwt;
}

function decodeJWT($jwt) {
    global $key, $issuer;
    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        if ($decoded->iss !== $issuer) {
            return false;
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
