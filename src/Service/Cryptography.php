<?php

namespace App\Service;

/**
 * This class encode and decode the string values and return it back with
 * encrypted or decrypted string.
 *  
 * @author Kumaresh Baksi <kumaresh.baksi@innoraft.com>
 */
class Cryptography
{
  /**
   * Encode function uses base64 encoding function to encrypt the string
   *
   * @param string $msg
   *   This is the string which will be encrypted.
   * 
   * @return string
   *    Method returns a encoded message.
   */
  public function encode(string $msg)
  {
    return urlencode(base64_encode($msg));
  }
  /**
   * Decode function uses base64 decoding function to decrypt the string
   *
   * @param string $msg
   *   This is the string which will be decrypted.
   * 
   * @return string
   *   Method returns a decoded message.
   */
  public function decode(string $msg)
  {
    return base64_decode(urldecode($msg));
  }
}