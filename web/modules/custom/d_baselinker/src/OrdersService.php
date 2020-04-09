<?php

namespace Drupal\d_baselinker;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\d_baselinker\Classes\ErrorHandler;
use Drupal\d_baselinker\Classes\OrderDTO;

/**
 * Service for interaction with order entities.
 *
 * @package Drupal\d_baselinker
 */
class OrdersService {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Baselinker utility service.
   *
   * @var \Drupal\d_baselinker\UtilityService
   */
  protected $utilityService;

  /**
   * OrdersService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory service.
   */
  public function __construct(EntityTypeManager $entity_type_manager, ConfigFactory $config_factory, UtilityService $utility_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->utilityService = $utility_service;
  }

  /**
   * Returns array of orders specified by request parameters.
   *
   * @param array $params
   *   Array of parameters from request.
   *
   * @return array
   *   Array of orders.
   */
  public function ordersGet(array $params) {
    try {
      $query = $this->entityTypeManager->getStorage('commerce_order')
        ->getQuery();
      $query->condition('state', 'completed', 'LIKE');
      if (!empty($params['time_from'])) {
        $query->condition('placed', $params['time_from'], '>=');
      }
      if (!empty($params['id_from'])) {
        $query->condition('order_id', $params['id_from'], '>=');
      }
      $orderIds = $query->execute();
      $ordersArray = [];
      foreach ($orderIds as $orderId) {
        $order = new OrderDTO($orderId);
        $ordersArray[$orderId] = $order->getObjectArray();

      }

      return $ordersArray;
    }
    catch (InvalidPluginDefinitionException $e) {
      \Drupal::logger('d_baselinker')->error($e->getMessage());
      $error = new ErrorHandler('database_connect', 'Error while retrieving orders');
      return $error->getErrorResponse();
    }
    catch (PluginNotFoundException $e) {
      \Drupal::logger('d_baselinker')->error($e->getMessage());
      $error = new ErrorHandler('database_connect', 'Error while retrieving orders');
      return $error->getErrorResponse();
    }
  }

  /**
   * Returns array of available order statuses.
   *
   * @return array
   *   Array of order statuses 'crc' => 'value'.
   */
  public function getStatuses() {
    $response = [];
    // Base linker supports only one type of order.
    $order_types = OrderType::load('default');
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    $workflow = $workflow_manager->createInstance($order_types->getWorkflowId());
    $states = $workflow->getStates();
    foreach ($states as $state) {
      $response[$this->utilityService->getNumericalFromString($state->getId())] = $state->getId();
    }

    return $response;
  }

  /**
   * Returns array of available order statuses.
   *
   * @return array
   *   Array of order statuses 'crc' => 'value'.
   */
  public function getDeliveries() {
    $response = [];
    $shipmentTypes = ShippingMethod::loadMultiple();
    foreach ($shipmentTypes as $shipmentType) {
      $response[$shipmentType->id()] = $shipmentType->getName();
    }

    return $response;
  }

  /**
   * Runs order update function based on input.
   *
   * @param array $params
   *   Request parameters.
   *
   * @return array
   *   Array response for encoding.
   */
  public function ordersUpdate(array $params) {
    switch ($params['update_type']) {
      case 'status':
        $response = $this->updateOrdersStatus($params['orders'], $params['update_value']);
        break;

      case 'delivery_number':
        $response = $this->updateOrderDelivery($params['orders'], $params['update_value']);
        break;

      default:
        $error = new ErrorHandler("unsupported_action", "No action: " . $params['update_type']);
        $response = $error->getErrorResponse();
    }
    return $response;
  }

  /**
   * Updates orders delivery tracking numbers.
   *
   * @param array $ordersIds
   *   Orders to update.
   * @param string $update_value
   *   Value to update to.
   *
   * @return array
   *   Array with counters key, containing number of updated records.
   */
  private function updateOrderDelivery(array $ordersIds, $update_value) {
    $orders = Order::loadMultiple($ordersIds);
    $counter = 0;
    foreach ($orders as $order) {
      $shipment = Shipment::load($order->get('shipments')
        ->getValue()[0]['target_id']);
      $shipment->set('tracking_code', $update_value);
      try {
        $shipment->save();
        $counter += 1;
      }
      catch (EntityStorageException $e) {
        $this->orderUpdateErrorHandler($order->id(), $e);
      }
    }
    return ['counters' => $counter];
  }

  /**
   * Updates orders statuses, based on baselinker request.
   *
   * @param array $ordersIds
   *   Orders to update.
   * @param string $update_value
   *   Value to update to.
   *
   * @return array
   *   Array with counters key, containing number of updated records.
   */
  private function updateOrdersStatus(array $ordersIds, $update_value) {
    $newStatus = $this->getStatuses()[$update_value];
    $counter = 0;
    if (!empty($newStatus)) {
      $orders = Order::loadMultiple($ordersIds);
      foreach ($orders as $order) {
        $order->set('state', $newStatus);
        try {
          $order->save();
          $counter += 1;
        }
        catch (EntityStorageException $e) {
          $this->orderUpdateErrorHandler($order->id(), $e);
        }
      }
    }
    else {
      \Drupal::logger('d_baselinker')
        ->error("Status {$update_value} doesn't exist.");
    }

    return ['counters' => $counter];
  }

  /**
   * Method for logging errors during update of orders.
   *
   * @param int $orderId
   *   Id of updated order.
   * @param \Exception $exception
   *   Thrown exception.
   */
  private function orderUpdateErrorHandler($orderId, \Exception $exception) {
    \Drupal::logger('d_baselinker')
      ->error("Error while updating order with id: {$orderId}. Error message: {$exception->getMessage()}");
  }

}
