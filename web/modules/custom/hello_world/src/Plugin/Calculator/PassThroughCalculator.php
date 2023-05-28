<?php

namespace Drupal\hello_world\Plugin\Calculator;

use Drupal\hello_world\Plugin\CalculatorBase;
// phpcs:ignore Drupal.Classes.UnusedUseStatement.UnusedUse
use Drupal\hello_world\Annotation\Calculator;

/**
 * A one of the  implementation of Calculator.
 *
 * @Calculator(
 *   id = "pass_through",
 *   label = @Translation("Pass through calculator")
 * )
 */
class PassThroughCalculator extends CalculatorBase {

  /**
   * {@inheritDoc}
   */
  public function calculate(int $val) {
    return $val;
  }

}
