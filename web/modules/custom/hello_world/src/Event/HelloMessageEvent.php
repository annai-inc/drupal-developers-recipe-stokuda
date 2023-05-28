<?php

namespace Drupal\hello_world\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * An example of Event implementation.
 */
class HelloMessageEvent extends GenericEvent {
  const EVENT = 'hello_world.hello_message';

  /**
   * Hello message.
   *
   * @var string
   */
  protected $message;

  /**
   * Set the hello message.
   *
   * @param string $message
   *   Hello message.
   */
  public function setValue($message) {
    $this->message = $message;
  }

  /**
   * Get the hello message.
   *
   * @return string
   *   Hello message.
   */
  public function getValue() {
    return $this->message;
  }

}
