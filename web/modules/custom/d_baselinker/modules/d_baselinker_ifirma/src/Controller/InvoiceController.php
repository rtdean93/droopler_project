<?php

namespace Drupal\d_baselinker_ifirma\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\d_baselinker\BaseLinkerControllerInterface;
use Drupal\d_baselinker\Classes\ErrorHandler;
use Drupal\d_baselinker_ifirma\InvoiceService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InvoiceController.
 *
 * @package Drupal\d_baselinker_ifirma\Controller
 */
class InvoiceController extends ControllerBase implements BaseLinkerControllerInterface {

  /**
   * Array of request parameters.
   *
   * @var array
   */
  protected $params;

  /**
   * Invoice service.
   *
   * @var \Drupal\d_baselinker_ifirma\InvoiceService
   */
  protected $invoiceService;

  /**
   * Module configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * InvoiceController constructor.
   *
   * @param \Drupal\d_baselinker_ifirma\InvoiceService $invoice_service
   *   Invoice service from container.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory service from container.
   */
  public function __construct(InvoiceService $invoice_service, ConfigFactory $config_factory) {
    $this->invoiceService = $invoice_service;
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('d_baselinker.settings');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('d_baselinker_ifirma.invoice_service'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function main(Request $request) {
    $this->getRequestParams($request);
    if (!empty($this->params['action'])) {
      $response = $this->performOperation($this->params['action']);
    }

    return new JsonResponse($response);
  }

  /**
   * {@inheritDoc}
   */
  public function performOperation($action) {
    switch ($action) {
      case 'invoice':
        $response = $this->invoiceService->updateInvoice($this->params);
        break;

      default:
        $error = new ErrorHandler('error', 'Unsuported operation');
        $response = $error->getErrorResponse();
    }

    return $response;
  }

  /**
   * {@inheritDoc}
   */
  public function getRequestParams(Request $request) {
    $queryParams = $request->query->all();
    \Drupal::logger('d_baselinker_ifirma')->notice($request->getQueryString());
    foreach ($queryParams as $key => $param) {
      $this->params[$key] = $param;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function userLogin() {
    $user = User::load($this->config->get('baselinker_user'));
    user_login_finalize($user);
  }

}
