<?php

namespace App\Facades;

use PulseFrame\Facades\Env;

/**
 * Class Connectwise
 * 
 * @category facades
 * @name Connectwise
 * 
 * This class is responsible for making API requests to the ConnectWise system. It initializes cURL settings, 
 * constructs the required headers, and performs HTTP requests. The responses are decoded and returned as 
 * associative arrays.
 */
class Connectwise
{
  private static $url;
  private static $curl;
  private static $decodedResponse;

  /**
   * Initialize environment variables and cURL settings.
   *
   * @category facades
   * 
   * This private function checks if the environment variables are loaded, and if not, it loads them using 
   * the Env facade. It initializes the base URL and cURL handle if they haven't been set already.
   * 
   * Example usage:
   * Connectwise::init();
   */
  private static function init()
  {
    if (is_null(self::$curl)) {
      self::$url = Env::get('connectwise.url');
      self::$curl = curl_init();
    }
  }

  /**
   * Make a request to the ConnectWise API.
   *
   * @category facades
   * 
   * @param string $endpoint The API endpoint.
   * @param string $method The HTTP method to use (default is 'GET').
   * @param array|null $data The data to send with the request (optional).
   * @return array The decoded JSON response from the ConnectWise API.
   *
   * This function prepares and executes a cURL request to the specified ConnectWise API endpoint. It includes
   * authorization headers and sets the HTTP method and request data as necessary. The response is decoded and
   * returned as an associative array.
   * 
   * Example usage:
   * $response = Connectwise::request('/company/contacts');
   * $postResponse = Connectwise::request('/company/contacts', 'POST', $data);
   */
  public static function request($endpoint, $method = 'GET', $data = null)
  {
    self::init();

    $url = self::$url . $endpoint;

    $headers = array(
      'Authorization: Basic ' . base64_encode(Env::get('connectwise.companyname') . "+" . Env::get('connectwise.publicKey') . ":" . Env::get('connectwise.privateKey')),
      'clientId: ' . Env::get('connectwise.clientId'),
      'Content-type: application/json'
    );

    curl_setopt_array(self::$curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_CUSTOMREQUEST => $method,
    ));

    if ($method === 'POST' && $data !== null) {
      curl_setopt(self::$curl, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec(self::$curl);
    curl_close(self::$curl);

    self::$decodedResponse = json_decode($response, true);

    return self::$decodedResponse;
  }
}