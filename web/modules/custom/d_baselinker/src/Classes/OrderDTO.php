<?php

namespace Drupal\d_baselinker\Classes;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\Entity\ShippingMethod;

class OrderDTO {

  /**
   * OrderDTO constructor.
   */
  public function __construct($orderId) {
    $utilities = \Drupal::service('d_baselinker.utility_service');
    $this->products = [];
    $order = Order::load($orderId);
    $shipment = Shipment::load($order->get('shipments')
      ->getValue()[0]['target_id']);
    $shipping = $shipment->getShippingProfile();
    $shippingAddress = $shipping->get('address')->get(0);
    $billing = $order->getBillingProfile();
    $billingAddress = $billing->get('address')->get(0);
    $orderedItems = $order->getItems();
    $paymentMethod = $order->get('payment_gateway')->getValue()[0]['target_id'];

    $this->delivery_fullname = "{$shippingAddress->getGivenName()} {$shippingAddress->getFamilyName()}";
    $this->delivery_company = $shippingAddress->getOrganization();
    $this->delivery_address = "{$shippingAddress->getAddressLine1()} {$shippingAddress->getAddressLine2()}";
    $this->delivery_city = $shippingAddress->getLocality();
    $this->delivery_postcode = $shippingAddress->getPostalCode();
    $this->delivery_country = $shippingAddress->getCountryCode();
    $this->invoice_fullname = "{$billingAddress->getGivenName()} {$billingAddress->getFamilyName()}";
    $this->invoice_company = $billingAddress->getOrganization();
    $this->invoice_address = "{$billingAddress->getAddressLine1()} {$billingAddress->getAddressLine2()}";
    $this->invoice_city = $billingAddress->getLocality();
    $this->invoice_postcode = $billingAddress->getPostalCode();
    $this->invoice_country = $billingAddress->getCountryCode();
    $this->invoice_nip = $billing->get('field_n')->value;
    $this->phone = $billing->get('field_numer_telefonu')->value;
    $this->email = $billing->get('field_email')->value;
    $this->date_add = $order->getCreatedTime();
    $this->payment_method = $this->getPaymentMethodID($paymentMethod);
    $this->user_comments = '';
    $this->status_id = $utilities->getNumericalFromString($order->getState()
      ->getId());
    $this->delivery_method_id = $shipment->getShippingMethodId();
    $this->delivery_price = $shipment->getAmount()->getNumber();
    $this->getOrderedProductsList($orderedItems);

  }

  /**
   * Creates associative array of products associated with order.
   *
   * @param array $orderedItems
   *   Array of IDs of ordered items.
   */
  private function getOrderedProductsList(array $orderedItems) {
    foreach ($orderedItems as $orderedItem) {
      /** @var \Drupal\commerce_product\Entity\ProductVariation $product */
      $productVariant = $orderedItem->getPurchasedEntity();
      $product = $productVariant->getProduct();
      $weight = $productVariant->get('weight')->getValue();
      $this->products[] = [
        'id' => $product->id(),
        'variant_id' => $productVariant->id(),
        'name' => $product->getTitle(),
        'quantity' => $orderedItem->getQuantity(),
        'tax' => $productVariant->get('field_vat')->value,
        'weight' => reset($weight)['number'],
        'price' => $productVariant->getPrice()->getNumber(),
      ];
    }
  }

  /**
   * Returns object in form of associative array.
   *
   * @return array
   *   Object values.
   */
  public function getObjectArray() {
    return get_object_vars($this);
  }

  /**
   * Returns payment ID as per baselinker docs.
   *
   * @param string $paymentMethod
   *   Payment method specified in commerce.
   *
   * @return string
   *   Id for baselinker.
   */
  private function getPaymentMethodId($paymentMethod) {
    switch ($paymentMethod) {
      case 'dotpay':
        $paymentBaselinkerId = 'DOT';
        break;

      default:
        $paymentBaselinkerId = 'POB';
        break;
    }
    return $paymentBaselinkerId;
  }

}
