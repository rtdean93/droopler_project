<?php


namespace Drupal\d_dotpay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Dotpay offsite Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id="d_dotpay_checkout",
 *   label = @Translation("Dotpay"),
 *   display_label = @Translation("Dotpay"),
 *   forms = {
 *    "offsite-payment" =
 *   "Drupal\d_dotpay\PluginForm\DotpayPaymentForm"
 *   },
 * )
 *
 * @package Drupal\d_dotpay\Plugin\Commerce\PaymentGateway
 */
class DotpayGateway extends OffsitePaymentGatewayBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'pin' => '',
        'seller_id' => '',
        'api_version' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);
    $form['pin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PIN'),
      '#description' => $this->t('Seller PIN from Dotpay.'),
      '#default_value' => $this->configuration['pin'],
      '#required' => TRUE,
    ];

    $form['seller_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Seller ID'),
      '#description' => $this->t('Seller ID number from Dotpay'),
      '#default_value' => $this->configuration['seller_id'],
      '#required' => TRUE,
    ];

    $form['api_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API version'),
      '#description' => $this->t('Api version to use from Dotpay documentaion.'),
      '#default_value' => $this->configuration['api_version'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    $this->configuration['seller_id'] = $values['seller_id'];
    $this->configuration['pin'] = $values['pin'];
    $this->configuration['api_version'] = $values['api_version'];
  }

}
