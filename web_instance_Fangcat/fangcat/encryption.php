<?php
class AES_Cipher {

    const CIPHER_METHOD = 'aes-128-cbc'; //Name of OpenSSL Cipher

    /**
     * Encrypt data using AES Cipher (CBC) with 128 bit key
     * 
     * @param type $key - key to use should be 16 bytes long (128 bits)
     * @param type $iv - initialization vector
     * @param type $data - data to encrypt
     * @return encrypted data in base64 encoding with iv attached at end after a :
     */

    public function __construct() {}

    public function encrypt($key, $iv, $data) {

        $encrypted = openssl_encrypt($data, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv);

        return $encrypted;
    }

    /**
     * Decrypt data using AES Cipher (CBC) with 128 bit key
     * 
     * @param byte[] $key
     * @param byte[] $iv
     * @return decrypted data
     */
    public function decrypt($key, $iv , $encrypted) {
        
        $decrypted = openssl_decrypt($encrypted, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv);
        
        return $decrypted;
    }

    public function genSecureIv() {
        $wasItSecure = false;
        $efforts = 0;
        $maxEfforts = 50;
        
        do {
            $efforts++;
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD),$wasItSecure);
            if(  $efforts == $maxEfforts ) {
                throw new Exception("unable to generate secure iv");
                break;
            }
        } while( !$wasItSecure );

        return $iv;
    }

    
    public function checkIv( $iv ) {
        //echo "<br>required size : ".openssl_cipher_iv_length(self::CIPHER_METHOD)."<br>";
        //echo "<br>size: ".strlen($iv)."<br>";
        return openssl_cipher_iv_length(self::CIPHER_METHOD) == strlen($iv);
    }
    

}

?>