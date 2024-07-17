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
