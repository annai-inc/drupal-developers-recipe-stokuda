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
use Drupal\Core\Database\Connection;

/**
 * A example of custom controller.
 */
class HelloWorldController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

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
                              CalculatorPluginManager $plugin_manager,
                              Connection $database) {
    $this->messenger = $messenger;
    $this->configFactory = $config_factory;
    $this->pluginManager = $plugin_manager;
    $this->database = $database;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hello_world.messenger'),
      $container->get('config.factory'),
      $container->get('plugin.manager.calculator'),
      $container->get('database'),
    );
  }

  /**
   * Show contents with Database API.
   *
   * @see https://www.drupal.org/docs/8/api/database-api/static-queries
   */
  public function showContent() {
    $query = <<<EOS
SELECT
node_field_data.nid AS node_field_data_nid,
node_field_data.title AS node_field_data_title,
node_field_data.type AS node_field_data_type,
node_field_data.status AS node_field_data_status,
node_field_data.changed AS node_field_data_changed,
users_field_data_node_field_data.uid AS users_field_data_node_field_data_uid
FROM {node_field_data} node_field_data
INNER JOIN {users_field_data} users_field_data_node_field_data
ON node_field_data.uid = users_field_data_node_field_data.uid
ORDER BY node_field_data.changed DESC
LIMIT 50 OFFSET 0
EOS;

    $records = $this->database->query($query)->fetchAll();

    $header = [
      $this->t('title'),
      $this->t('content type'),
      $this->t('author'),
      $this->t('published'),
      $this->t('updated'),
    ];

    $rows = [];
    foreach ($records as $record) {
      /** @var \Drupal\node\Entity\NodeType $node_type */
      $node_type = \Drupal::service('entity_type.manager')->getStorage('node_type')->load($record->node_field_data_type);
      /** @var \Drupal\user\Entity\User $account */
      $account = \Drupal\user\Entity\User::load($record->users_field_data_node_field_data_uid);
      /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
      $date_formatter = \Drupal::service('date.formatter');

      $rows[] = [
        $record->node_field_data_title,
        $node_type->get('name'),
        $account->getDisplayName(),
        $record->node_field_data_status == 1 ? $this->t('published') : $this->t('unpublished'),
        $date_formatter->format($record->node_field_data_changed, 'short'),
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
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
    $calculator = $this->pluginManager->createInstance($this->getCalculatorPluginIdByRadix($val%3));

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
  private function getCalculatorPluginIdByRadix($radix) {
    $definitions = $this->pluginManager->getDefinitions();
    foreach ($definitions as $definition) {
      if ($definition['radix'] == $radix) {
        return $definition['id'];
      }
    }
    $top = array_slice($definitions, 0, 1);
    return $top[key($top)]['id'];
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
    \Drupal::service('logger.factory')->get('hello_world')->info('hello is hello ');
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
