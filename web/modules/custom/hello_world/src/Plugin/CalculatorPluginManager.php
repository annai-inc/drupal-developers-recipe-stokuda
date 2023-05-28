<?php

namespace Drupal\hello_world\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * The calculator manager.
 */
class CalculatorPluginManager extends DefaultPluginManager {

  /**
   * Constructs a CalculatorPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Calculator',
      $namespaces,
      $module_handler,
      'Drupal\hello_world\Plugin\CalculatorInterface',
      'Drupal\hello_world\Annotation\Calculator'
    );
    $this->alterInfo('hello_world_calculator_info');
    $this->setCacheBackend($cache_backend, 'hello_world_calculator_info_plugins');
  }


}
