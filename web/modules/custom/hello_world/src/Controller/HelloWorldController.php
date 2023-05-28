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
use Drupal\hello_world\EchoMessageServiceInterface;
use Drupal\hello_world\Plugin\CalculatorPluginManager;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * A example of custom controller.
 */
class HelloWorldController extends ControllerBase {

  /**
   * The plugin manager of Caluclator.
   *
   * @var \Drupal\hello_world\Plugin\CalculatorPluginManager
   */
  protected $pluginManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\hello_world\EchoMessageServiceInterface
   */
  protected $messenger;

  /**
   * A construtor of HelloWorldController.
   *
   * @param \Drupal\hello_world\EchoMessageServiceInterface $messenger
   *   The messenger service.
   */
  public function __construct(EchoMessageServiceInterface $messenger,
                              ConfigFactoryInterface $config_factory,
                              CalculatorPluginManager $plugin_manager) {
    $this->messenger = $messenger;
    $this->configFactory = $config_factory;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hello_world.messenger'),
      $container->get('config.factory'),
      $container->get('plugin.manager.calculator'),
    );
  }

  /**
   * Calculate value by Calculator Plugin at random.
   *
   * @params int $val
   *   Input value of calculation.
   *
   * @return array
   *   Rendered array.
   */
  public function calculate(int $val) {
    /** @var \Drupal\hello_world\Plugin\CalculatorInterface @calculator */
    $calculator = $this->pluginManager->createInstance($this->getCalculatorPluginId());

    $result = [];
    $result['plugin id'] = $calculator->getPluginId();
    $result['result'] = $calculator->calculate($val);

    return [
      "#markup" => json_encode($result, JSON_PRETTY_PRINT),
    ];
  }

  /**
   * Get a plugin id of Calculator at random.
   *
   * @return string
   *   Plugin id of Caluclator
   */
  private function getCalculatorPluginId() {
    $seed = random_int(0, 2);

    /** @var string $plugin_id */
    $plugin_id = 'pass_through';

    switch ($seed) {
      case 0:
        $plugin_id = 'twice';
        break;

      case 1:
        $plugin_id = 'square';
        break;

      default:
        $plugin_id = 'pass_through';
        break;
    }

    return $plugin_id;
  }

  /**
   * Just say a configured hello message.
   */
  public function helloWorld() {
    return [
      "#markup" => $this->messenger->helloWorld(),
    ];
  }

  /**
   * Just say something by use param.
   */
  public function saySomething(string $message) {
    return [
      "#markup" => $this->messenger->saySomething($message),
    ];
  }

  /**
   * Inspect user information.
   */
  public function inspectUser(AccountInterface $user = NULL) {
    return [
      "#markup" => $this->messenger->inspectUser($user),
    ];
  }

  /**
   * Inspect node information.
   */
  public function inspectNode(NodeInterface $node) {
    return [
      "#markup" => $this->messenger->inspectNode($node),
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
