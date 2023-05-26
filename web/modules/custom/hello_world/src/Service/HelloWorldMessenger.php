<?php

namespace Drupal\hello_world\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\hello_world\EchoMessageServiceInterface;
use Drupal\node\NodeInterface;

/**
 * A service that echo messages.
 */
class HelloWorldMessenger implements EchoMessageServiceInterface {

  use StringTranslationTrait;

  /**
   * Just say configured hello message.
   *
   * @inheritDoc
   */
  public function helloWorld() {
    return \Drupal::service('config.factory')->get('hello_world.settings')->get('hello_message');
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
    if (\Drupal::moduleHandler()->moduleExists("devel")) {
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
