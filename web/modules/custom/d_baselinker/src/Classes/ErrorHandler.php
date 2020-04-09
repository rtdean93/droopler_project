<?php

namespace Drupal\d_baselinker\Classes;

/**
 * Class ErrorHandler creates a simple object containing error message.
 *
 * @package Drupal\d_baselinker\Classes
 */
class ErrorHandler {

  /**
   * Contains error code.
   *
   * @var string
   */
  protected $errorCode;

  /**
   * Contains error message.
   *
   * @var string
   */
  protected $errorText;

  /**
   * ErrorHandler constructor.
   *
   * @param string $error_code
   *   Error code.
   * @param string $error_text
   *   Error message.
   */
  public function __construct($error_code, $error_text = '') {
    $this->errorCode = $error_code;
    $this->errorText = $error_text;
  }

  /**
   * Returns array prepared for JSON encoding.
   *
   * @return array
   *   Array containing error message and
   */
  public function getErrorResponse() {
    return [
      'error' => TRUE,
      'error_code' => $this->errorCode,
      'error_text' => $this->errorText,
    ];
  }

}
