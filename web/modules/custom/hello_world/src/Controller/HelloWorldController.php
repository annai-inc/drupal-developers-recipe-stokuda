<?php

namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * A example of custom controller.
 */
class HelloWorldController extends ControllerBase {

  /**
   * Just say "Hello World!".
   */
  public function helloWorld() {
    return [
      "#markup" => "Hello World!",
    ];
  }

  /**
   * Just say something by use param.
   */
  public function saySomething(string $message) {
    return [
      "#markup" => $message,
    ];
  }
}
