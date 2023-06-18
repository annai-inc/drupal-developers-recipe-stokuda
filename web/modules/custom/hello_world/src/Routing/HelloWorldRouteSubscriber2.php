<?php

namespace Drupal\hello_world\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 */
class HelloWorldRouteSubscriber2 extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($tipsRoute = $collection->get('filter.tips_all')) {
      $helloRoute = $collection->get('hello_world.hello');
      $tipsRoute->setDefaults($helloRoute->getDefaults());
    }
  }

}
