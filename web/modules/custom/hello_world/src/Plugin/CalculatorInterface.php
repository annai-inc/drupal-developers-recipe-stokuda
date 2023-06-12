<?php

namespace Drupal\hello_world\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * A interface of the Calculator.
 */
interface CalculatorInterface extends PluginInspectionInterface {

  /**
   * Calculate with some logic.
   *
   * @params int $val
   *
   * @return int
   *   The result of Calculation.
   */
  public function calculate(int $val);

}
