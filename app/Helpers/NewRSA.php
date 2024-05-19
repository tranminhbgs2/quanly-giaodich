<?php

namespace App\Helpers;

/**
 * RSA Algorithmic class
 * Signature and ciphertext encoding: base64 string/hexadecimal string/binary string stream
 * Filling mode: PKCS1 Padding (encryption and decryption)/NOPadding (decryption)
 *
 * Notice:Only accepts a single block. Block size is equal to the RSA key size!
 * If the key length is 1024 bit s, the encrypted data should be less than 128 bytes, plus 11 bytes of information of PKCS1 Padding itself, so the plaintext should be less than 117 bytes.
 *
 * @author: ZHIHUA_WEI
 * @version: 1.0.0
 * @date: 2017/06/30
 */
class NewRSA
{
    private $pubKey = null;
    private $priKey = null;

    /**
     * Constructor
     *
     * @param string Public key file (passed in when checking and encrypting)
     * @param string Private key file (incoming when signing and decrypting)
     */
    public function __construct($public_key_file = '', $private_key_file = '')
    {
        if ($public_key_file) {
            $this->setPublicKey($public_key_file);
        }
        if ($private_key_file) {
            $this->setPrivateKey($private_key_file);
        }
    }

    // Private methods
    /**
     * Custom error handling
     */
    private function _error($msg)
    {
        die('RSA Error:' . $msg); //TODO
    }

    /**
     * Detecting Filling Type
     * Encryption only supports PKCS1_PADDING
     * Decryption supports PKCS1_PADDING and NO_PADDING
     *
     * @param int Filling mode
     * @param string Encryption en/decryption de
     * @return bool
     */
    private function _checkPadding($padding, $type)
    {
        if ($type == 'en') {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        } else {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING:
                case OPENSSL_NO_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    private function _encode($data, $code)
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_encode('' . $data);
                break;
            case 'hex':
                $data = bin2hex($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    private function _decode($data, $code)
    {
        switch (strtolower($code)) {
            case 'base64':
                $data = base64_decode($data);
                break;
            case 'hex':
                $data = $this->_hex2bin($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    public function setPublicKey($file)
    {
        $key_content = $this->_readFile($file);
        if ($key_content) {
            $this->pubKey = openssl_get_publickey($key_content);
        }
    }

    public function setPrivateKey($file)
    {
        $key_content = $this->_readFile($file);
        if ($key_content) {
            $this->priKey = openssl_get_privatekey($key_content);
        }
    }

    private function _readFile($file)
    {
        $ret = false;
        if (!file_exists($file)) {
            $this->_error("The file {$file} is not exists");
        } else {
            $ret = file_get_contents($file);
        }
        return $ret;
    }

    private function _hex2bin($hex = false)
    {
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }

    /**
     * Generate signature
     *
     * @param string Signature material
     * @param string Signature encoding (base64/hex/bin)
     * @return Signature value
     */
    public function sign($data, $code = 'base64')
    {
        $ret = false;
        if (openssl_sign($data, $ret, $this->priKey)) {
            $ret = $this->_encode($ret, $code);
        }
        return $ret;
    }

    /**
     * Verify signature
     *
     * @param string Signature material
     * @param string Signature value
     * @param string Signature encoding (base64/hex/bin)
     * @return bool
     */
    public function verify($data, $sign, $code = 'base64')
    {
        $ret = false;
        $sign = $this->_decode($sign, $code);
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $this->pubKey)) {
                case 1:
                    $ret = true;
                    break;
                case 0:
                case -1:
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    /**
     * encryption
     *
     * @param string Plaintext
     * @param string Ciphertext encoding (base64/hex/bin)
     * @param int Filling mode (apparently php has bug s, so only OPENSSL_PKCS1_PADDING is currently supported)
     * @return string ciphertext
     */
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING)
    {
        if ($this->pubKey) {
            $ret = false;
            if (!$this->_checkPadding($padding, 'en')) $this->_error('padding error');
            if (openssl_public_encrypt($data, $result, $this->pubKey, $padding)) {
                $ret = $this->_encode($result, $code);
            }
            return $ret;
        } else {
            return null;
        }
    }

    /**
     * Decrypt
     *
     * @param string ciphertext
     * @param string Ciphertext encoding (base64/hex/bin)
     * @param int Filling mode (OPENSSL_PKCS1_PADDING/OPENSSL_NO_PADDING)
     * @param bool Whether to flip plaintext (When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block)
     * @return string Plaintext
     */
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false)
    {
        if ($this->priKey) {
            $ret = false;
            $data = $this->_decode($data, $code);
            if (!$this->_checkPadding($padding, 'de')) $this->_error('padding error');
            if ($data !== false) {
                if (openssl_private_decrypt($data, $result, $this->priKey, $padding)) {
                    $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
                }
            }
            return $ret;
        } else {
            return null;
        }
    }
}
