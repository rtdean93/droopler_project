<?php

namespace Drupal\d_baselinker;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides interface for baselinker submodule controllers.
 *
 * @package Drupal\d_baselinker
 */
interface BaseLinkerControllerInterface {

  /**
   * Returns data from services according to send operation.
   *
   * @param string $action
   *   Action to perform.
   *
   * @return array
   *   Array containing data for JSON encoding.
   */
  public function performOperation($action);

  /**
   * Processes parameters from request object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request sent to the controller.
   */
  public function getRequestParams(Request $request);

  /**
   * Logs in user specified in config in order to access data.
   */
  public function userLogin();

  /**
   * Main controller method. Returns JSON response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request passed into controller.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Controller response in JSON format.
   */
  public function main(Request $request);

}
