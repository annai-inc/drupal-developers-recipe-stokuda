<?php

namespace Drupal\hello_world\Plugin\Calculator;

use Drupal\hello_world\Plugin\CalculatorBase;
// phpcs:ignore Drupal.Classes.UnusedUseStatement.UnusedUse
use Drupal\hello_world\Annotation\Calculator;

/**
 * A one of the  implementation of Calculator.
 *
 * @Calculator(
 *   id = "twice",
 *   label = @Translation("Twice calculator")
 * )
 */
class TwiceCalculator extends CalculatorBase {

  /**
   * {@inheritDoc}
   */
  public function calculate(int $val) {
    return 2 * $val;
  }

}
