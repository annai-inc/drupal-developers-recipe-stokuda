<?php

namespace Drupal\bonjour_message_override\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hello_world\Event\HelloMessageEvent;

/**
 */
class BonjourMessageOverrideSubscriber implements EventSubscriberInterface {

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[HelloMessageEvent::EVENT][] = ['onHelloMessage', 100];
    return $events;
  }

  /**
   * Handler for the kernel request event.
   *
   * @param \Drupal\hello_world\Event\HelloMessageEvent $event
   *   The response event.
   */
  public function onHelloMessage(HelloMessageEvent $event) {
    $event->setValue("bonjour message");
  }

}
