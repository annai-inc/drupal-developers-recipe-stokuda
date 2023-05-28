<?php

namespace Drupal\hello_world\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\hello_world\Plugin\CalculatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class of Calculator.
 */
abstract class CalculatorBase extends PluginBase implements CalculatorInterface, ContainerFactoryPluginInterface {

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
                       $plugin_id,
                       $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

}
