<?php

namespace Drupal\d_commerce_inpost\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\profile\Entity\Profile;
use Drupal\Core\Language\LanguageInterface;

/**
 * Add InPost geowidget.
 *
 * @CommerceCheckoutPane(
 *   id = "d_commerce_inpost_checkout_pane",
 *   label = @Translation("InPost geowidget"),
 * )
 */
class InPostPointPane extends CheckoutPaneBase {

  /**
   * Add hidden field 'd_commerce_inpost_point' and fieldset 'shipping_filed_d_commerce_inpost'.
   *
   * @inheritDoc
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $complete_form['shipping_methods_pane']['shipping_filed_d_commerce_inpost'] = [
      '#type' => 'fieldset',
      '#attributes' => ['class' => ['d-commerce-inpost-wrapper']],
      '#markup' => '<div id="d-commerce-inpost-point-address" class="d-commerce-inpost-point-address-data"></div>
                    <div id="d-commerce-inpost-geowidget"></div>',
      '#states' => [
        'visible' => [
          ":input[name='shipping_methods_pane[shipments][0][shipping_method][0]']" => ['value' => '2--default'],
        ],
      ],
    ];
    $complete_form['d_commerce_inpost_point_name'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'd-commerce-inpost-point-name'],
    ];
    $complete_form['d_commerce_inpost_point_address_line1'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'd-commerce-inpost-point-address-line1'],
    ];
    $complete_form['d_commerce_inpost_point_address_line2'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'd-commerce-inpost-point-address-line2'],
    ];
    return $pane_form;
  }

  /**
   * Save InPost point name and address.
   *
   * @inheritDoc
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValues();
    if (isset($values['d_commerce_inpost_point_name']) && $values['shipping_methods_pane']['shipments'][0]['shipping_method'][0] == '2--default') {
      $shipments = $this->order->get('shipments')->referencedEntities();
      if (isset($shipments[0])) {
        $address = $shipments[0]->getShippingProfile()
          ->get('address')
          ->first();
        $given_name = $address->getGivenName();
        $family_name = $address->getFamilyName();
        $profile = Profile::create([
          'type' => 'customer',
          'uid' => 0,
          'address' => $this->shippingAddress($values, $given_name, $family_name),
          'data' => [
            'copy_to_address_book' => FALSE,
          ],
        ]);
        $profile->save();
        $shipments = $this->order->get('shipments')->referencedEntities();
        $shipments[0]->setShippingProfile($profile);
        $shipments[0]->save();
      }
      $this->order->setData('inpost_point', [
        'name' => $values['d_commerce_inpost_point_name'],
        'address_line1' => $values['d_commerce_inpost_point_address_line1'],
        'address_line2' => $values['d_commerce_inpost_point_address_line2'],
      ]);
    }
    parent::submitPaneForm($pane_form, $form_state, $complete_form);
  }

  /**
   * Get shipping address from InPost point.
   *
   * @param array $values
   *   Values from checkout 'Shipping address' Pane.
   * @param string $given_name
   *   Customer first name from default shipping address.
   * @param string $family_name
   *   Customer last name from default shipping address.
   *
   * @return array
   *   Shipping address for order.
   */
  public function shippingAddress(array $values, $given_name, $family_name) {
    $language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
    return [
      'country_code' => strtoupper($language->getId()),
      'langcode' => $language->getId(),
      'given_name' => $given_name,
      'family_name' => $family_name,
      'organization' => '',
      'address_line1' => $values['d_commerce_inpost_point_address_line1'],
      'address_line2' => '',
      'postal_code' => strlen($values['d_commerce_inpost_point_address_line2']) > 8 ? substr($values['d_commerce_inpost_point_address_line2'], 0, 6) : '',
      'locality' => strlen($values['d_commerce_inpost_point_address_line2']) > 8 ? substr($values['d_commerce_inpost_point_address_line2'], 7) : '',
      'additional_name' => $values['d_commerce_inpost_point_name'],
      'sorting_code' => NULL,
      'dependent_locality' => NULL,
      'administrative_area' => NULL,
    ];
  }

}
