# Project Setup Instructions

Thank you for using this project! To ensure everything works smoothly, please follow the steps below to configure your PHP environment properly.

## Update PHP Configuration

Before running the project, please locate the `php.ini` file in your PHP installation directory and make the following changes:

### Current Configuration
```ini
php_value upload_max_filesize 50M
php_value post_max_size 60M
php_value memory_limit 128M
php_value max_execution_time 300
php_value max_input_time 300

Replace the above values with the following:

upload_max_filesize = 10G
post_max_size = 10G
memory_limit = -1
max_execution_time = 3600
max_input_time = 3600
