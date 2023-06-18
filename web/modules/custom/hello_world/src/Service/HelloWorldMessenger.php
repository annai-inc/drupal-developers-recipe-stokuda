<?php

namespace Drupal\hello_world\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\hello_world\EchoMessageServiceInterface;
use Drupal\node\NodeInterface;
use Drupal\core\config\ConfigFactoryInterface;
use Drupal\core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\hello_world\Event\HelloMessageEvent;

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
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * A construtor of HelloWorldController.
   *
   * @param \Drupal\core\config\ConfigFactoryInterface $configFactory
   *   The messenger service.
   * @param \Drupal\core\Extension\ModuleHandlerInterface $moduleHandler
   *   The messenger service.
   */
  public function __construct(ConfigFactoryInterface $configFactory,
                              ModuleHandlerInterface $moduleHandler,
                              EventDispatcherInterface $eventDispatcher) {
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('event_dispatcher'),
    );
  }
  /**
   * Just say configured hello message.
   *
   * @inheritDoc
   */
  public function helloWorld() {
    /** @var string $default_message */
    $default_message = $this->configFactory->get('hello_world.settings')->get('hello_message');

    /** @var \Drupal\hello_world\Event\HelloMessageEvent $event */
    $event = new HelloMessageEvent();
    $event->setValue($default_message);
    $this->eventDispatcher->dispatch($event, HelloMessageEvent::EVENT);
    return $event->getValue();
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
