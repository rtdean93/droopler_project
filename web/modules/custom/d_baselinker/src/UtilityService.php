<?php

namespace Drupal\d_baselinker;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class provides additional methods to handle baselinker requests.
 *
 * @package Drupal\d_baselinker
 */
class UtilityService {

  /**
   * Stores module configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $configuration;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Array of supported baselinker methods.
   *
   * @var array
   */
  protected $supportedMethods = [
    "fileversion",
    "supportedmethods",
    "ordersget",
    "orderupdate",
    "statuseslist",
    "deliverymethodslist",
  ];

  /**
   * UtilityService constructor.
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
    $this->configuration = $this->configFactory->get('d_baselinker.settings');
  }

  /**
   * Returns version of integration file.
   *
   * @return array
   *   Array with version information, as per baselinker integration file.
   */
  public function getVersion() {
    return [
      'platform' => "Drupal Commerce",
      'version' => $this->configuration->get('version'),
      'standard' => $this->configuration->get('standard'),
    ];
  }

  /**
   * Returns all supported methods defined in this object.
   *
   * @return array
   *   Array of methods supported in this module.
   */
  public function getSupportedMethods() {
    return $this->supportedMethods;
  }

  /**
   * Generates crc32 from given string.
   *
   * @param string $string
   *   String to get crc from.
   *
   * @return int
   *   CRC checksum mod 1000000 value, as per baselinker requirements.
   */
  public function getNumericalFromString($string) {
    return sprintf('%u', crc32($string)) % 1000000;
  }

}
