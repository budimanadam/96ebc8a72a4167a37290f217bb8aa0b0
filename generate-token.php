<?php
require __DIR__ . '/vendor/autoload.php';
require 'auth.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jwt = authenticate();
    http_response_code(200);
    echo json_encode(array("JWT" => $jwt));
}
?>
