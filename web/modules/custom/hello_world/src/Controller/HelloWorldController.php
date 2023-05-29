<?php
/**
 * HelloWorldController is fantastic.
 * PHP VERSION >= 8.0.0
 */
namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Node\NodeInterface;
use Drupal\Core\Access\AccessResult;

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
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('devel')) {
      dpm($user);
    }
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

  /**
   * Access check for helloWorld().
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result. @see \Drupal\Core\Access\AccessResultInterface
   */
  public function helloWorldAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'show hello message');
  }

  /**
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result. @see \Drupal\Core\Access\AccessResultInterface
   */
  public function hasAdminAccess(AccountInterface $account, String $message) {
    if (str_contains($message, "a")) {
      return AccessResult::forbidden();
    }
    return AccessResult::allowed();
  }
}
