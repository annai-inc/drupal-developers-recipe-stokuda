<?php

namespace Drupal\hello_world;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * A service interface the echo messages.
 */
interface EchoMessageServiceInterface {

  /**
   * Just say some message.
   *
   * @return string
   *   The hello message.
   */
  public function helloWorld();

  /**
   * Just say something genarated from arguments.
   *
   * @return string
   *   the message genarated from arguments.
   */
  public function saySomething(string $message);

  /**
   * Inspect user information.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   An object that, when cast to a string, returns the translated string.
   */
  public function inspectUser(AccountInterface $user);

  /**
   * Inspect node information.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   An object that, when cast to a string, returns the translated string.
   */
  public function inspectNode(NodeInterface $node);

}
