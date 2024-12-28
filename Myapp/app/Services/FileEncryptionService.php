<?php

namespace App\Services;

use Exception;

class FileEncryptionService
{
    protected $encryptionKey;

    public function __construct()
    {   
        $base64Key = config('app.encryption_key');
        if (!$base64Key) {
            throw new Exception('Encryption key not set in configuration.');
        }

        $this->encryptionKey = base64_decode($base64Key, true);

        if ($this->encryptionKey === false) {
            throw new Exception('Invalid Base64 encoding for the encryption key.');
        }

        if (strlen($this->encryptionKey) !== 32) {
            throw new Exception('Decoded encryption key must be 32 bytes long.');
        }
    }
    public function encryptFile($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            throw new Exception('Unable to open the file for encryption.');
        }
    
        $fileContent = stream_get_contents($handle);
        fclose($handle);
    
        if ($fileContent === false) {
            throw new Exception('Error reading the file for encryption.');
        }
    
        // Generate an IV
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
    
        // Encrypt the file content
        $encryptedContent = openssl_encrypt(
            $fileContent,
            'aes-256-cbc',
            $this->encryptionKey,
            0,
            $iv
        );
    
        if ($encryptedContent === false) {
            throw new Exception('Encryption failed.');
        }
    
        return base64_encode($iv . $encryptedContent);
    }
    

    public function decryptFile($encryptedFilePath)
    {
        $handle = fopen($encryptedFilePath, 'rb');
        if (!$handle) {
            throw new Exception('Unable to open the encrypted file.');
        }
    
        $data = stream_get_contents($handle);
        fclose($handle);
    
        if ($data === false) {
            throw new Exception('Error reading the encrypted file.');
        }
    
        $data = base64_decode($data);
        if ($data === false) {
            throw new Exception('Failed to decode Base64 content.');
        }
    
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    
        // Extract IV and encrypted data
        $iv = substr($data, 0, $ivLength);
        $cipherText = substr($data, $ivLength);
    
        if ($iv === false || $cipherText === false) {
            throw new Exception('Invalid encrypted file format.');
        }
    
        // Decrypt the content
        $decryptedContent = openssl_decrypt(
            $cipherText,
            'aes-256-cbc',
            $this->encryptionKey,
            0,
            $iv
        );
    
        if ($decryptedContent === false) {
            throw new Exception('Decryption failed.');
        }
    
        return $decryptedContent;
    }
    
}
