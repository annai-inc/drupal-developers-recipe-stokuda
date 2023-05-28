<?php

namespace Drupal\hello_world\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * An example of the EventSubscriber.
 *
 * Subscribes to the Kernel Request event and redirects to the google
 * when the user has the "annoymouse" role.
 */
class HelloWorldRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];
    return $events;
  }

  /**
   * Handler for the kernel request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The response event.
   */
  public function onRequest(RequestEvent $event) {
    $routeName = \Drupal::routeMatch()->getRouteName();
    if ($routeName !== 'search.view_node_search') {
      return;
    }
    if ($this->currentUser->isAnonymous()) {
      /** @var \Drupal\Core\Routing\TrustedRedirectResponse $response */
      $response = new TrustedRedirectResponse('https://google.com');
      $event->setResponse($response);
    }
  }

}
