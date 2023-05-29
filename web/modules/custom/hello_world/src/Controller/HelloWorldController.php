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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\RoleStorageInterface;

/**
 * A example of custom controller.
 */
class HelloWorldController extends ControllerBase {


  public function __construct(PermissionHandlerInterface $permission_handler, RoleStorageInterface $role_storage, ConfigFactoryInterface $config_factory) {
    $this->permissionHandler = $permission_handler;
    $this->roleStorage = $role_storage;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.permissions'),
      $container->get('entity_type.manager')->getStorage('user_role'),
      $container->get('config.factory')
    );
  }

  /**
   * Just say a configured hello message.
   */
  public function helloWorld() {
    return [
      "#markup" => \Drupal::service('config.factory')->get('hello_world.settings')->get('hello_message'),
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
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result. @see \Drupal\Core\Access\AccessResultInterface
   */
  public function validateForbiddenMessage(AccountInterface $account, String $message) {
    $forbidden_message = $this->config("hello_world.settings")->get('forbidden_message');
    if (!isset($forbidden_message) || !is_string($forbidden_message) || strlen($forbidden_message) == 0) {
      return AccessResult::allowed();
    }
    foreach(explode("\n", $forbidden_message) as $one_liner_message) {
      if (str_contains($message, $one_liner_message)) {
        return AccessResult::forbidden();
      }
    }
    return AccessResult::allowed();
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
