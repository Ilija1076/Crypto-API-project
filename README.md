# Symfony Cryptocurrency Project

## Overview
This project is a Symfony application designed to fetch cryptocurrency data from the CoinGecko API, store it in a MySQL database, and provide various API endpoints for querying the data.

## Requirements
- PHP 7.3 or higher
- Composer
- MySQL
- Docker (optional)

## Getting Started

### Without Docker

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/crypto-project.git
   cd crypto-project
Make sure you have Composer installed, then run:
composer install
php bin/console doctrine:database:create cryptodb
php bin/console doctrine:migrations:migrate
php bin/console app:fetch-crypto-data

And you have to change the .env file database url to match your localhost address in case you are using some local hosting 
for example DATABASE_URL="mysql://root:password@127.0.0.1:3306/cryptodb"

###With Docker
Prerequisites
Ensure you have the following installed on your machine:

Docker
Docker Compose

The project uses Docker containers for the web server (Nginx), the PHP application, and the MySQL database. The Docker configuration is managed via docker-compose.yml.

docker-compose up --build -d
docker ps
You should see containers for:

PHP application (app)
Nginx (webserver)
MySQL (db)
phpmyadmin

Access the PHP Container:
docker exec -it app bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console app:fetch-cryptocurrencies
Access the Application
API Endpoints:

Access the API through your browser or via cURL/Postman:
/api/crypto-currency/{symbol}: Get data for a specific cryptocurrency by its symbol.
/api/crypto-currency?min={value}: Get cryptocurrencies with a price greater than the specified value.
/api/crypto-currency?max={value}: Get cryptocurrencies with a price lower than the specified value.
/api/crypto-currency/top-10-current: Get the top 10 cryptocurrencies by market cap.
/api/crypto-currency/top-10-ath: Get the top 10 cryptocurrencies by all time high.
/api/crypto-currency/compare?symbol1={symbol1}&symbol2={symbol2}: Compare two cryptocurrencies.


Visit the Nginx web server at http://localhost:8080 for the HTML views that display the cryptocurrency data in a user-friendly format.
for example:http://localhost:8080/index.php/api/crypto-currency
Stop the application:
docker-compose down

