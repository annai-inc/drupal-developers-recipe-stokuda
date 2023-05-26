<?php
/**
 * HelloWorldController is fantastic.
 * PHP VERSION >= 8.0.0
 */
namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Node\NodeInterface;

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

  /**
   * Inspect user information.
   */
  public function inspectUser(AccountInterface $user = NULL) {
    dpm($user);

    $content = "User id: " . $user->id() . ", username: " . $user->getAccountName();

    return [
      "#markup" => $content,
    ];
  }

  /**
   * Inspect node information.
   */
  public function inspectNode(NodeInterface  $node = NULL) {
    $content = "node id: " . $node->id() . ", title: " . $node->getTitle();

    return [
      "#markup" => $content,
    ];
  }
}
