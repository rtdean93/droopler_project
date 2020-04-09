<?php

namespace Drupal\d_dotpay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Service provides interaction with Dotpay API.
 *
 * @package Drupal\d_dotpay
 */
class DotpayAPIOperations {

  /**
   * Guzzle http client instance.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * DotpayAPIOperations constructor.
   *
   * @param \GuzzleHttp\Client $http_client
   *   Guzzle http client.
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Method generates chk value required by Dotpay.
   *
   * @param string $dotpayPin
   *   PIN from dotpay configuration.
   * @param array $data
   *   Array of additional parameters.
   *
   * @return string
   *   Hash for dotpay chk value.
   */
  public function generateChk($dotpayPin, array $data) {
    $chk = $dotpayPin .
      (isset($data['api_version']) ? $data['api_version'] : NULL) .
      (isset($data['lang']) ? $data['lang'] : NULL) .
      (isset($data['id']) ? $data['id'] : NULL) .
      (isset($data['pid']) ? $data['pid'] : NULL) .
      (isset($data['amount']) ? (float) $data['amount'] : NULL) .
      (isset($data['currency']) ? $data['currency'] : NULL) .
      (isset($data['description']) ? $data['description'] : NULL) .
      (isset($data['control']) ? $data['control'] : NULL) .
      (isset($data['channel']) ? $data['channel'] : NULL) .
      (isset($data['credit_card_brand']) ? $data['credit_card_brand'] : NULL) .
      (isset($data['ch_lock']) ? $data['ch_lock'] : NULL) .
      (isset($data['channel_groups']) ? $data['channel_groups'] : NULL) .
      (isset($data['onlinetransfer']) ? $data['onlinetransfer'] : NULL) .
      (isset($data['url']) ? $data['url'] : NULL) .
      (isset($data['type']) ? $data['type'] : NULL) .
      (isset($data['buttontext']) ? $data['buttontext'] : NULL) .
      (isset($data['urlc']) ? $data['urlc'] : NULL) .
      (isset($data['firstname']) ? $data['firstname'] : NULL) .
      (isset($data['lastname']) ? $data['lastname'] : NULL) .
      (isset($data['email']) ? $data['email'] : NULL) .
      (isset($data['street']) ? $data['street'] : NULL) .
      (isset($data['street_n1']) ? $data['street_n1'] : NULL) .
      (isset($data['street_n2']) ? $data['street_n2'] : NULL) .
      (isset($data['state']) ? $data['state'] : NULL) .
      (isset($data['addr3']) ? $data['addr3'] : NULL) .
      (isset($data['city']) ? $data['city'] : NULL) .
      (isset($data['postcode']) ? $data['postcode'] : NULL) .
      (isset($data['phone']) ? $data['phone'] : NULL) .
      (isset($data['country']) ? $data['country'] : NULL) .
      (isset($data['code']) ? $data['code'] : NULL) .
      (isset($data['p_info']) ? $data['p_info'] : NULL) .
      (isset($data['p_email']) ? $data['p_email'] : NULL) .
      (isset($data['n_email']) ? $data['n_email'] : NULL) .
      (isset($data['expiration_date']) ? $data['expiration_date'] : NULL) .
      (isset($data['deladdr']) ? $data['deladdr'] : NULL) .
      (isset($data['recipient_account_number']) ? $data['recipient_account_number'] : NULL) .
      (isset($data['recipient_company']) ? $data['recipient_company'] : NULL) .
      (isset($data['recipient_first_name']) ? $data['recipient_first_name'] : NULL) .
      (isset($data['recipient_last_name']) ? $data['recipient_last_name'] : NULL) .
      (isset($data['recipient_address_street']) ? $data['recipient_address_street'] : NULL) .
      (isset($data['recipient_address_building']) ? $data['recipient_address_building'] : NULL) .
      (isset($data['recipient_address_apartment']) ? $data['recipient_address_apartment'] : NULL) .
      (isset($data['recipient_address_postcode']) ? $data['recipient_address_postcode'] : NULL) .
      (isset($data['recipient_address_city']) ? $data['recipient_address_city'] : NULL) .
      (isset($data['application']) ? $data['application'] : NULL) .
      (isset($data['application_version']) ? $data['application_version'] : NULL) .
      (isset($data['warranty']) ? $data['warranty'] : NULL) .
      (isset($data['bylaw']) ? $data['bylaw'] : NULL) .
      (isset($data['personal_data']) ? $data['personal_data'] : NULL) .
      (isset($data['credit_card_number']) ? $data['credit_card_number'] : NULL) .
      (isset($data['credit_card_expiration_date_year']) ? $data['credit_card_expiration_date_year'] : NULL) .
      (isset($data['credit_card_expiration_date_month']) ? $data['credit_card_expiration_date_month'] : NULL) .
      (isset($data['credit_card_security_code']) ? $data['credit_card_security_code'] : NULL) .
      (isset($data['credit_card_store']) ? $data['credit_card_store'] : NULL) .
      (isset($data['credit_card_store_security_code']) ? $data['credit_card_store_security_code'] : NULL) .
      (isset($data['credit_card_customer_id']) ? $data['credit_card_customer_id'] : NULL) .
      (isset($data['credit_card_id']) ? $data['credit_card_id'] : NULL) .
      (isset($data['blik_code']) ? $data['blik_code'] : NULL) .
      (isset($data['credit_card_registration']) ? $data['credit_card_registration'] : NULL) .
      (isset($data['surcharge_amount']) ? $data['surcharge_amount'] : NULL) .
      (isset($data['surcharge']) ? $data['surcharge'] : NULL) .
      (isset($data['surcharge']) ? $data['surcharge'] : NULL) .
      (isset($data['ignore_last_payment_channel']) ? $data['ignore_last_payment_channel'] : NULL) .
      (isset($data['vco_call_id']) ? $data['vco_call_id'] : NULL) .
      (isset($data['vco_update_order_info']) ? $data['vco_update_order_info'] : NULL) .
      (isset($data['vco_subtotal']) ? $data['vco_subtotal'] : NULL) .
      (isset($data['vco_shipping_handling']) ? $data['vco_shipping_handling'] : NULL) .
      (isset($data['vco_tax']) ? $data['vco_tax'] : NULL) .
      (isset($data['vco_discount']) ? $data['vco_discount'] : NULL) .
      (isset($data['vco_gift_wrap']) ? $data['vco_gift_wrap'] : NULL) .
      (isset($data['vco_misc']) ? $data['vco_misc'] : NULL) .
      (isset($data['vco_promo_code']) ? $data['vco_promo_code'] : NULL) .
      (isset($data['credit_card_security_code_required']) ? $data['credit_card_security_code_required'] : NULL) .
      (isset($data['credit_card_operation_type']) ? $data['credit_card_operation_type'] : NULL) .
      (isset($data['credit_card_avs']) ? $data['credit_card_avs'] : NULL) .
      (isset($data['credit_card_threeds']) ? $data['credit_card_threeds'] : NULL) .
      (isset($data['customer']) ? $data['customer'] : NULL) .
      (isset($data['gp_token']) ? $data['gp_token'] : NULL) .
      (isset($data['blik_refusenopayid']) ? $data['blik_refusenopayid'] : NULL) .
      (isset($data['auto_reject_date']) ? $data['auto_reject_date'] : NULL) .
      (isset($data['ap_token']) ? $data['ap_token'] : NULL);

    return hash('sha256', $chk);
  }

  /**
   * Method returns available payment channels for given seller.
   *
   * @param string $mode
   *   Gateway mode from configuration.
   * @param int $dotpayId
   *   Seller ID.
   * @param int $amount
   *   Transaction amount.
   * @param string $currency
   *   Transaction currency.
   *
   * @return object|null
   *   Object containing payment channels.
   */
  public function getAvailablePaymentChannels($mode, $dotpayId = 0, $amount = 1000, $currency = 'PLN') {
    $apiUrl = sprintf("%spayment_api/v1/channels/", $this->getApiAddress($mode));
    $response = NULL;
    try {
      $request = $this->httpClient->get($apiUrl, [
        'query' => [
          'id' => $dotpayId,
          'currency' => $currency,
          'amount' => (float) $amount,
          'format' => 'json',
        ],
      ]);
      if ($request->getStatusCode() != 200) {
        \Drupal::logger('d_dotpay')
          ->error('API returned: ' . $request->getStatusCode());
      }
      else {
        $response = json_decode($request->getBody()->getContents());
      }
    }
    catch (ClientException $e) {
      \Drupal::logger('d_dotpay')->error($e->getMessage());
    }

    return $response;
  }

  /**
   * Returns API address based on gateway mode.
   *
   * @param string $mode
   *   API mode set in configuration form.
   *
   * @return string
   *   API address.
   */
  public function getApiAddress($mode) {
    switch ($mode) {
      case 'test':
        $apiUrl = 'https://ssl.dotpay.pl/test_payment/';
        break;

      default:
        $apiUrl = 'https://ssl.dotpay.pl/t2/';
        break;
    }

    return $apiUrl;
  }

}
