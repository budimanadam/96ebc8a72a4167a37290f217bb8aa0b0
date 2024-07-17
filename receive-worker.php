<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Getting for .env at the root directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

// RabbitMQ configuration
$host = $_ENV['HOST'];
$port = $_ENV['PORT'];
$user = $_ENV['USER'];
$pass = $_ENV['PASS'];
$queue = $_ENV['QUEUE'];

$connection = new AMQPStreamConnection($host, $port, $user, $pass);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] Received ', $msg->getBody(), "\n";
    $payload = json_decode($msg->getBody());

    $resultSendEmail = sendEmail($payload->recipient, $payload->subject, $payload->body);
};

$channel->basic_consume($queue, '', false, true, false, false, $callback);

try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage();
}

function sendEmail($recipient, $subject, $body) {
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    $senderEmail = $_ENV['SENDER_EMAIL'];
    $senderAppPassword = $_ENV['SENDER_APP_PASSWORD'];

    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $senderEmail;
    $mail->Password = $senderAppPassword;
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    //Recipients
    $mail->setFrom($recipient, 'Sender');
    $mail->addAddress($recipient, 'Recipient');

    //Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
    echo 'Message has been sent';
    return true;
}

$channel->close();
$connection->close();