<?php

namespace Drupal\hello_world\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\hello_world\EchoMessageServiceInterface;
use Drupal\node\NodeInterface;
use Drupal\core\config\ConfigFactoryInterface;
use Drupal\core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * A service that echo messages.
 */
class HelloWorldMessenger implements EchoMessageServiceInterface {

  use StringTranslationTrait;

  /**
   * The messenger service.
   */
  protected $configFactory;

  /**
   * The messenger service.
   */
  protected $moduleHandler;

  /**
   * A construtor of HelloWorldController.
   *
   * @param \Drupal\core\config\ConfigFactoryInterface $configFactory
   *   The messenger service.
   * @param \Drupal\core\Extension\ModuleHandlerInterface $moduleHandler
   *   The messenger service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ModuleHandlerInterface $moduleHandler) {
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
    );
  }
  /**
   * Just say configured hello message.
   *
   * @inheritDoc
   */
  public function helloWorld() {
    return $this->configFactory->get('hello_world.settings')->get('hello_message');
  }

  /**
   * Just echo back message by from argument.
   *
   * @inheritDoc
   */
  public function saySomething(string $message) {
    return $message;
  }

  /**
   * Inspect user information.
   *
   * @inheritDoc
   */
  public function inspectUser(AccountInterface $user) {
    if ($this->moduleHandler->moduleExists("devel")) {
      dpm($user);
    }

    return $this->t(
      "User id: %user_id, username: %user_name",
      ["%user_id" => $user->id(), '%user_name' => $user->getAccountName()]
    );
  }

  /**
   * Inspect node information.
   *
   * @inheritDoc
   */
  public function inspectNode(NodeInterface $node) {
    return $this->t(
      "Node id: %node_id, title: %title",
      ["%node_id" => $node->id(), '%title' => $node->getTitle()]
    );
  }

}
