<?php

namespace Drupal\d_baselinker_ifirma;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\d_baselinker\Classes\ErrorHandler;
use Drupal\d_baselinker\CurlService;

/**
 * Class InvoiceService provides methods to interact with ifirma invoice API.
 *
 * @package Drupal\d_baselinker_ifirma
 */
class InvoiceService {

  /**
   * Curl service instance.
   *
   * @var \Drupal\d_baselinker\CurlService
   */
  protected $curlService;

  /**
   * Config factory instance.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Module configuration object.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * InvoiceService constructor.
   *
   * @param \Drupal\d_baselinker\CurlService $curl_service
   *   Curl service instance.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory instance.
   */
  public function __construct(CurlService $curl_service, ConfigFactory $config_factory) {
    $this->curlService = $curl_service;
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('d_baselinker_ifirma.settings');
  }

  /**
   * Updates specified order with invoice data.
   *
   * @param array $params
   *   Request params.
   *
   * @return array
   *   Response array for encoding.
   */
  public function updateInvoice(array $params) {
    try {
      $order = Order::load($params['id']);
      if (!empty($order)) {
        $order->set('invoice_number', $params['invoice_no']);
        $order->set('baselinker_order_id', $params['baselinker_id']);
        $file = $this->prepareInvoice($params['invoice_no']);
        $order->set('invoice', $file);
      }
      $order->save();
      $response = [
        'file_updated' => $params['baselinker_id'],
      ];
    }
    catch (EntityStorageException $e) {
      \Drupal::logger('d_baselinker_ifirma')->error($e->getMessage());
      $error = new ErrorHandler('operation_failed', "Failed to update order: {$params['baselinker_id']}");
      $response = $error->getErrorResponse();
    }

    return $response;
  }

  /**
   * Downloads PDF invoice from API, and creates new File entity.
   *
   * @param string $invoiceNumber
   *   Invoice number to download.
   *
   * @return \Drupal\file\FileInterface|false
   *   Returns whether the file entity has been created.
   */
  public function prepareInvoice($invoiceNumber) {
    $invoiceNumber = preg_replace('/[\W]/', '_', $invoiceNumber);
    $url = "{$this->config->get('api_url')}/${invoiceNumber}.pdf.single";
    $username = $this->config->get('username');
    $key = $this->config->get('api_key');
    if (empty($this->config->get('api_url'))) {
      \Drupal::logger('d_baselinker_ifirma')
        ->error('Required parameter missing: api_url');
      return FALSE;
    }
    $encodingKey = $this->prepareKey($key);
    $hashMessage = $this->getHashMessage($url, $this->config->get('key_type'), $username, $encodingKey);
    $options = [
      'headers' => [
        'Accept: application/pdf',
        'Content-type: application/pdf; charset = UTF-8',
        "Authentication: IAPIS user={$username}, hmac-sha1={$hashMessage}",
      ],
      'options' => [
        'CURLOPT_TIMEOUT' => 300,
        'CURLOPT_CONNECTTIMEOUT' => 100,
        'CURLOPT_HTTPGET' => TRUE,
        'CURLOPT_SSL_VERIFYHOST' => 0,
        'CURLOPT_SSL_VERIFYPEER' => 0,
      ],
      'file' => 'invoice.pdf',
    ];
    $response = $this->curlService->curlGet($url, $options);
    if ($response['body']) {
      $file_data = fopen('invoice.pdf', 'r');
      $file = file_save_data($file_data, "public://{$invoiceNumber}.pdf", FileSystemInterface::EXISTS_REPLACE);
      unlink($file_data);
      if (!empty($file)) {
        return $file;
      }
    }
    return FALSE;
  }

  /**
   * Recalculates api key from hexadecimal to decimal binary.
   *
   * @param string $key
   *   API key in hexadecimal format.
   *
   * @return string
   *   Binary representation of the API key.
   */
  private function prepareKey($key) {
    $binaryKey = '';
    $hexes = str_split($key, 2);
    foreach ($hexes as $hex) {
      $binaryKey .= chr(hexdec($hex));
    }

    return $binaryKey;
  }

  /**
   * Encodes hash message needed for API communication.
   *
   * @param string $url
   *   API url.
   * @param string $keyName
   *   Type of key used.
   * @param string $userName
   *   Username for application.
   * @param string $encodingKey
   *   Key for the encryption.
   *
   * @return string
   *   String containing hashed parameters, encrypted with the key.
   */
  private function getHashMessage($url, $keyName, $userName, $encodingKey) {
    return hash_hmac('sha1', $url . $userName . $keyName, $encodingKey);
  }

}
