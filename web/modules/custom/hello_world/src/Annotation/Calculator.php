<?php

namespace Drupal\hello_world\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Calculator annotation object.
 *
 * @see \Drupal\hello_world\Plugin\CalculatorPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class Calculator extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * Radix.
   *
   * @var int
   */
  public $radix;
}
