<?php

namespace Drupal\d_baselinker\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\d_baselinker\BaseLinkerControllerInterface;
use Drupal\d_baselinker\Classes\ErrorHandler;
use Drupal\d_baselinker\OrdersService;
use Drupal\d_baselinker\UtilityService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Main controller for baselinker request processing.
 *
 * @package Drupal\d_baselinker\Controller
 */
class BaseLinkerController extends ControllerBase implements BaseLinkerControllerInterface {

  /**
   * Communication password, from baselinker API.
   *
   * @var string
   */
  private $password;

  /**
   * Instance of orders service.
   *
   * @var \Drupal\d_baselinker\OrdersService
   */
  protected $ordersService;

  /**
   * Instance of utility service.
   *
   * @var \Drupal\d_baselinker\UtilityService
   */
  protected $utilityService;

  /**
   * Instance of drupal config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Array of request parameters.
   *
   * @var array
   */
  protected $params;

  /**
   * Module configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * BaseLinkerController constructor.
   *
   * @param \Drupal\d_baselinker\OrdersService $orders_service
   *   Order service instance from container.
   * @param \Drupal\d_baselinker\UtilityService $utility_service
   *   Order service instance from container.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Order service instance from container.
   */
  public function __construct(OrdersService $orders_service, UtilityService $utility_service, ConfigFactory $config_factory) {
    $this->ordersService = $orders_service;
    $this->utilityService = $utility_service;
    $this->configFactory = $config_factory;
    $this->config = $config_factory->get('d_baselinker.settings');
    $this->password = $this->config->get('communication_password');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('d_baselinker.orders_service'),
      $container->get('d_baselinker.utility_service'),
      $container->get('config.factory')
    );
  }

  /**
   * Main controller method. Returns JSON response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request passed into controller.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Controller response in JSON format.
   */
  public function main(Request $request) {
    $this->getRequestParams($request);
    if (!isset($this->params['bl_pass'])) {
      $error = new ErrorHandler('no_password', 'Odwołanie do pliku bez podania hasła. Jest to poprawny komunikat jeśli plik integracyjny został otworzony w przeglądarce internetowej.');
      return new JsonResponse($error->getErrorResponse());
    }
    if ($this->checkPassword($this->params['bl_pass'])) {
      $this->userLogin();
      return new JsonResponse($this->performOperation($this->params['action']));
    }
    else {
      $error = new ErrorHandler("incorrect_password");
      return new JsonResponse($error->getErrorResponse());
    }
  }

  /**
   * Returns if provided password is identical with the one in configuration.
   *
   * @param string $password
   *   Password sent in the request.
   *
   * @return bool
   *   Returns true if password is identical with configuration.
   */
  private function checkPassword($password) {
    return $password === $this->password;
  }

  /**
   * Processes parameters from request object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request sent to the controller.
   */
  public function getRequestParams(Request $request) {
    $requestParams = $request->request;
    $params = [];
    foreach ($requestParams as $key => $requestParam) {
      $params[$key] = $requestParam;
    }
    \Drupal::logger('d_baselinker')->notice(json_encode($params));
    if (isset($params['orders_ids'])) {
      $params['orders'] = explode(',', $params['orders_ids']);
      unset($params['orders_ids']);
    }
    if (isset($params['products_id'])) {
      $params['products'] = explode(',', $params['products_id']);
      unset($params['products_id']);
    }
    if (isset($params['fields'])) {
      $params['fields'] = explode(',', $params['fields']);
    }
    if (isset($params['products'])) {
      $params['products'] = json_decode(stripslashes($params['products']), TRUE);
    }
    $this->params = $params;
  }

  /**
   * Logs in user specified in config in order to access data.
   */
  public function userLogin() {
    $user = User::load($this->config->get('baselinker_user'));
    user_login_finalize($user);
  }

  /**
   * {@inheritDoc}
   */
  public function performOperation($action) {
    switch ($action) {
      case 'OrdersGet':
        $response = $this->ordersService->ordersGet($this->params);
        break;

      case 'OrderUpdate':
        $response = $this->ordersService->ordersUpdate($this->params);
        break;

      case 'FileVersion':
        $response = $this->utilityService->getVersion();
        break;

      case 'SupportedMethods':
        $response = $this->utilityService->getSupportedMethods();
        break;

      case 'StatusesList':
        $response = $this->ordersService->getStatuses();
        break;

      case 'DeliveryMethodsList':
        $response = $this->ordersService->getDeliveries();
        break;

      default:
        $error = new ErrorHandler("unsupported_action", "No action: {$this->params['action']}");
        $response = $error->getErrorResponse();
    }

    return $response;
  }

}
