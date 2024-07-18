## email-queue-worker

email-queue-worker version 3.1.0.

[email-queue-worker Backend]

- Using PHP


## Server Requirements

- PHP 7.1.x
- Postgres DB 15.x
- Composer 2.6.x
- RabbitMQ 3.12.x

## Environment Setup

- Run `composer install --ignore-platform-req=ext-sockets` to install all required package for backend
- Copy `.env.example` to `.env` and fill in appropriate values accordingly

## Database Setup

- Run the `psql` script in postgres

## How to the project

- Run `php -S localhost:8000` for the API
- Run `php receive-worker.php` for the worker

## Generate Token

```
curl --location --request POST 'http://localhost:8000/generate-token.php' \
--header 'Content-Type: application/json'
```
## Send Email to Queue

```
curl --location 'http://localhost:8000/send-email-queue.php' \
--header 'Content-Type: application/json' \
--header 'Authorization: {auth}' \
--data-raw '{
    "recipient": "test@yahoo.co.id",
    "subject": "Subject Email",
    "body": "Body Email."
}'
```