<?php

namespace Drupal\hello_world_override\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hello_world\Event\HelloMessageEvent;

/**
 */
class HelloWorldOverrideSubscriber implements EventSubscriberInterface {

  /**
   * Constructor.
   */
  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[HelloMessageEvent::EVENT][] = ['onHelloMessage', 50];
    return $events;
  }

  /**
   * Handler for the kernel request event.
   *
   * @param \Drupal\hello_world\Event\HelloMessageEvent $event
   *   The response event.
   */
  public function onHelloMessage(HelloMessageEvent $event) {
    $event->setValue($event->getValue() . " override");
  }

}
