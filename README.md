# Project Setup Instructions
## Clone the Project

## Install Dependencies :
composer install
npm install

## Configure .env File:
change (.env.example) To-> (.env)

## Update database details in .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name ->FOR EXAMPLE : DB_DATABASE=encryptgo 
DB_USERNAME=root
DB_PASSWORD=

## Use The Laravel built-in command to generate a secure encryption key :
php artisan key:generate --show
and update .env with it
For example : APP_ENCRYPTION_KEY=o67xGZq0Q3x6FcsMfLqd05I2jTtvIjLNNnunzaPjClI=
make sure the key name APP_ENCRYPTION_KEY
