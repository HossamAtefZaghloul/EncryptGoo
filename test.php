<?php

$key1 = 'base64:LhG2UQdU3Bv05AwpKHvrOJ3khUhDbtxhTb5IrQssAxU=';
$decodedKey1 = base64_decode($key1);

if (strlen($decodedKey1) !== 32) {
    echo 'Failed'. "\n";
}

echo 'Key is valid and 32 bytes long.';


$key = 'o67xGZq0Q3x6FcsMfLqd05I2jTtvIjLNNnunzaPjClI=';
$decodedKey = base64_decode($key);

if (strlen($decodedKey) !== 32) {
    echo 'Failed' . "\n";
}

echo 'Key is valid and 32 bytes long.';