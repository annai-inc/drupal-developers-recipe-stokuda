<?php

namespace Drupal\hello_world\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * An example of the RouteSubscriber.
 *
 * Bypass access check on the 'help.main' route.
 */
class HelloWorldRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('help.main')) {
      $route->setRequirements(['_access' => 'TRUE']);
    }
  }

}
