<?php

namespace Drupal\d_baselinker;

/**
 * Class CurlService.
 *
 * Wrapper for interacting with external APIs.
 */
class CurlService {

  /**
   * Method calls specified URL and returns response.
   *
   * @param string $url
   *   Url to send requests to.
   * @param array|null $parameters
   *   Request parameters e.g headers for request.
   *
   * @return array[
   *   'information' => (array) Contains response headers.
   *   'body' => (array) Contains response body.
   *              ]
   */
  public function curlGet($url, array $parameters = NULL) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    switch (TRUE) {
      case !empty($parameters['headers']):
        curl_setopt($ch, CURLOPT_HTTPHEADER, $parameters['headers']);

      case !empty($parameters['options']):
        $this->setCurlOptions($parameters['options'], $ch);

      case !empty($parameters['file']):
        $file = fopen($parameters['file'], 'w+');
        curl_setopt($ch, CURLOPT_FILE, $file);

      default:
        break;
    }

    $body = curl_exec($ch);
    $information = curl_getinfo($ch);
    curl_close($ch);
    if (!empty($file)) {
      fclose($file);
    }
    return [
      'body' => $body,
      'information' => $information,
    ];
  }

  /**
   * Sets options for the curl handle.
   *
   * @param array $options
   *   Curl options array.
   * @param resource $ch
   *   Curl handle to modify.
   */
  private function setCurlOptions(array $options, &$ch) {
    foreach ($options as $option => $value) {
      curl_setopt($ch, $option, $value);
    }
  }

}
