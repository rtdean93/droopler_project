<?php

namespace Drupal\d_dotpay\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a custom message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "d_dotpay_checkout_pane",
 *   label = @Translation("Dotpay checkout"),
 * )
 */
class DotpayCheckoutPane extends CheckoutPaneBase {

  /**
   * Payment gateway plugin.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $gateway;

  /**
   * Payment gateway plugin configuration.
   *
   * @var array
   */
  protected $gatewayConfiguration;

  /**
   * DotpayCheckoutPane constructor.
   *
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);
    $this->gateway = $this->entityTypeManager->getStorage('commerce_payment_gateway')
      ->loadByProperties([
        'plugin' => 'd_dotpay_checkout',
      ]);
    $this->gatewayConfiguration = reset($this->gateway)->getPluginConfiguration();
  }

  /**
   * {@inheritDoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    if (isset($complete_form['payment_information'])) {
      $complete_form['payment_information']['channels'] = [
        '#type' => 'fieldset',
        '#attributes' => [
          'class' => [
            'payment-channel-container',
          ],
        ],
        '#states' => [
          'visible' => [
            ':input[name="payment_information[payment_method]"]' => ['value' => 'dotpay'],
          ],
        ],
      ];
      $complete_form['payment_information']['channels']['payment_channel'] = [
        '#type' => 'radios',
        '#title' => t('Select payment Channel'),
        '#options' => $this->generatePaymentChannels(),
      ];
      $complete_form['payment_information']['blik_code'] = [
        '#type' => 'textfield',
        '#title' => t('Blik code'),
        '#states' => [
          'visible' => [
            ':input[name="payment_information[payment_method]"]' => ['value' => 'dotpay_blik'],
          ],
        ],
      ];
    }

    return $pane_form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValues();
    if ($values['payment_information']['payment_method'] == 'dotpay') {
      $this->order->setData('payment_channel', $values['payment_information']['channels']['payment_channel']);
    }
    if ($values['payment_information']['payment_method'] == 'dotpay_blik') {
      $this->order->setData('payment_channel', 73);
      $this->order->setData('blik_code', $values['payment_information']['blik_code']);
    }
    parent::submitPaneForm($pane_form, $form_state, $complete_form);
  }

  /**
   * {@inheritDoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValues();
    if ($values['payment_information']['payment_method'] === 'dotpay_blik' && !preg_match('/^[\d]{6}$/m', $values['payment_information']['blik_code'])) {
      $form_state->setErrorByName('blik_code', $this->t('Provide valid 6 characters Blik code.'));
    }
    parent::validatePaneForm($pane_form, $form_state, $complete_form);
  }

  /**
   * Returns array of markups for payment channels radio selection.
   *
   * @return array
   *   Markup array for options.
   */
  private function generatePaymentChannels() {
    $separatedChannels = [73];
    $channels = [];
    $dapi = \Drupal::service('d_dotpay.api_operations');
    $price = $this->order->getTotalPrice();
    $options = $dapi->getAvailablePaymentChannels(
      $this->gatewayConfiguration['mode'],
      $this->gatewayConfiguration['seller_id'],
      $price->getNumber(),
      $price->getCurrencyCode());
    if (!empty($options)) {
      foreach ($options->channels as $channel) {
        if (in_array($channel->id, $separatedChannels)) {
          continue;
        }
        if ($channel->is_disable === 'False' && $channel->is_not_online === 'False') {
          $channels[$channel->id] =
            [
              '#theme' => 'payment_channel',
              '#channelId' => $channel->id,
              '#channelLogo' => $channel->logo,
              '#channelName' => $channel->name,
            ];
        }
      }
    }

    return $channels;
  }

}
