# Project Setup Instructions
## Clone the Project

## Install Dependencies :
### cd myapp
### composer install
### npm install

## Configure .env File:
### change (.env.example) To-> (.env)

## Update database details in .env:
### DB_CONNECTION=mysql
### DB_HOST=127.0.0.1
### DB_PORT=3306
### DB_DATABASE=your_database_name ->FOR EXAMPLE : DB_DATABASE=encryptgo 
### DB_USERNAME=root
### DB_PASSWORD=

## Update PHP Configuration
### Locate the php.ini file:
### Windows: C:\xampp\php\php.ini
### Modify the following settings:
### upload_max_filesize = 10G
### post_max_size = 10G
### memory_limit = -1
### max_execution_time = 3600
### max_input_time = 3600

## Use The Laravel built-in command to generate a secure encryption key :
### php artisan key:generate --show
### and update .env with it
### For example : APP_ENCRYPTION_KEY=o67xGZq0Q3x6FcsMfLqd05I2jTtvIjLNNnunzaPjClI=
### make sure the key name is APP_ENCRYPTION_KEY

## Run database migrations:
### //Make sure to run xampp first
### php artisan migrate

## Start the Server
### php artisan serve
### npm run dev

