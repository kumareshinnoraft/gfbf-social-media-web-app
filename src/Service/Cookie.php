<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use App\Service\Cryptography;

/**
 * This class stores cookie in the browser also checks if user is logged
 * or not and deletes the cookies when required. And remove cookie is used to
 * remove the cookie.
 * 
 * @author Kumaresh Baksi <kumaresh.baksi@innoraft.com>
 */
class Cookie
{
  /**
   * This object encode and decode string.
   * 
   * @var object
   */
  private $cryptography;
  /**
   * Request variable reads the different request from
   * different pages.
   *   
   * @var object
   */
  private $request;
  /**
   * Constructor is used to initialize the objects
   */
  public function __construct()
  {
    $this->cryptography = new Cryptography();
    $this->request      = new Request();
  }
  /**
   * Set cookie function se the cookie in a serialized form.
   *
   * @param  array $value
   *   Array value contain three parameter, user, email and username.
   */
  public function setCookie(array $value)
  {
    // Serializing the values.
    $serializedValues = serialize($value);

    // Encoding the values.
    $encodedValue = $this->cryptography->encode($serializedValues);

    // Setting cookie data for one month. 
    setcookie("user-info", $encodedValue, time() + (86400 * 30), "/");

  }
  /**
   * Get cookie function is used to extract the value and returns the value.
   *
   * @param  string $name
   *   Name parameter is the key of the value user is requesting for.
   * @param  object $request
   *   An request object understands in which page value is required.
   * 
   * @return mixed
   *   If value of the key is found return string instead boolean.
   */
  public function getCookie(string $name, object $request)
  {

    $cookies = $request->cookies;
    $encodedValue = $cookies->get("user-info");

    if (isset($encodedValue)) {
      $decodedValue = $this->cryptography->decode($encodedValue);
    } else {
      return FALSE;
    }
    // Deserializing the values
    $deserializedValue = unserialize($decodedValue);
    return $deserializedValue[$name];
  }
  /**
   * This function returns if user active or not
   *
   * @param  object $request
   *   Request object is taken from the user to identify which
   *   page is requesting isActive method.
   * 
   * @return boolean
   *   If user status is active function returns TRUE instead FALSE.
   */
  public function isActive(object $request)
  {
    $cookies = $request->cookies;
    $encodedValue = $cookies->get("user-info");

    // First check if cookie is present.
    if (isset($encodedValue)) {
      $decodedValue = $this->cryptography->decode($encodedValue);
      $unsterilizedValue = unserialize($decodedValue);
      if ($unsterilizedValue["email"]) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Remove Cookie function is used to remove user data from cookie.
   *
   * @param  object $request
   *   Request is needed to get the cookies of the pages.
   * 
   * @return void
   *   This function does not returns anything as it just remove the user.
   */
  public function removeCookie(object $request)
  {
    $cookies = $request->cookies;
    if ($cookies->has("user-info")) {
      // Setting the data of the key user-info to FALSE.
      setcookie("user-info", FALSE, time() - (86400 * 30), "/");
    }
  }
}