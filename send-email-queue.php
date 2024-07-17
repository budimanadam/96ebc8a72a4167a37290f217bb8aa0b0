<?php

//Load Composer's autoloader
require 'vendor/autoload.php';
require 'auth.php';

//Import package that is needed
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

function sendQueue($recipient, $subject, $body) {
    // RabbitMQ configuration
    $host = $_ENV['HOST'];
    $port = $_ENV['PORT'];
    $user = $_ENV['USER'];
    $pass = $_ENV['PASS'];
    $queue = $_ENV['QUEUE'];

    // Connect to RabbitMQ server
    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $connection->channel();

    // Declare the queue
    $channel->queue_declare($queue, false, true, false, false);

    // Prepare email data
    $emailData = array(
        'recipient' => $recipient,
        'subject' => $subject,
        'body' => $body
    );

    // Create a message
    $msg = new AMQPMessage(
        json_encode($emailData),
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );

    // Publish the message to the queue
    $channel->basic_publish($msg, '', $queue);

    // Close channel and connection
    $channel->close();
    $connection->close();
    return true;
}

// Example endpoint to send email asynchronously
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Getting for .env at the root directory
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
        $dotenv->load();

        // PostgreSQL configuration
        $dbhost = $_ENV['DBHOST'];
        $dbport = $_ENV['DBPORT'];
        $dbname = $_ENV['DBNAME'];
        $dbuser = $_ENV['DBUSER'];
        $dbpass = $_ENV['DBPASS'];

        // Get JWT token from Authorization header
        $headers = apache_request_headers();

        $jwt = $headers['Authorization'] ?? '';
        
        if (decodeJWT($jwt)) {
            // Get POST data
            $data = json_decode(file_get_contents('php://input'), true);
            $recipient = $data['recipient'];
            $subject = $data['subject'];
            $body = $data['body'];

            $connection_string = "host={$dbhost} dbname={$dbname} user={$dbuser} password={$dbpass}";
            $dbconn = pg_connect($connection_string);

            if (!$dbconn) {
                die("Error in connection: " . pg_last_error());
            } else {
                echo "Connected successfully";
            }

            // SQL query to insert data into table
            $query = "INSERT INTO sent_emails (recipient_email, subject, body) VALUES ($1, $2, $3)";

            // Execute query with parameters
            $result = pg_query_params($dbconn, $query, array($recipient, $subject, $body));

            //Send to Queue
            $resultSendQueue = sendQueue($recipient, $subject, $body);

            ob_end_clean();
            http_response_code(200);
            echo json_encode(array("message" => "Email sent"));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Unauthorized"));
        }
    } catch (\Throwable $th) {
        throw $th;
    }   
}
?>
