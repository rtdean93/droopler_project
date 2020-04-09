<?php


namespace Drupal\d_dotpay\PluginForm;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class provides offsite payment form for Dotpay.
 *
 * @package Drupal\d_dotpay\PluginForm
 */
class DotpayPaymentForm extends PaymentOffsiteForm {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $payment = $this->entity;
    $dotpayApi = \Drupal::service('d_dotpay.api_operations');
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $configuration = $payment_gateway_plugin->getConfiguration();
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $payment->getOrder();
    $customer = $order->getCustomer();
    $billing_profile = $order->getBillingProfile();
    $billing_address = $billing_profile->get('address')->getValue()[0];
    $data = [
      'api_version' => $configuration['api_version'],
      'id' => $configuration['seller_id'],
      'amount' => $payment->getAmount()->getNumber(),
      'currency' => $payment->getAmount()->getCurrencyCode(),
      'description' => sprintf("%s: %s", t('Order number'), $payment->getOrderId()),
      'channel' => $order->getData('payment_channel'),
      'url' => $form['#return_url'],
      'type' => '4',
      'bylaw' => '1',
      'personal_data' => '1',
      'firstname' => $billing_address['given_name'],
      'lastname' => $billing_address['family_name'],
      'email' => $customer->getEmail(),
      'street' => '',
      'postcode' => $billing_address['postal_code'],
      'country' => $billing_address['country_code'],
      'lang' => $billing_address['langcode'],
      'city' => $billing_address['locality'],
    ];
    if ($order->getData('payment_channel') == 73) {
      $data['blik_code'] = $order->getData('blik_code');
    }

    $data['chk'] = $dotpayApi->generateChk($configuration['pin'], $data);
    $apiUrl = $dotpayApi->getApiAddress($configuration['mode']);

    return $this->buildRedirectForm($form, $form_state, $apiUrl, $data);
  }

}
